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
$number_of_days = 7;

$employee_id = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_employee_id']);
create_cookie('employee_id', $employee_id, 30);
$pseudo_date_unix = time() - (date('w') - 1) * PDR_ONE_DAY_IN_SECONDS;
$pseudo_date_sql_start = date('Y-m-d', $pseudo_date_unix);
$pseudo_date_sql_end = date('Y-m-d', $pseudo_date_unix + ($number_of_days - 1) * PDR_ONE_DAY_IN_SECONDS);

$workforce = new workforce($pseudo_date_sql_start);
$branch_id = $workforce->List_of_employees[$employee_id]->principle_branch_id;

$Principle_employee_roster = roster::read_principle_employee_roster_from_database($employee_id, $pseudo_date_sql_start, $pseudo_date_sql_end);
$Principle_roster = roster::read_principle_roster_from_database($branch_id, $pseudo_date_sql_start, $pseudo_date_sql_end);
roster::transfer_lunch_breaks($Principle_employee_roster, $Principle_roster);

//Produziere die Ausgabe
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
?>
<a name=top></a>
<?php
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
$session->exit_on_missing_privilege('create_roster');
echo "<div id=main-area>\n";
//TODO: find out how to respect the lunch breaks!
echo build_html_navigation_elements::build_select_employee($employee_id, $workforce->List_of_employees);

echo "<form method='POST' id='change_principle_roster_employee'>";
echo build_html_navigation_elements::build_button_submit('change_principle_roster_employee');
echo "</form>";

$html_text = '';
$html_text .= "<table>\n";
$html_text .= "<thead>\n";
$html_text .= "<tr>\n";
$Weekday_names = build_html_navigation_elements::get_weekday_names();
foreach ($Weekday_names as $weekday_name) {
    //Wochentag
    $html_text .= "<td width=10%>";
    $html_text .= $weekday_name;
    $html_text .= "</td>\n";
}
$max_employee_count = roster::calculate_max_employee_count($Principle_employee_roster);
for ($table_input_row_iterator = 0; $table_input_row_iterator < $max_employee_count; $table_input_row_iterator++) {
    $html_text .= "<tr>\n";
    foreach (array_keys($Principle_employee_roster) as $day_iterator) {
        $html_text .= build_html_roster_views::build_roster_input_row($Principle_employee_roster, $day_iterator, $table_input_row_iterator, $max_employee_count, $pseudo_date_unix, $branch_id);
    }
    $html_text .= "</tr>\n";
}
echo $html_text;
echo "</tr>\n";
/*
 * TODO: Write JavaScript Code to allow adding more rows to the form
  echo "<tr>";
  foreach (array_keys($Grundplan) as $wochentag) {
  //TODO: Write Javascript for adding an entry:
  echo "<td id='add_entry_$wochentag'><p><a href='#' onclick='alert(\"Sorry, this feature is not yet implemented.\");add_entry_to_change_principle_roster_employee_form()'>" . gettext("Add row") . "</a></p></td>";
  }
  echo "</tr>";
 *
 */
echo "</tbody>\n";
echo "<tfoot>\n";
echo "<tr>\n";
echo "<td colspan=$number_of_days>\n";

//Das folgende wird wohl durch ${spalte} mit $spalte=Stunden ausgelöst, wenn $_POST ausgelesen wird. Dadurch wird $Stunden zum String.
unset($Stunden); //Aber ohne dieses Löschen versagt die folgende Schleife. Sie wird als String betrachtet.
foreach ($Principle_employee_roster as $Principle_employee_roster_day_array) {
    foreach ($Principle_employee_roster_day_array as $roster_object) {
        $Stunden[$employee_id][] = $roster_object->working_hours;
    }
}
echo "Wochenstunden ";
ksort($Stunden);
foreach ($Stunden as $mitarbeiter => $stunden) {
    echo array_sum($stunden);
    echo ' / ';
    echo $workforce->List_of_employees[$mitarbeiter]->working_week_hours;
    if ($workforce->List_of_employees[$mitarbeiter]->working_week_hours != array_sum($stunden)) {
        $differenz = array_sum($stunden) - $workforce->List_of_employees[$mitarbeiter]->working_week_hours;
        echo " <b>( " . $differenz . " )</b>";
    }
}
echo "</td>\n";
echo "</tr>\n";
echo "</tfoot>\n";
echo "</table>\n";
echo "</div>\n";

$roster_image_bar_plot = new roster_image_bar_plot($Principle_employee_roster);
echo $roster_image_bar_plot->svg_string;


require PDR_FILE_SYSTEM_APPLICATION_PATH . 'contact-form.php';
?>
</body>
</html>
