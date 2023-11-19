<?php

/*
 * Copyright (C) 2023 Mandelkow
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
 * A class that builds an HTML table for displaying vacation data.
 *
 * @author Mandelkow
 */
class vacationPageBuilder {

    /**
     * Builds an overview table for displaying vacation data for a specific year.
     *
     * @param int $year The year for which vacation data should be displayed.
     * @return string HTML representation of the vacation overview table.
     */
    public function build_overview_table(int $year): string {
        $table_head = $this->build_overview_table_head($year);
        $table_body = $this->build_overview_table_body($year);
        $table = "<table id='overtime_overview_table'>" . $table_head . $table_body . "</table>\n";
        return $table;
    }

    /**
     * Builds the header of the overview table.
     *
     * @param int $year The year for which vacation data should be displayed.
     * @return string HTML table header.
     */
    private function build_overview_table_head(int $year): string {
        $nextYear = $year + 1;
        $table_head = "<thead>";
        $table_head .= "<th>" . gettext('Employee') . "</th>";
        $table_head .= "<th>" . gettext('Vacation Entitlement') . "</th>";
        $table_head .= "<th>" . gettext('Taken Vacation') . "</th>";
        $table_head .= "<th>" . sprintf(gettext('Remaining Vacation for %1$s'), $nextYear) . "</th>";
        $table_head .= "</thead>\n";
        return $table_head;
    }

    /**
     * Builds the body of the overview table.
     *
     * @param int $year The year for which vacation data should be displayed.
     * @return string HTML table body.
     */
    private function build_overview_table_body(int $year): string {
        $startDateObject = new \DateTime("$year-01-01");
        $endDateObject = new \DateTime("$year-12-31");
        $workforce = new \workforce($startDateObject->format("Y-m-d"), $endDateObject->format("Y-m-d"));
        $table_rows = "<tbody>";

        foreach (array_keys($workforce->List_of_employees) as $employee_key) {
            $number_of_holidays_due = \absence::get_number_of_holidays_due($employee_key, $workforce, $year);
            $number_of_holidays_principle = $workforce->List_of_employees[$employee_key]->holidays;
            $number_of_holidays_taken = \absence::get_number_of_holidays_taken($employee_key, $year);
            $number_of_remaining_holidays = $number_of_holidays_due - $number_of_holidays_taken;

            $table_rows .= "<tr>";
            $table_rows .= "<td>" . $workforce->List_of_employees[$employee_key]->first_name . " " . $workforce->List_of_employees[$employee_key]->last_name . "</td>";
            $table_rows .= "<td>" . $number_of_holidays_due . " / " . $number_of_holidays_principle . "</td>";
            $table_rows .= "<td>" . $number_of_holidays_taken . "</td>";
            $table_rows .= "<td>" . $number_of_remaining_holidays . "</td>";
            $table_rows .= "</tr>\n";
        }
        $table_rows .= "</tbody>\n";
        return $table_rows;
    }
}
