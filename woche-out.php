<?php
require_once "default.php";
require_once PDR_FILE_SYSTEM_APPLICATION_PATH . "db-lesen-abwesenheit.php";

$tage = 7; //One week

$branch_id = user_input::get_variable_from_any_input('mandant', FILTER_SANITIZE_NUMBER_INT, min($List_of_branch_objects));
$mandant = $branch_id;
create_cookie('mandant', $branch_id, 30);

$error_message_html = "";
$overlay_message_html = "";
$Fehlermeldung = array();
$Warnmeldung = array();

$date_sql_user_input = user_input::get_variable_from_any_input('datum', FILTER_SANITIZE_NUMBER_INT, date('Y-m-d'));
$date_sql = general_calculations::get_first_day_of_week($date_sql_user_input);
$date_unix = strtotime($date_sql);
$datum = $date_sql;
$date_sql_start = $date_sql;
$date_sql_end = date('Y-m-d', strtotime('+ ' . ($tage - 1) . ' days', $date_unix));
create_cookie('datum', $date_sql, 1);

for ($i = 0; $i < $tage; $i++) {
    $Week_dates_unix[] = strtotime(' +' . $i . ' days', $date_unix);
}

//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/read_roster_array_from_db.php';
$Dienstplan = read_roster_array_from_db($date_sql, $tage, $branch_id); //Die Funktion ruft die Daten nur für den angegebenen Mandanten und für den angegebenen Zeitraum ab.
$Roster = roster::read_roster_from_database($branch_id, $date_sql_start, $date_sql_end);
foreach (array_keys($List_of_branch_objects) as $other_branch_id) {
    /*
     * The $Branch_roster contanins all the rosters from all branches, including the current branch.
     */
    $Branch_roster[$other_branch_id] = roster::read_branch_roster_from_database($branch_id, $other_branch_id, $date_sql_start, $date_sql_end);
}
$VKcount = count($List_of_employees); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
$VKmax = max(array_keys($List_of_employees)); //Wir suchen nach der höchsten VK-Nummer VKmax. Diese wird für den <option>-Bereich benötigt.
//Build a div containing assignment of tasks:
require 'task-rotation.php';
//TODO: Works only for "Rezeptur" right now!
$task = "Rezeptur";
$weekly_rotation_div_html = task_rotation_main($Week_dates_unix, $task);

//Produziere die Ausgabe
require 'head.php';
require 'navigation.php';
require 'src/php/pages/menu.php';
$main_div_html = "\t\t<div id='main-area'>\n";
$date_info_line_html = "\t\t\t<div id=date_info_line class='no-print'>" . gettext("calendar week") . strftime(' %V', $date_unix) . "</div>\n";
$main_div_html .= $date_info_line_html;

//Support for various branch clients.
$main_div_html .= build_select_branch($branch_id, $date_sql);

$duty_roster_form_html = "\t\t<form id=duty_roster_form method=post>\n";
$buttons_div_html = "";
$buttons_div_html .= "<div id=buttons_div class=no-print>";
$buttons_div_html .= $backward_button_week_img;
$buttons_div_html .= $forward_button_week_img;
$buttons_div_html .= "<br><br>";
$buttons_div_html .= "\t\t\t\t\t<input name=date_sql type=date id=date_chooser_input class='datepicker' value=" . date('Y-m-d', $date_unix) . ">\n";
$buttons_div_html .= "\t\t\t\t\t<input type=submit name=tagesAuswahl value=Anzeigen>\n";
$buttons_div_html .= "<br><br>";
$buttons_div_html .= "\t\t\t\t<a href='woche-in.php?datum=" . $date_sql . "' class=no-print>[" . gettext("Edit") . "]</a>\n";
$buttons_div_html .= "<br><br></div>";
$duty_roster_form_html .= $buttons_div_html;

$table_html = "\t\t\t\t<table id=duty-rooster-table>\n";
$head_table_html = "";
$head_table_html .= "\t\t\t\t\t<thead>\n";
$head_table_html .= "\t\t\t\t\t<tr>\n";
for ($i = 0; $i < count($Dienstplan); $i++) {//Datum
    $date_sql = $Dienstplan[$i]['Datum'][0];
    $date_unix = strtotime($date_sql);
    $head_table_html .= "\t\t\t\t\t\t<td>";
    $head_table_html .= "<a href='tag-out.php?datum=" . $Dienstplan[$i]["Datum"][0] . "'>";
    $head_table_html .= strftime('%A', strtotime($Dienstplan[$i]["Datum"][0]));
    $head_table_html .= " \n";
    $head_table_html .= "<input type=hidden size=2 name=Dienstplan[" . $i . "][Datum][0] value=" . $Dienstplan[$i]["Datum"][0] . ">";
    $head_table_html .= "<input type=hidden name=mandant value=" . htmlentities($branch_id) . ">";
    $head_table_html .= strftime('%d.%m.', strtotime($Dienstplan[$i]["Datum"][0]));
    $holiday = holidays::is_holiday($date_unix);
    if (FALSE !== $holiday) {
        $head_table_html .= " <br>" . $holiday . " ";
    }
    if (FALSE !== $holiday AND date('N', $date_unix) < 6) {
        foreach ($Mandanten_mitarbeiter as $employee_id => $nachname) {
            if (!isset($bereinigte_Wochenstunden_Mitarbeiter[$employee_id])) {
                $bereinigte_Wochenstunden_Mitarbeiter[$employee_id] = $List_of_employee_working_week_hours[$employee_id] - $List_of_employee_working_week_hours[$employee_id] / 5;
            } else {
                $bereinigte_Wochenstunden_Mitarbeiter[$employee_id] = $bereinigte_Wochenstunden_Mitarbeiter[$employee_id] - $List_of_employee_working_week_hours[$employee_id] / 5;
            }
        }
    }

    if (FALSE !== pharmacy_emergency_service::having_emergency_service($date_sql)) {
        $head_table_html .= "<br> <em>NOTDIENST</em> ";
    }
    $head_table_html .= "</a></td>\n";
}
$head_table_html .= "\t\t\t\t\t</tr></thead>";
$table_html .= $head_table_html;

$table_body_html = "<tbody>";
$table_body_html .= build_html_roster_views::build_roster_readonly_table($Roster, $branch_id);
if (isset($Overlay_message)) {
    $overlay_message_html .= "\t\t<div class='overlay no-print'>\n";
    $Overlay_message = array_unique($Overlay_message);
    foreach ($Overlay_message as $message) {
        $overlay_message_html .= "\t\t\t<H1>" . $message . "</H1>\n";
    }
    $overlay_message_html .= "\t\t</div>\n";
}
$table_html .= $table_body_html;
$table_html .= build_html_roster_views::build_roster_readonly_branch_table_rows($Branch_roster, $branch_id, $date_sql_start, $date_sql_end);

$table_html .= "\t\t\t\t\t</tbody>\n";
//echo "\t\t\t\t</div>\n";
$table_foot_html = "\t\t\t\t\t<tfoot>"
        //. "<tr class=page-break></tr>"
        . "\n\t\t\t\t\t\t<tr>\n";

//Wir werfen einen Blick in den Urlaubsplan und schauen, ob alle da sind.
for ($i = 0; $i < count($Dienstplan); $i++) {
    $datum = ($Dienstplan[$i]['Datum'][0]);
    $date_unix = strtotime($datum);
    $Abwesende = db_lesen_abwesenheit($datum);
    if (isset($Abwesende)) {
        foreach ($Abwesende as $employee_id => $reason) {
            if (FALSE !== holidays::is_holiday($date_unix) AND date('N', $date_unix) < 6) {
                //An Feiertagen whaben wir die Stunden bereits abgezogen. Keine weiteren Abwesenheitsgründe notwendig.
                if (!isset($bereinigte_Wochenstunden_Mitarbeiter[$employee_id])) {
                    $bereinigte_Wochenstunden_Mitarbeiter[$employee_id] = $List_of_employee_working_week_hours[$employee_id] - $List_of_employee_working_week_hours[$employee_id] / 5;
                } else {
                    $bereinigte_Wochenstunden_Mitarbeiter[$employee_id] = $bereinigte_Wochenstunden_Mitarbeiter[$employee_id] - $List_of_employee_working_week_hours[$employee_id] / 5;
                }
            }
        }
    }

    //Jetzt notieren wir die Urlauber und die Kranken Mitarbeiter unten in der Tabelle.

    if (isset($Abwesende)) {
        $table_foot_html .= build_html_roster_views::build_absentees_column($Abwesende);
    } else {
        $table_foot_html .= "</td><td>";
    }
}
$table_foot_html .= "\t\t\t\t\t</tr>\n";
$table_foot_html .= "\t\t\t\t\t</tfoot>\n";

$table_html .= $table_foot_html;
$table_html .= "\t\t\t\t</table>\n\t\t\t"
        . "$weekly_rotation_div_html"
        . "</div>";

$table_div_html = "<div id=table_overlay_area>";
$table_div_html .= $overlay_message_html;
$table_div_html .= $table_html;

$duty_roster_form_html .= $table_div_html;

$Working_hours_week = roster::calculate_working_hours_weekly_from_branch_roster($Branch_roster);
//An leeren Wochen soll nicht gerechnet werden.
if (array() !== $Roster and isset($Working_hours_week)) {
    $week_hours_table_html = "<div id=week_hours_table_div>\n";
    $week_hours_table_html .= "<H2>Wochenstunden</H2>\n";
    $week_hours_table_html .= "<p>\n";
    print_debug_variable($Working_hours_week);
    foreach ($Working_hours_week as $mitarbeiter => $stunden) {
        if (FALSE === array_key_exists($mitarbeiter, $Mandanten_mitarbeiter)) {
            continue; /* Only employees who belong to the branch are shown. */
        }
        $week_hours_table_html .= "<span>" . $List_of_employees[$mitarbeiter] . " " . round($stunden, 2);
        $week_hours_table_html .= " / ";
        if (isset($bereinigte_Wochenstunden_Mitarbeiter[$mitarbeiter])) {
            $week_hours_table_html .= round($bereinigte_Wochenstunden_Mitarbeiter[$mitarbeiter], 1) . "\n";
            if (round($bereinigte_Wochenstunden_Mitarbeiter[$mitarbeiter], 1) != round($stunden, 1)) {
                $differenz = round($stunden, 2) - round($bereinigte_Wochenstunden_Mitarbeiter[$mitarbeiter], 2);
                $week_hours_table_html .= " <b>( " . $differenz . " )</b>\n";
            }
        } else {
            $week_hours_table_html .= round($List_of_employee_working_week_hours[$mitarbeiter], 1) . "\n";
            if (round($List_of_employee_working_week_hours[$mitarbeiter], 1) != round($stunden, 1)) {
                $differenz = round($stunden, 2) - round($List_of_employee_working_week_hours[$mitarbeiter], 2);
                $week_hours_table_html .= " <b>( " . $differenz . " )</b>\n";
            }
        }

        $week_hours_table_html .= "</span>\n";
    }
    $week_hours_table_html .= "</p>\n";
    $duty_roster_form_html .= $week_hours_table_html;
}
// echo $submit_button;
$duty_roster_form_html .= "</form>\n";
$main_div_html .= $duty_roster_form_html;

$main_div_html .= "</div>\n";

$warning_message_html = build_warning_messages($Fehlermeldung, $Warnmeldung);

echo $warning_message_html;

echo $main_div_html;

require 'contact-form.php';
?>
</BODY>
</HTML>
