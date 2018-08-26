<?php

/*
 * Copyright (C) 2017 Mandelkow
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require '../../../default.php';
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
$workforce = new workforce($pseudo_date_sql);

if (filter_has_var(INPUT_POST, 'submit_roster')) {
    user_input::principle_roster_write_user_input_to_database($branch_id);
}

$Principle_roster = roster::read_principle_roster_from_database($branch_id, $pseudo_date_sql);


$VKcount = count($workforce->List_of_employees); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
$VKmax = max(array_keys($workforce->List_of_employees));

//Produziere die Ausgabe
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
$session->exit_on_missing_privilege('create_roster');

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
$html_text .= "<form accept-charset='utf-8' id=principle_roster_form method=post>\n";
$html_text .= "<table>\n";
$max_employee_count = roster::calculate_max_employee_count($Principle_roster);
$day_iterator = $pseudo_date_unix; //Just in case the loop does not define it for build_html_roster_views::build_roster_input_row_add_row
for ($table_input_row_iterator = 0; $table_input_row_iterator < $max_employee_count; $table_input_row_iterator++) {
    $html_text .= "<tr>\n";
    foreach (array_keys($Principle_roster) as $day_iterator) {
        $html_text .= build_html_roster_views::build_roster_input_row($Principle_roster, $day_iterator, $table_input_row_iterator, $max_employee_count, $branch_id, array('add_select_employee'));
    }
    $html_text .= "</tr>\n";
}
$html_text .= build_html_roster_views::build_roster_input_row_add_row($day_iterator, $table_input_row_iterator, $max_employee_count, $branch_id);

$html_text .= "</table>\n";
$html_text .= "</form>\n";
echo $html_text;
if (!empty($Principle_roster)) {
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

require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php';

echo "</body>\n";
echo '</html>';
