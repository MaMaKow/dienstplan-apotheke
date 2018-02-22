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

    public function read_roster_from_database($date_sql_start, $date_sql_end, $branch_id) {
        //Abruf der gespeicherten Daten aus der Datenbank
        //$number_of_days ist die Anzahl der Tage. 5 Tage = Woche; 1 Tag = 1 Tag.
        //Branch #0 can be used for the boss, the cleaning lady, and other special people, who do not regularly appear in the roster.
        $date_unix_start = strtotime($date_sql_start);
        $date_unix_end = strtotime($date_sql_end);
        for ($date_unix = $date_unix_start; $date_unix <= $date_unix_end; $date_unix += PDR_ONE_DAY_IN_SECONDS) {
            $date_sql = date('Y-m-d', $date_unix);
            $sql_query = 'SELECT DISTINCT Dienstplan.* '
                    . 'FROM `Dienstplan` '
                    . 'WHERE Dienstplan.Mandant = "' . $branch_id . '" AND `Datum` = "' . $date_sql . '" '
                    . 'ORDER BY `Dienstbeginn` ASC, `Dienstende` ASC, `Mittagsbeginn` ASC;';
            $result = mysqli_query_verbose($sql_query);

            while ($row = mysqli_fetch_object($result)) {
                $Roster[$date_unix][] = new roster_item($row->Datum, $row->VK, $row->Dienstbeginn, $row->Dienstende, $row->Mittagsbeginn, $row->Mittagsende, $row->Kommentar);
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

    public function get_employee_id_from_roster($day_iterator, $array_column) {

    }

}
