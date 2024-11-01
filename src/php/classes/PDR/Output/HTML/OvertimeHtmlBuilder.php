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

/**
 * Build HTML views from overtime data
 *
 * @author Mandelkow
 */
class OvertimeHtmlBuilder {

    public static function buildOverviewTable() {
        $table_head = self::buildOverviewTableHead();
        $table_body = self::buildOverviewTableBody();
        $table = "<table id='overtimeOverviewTable'>" . $table_head . $table_body . "</table>\n";
        return $table;
    }

    private static function buildOverviewTableHead() {
        $tableHead = "<thead>";
        $tableHead .= "<th>" . gettext('Employee') . "</th>";
        $tableHead .= "<th>" . gettext('Balance') . "</th>";
        $tableHead .= "<th>" . gettext('Date') . "</th>";
        $tableHead .= "</thead>\n";
        return $tableHead;
    }

    private static function buildOverviewTableBody() {
        $startDateObject = new \DateTime("October last year");
        $endDateObject = new \DateTime("last day of December this year");
        $workforce = new \workforce($startDateObject->format("Y-m-d"), $endDateObject->format("Y-m-d"));
        $tableRows = "<tbody>";
        // Create a DateTime object for the current date
        $currentDate = new \DateTime();

        // Calculate the date three months ago
        $threeMonthsAgo = clone $currentDate; // Create a copy of the current date
        $threeMonthsAgo->modify('-3 months'); // Subtract three months
        foreach (array_keys($workforce->List_of_employees) as $employeeKey) {
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
                    . $workforce->List_of_employees[$currentOvertime->getEmployeeKey()]->first_name
                    . "&nbsp;"
                    . $workforce->List_of_employees[$currentOvertime->getEmployeeKey()]->last_name
                    . "</td>";
            $tableRows .= "<td>" . $currentOvertime->getBalance() . "</td>";
            $dateString = $dateObject->format('d.m.Y');
            $tableRows .= "<td>" . $dateString . "</td>";
            $tableRows .= "</tr>\n";
        }

        $tableRows .= "</tbody>\n";
        return $tableRows;
    }

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
