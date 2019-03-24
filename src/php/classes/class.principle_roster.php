<?php

/*
 * Copyright (C) 2019 Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
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

/**
 * The principle roster is the standard repeating roster
 *
 *
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class principle_roster extends roster {

    /**
     * read_principle_roster_from_database() accepts an array of options.
     * OPTION_CONTINUE_ON_ABSENCE is one of the possible options.
     * Absent employees will be excluded from the roster array, if the option is set.
     */
    const OPTION_CONTINUE_ON_ABSENCE = 'continue_on_absence';

    public static function read_principle_roster_from_database($branch_id, $date_sql_start, $date_sql_end = NULL, $Options = array()) {
        global $workforce;
        if (NULL === $date_sql_end) {
            $date_sql_end = $date_sql_start;
        }
        if (array() !== $Options and ! is_array($Options)) {
            $Options = (array) $Options;
        }
        $date_object_end = new DateTime($date_sql_end);
        $Roster = array();
        for ($date_object = new DateTime($date_sql_start); $date_object <= $date_object_end; $date_object->add(new DateInterval('P1D'))) {
            $date_sql = $date_object->format('Y-m-d');
            $Absentees = absence::read_absentees_from_database($date_sql);
            $weekday = $date_object->format('N');
            $sql_query = "SELECT * FROM `Grundplan` "
                    . "WHERE `Wochentag` = :weekday "
                    . "AND `Mandant` = :branch_id "
                    . "ORDER BY `Dienstbeginn` + `Dienstende`, `Dienstbeginn`";

            $result = database_wrapper::instance()->run($sql_query, array('branch_id' => $branch_id, 'weekday' => $weekday));
            $roster_row_iterator = 0;
            while ($row = $result->fetch(PDO::FETCH_OBJ)) {
                if (in_array(self::OPTION_CONTINUE_ON_ABSENCE, $Options) and isset($Absentees[$row->VK])) {
                    /*
                     * Absent employees will be excluded, if an actual roster is built.
                     */
                    continue 1;
                }
                if (isset($workforce->List_of_employees) AND array_search($row->VK, array_keys($workforce->List_of_employees)) === false) {
                    /*
                     * Exclude non-existent employees from the principle roster:
                     */
                    continue 1;
                }
                $Roster[$date_object->format('U')][$roster_row_iterator] = new roster_item($date_sql, (int) $row->VK, $row->Mandant, $row->Dienstbeginn, $row->Dienstende, $row->Mittagsbeginn, $row->Mittagsende);
                $roster_row_iterator++;
                //TODO: Make sure, that real NULL values are inserted into the database! By every php-file that inserts anything into the grundplan!
            }
        }
        self::determine_lunch_breaks($Roster);
        return $Roster;
    }

    public static function read_principle_employee_roster_from_database($employee_id, $date_sql_start, $date_sql_end = NULL) {
        global $workforce;
        if (NULL === $date_sql_end) {
            $date_sql_end = $date_sql_start;
        }
        $date_object_end = new DateTime($date_sql_end);
        $Roster = array();
        for ($date_object = new DateTime($date_sql_start); $date_object <= $date_object_end; $date_object->add(new DateInterval('P1D'))) {
            $date_sql = $date_object->format('Y-m-d');
            $weekday = $date_object->format('w');
            $sql_query = "SELECT * FROM `Grundplan` "
                    . " WHERE `Wochentag` = :weekday "
                    . " AND `VK` = :employee_id "
                    . " ORDER BY `Dienstbeginn` + `Dienstende`, `Dienstbeginn`";

            $result = database_wrapper::instance()->run($sql_query, array('weekday' => $weekday, 'employee_id' => $employee_id));
            $roster_row_iterator = 0;
            while ($row = $result->fetch(PDO::FETCH_OBJ)) {
                $Roster[$date_object->format('U')][$roster_row_iterator] = new roster_item($date_sql, (int) $row->VK, $row->Mandant, $row->Dienstbeginn, $row->Dienstende, $row->Mittagsbeginn, $row->Mittagsende);
                $roster_row_iterator++;
            }
            if (0 === $roster_row_iterator) {
                /*
                 * If there is no roster on a given day, we insert one empty roster_item.
                 * This is important for weekly views. Non existent rosters would misalign the tables.
                 */
                $branch_id = $workforce->List_of_employees[$employee_id]->principle_branch_id;
                $Roster[$date_object->format('U')][$roster_row_iterator] = new roster_item_empty($date_sql, $branch_id);
            }
        }
        return $Roster;
    }

    /**
     * This function determines the optimal lunch breaks.
     *
     * It considers the principle lunch breaks.
     * @return array $Roster
     */
    public static function determine_lunch_breaks($Roster) {
        global $workforce;
        $lunch_break_length_standard = 30 * 60;
        foreach (array_keys($Roster) as $date_unix) {
            if (empty($Roster[$date_unix])) {
                return FALSE;
            }
            foreach ($Roster[$date_unix] as $roster_item_object) {
                $break_start_taken_int[] = $roster_item_object->break_start_int;
                $break_end_taken_int[] = $roster_item_object->break_end_int;
            }
            $lunch_break_start = roster_item::convert_time_to_seconds('11:30:00');
            foreach ($Roster[$date_unix] as $roster_item_object) {
                $employee_id = $roster_item_object->employee_id;
                if (!empty($workforce->List_of_employees[$employee_id]->lunch_break_minutes) AND ! ($roster_item_object->break_start_int > 0) AND ! ($roster_item_object->break_end_int > 0)) {
                    //Zunächst berechnen wir die Stunden, damit wir wissen, wer überhaupt eine Mittagspause bekommt.
                    $duty_seconds_with_a_break = $roster_item_object->duty_end_int - $roster_item_object->duty_start_int - $workforce->List_of_employees[$employee_id]->lunch_break_minutes * 60;
                    if ($duty_seconds_with_a_break >= 6 * 3600) {
                        //echo "Mehr als 6 Stunden, also gibt es Mittag!";
                        //Wer länger als 6 Stunden Arbeitszeit hat, bekommt eine Mittagspause.
                        $lunch_break_end = $lunch_break_start + $workforce->List_of_employees[$employee_id]->lunch_break_minutes * 60;
                        for ($number_of_trys = 0; $number_of_trys < 3; $number_of_trys++) {
                            if (FALSE !== array_search($lunch_break_start, $break_start_taken_int) OR FALSE !== array_search($lunch_break_end, $break_end_taken_int)) {
                                //Zu diesem Zeitpunkt startet schon jemand sein Mittag. Wir warten 30 Minuten (1800 Sekunden)
                                $lunch_break_start += $lunch_break_length_standard;
                                $lunch_break_end += $lunch_break_length_standard;
                                continue;
                            } else {
                                break;
                            }
                        }
                        $roster_item_object->break_start_int = $lunch_break_start;
                        $roster_item_object->break_start_sql = roster_item::format_time_integer_to_string($lunch_break_start);
                        $roster_item_object->break_end_int = $lunch_break_end;
                        $roster_item_object->break_end_sql = roster_item::format_time_integer_to_string($lunch_break_end);
                        /*
                         * Preparartion for the next iteration:
                         */
                        $lunch_break_start = $lunch_break_end;
                    }
                } elseif (!empty($employee_id) AND ! empty($roster_item_object->break_start_int) AND empty($roster_item_object->break_end_int)) {
                    $roster_item_object->break_end_int = $roster_item_object->break_start_int + $workforce->List_of_employees[$employee_id]->lunch_break_minutes;
                    $roster_item_object->break_end_sql = roster_item::format_time_integer_to_string($roster_item_object->break_end_int);
                } elseif (!empty($employee_id) AND empty($roster_item_object->break_start_int) AND ! empty($roster_item_object->break_end_int)) {
                    $roster_item_object->break_start_int = $roster_item_object->break_end_int - $workforce->List_of_employees[$employee_id]->lunch_break_minutes;
                    $roster_item_object->break_start_sql = roster_item::format_time_integer_to_string($roster_item_object->break_start_int);
                }
            }
        }
        return NULL;
    }

}
