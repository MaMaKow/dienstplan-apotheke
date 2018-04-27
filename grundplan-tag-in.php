<?php

require 'default.php';
#Diese Seite wird den kompletten Grundplan eines einzelnen Wochentages anzeigen.

$employee_id = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_employee_id']);
$branch_id = user_input::get_variable_from_any_input('mandant', FILTER_SANITIZE_NUMBER_INT, min(array_keys($List_of_branch_objects)));
create_cookie('mandant', $branch_id, 30);
/*
 * weekday
 */
$weekday = user_input::get_variable_from_any_input('weekday', FILTER_SANITIZE_NUMBER_INT, 1);
create_cookie('weekday', $weekday, 1);
$pseudo_date_unix = time() + ($weekday - date('w')) * PDR_ONE_DAY_IN_SECONDS;
$pseudo_date_sql = date('Y-m-d', $pseudo_date_unix);

if (filter_has_var(INPUT_POST, 'submit_roster')) {
    //TODO: Test if this works:
    user_input::principle_roster_write_user_input_to_database($branch_id);
}

//Hole eine Liste aller Mitarbeiter
//We construct a pseudo date for the chosen weekday.

$workforce = new workforce($pseudo_date_sql);

$Principle_roster = roster::read_principle_roster_from_database($branch_id, $pseudo_date_sql);


$VKcount = count($workforce->List_of_employees); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
$VKmax = max(array_keys($workforce->List_of_employees));

//Produziere die Ausgabe
require 'head.php';
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
for ($table_input_row_iterator = 0; $table_input_row_iterator < $max_employee_count; $table_input_row_iterator++) {
    $html_text .= "<tr>\n";
    foreach (array_keys($Principle_roster) as $day_iterator) {
        $html_text .= build_html_roster_views::build_roster_input_row($Principle_roster, $day_iterator, $table_input_row_iterator, $max_employee_count, $pseudo_date_unix, $branch_id);
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
    $roster_image_bar_plot = new roster_image_bar_plot($Principle_roster);
    echo $roster_image_bar_plot->svg_string;
    echo "<br>\n";
    $Changing_times = roster::calculate_changing_times($Principle_roster);
    $Attendees = roster_headcount::headcount_roster($Principle_roster, $Changing_times);
    echo roster_image_histogramm::draw_image_histogramm($Principle_roster, $branch_id, $Attendees, $pseudo_date_unix);
    echo "</div>\n";
    echo "</div>\n";
}
echo '</div>';

require 'contact-form.php';

echo "</body>\n";
echo '</html>';
