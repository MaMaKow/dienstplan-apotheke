<?php

/*
 * Copyright (C) 2018 Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of class
 *
 * @author Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
abstract class roster {
    /*
     * Read the roster data from the database.
     * @param $start_date_sql string A string representation in the form of 'Y-m-d'. The first day, that is to be read.
     * @param $end_date_sql string A string representation in the form of 'Y-m-d'. The last day, that is to be read.
     */

    public static function read_roster_from_database($branch_id, $date_sql_start, $date_sql_end = NULL) {
        if (NULL === $date_sql_end) {
            $date_sql_end = $date_sql_start;
        }
        $date_unix_start = strtotime($date_sql_start);
        $date_unix_end = strtotime($date_sql_end);
        $Roster = array();
        for ($date_unix = $date_unix_start; $date_unix <= $date_unix_end; $date_unix += PDR_ONE_DAY_IN_SECONDS) {
            $date_sql = date('Y-m-d', $date_unix);
            $sql_query = 'SELECT DISTINCT Dienstplan.* '
                    . 'FROM `Dienstplan` '
                    . 'WHERE Dienstplan.Mandant = "' . $branch_id . '" AND `Datum` = "' . $date_sql . '" '
                    . 'ORDER BY `Dienstbeginn` ASC, `Dienstende` ASC, `Mittagsbeginn` ASC;';
            $result = mysqli_query_verbose($sql_query);

            $roster_row_iterator = 0;
            while ($row = mysqli_fetch_object($result)) {
                try {
                    $Roster[$date_unix][$roster_row_iterator] = new roster_item($row->Datum, $row->VK, $row->Dienstbeginn, $row->Dienstende, $row->Mittagsbeginn, $row->Mittagsende, $row->Kommentar);
                } catch (PDRRosterLogicException $exception) {
                    throw new PDRRosterLogicException($exception->getMessage());
                }
                $roster_row_iterator++;
            }
            /*
             * We mark empty roster days as empty:
             */
            if (!isset($Roster[$date_unix])) {
                $Roster[$date_unix]["empty"] = TRUE;
            }
        }
        return $Roster;
    }

    public static function get_employee_id_from_roster($Roster, $day_iterator, $roster_row_iterator) {
        return $Roster[$day_iterator][$roster_row_iterator]->employee_id;
    }

    public static function get_duty_start_from_roster($Roster, $day_iterator, $roster_row_iterator) {
        return roster_item::format_time_integer_to_string($Roster[$day_iterator][$roster_row_iterator]->duty_start_int);
    }

    public static function get_duty_end_from_roster($Roster, $day_iterator, $roster_row_iterator) {
        return roster_item::format_time_integer_to_string($Roster[$day_iterator][$roster_row_iterator]->duty_end_int);
    }

    public static function get_break_start_from_roster($Roster, $day_iterator, $roster_row_iterator) {
        return roster_item::format_time_integer_to_string($Roster[$day_iterator][$roster_row_iterator]->break_start_int);
    }

    public static function get_break_end_from_roster($Roster, $day_iterator, $roster_row_iterator) {
        return roster_item::format_time_integer_to_string($Roster[$day_iterator][$roster_row_iterator]->break_end_int);
    }

    public static function get_comment_from_roster($Roster, $day_iterator, $roster_row_iterator) {
        return $Roster[$day_iterator][$roster_row_iterator]->comment;
    }

}
