<?php

/*
 * Copyright (C) 2024 Mandelkow
 *
 * Dienstplan Apotheke
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace PDR\Output\HTML;

use PDR\Roster\Overtime;

/**
 * Build HTML views from overtime data
 *
 * @author Mandelkow
 */
class OvertimeHtmlBuilder {

    /**
     * Builds an HTML form for deleting an overtime entry.
     *
     * @param Overtime $overtime The object representing the overtime entry to be deleted.
     *
     * @return string The HTML code for the delete form.
     */
    public static function buildFormOvertimeDelete(Overtime $overtime): string {
        $deleteFormId = "deleteForm_" . htmlspecialchars($overtime->getDate()->format("Y-m-d"));
        $deleteFormString = "";
        $deleteButtonText = "<button id=deleteButton_" . $overtime->getDate()->format("Y-m-d") . " type=submit form='$deleteFormId' name=deleteRow class='button-small delete_button no-print' title='Diese Zeile löschen' name=command value=delete>\n"
            . "<img src='" . \PDR_HTTP_SERVER_APPLICATION_PATH . "img/md_delete_forever.svg' alt='Diese Zeile löschen'>\n"
            . "</button>\n";

        $deleteFormString .= $deleteButtonText;
        $deleteFormString .= " <input type=hidden name=deletionEmployeeKey value='" . htmlspecialchars($overtime->getEmployeeKey()) . "' form='$deleteFormId'>\n";
        $deleteFormString .= " <input type=hidden name=deletionDate value='" . htmlspecialchars($overtime->getDate()->format("Y-m-d")) . "' form='$deleteFormId'>\n";
        $deleteFormString .= " <input type=hidden name=deletionHours value='" . htmlspecialchars($overtime->getHours()) . "' form='$deleteFormId'>\n";
        $deleteFormString .= " <input type=hidden name=deletionReason value='" . htmlspecialchars($overtime->getReason()) . "' form='$deleteFormId'>\n";
        $deleteFormString .= "<form accept-charset='utf-8' onsubmit='return confirmDelete()' method=POST id='$deleteFormId'>\n";
        $deleteFormString .= "</form>\n";
        return $deleteFormString;
    }

    /**
     * Builds an HTML form for editing an existing overtime entry.
     *
     * @param Overtime $overtime The object representing the overtime entry to be edited.
     *
     * @return string The HTML code for the edit form.
     */
    public static function buildFormOvertimeEdit(Overtime $overtime) {
        $formId = "editForm_" . htmlspecialchars($overtime->getDate()->format("Y-m-d"));
        $formString = "";
        $formString .= "<td>" . PHP_EOL;
        $formString .= " <input form=$formId type=hidden name=editEmployeeKey value='" . htmlspecialchars($overtime->getEmployeeKey()) . "'>" . PHP_EOL;
        $formString .= " <input readOnly form=$formId type=date name=editDateNew value='" . htmlspecialchars($overtime->getDate()->format("Y-m-d")) . "'>" . PHP_EOL;
        $formString .= " <input form=$formId type=hidden name=editDateOld value='" . htmlspecialchars($overtime->getDate()->format("Y-m-d")) . "'>" . PHP_EOL;
        $formString .= "</td><td>" . PHP_EOL;
        $formString .= " <input readOnly form=$formId type=number name=editHoursNew value='" . htmlspecialchars($overtime->getHours()) . "' step='0.25'>" . PHP_EOL;
        $formString .= " <input form=$formId type=hidden name=editHoursOld value='" . htmlspecialchars($overtime->getHours()) . "'>" . PHP_EOL;
        $formString .= "</td><td>" . PHP_EOL;
        $formString .= htmlspecialchars($overtime->getBalance()) . PHP_EOL;
        $formString .= "</td><td>" . PHP_EOL;
        $formString .= " <input readOnly form=$formId type=string name=editReasonNew value='" . htmlspecialchars($overtime->getReason()) . "'>" . PHP_EOL;
        $formString .= " <input form=$formId type=hidden name=editReasonOld value='" . htmlspecialchars($overtime->getReason()) . "'>" . PHP_EOL;
        $formString .= "</td><td>" . PHP_EOL;
        $formString .= " <button id=editButton_" . $overtime->getDate()->format("Y-m-d") . " class='no-print button-small' title='Diese Zeile bearbeiten' onClick='overtime_edit_existing_entries(\"$formId\");'>"
            . '<img src="/apotheke/dienstplan-test/img/md_edit.svg" alt="Diese Zeile bearbeiten">'
            . '</button>' . PHP_EOL;
        $formString .= self::buildFormOvertimeDelete($overtime);
        $formString .= self::buildButtonSubmitSave($overtime);
        $formString .= self::buildButtonCancelEdit($overtime);
        $formString .= "</td>" . PHP_EOL;
        $formString .= "<form accept-charset='utf-8' method=POST id=$formId></form>" . PHP_EOL;
        $formString .= "";
        return $formString;
    }

    /**
     * Builds an HTML button for canceling the editing of a specific row.
     *
     * @param \PDR\Roster\Overtime $overtime The object representing the row for which the cancel edit button is generated.
     *
     * @return string The HTML code for the cancel edit button.
     */
    public static function buildButtonCancelEdit(\PDR\Roster\Overtime $overtime): string {
        $formId = "editForm_" . htmlspecialchars($overtime->getDate()->format("Y-m-d"));
        $buttonText = "<button id='cancel_" . $overtime->getDate()->format("Y-m-d") . "' class='button-small no-print' title='Bearbeitung abbrechen' onclick='return cancelOvertimeEdit(\"$formId\")' style='display: none; border-radius: 32px; background-color: transparent;'>\n"
            . "<img src='" . \PDR_HTTP_SERVER_APPLICATION_PATH . "img/backward.png' alt='Bearbeitung abbrechen'>\n"
            . "</button>\n";
        return $buttonText;
    }

    /**
     * Builds an HTML button for submitting changes to a specific row.
     *
     * @param \PDR\Roster\Overtime $overtime The object representing the row for which the save button is generated.
     *
     * @return string The HTML code for the save button.
     */
    public static function buildButtonSubmitSave(\PDR\Roster\Overtime $overtime): string {
        $formId = "editForm_" . htmlspecialchars($overtime->getDate()->format("Y-m-d"));
        $buttonText = "<button type='submit' id='save_" . $overtime->getDate()->format("Y-m-d") . "' form='$formId' class='button-small no-print' title='Veränderungen dieser Zeile speichern' name='command' value='replace' style='display: none; border-radius: 32px;'>\n"
            . "<img src='" . \PDR_HTTP_SERVER_APPLICATION_PATH . "img/md_save.svg' alt='Veränderungen dieser Zeile speichern'>\n"
            . "</button>\n";
        return $buttonText;
    }

    /**
     * Builds an overview table for displaying overtime information.
     *
     * @return string The HTML code for the overview table.
     */
    public static function buildOverviewTable() {
        $table_head = self::buildOverviewTableHead();
        $table_body = self::buildOverviewTableBody();
        $table = "<table id='overtimeOverviewTable'>" . $table_head . $table_body . "</table>\n";
        return $table;
    }

    /**
     * Builds the header of the overview table for displaying overtime information.
     */
    private static function buildOverviewTableHead() {
        $tableHead = "<thead>";
        $tableHead .= "<th>" . gettext('Employee') . "</th>";
        $tableHead .= "<th>" . gettext('Balance') . "</th>";
        $tableHead .= "<th>" . gettext('Date') . "</th>";
        $tableHead .= "</thead>\n";
        return $tableHead;
    }

    /**
     * Builds the body of the overview table for displaying overtime information.
     * Overtime entries that have not been updated in the last three months are highlighted with a specific CSS class.
     * The balance is also highlighted based on its value, with different classes for positive, negative, and zero balances.
     */
    private static function buildOverviewTableBody() {
        $startDateObject = new \DateTime("October last year");
        $endDateObject = new \DateTime("last day of December this year");
        $workforce = new \PDR\Workforce\Workforce($startDateObject->format("Y-m-d"), $endDateObject->format("Y-m-d"));
        $tableRows = "<tbody>";
        // Create a DateTime object for the current date
        $currentDate = new \DateTime();

        // Calculate the date three months ago
        $threeMonthsAgo = clone $currentDate; // Create a copy of the current date
        $threeMonthsAgo->modify('-3 months'); // Subtract three months
        foreach (array_keys($workforce->getListOfEmployees()) as $employeeKey) {
            /**
             * @todo: Move database call to database class.
             * Create a class "Overtime" and a class "CollectionOfOvertimes"
             */
            $currentOvertime = \PDR\Database\OvertimeDatabaseHandler::getCurrentOvertime($employeeKey);
            $dateObject = $currentOvertime->getDate();
            $class = self::getBalanceClass($currentOvertime->getBalance());
            if ($dateObject < $threeMonthsAgo) {
                $class .= " " . "not-updated";
            }
            $tableRows .= "<tr class='$class'>";
            $tableRows .= "<td>"
                . $workforce->getEmployeeFirstName($currentOvertime->getEmployeeKey())
                . "&nbsp;"
                . $workforce->getEmployeeLastName($currentOvertime->getEmployeeKey())
                . "</td>";
            $tableRows .= "<td>" . $currentOvertime->getBalance() . "</td>";
            $dateString = $dateObject->format('d.m.Y');
            $tableRows .= "<td>" . $dateString . "</td>";
            $tableRows .= "</tr>\n";
        }

        $tableRows .= "</tbody>\n";
        return $tableRows;
    }

    /**
     * Determines the CSS class for the balance based on its value.
     *
     * @param float $balance The balance value.
     * @return string The CSS class for the balance.
     */
    private static function getBalanceClass(float $balance): String {
        $class = "";
        switch (TRUE) {
            case 40 < $balance:
                $class = "positive-very-high";
                break;
            case 20 < $balance:
                $class = "positive-high";
                break;
            case 0 == $balance:
                $class = "zero";
                break;
            case 0 > $balance:
                $class = "negative";
                break;
            default:
                $class = "positive";
                break;
        }
        return $class;
    }
}
