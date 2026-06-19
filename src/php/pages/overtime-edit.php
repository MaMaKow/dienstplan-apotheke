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

use PDR\Roster\Overtime;

require '../../../default.php';
/*
 * TODO: Edit option for existing entries
 */
$year = user_input::get_variable_from_any_input('year', FILTER_SANITIZE_NUMBER_INT, date('Y'));
$dateStartObject = new DateTime("$year-01-01");
$dateEndObject = new DateTime("$year-12-31");
$workforce = new PDR\Workforce\Workforce($dateStartObject->format("Y-m-d"), $dateEndObject->format("Y-m-d"));
\PDR\Utility\GeneralUtility::createCookie('year', $year, 1);
$employeeKey = user_input::get_variable_from_any_input('employee_key', FILTER_SANITIZE_NUMBER_INT, $workforce->getDefaultEmployeeKey());
\PDR\Utility\GeneralUtility::createCookie('employee_key', $employeeKey, 1);

\PDR\Input\OvertimeInputHandler::handleUserInput($session, $employeeKey);
$userDialog = new user_dialog();
if (isset($_POST) && !empty($_POST)) {
    $userDialog->storeMessagesInSession();
    // POST data has been submitted
    $location = PDR_HTTP_SERVER_APPLICATION_PATH . 'src/php/pages/overtime-edit.php' . "?year=$year&employee_key=$employeeKey";
    header('Location:' . $location);
    die("<p>Redirect to: <a href=$location>$location</a></p>");
}
$userDialog->readMessagesFromSession();
$currentOvertime = \PDR\Database\OvertimeDatabaseHandler::getCurrentOvertime($employeeKey);
$OvertimeCollection = \PDR\Database\OvertimeDatabaseHandler::getEmployeeOvertimes($employeeKey);
$balance = $currentOvertime->getBalance();
$date_old = $currentOvertime->getDate()->format("Y-m-d");
/*
 * Get the overtime data for the chosen year:
 */
$tablebody = "<tbody>\n";
$i = 1;
foreach ($OvertimeCollection as $overtime) {
    $tablebody .= "<tr>\n";
    $tablebody .= \PDR\Output\HTML\OvertimeHtmlBuilder::buildFormOvertimeEdit($overtime);
    $tablebody .= "\n</tr>\n";
    $i++;
}
$tablebody .= "</tbody>\n";



//Start of output:
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';

echo "<div id=mainArea>\n";
$user_dialog = new user_dialog();
echo $user_dialog->build_messages();

echo form_element_builder::build_html_select_year($year);
echo build_html_navigation_elements::build_select_employee($employeeKey, $workforce->getListOfEmployees());
echo build_html_navigation_elements::build_button_open_readonly_version('src/php/pages/overtime-read.php', array('employee_key' => $employeeKey));

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
echo "<input type=number step='0.25' onchange=update_overtime_balance() id=stunden name=stunden form=insert_new_overtime>\n";
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
    . "<input hidden name=employee_key value=" . htmlspecialchars($employeeKey) . " form=insert_new_overtime>\n"
    . "<input hidden id='user_sequence_warning' name=user_has_been_warned_about_date_sequence value='0' form=insert_new_overtime>\n"
    . "<input hidden id='date_of_last_entry' name='date_of_last_entry' value='$date_old' form=insert_new_overtime>\n"
    . "</form>\n";
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php';
?>
</body>

</html>