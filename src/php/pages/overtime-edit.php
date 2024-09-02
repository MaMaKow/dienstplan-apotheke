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
/*
 * TODO: Edit option for existing entries
 */
$year = user_input::get_variable_from_any_input('year', FILTER_SANITIZE_NUMBER_INT, date('Y'));
$dateStartObject = new DateTime("$year-01-01");
$dateEndObject = new DateTime("$year-12-31");
$workforce = new workforce($dateStartObject->format("Y-m-d"), $dateEndObject->format("Y-m-d"));
\PDR\Utility\GeneralUtility::createCookie('year', $year, 1);
$employee_key = user_input::get_variable_from_any_input('employee_key', FILTER_SANITIZE_NUMBER_INT, $workforce->get_default_employee_key());
\PDR\Utility\GeneralUtility::createCookie('employee_key', $employee_key, 1);

overtime::handle_user_input($session, $employee_key);
if (isset($_POST) && !empty($_POST)) {
    // POST data has been submitted
    $location = PDR_HTTP_SERVER_APPLICATION_PATH . 'src/php/pages/overtime-edit.php' . "?year=$year&employee_key=$employee_key";
    header('Location:' . $location);
    die("<p>Redirect to: <a href=$location>$location</a></p>");
}
list($balance, $date_old) = overtime::get_current_balance($employee_key);
/*
 * Get the overtime data for the chosen year:
 */
$sql_query = "SELECT * FROM `Stunden` WHERE `employee_key` = :employee_key and Year(`Datum`) = :year ORDER BY `Datum` DESC";
$result = database_wrapper::instance()->run($sql_query, array('employee_key' => $employee_key, 'year' => $year));
$tablebody = "<tbody>\n";
$i = 1;
while ($row = $result->fetch(PDO::FETCH_OBJ)) {
    $tablebody .= "<tr>\n";
    $tablebody .= "<td>\n";
    $tablebody .= "<form accept-charset='utf-8' onsubmit='return confirmDelete()' method=POST id=delete_" . htmlspecialchars($row->Datum) . ">\n";
    $tablebody .= "" . date('d.m.Y', strtotime($row->Datum));
    $tablebody .= " <input class=no-print type=submit name=deleteRow value='X' title='Diesen Datensatz löschen'>\n";
    $tablebody .= " <input type=hidden name=deletionEmployeeKey value='" . htmlspecialchars($employee_key) . "'>\n";
    $tablebody .= " <input type=hidden name=deletionDate value='" . htmlspecialchars($row->Datum) . "'>\n";
    $tablebody .= "</form>\n";
    $tablebody .= "\n</td>\n";
    $tablebody .= "<td>\n";
    $tablebody .= htmlspecialchars($row->Stunden);
    $tablebody .= "\n</td>\n";
    $tablebody .= "<td>\n";
    $tablebody .= htmlspecialchars($row->Saldo);
    $tablebody .= "\n</td>\n";
    $tablebody .= "<td>\n";
    $tablebody .= htmlspecialchars($row->Grund);
    $tablebody .= "\n</td>\n";
    $tablebody .= "\n</tr>\n";
    $i++;
}
$tablebody .= "</tbody>\n";

//Start of output:
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
$session->exit_on_missing_privilege('create_overtime');

echo "<div id=mainArea>\n";
$user_dialog = new user_dialog();
echo $user_dialog->build_messages();

echo form_element_builder::build_html_select_year($year);
echo build_html_navigation_elements::build_select_employee($employee_key, $workforce->List_of_employees);
echo build_html_navigation_elements::build_button_open_readonly_version('src/php/pages/overtime-read.php', array('employee_key' => $employee_key));

echo "<table>\n";
//Heading
echo "<thead>\n";
echo "<tr>\n"
 . "<th>\n"
 . "Datum\n"
 . "</th>\n"
 . "<th>\n"
 . "Stunden\n"
 . "</th>\n"
 . "<th>\n"
 . "Saldo\n"
 . "</th>\n"
 . "<th>\n"
 . "Grund\n"
 . "</th>\n"
 . "</tr>\n"
 . "</thead>\n";

/*
 * Input fields.
 * The balance will be visibly calculated by JavaScript.
 * But the calculated value is not used as an input.
 */
echo "<tr class='no-print'>\n";
echo "<td>\n";
echo "<input type=date id='date_chooser_input' class='datepicker' value=" . date('Y-m-d') . " name=datum form=insert_new_overtime  autofocus>\n";
echo "</td>\n";
echo "<td>\n";
echo "<input type=text onchange=update_overtime_balance() id=stunden name=stunden form=insert_new_overtime>\n";
echo "</td>\n";
echo "<td>\n";
echo "<p><span id=balance_new>" . htmlspecialchars($balance) . " </span><span id='balance_old' data-balance='" . htmlspecialchars($balance) . "'>&nbsp;</span></p>\n";
echo "</td>\n";
echo "<td>\n";
echo "<input type=text id=grund name=grund form=insert_new_overtime>\n";
echo "</td>\n";
echo "<td>";
echo "<input class=no-print type=submit name=submitStunden value='Eintragen' form=insert_new_overtime></td>\n";
echo "</tr>\n";
//Ausgabe
echo "$tablebody";
echo "</table>\n";
echo "</div>\n";
echo "<form accept-charset='utf-8' method=POST id=insert_new_overtime onsubmit='return overtime_input_validation();'>\n"
 . "<input hidden name=employee_key value=" . htmlspecialchars($employee_key) . " form=insert_new_overtime>\n"
 . "<input hidden id='user_sequence_warning' name=user_has_been_warned_about_date_sequence value='0' form=insert_new_overtime>\n"
 . "<input hidden id='date_of_last_entry' name='date_of_last_entry' value='$date_old' form=insert_new_overtime>\n"
 . "</form>\n";
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php';
?>
</body>
</html>
