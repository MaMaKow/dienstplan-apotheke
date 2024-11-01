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
$employeeKey = user_input::get_variable_from_any_input('employee_key', FILTER_SANITIZE_NUMBER_INT, $workforce->get_default_employee_key());
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
$balance = $currentOvertime->getBalance();
$date_old = $currentOvertime->getDate()->format("Y-m-d");
/*
 * Get the overtime data for the chosen year:
 */
$sql_query = "SELECT * FROM `Stunden` WHERE `employee_key` = :employee_key and Year(`Datum`) = :year ORDER BY `Datum` DESC";
$result = database_wrapper::instance()->run($sql_query, array('employee_key' => $employeeKey, 'year' => $year));
$tablebody = "<tbody>\n";
$i = 1;
while ($row = $result->fetch(PDO::FETCH_OBJ)) {
    $tablebody .= "<tr>\n";
    $tablebody .= buildFormOvertimeEdit($row);
    $tablebody .= "\n</tr>\n";
    $i++;
}
$tablebody .= "</tbody>\n";

/**
 *
 * @param stdClass $rowObject
 * @return string
 * @todo Write class OvertimeHtmlBuilder with these functions.
 */
function buildFormOvertimeDelete(stdClass $rowObject) {
    $deleteFormId = "deleteForm_" . htmlspecialchars($rowObject->Datum);
    $deleteFormString = "";
    $deleteButtonText = "<button id=deleteButton_$rowObject->Datum type=submit form='$deleteFormId' name=deleteRow class='button-small delete_button no-print' title='Diese Zeile löschen' name=command value=delete>\n"
            . "<img src='" . \PDR_HTTP_SERVER_APPLICATION_PATH . "img/md_delete_forever.svg' alt='Diese Zeile löschen'>\n"
            . "</button>\n";

    $deleteFormString .= $deleteButtonText;
    $deleteFormString .= " <input type=hidden name=deletionEmployeeKey value='" . htmlspecialchars($rowObject->employee_key) . "' form='$deleteFormId'>\n";
    $deleteFormString .= " <input type=hidden name=deletionDate value='" . htmlspecialchars($rowObject->Datum) . "' form='$deleteFormId'>\n";
    $deleteFormString .= " <input type=hidden name=deletionHours value='" . htmlspecialchars($rowObject->Stunden) . "' form='$deleteFormId'>\n";
    $deleteFormString .= "<form accept-charset='utf-8' onsubmit='return confirmDelete()' method=POST id='$deleteFormId'>\n";
    $deleteFormString .= "</form>\n";
    return $deleteFormString;
}

function buildFormOvertimeEdit(stdClass $rowObject) {
    $formId = "editForm_" . htmlspecialchars($rowObject->Datum);
    $formString = "";
    $formString .= "<td>" . PHP_EOL;
    $formString .= " <input form=$formId type=hidden name=editEmployeeKey value='" . htmlspecialchars($rowObject->employee_key) . "'>" . PHP_EOL;
    $formString .= " <input readOnly form=$formId type=date name=editDateNew value='" . htmlspecialchars($rowObject->Datum) . "'>" . PHP_EOL;
    $formString .= " <input form=$formId type=hidden name=editDateOld value='" . htmlspecialchars($rowObject->Datum) . "'>" . PHP_EOL;
    $formString .= "</td><td>" . PHP_EOL;
    $formString .= " <input readOnly form=$formId type=number name=editHoursNew value='" . htmlspecialchars($rowObject->Stunden) . "' step='0.25'>" . PHP_EOL;
    $formString .= " <input form=$formId type=hidden name=editHoursOld value='" . htmlspecialchars($rowObject->Stunden) . "'>" . PHP_EOL;
    $formString .= "</td><td>" . PHP_EOL;
    $formString .= htmlspecialchars($rowObject->Saldo) . PHP_EOL;
    $formString .= "</td><td>" . PHP_EOL;
    $formString .= " <input readOnly form=$formId type=string name=editReasonNew value='" . htmlspecialchars($rowObject->Grund) . "'>" . PHP_EOL;
    $formString .= " <input form=$formId type=hidden name=editReasonOld value='" . htmlspecialchars($rowObject->Grund) . "'>" . PHP_EOL;
    $formString .= "</td><td>" . PHP_EOL;
    $formString .= " <button id=editButton_$rowObject->Datum class='no-print button-small' title='Diese Zeile bearbeiten' onClick='overtime_edit_existing_entries(\"$formId\");'>"
            . '<img src="/apotheke/dienstplan-test/img/md_edit.svg" alt="Diese Zeile bearbeiten">'
            . '</button>' . PHP_EOL;
    $formString .= buildFormOvertimeDelete($rowObject);
    $formString .= buildButtonSubmitSave($rowObject);
    $formString .= buildButtonCancelEdit($rowObject);
    $formString .= "</td>" . PHP_EOL;
    $formString .= "<form accept-charset='utf-8' method=POST id=$formId></form>" . PHP_EOL;
    $formString .= "";
    return $formString;
}

/**
 * Builds an HTML button for canceling the editing of a specific row.
 *
 * @param \stdClass $rowObject The object representing the row for which the cancel edit button is generated.
 *
 * @return string The HTML code for the cancel edit button.
 */
function buildButtonCancelEdit(\stdClass $rowObject): string {
    $formId = "editForm_" . htmlspecialchars($rowObject->Datum);
    $buttonText = "<button id='cancel_$rowObject->Datum' class='button-small no-print' title='Bearbeitung abbrechen' onclick='return cancelOvertimeEdit(\"$formId\")' style='display: none; border-radius: 32px; background-color: transparent;'>\n"
            . "<img src='" . \PDR_HTTP_SERVER_APPLICATION_PATH . "img/backward.png' alt='Bearbeitung abbrechen'>\n"
            . "</button>\n";
    return $buttonText;
}

/**
 * Builds an HTML button for submitting changes to a specific row.
 *
 * @param \stdClass $rowObject The object representing the row for which the save button is generated.
 *
 * @return string The HTML code for the save button.
 */
function buildButtonSubmitSave(\stdClass $rowObject): string {
    $formId = "editForm_" . htmlspecialchars($rowObject->Datum);
    $buttonText = "<button type='submit' id='save_$rowObject->Datum' form='$formId' class='button-small no-print' title='Veränderungen dieser Zeile speichern' name='command' value='replace' style='display: none; border-radius: 32px;'>\n"
            . "<img src='" . \PDR_HTTP_SERVER_APPLICATION_PATH . "img/md_save.svg' alt='Veränderungen dieser Zeile speichern'>\n"
            . "</button>\n";
    return $buttonText;
}

//Start of output:
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';

echo "<div id=mainArea>\n";
$user_dialog = new user_dialog();
echo $user_dialog->build_messages();

echo form_element_builder::build_html_select_year($year);
echo build_html_navigation_elements::build_select_employee($employeeKey, $workforce->List_of_employees);
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
