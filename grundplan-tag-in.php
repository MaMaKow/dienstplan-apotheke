<?php

require 'default.php';
#Diese Seite wird den kompletten Grundplan eines einzelnen Wochentages anzeigen.

$employee_id = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_employee_id']);
$branch_id = user_input::get_variable_from_any_input('mandant', FILTER_SANITIZE_NUMBER_INT, min($List_of_branch_objects));
$mandant = $branch_id;
create_cookie('mandant', $branch_id, 30);

if (filter_has_var(INPUT_POST, 'submit_roster')) {
    //TODO: Test if this works:
    user_input::principle_roster_write_user_input_to_database();
}

$weekday = user_input::get_variable_from_any_input('weekday', FILTER_SANITIZE_NUMBER_INT, 1);
create_cookie('weekday', $weekday, 1);


//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';

$sql_query = 'SELECT *
FROM `Grundplan`
WHERE `Wochentag` = "' . $weekday . '"
	AND `Mandant`="' . $branch_id . '"
	ORDER BY `Dienstbeginn` + `Dienstende`, `Dienstbeginn`
;';
$result = mysqli_query_verbose($sql_query);
unset($Grundplan);
while ($row = mysqli_fetch_object($result)) {
    $Grundplan[$weekday]['Wochentag'][] = $row->Wochentag;
    $Grundplan[$weekday]['VK'][] = $row->VK;
    $Grundplan[$weekday]['Dienstbeginn'][] = $row->Dienstbeginn;
    $Grundplan[$weekday]['Dienstende'][] = $row->Dienstende;
    $Grundplan[$weekday]['Mittagsbeginn'][] = $row->Mittagsbeginn;
    $Grundplan[$weekday]['Mittagsende'][] = $row->Mittagsende;

    if (!empty($row->Mittagsbeginn) and ! empty($row->Mittagsende) and $row->Mittagsbeginn > 0 and $row->Mittagsende > 0) {
        $sekunden = strtotime($row->Dienstende) - strtotime($row->Dienstbeginn);
        $mittagspause = strtotime($row->Mittagsende) - strtotime($row->Mittagsbeginn);
        $sekunden = $sekunden - $mittagspause;
        $stunden = round($sekunden / 3600, 1);
    } else {
        $sekunden = strtotime($row->Dienstende) - strtotime($row->Dienstbeginn);
        //Wer länger als 6 Stunden Arbeitszeit hat, bekommt eine Mittagspause.
        if ($sekunden - $List_of_employee_lunch_break_minutes[$row->VK] * 60 >= 6 * 3600) {
            $mittagspause = $List_of_employee_lunch_break_minutes[$row->VK] * 60;
            $sekunden = $sekunden - $mittagspause;
        } else {
            $mittagspause = false;
        }
        $stunden = round($sekunden / 3600, 1);
    }
    $Grundplan[$weekday]['Stunden'][] = $stunden;
    $Grundplan[$weekday]['Pause'][] = $mittagspause / 60;
    $Grundplan[$weekday]['Kommentar'][] = $row->Kommentar;
    $Grundplan[$weekday]['Mandant'][] = $row->Mandant;

    if ($List_of_employee_professions[$row->VK] == "Apotheker") {
        $worker_style = 1;
    } elseif ($List_of_employee_professions[$row->VK] == "PI") {
        $worker_style = 1;
    } elseif ($List_of_employee_professions[$row->VK] == "PTA") {
        $worker_style = 2;
    } elseif ($List_of_employee_professions[$row->VK] == "PKA") {
        $worker_style = 3;
    } else {
        //anybody else
        $worker_style = 3;
    }
}
//Wir füllen komplett leere Tage mit Werten, damit trotzdem eine Anzeige entsteht.
if (!isset($Grundplan[$weekday])) {
    $Grundplan[$weekday]['Wochentag'][] = $weekday;
    $Grundplan[$weekday]['VK'][] = '';
    $Grundplan[$weekday]['Dienstbeginn'][] = '-';
    $Grundplan[$weekday]['Dienstende'][] = '-';
    $Grundplan[$weekday]['Mittagsbeginn'][] = '-';
    $Grundplan[$weekday]['Mittagsende'][] = '-';
    $Grundplan[$weekday]['Stunden'][] = '-';
    $Grundplan[$weekday]['Kommentar'][] = '-';
}

/*
 * Wir zeichnen eine Kurve der Anzahl der Mitarbeiter.
 * Dazu übersetzen wir unsere Variablen in die korrekten Namen für das übliche Histrogramm
 */
$Dienstplan[0] = $Grundplan[$weekday]; //We will use $Dienstplan[0] for functions that are written for the use with single days as a workaround.
$tag = $weekday;
//We construct a pseudo date for the chosen weekday.
$pseudo_date = time() + ($weekday - date('w')) * PDR_ONE_DAY_IN_SECONDS;
$pseudo_date_sql = date('Y-m-d', $pseudo_date);
$Principle_roster = roster::read_principle_roster_from_database($branch_id, $pseudo_date_sql);


$VKcount = count($List_of_employees); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
$VKmax = max(array_keys($List_of_employees));

//Produziere die Ausgabe
require 'head.php';
require 'navigation.php';
require 'src/php/pages/menu.php';
if (!$session->user_has_privilege('create_roster')) {
    echo build_warning_messages("", ["Die notwendige Berechtigung zum Erstellen von Dienstplänen fehlt. Bitte wenden Sie sich an einen Administrator."]);
    //die("Die notwendige Berechtigung zum Erstellen von Dienstplänen fehlt. Bitte wenden Sie sich an einen Administrator.");
    die();
}

//Hier beginnt die Normale Ausgabe.
echo "<H1>Grundplan Tagesansicht</H1>\n";
echo "<div id=main-area>\n";
echo build_html_navigation_elements::build_select_branch($branch_id, $pseudo_date_sql);
//Auswahl des Wochentages
echo build_html_navigation_elements::build_select_weekday($weekday);

echo "<div id=navigation_elements>";
echo build_html_navigation_elements::build_button_submit('principle_roster_form');
echo "</div>\n";
$html_text = '';
$html_text .= "<form id=principle_roster_form method=post>\n";
$html_text .= "<table>\n";
$max_employee_count = roster::calculate_max_employee_count($Principle_roster);
print_debug_variable($Principle_roster);
for ($table_input_row_iterator = 0; $table_input_row_iterator < $max_employee_count; $table_input_row_iterator++) {
    $html_text .= "<tr>\n";
    foreach (array_keys($Principle_roster) as $day_iterator) {
        $html_text .= build_html_roster_views::build_roster_input_row($Principle_roster, $day_iterator, $table_input_row_iterator, $max_employee_count, $pseudo_date, $branch_id);
    }
    $html_text .= "</tr>\n";
}

$html_text .= "</table>\n";
$html_text .= "</form>\n";
echo $html_text;
if (!empty($Principle_roster)) {
    //TODO: This does not work yet. PLease check Dienstplan equals Grundplan?
    echo "<div class=above-image>\n";
    echo "<div class=image>\n";
    echo roster_image_bar_plot::draw_image_dienstplan($Principle_roster);
    echo "<br>\n";
    $Changing_times = roster::calculate_changing_times($Principle_roster);
    $Attendees = roster_headcount::headcount_roster($Principle_roster, $Changing_times);
    echo roster_image_histogramm::draw_image_histogramm($Principle_roster, $branch_id, $Attendees, $pseudo_date);
    echo "</div>\n";
    echo "</div>\n";
}
echo '</div>';

require 'contact-form.php';

echo "</body>\n";
echo '</html>';
