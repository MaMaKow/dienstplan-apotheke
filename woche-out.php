<?php
require_once "default.php";

$tage = 7; //One week

$branch_id = user_input::get_variable_from_any_input('mandant', FILTER_SANITIZE_NUMBER_INT, min(array_keys($List_of_branch_objects)));
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
$workforce = new workforce($date_sql);
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/read_roster_array_from_db.php';
$Dienstplan = read_roster_array_from_db($date_sql, $tage, $branch_id); //Die Funktion ruft die Daten nur für den angegebenen Mandanten und für den angegebenen Zeitraum ab.
$Roster = roster::read_roster_from_database($branch_id, $date_sql_start, $date_sql_end);
foreach (array_keys($List_of_branch_objects) as $other_branch_id) {
    /*
     * The $Branch_roster contanins all the rosters from all branches, including the current branch.
     */
    $Branch_roster[$other_branch_id] = roster::read_branch_roster_from_database($branch_id, $other_branch_id, $date_sql_start, $date_sql_end);
}
$VKcount = count($workforce->List_of_employees); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
$VKmax = max(array_keys($workforce->List_of_employees)); //Wir suchen nach der höchsten VK-Nummer VKmax. Diese wird für den <option>-Bereich benötigt.
//Build a div containing assignment of tasks:
require 'task-rotation.php';
//TODO: Works only for "Rezeptur" right now!
$weekly_rotation_div_html = task_rotation_main(array_keys($Roster), "Rezeptur", $branch_id);

//Produziere die Ausgabe
require 'head.php';
require 'src/php/pages/menu.php';
$main_div_html = "<div id='main-area'>\n";
$date_info_line_html = "<div id=date_info_line class='no-print'>" . gettext("calendar week") . strftime(' %V', $date_unix) . "</div>\n";
$main_div_html .= $date_info_line_html;

//Support for various branch clients.
$main_div_html .= build_html_navigation_elements::build_select_branch($branch_id, $date_sql);

$duty_roster_form_html = "";
$buttons_div_html = "";
$buttons_div_html .= "<div id=buttons_div class=no-print>";
$buttons_div_html .= build_html_navigation_elements::build_button_week_backward($date_sql);
$buttons_div_html .= build_html_navigation_elements::build_button_week_forward($date_sql);
$buttons_div_html .= build_html_navigation_elements::build_input_date($date_sql);
$buttons_div_html .= "</div>";
$duty_roster_form_html .= $buttons_div_html;

$table_html = "<table id=duty-rooster-table>\n";
$table_html .= build_html_roster_views::build_roster_read_only_table_head($Roster);

$table_body_html = build_html_roster_views::build_roster_readonly_table($Roster, $branch_id);
if (isset($Overlay_message)) {
    $overlay_message_html .= "<div class='overlay no-print'>\n";
    $Overlay_message = array_unique($Overlay_message);
    foreach ($Overlay_message as $message) {
        $overlay_message_html .= "<H1>" . $message . "</H1>\n";
    }
    $overlay_message_html .= "</div>\n";
}
$table_html .= $table_body_html;
$table_html .= build_html_roster_views::build_roster_readonly_branch_table_rows($Branch_roster, $branch_id, $date_sql_start, $date_sql_end);

$table_html .= "</tbody>\n";
//echo "</div>\n";
$table_foot_html = "<tfoot>"
        //. "<tr class=page-break></tr>"
        . "\n<tr>\n";

//Wir werfen einen Blick in den Urlaubsplan und schauen, ob alle da sind.
foreach (array_keys($Roster) as $date_unix) {
    $date_sql = date('Y-m-d', $date_unix);
    $Abwesende = absence::read_absentees_from_database($date_sql);

    //Jetzt notieren wir die Urlauber und die Kranken Mitarbeiter unten in der Tabelle.
    if (isset($Abwesende)) {
        $table_foot_html .= build_html_roster_views::build_absentees_column($Abwesende);
    } else {
        $table_foot_html .= "</td><td>";
    }
}
$table_foot_html .= "</tr>\n";
$table_foot_html .= "</tfoot>\n";

$table_html .= $table_foot_html;
$table_html .= "</table>\n"
        . "$weekly_rotation_div_html"
        . "</div>";

$table_div_html = "<div id=table_overlay_area>";
$table_div_html .= $overlay_message_html;
$table_div_html .= $table_html;

$duty_roster_form_html .= $table_div_html;

$Working_hours_week_have = roster::calculate_working_hours_weekly_from_branch_roster($Branch_roster);
//An leeren Wochen soll nicht gerechnet werden.
if (array() !== $Roster and isset($Working_hours_week_have)) {
    $Working_hours_week_should = build_html_roster_views::calculate_working_hours_week_should($Roster);
    $duty_roster_form_html .= build_html_roster_views::build_roster_working_hours_div($Working_hours_week_have, $Working_hours_week_should);
}
// echo $submit_button;
$main_div_html .= $duty_roster_form_html;

$main_div_html .= "</div>\n";

$warning_message_html = build_warning_messages($Fehlermeldung, $Warnmeldung);

echo $warning_message_html;

echo $main_div_html;

require 'contact-form.php';
?>
</BODY>
</HTML>
