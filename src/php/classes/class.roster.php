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

    const OPTION_CONTINUE_ON_ABSENCE = 'continue_on_absence';

    /**
     * Read the roster data from the database.
     * @param $start_date_sql string A string representation in the form of 'Y-m-d'. The first day, that is to be read.
     * @param $end_date_sql string A string representation in the form of 'Y-m-d'. The last day, that is to be read.
     */
    public static function read_employee_roster_from_database($employee_id, $date_sql_start, $date_sql_end = NULL) {
        /*
         * TODO: unify this with read_roster_from_database
         * Make them both one function perhaps.
         */
        if (NULL === $date_sql_end) {
            $date_sql_end = $date_sql_start;
        }
        $date_unix_start = strtotime($date_sql_start);
        $date_unix_end = strtotime($date_sql_end);
        $Roster = array();
        $the_whole_roster_is_empty = TRUE;
        for ($date_unix = $date_unix_start; $date_unix <= $date_unix_end; $date_unix += PDR_ONE_DAY_IN_SECONDS) {
            $date_sql = date('Y-m-d', $date_unix);
            $sql_query = 'SELECT * '
                    . 'FROM `Dienstplan` '
                    . "WHERE `Datum` = :date and `VK` = :employee_id "
                    . 'ORDER BY `Dienstbeginn` ASC, `Dienstende` ASC, `Mittagsbeginn` ASC;';
            $result = database_wrapper::instance()->run($sql_query, array('date' => $date_sql, 'employee_id' => $employee_id));

            $roster_row_iterator = 0;
            while ($row = $result->fetch(PDO::FETCH_OBJ)) {
                $Roster[$date_unix][$roster_row_iterator] = new roster_item($row->Datum, (int) $row->VK, $row->Mandant, $row->Dienstbeginn, $row->Dienstende, $row->Mittagsbeginn, $row->Mittagsende, $row->Kommentar);
                $the_whole_roster_is_empty = FALSE;
                $roster_row_iterator++;
            }
            if (0 === $roster_row_iterator) {
                /*
                 * If there is no roster on a given day, we insert one empty roster_item.
                 * This is important for weekly views. Non existent rosters would misalign the tables.
                 */
                $Roster[$date_unix][$roster_row_iterator] = new roster_item_empty($date_sql, NULL);
            }
        }
        if (TRUE === $the_whole_roster_is_empty) {
            /* reset the roster to be completely empty */
            //$Roster = array();
        }
        return $Roster;
    }

    /*
     * Read the roster data from the database.
     * @param $start_date_sql string A string representation in the form of 'Y-m-d'. The first day, that is to be read.
     * @param $end_date_sql string A string representation in the form of 'Y-m-d'. The last day, that is to be read.
     */

    public static function read_roster_from_database($branch_id, $date_sql_start, $date_sql_end = NULL) {
        /*
         * TODO: unify this with read_branch_roster_from_database
         * Make them both one function perhaps.
         */
        if (NULL === $date_sql_end) {
            $date_sql_end = $date_sql_start;
        }
        $date_unix_start = strtotime($date_sql_start);
        $date_unix_end = strtotime($date_sql_end);
        $Roster = array();
        $the_whole_roster_is_empty = TRUE;
        for ($date_unix = $date_unix_start; $date_unix <= $date_unix_end; $date_unix += PDR_ONE_DAY_IN_SECONDS) {
            $date_sql = date('Y-m-d', $date_unix);
            $sql_query = 'SELECT * '
                    . 'FROM `Dienstplan` '
                    . 'WHERE Mandant = :branch_id AND `Datum` = :date '
                    . 'ORDER BY `Dienstbeginn` ASC, `Dienstende` ASC, `Mittagsbeginn` ASC;';
            $result = database_wrapper::instance()->run($sql_query, array('branch_id' => $branch_id, 'date' => $date_sql));
            $roster_row_iterator = 0;
            while ($row = $result->fetch(PDO::FETCH_OBJ)) {
                $Roster[$date_unix][$roster_row_iterator] = new roster_item($row->Datum, (int) $row->VK, $row->Mandant, $row->Dienstbeginn, $row->Dienstende, $row->Mittagsbeginn, $row->Mittagsende, $row->Kommentar);
                $the_whole_roster_is_empty = FALSE;
                $roster_row_iterator++;
            }
            if (0 === $roster_row_iterator) {
                /*
                 * If there is no roster on a given day, we insert one empty roster_item.
                 * This is important for weekly views. Non existent rosters would misalign the tables.
                 */
                $Roster[$date_unix][$roster_row_iterator] = new roster_item_empty($date_sql, $branch_id);
            }
        }
        /*
          if (TRUE === $the_whole_roster_is_empty) {
          // reset the roster to be completely empty
          $Roster = array();
          }
         */
        return $Roster;
    }

    public static function read_branch_roster_from_database($branch_id, $other_branch_id, $date_sql_start, $date_sql_end = NULL) {
        if (NULL === $date_sql_end) {
            $date_sql_end = $date_sql_start;
        }
        $date_unix_start = strtotime($date_sql_start);
        $date_unix_end = strtotime($date_sql_end);
        $Roster = array();
        $the_whole_roster_is_empty = TRUE;
        for ($date_unix = $date_unix_start; $date_unix <= $date_unix_end; $date_unix += PDR_ONE_DAY_IN_SECONDS) {
            $date_sql = date('Y-m-d', $date_unix);
            $sql_query = 'SELECT DISTINCT Dienstplan.* '
                    . ' FROM `Dienstplan` LEFT JOIN employees ON Dienstplan.VK=employees.id '
                    . ' WHERE Dienstplan.Mandant = :other_branch_id AND `Datum` = :date AND employees.branch = :branch_id '
                    . ' ORDER BY `Dienstbeginn` ASC, `Dienstende` ASC, `Mittagsbeginn` ASC;';
            $result = database_wrapper::instance()->run($sql_query, array('branch_id' => $branch_id, 'other_branch_id' => $other_branch_id, 'date' => $date_sql));

            $roster_row_iterator = 0;
            while ($row = $result->fetch(PDO::FETCH_OBJ)) {
                $Roster[$date_unix][$roster_row_iterator] = new roster_item($row->Datum, (int) $row->VK, $row->Mandant, $row->Dienstbeginn, $row->Dienstende, $row->Mittagsbeginn, $row->Mittagsende, $row->Kommentar);
                $the_whole_roster_is_empty = FALSE;
                $roster_row_iterator++;
            }
            if (0 === $roster_row_iterator) {
                /*
                 * If there is no roster on a given day, we insert one empty roster_item.
                 * This is important for weekly views. Non existent rosters would misalign the tables.
                 */
                $Roster[$date_unix][$roster_row_iterator] = new roster_item_empty($date_sql, $branch_id);
            }
        }
        if (TRUE === $the_whole_roster_is_empty) {
            /* reset the roster to be completely empty */
            $Roster = array();
        }
        return $Roster;
    }

    public static function read_principle_roster_from_database($branch_id, $date_sql_start, $date_sql_end = NULL, $Options = array()) {
        //database_wrapper::instance()->run("UPDATE `Grundplan` SET `Mittagsbeginn` = NULL WHERE `Grundplan`.`Mittagsbeginn` = '0:00:00'");
        //database_wrapper::instance()->run("UPDATE `Grundplan` SET `Mittagsende` = NULL WHERE `Grundplan`.`Mittagsende` = '0:00:00'");
        /*
         * TODO: Make sure, that these two repair calls are not necessary anymore
         */
        global $workforce;
        if (NULL === $date_sql_end) {
            $date_sql_end = $date_sql_start;
        }
        if (array() !== $Options and ! is_array($Options)) {
            $Options = (array) $Options;
        }
        $date_unix_start = strtotime($date_sql_start);
        $date_unix_end = strtotime($date_sql_end);
        $Roster = array();
        for ($date_unix = $date_unix_start; $date_unix <= $date_unix_end; $date_unix += PDR_ONE_DAY_IN_SECONDS) {
            $date_sql = date('Y-m-d', $date_unix);
            $Absentees = absence::read_absentees_from_database($date_sql);
            $weekday = date("N", $date_unix);
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
                $Roster[$date_unix][$roster_row_iterator] = new roster_item($date_sql, (int) $row->VK, $row->Mandant, $row->Dienstbeginn, $row->Dienstende, $row->Mittagsbeginn, $row->Mittagsende);
                $roster_row_iterator++;
                //TODO: Make sure, that real NULL values are inserted into the database! By every php-file that inserts anything into the grundplan!
            }
        }
        roster::determine_lunch_breaks($Roster);
        return $Roster;
    }

    public static function read_principle_employee_roster_from_database($employee_id, $date_sql_start, $date_sql_end = NULL) {
        global $workforce;
        if (NULL === $date_sql_end) {
            $date_sql_end = $date_sql_start;
        }
        $date_unix_start = strtotime($date_sql_start);
        $date_unix_end = strtotime($date_sql_end);
        $Roster = array();
        for ($date_unix = $date_unix_start; $date_unix <= $date_unix_end; $date_unix += PDR_ONE_DAY_IN_SECONDS) {
            $date_sql = date('Y-m-d', $date_unix);
            $weekday = date("N", $date_unix);
            $sql_query = "SELECT * FROM `Grundplan` "
                    . " WHERE `Wochentag` = :weekday "
                    . " AND `VK` = :employee_id "
                    . " ORDER BY `Dienstbeginn` + `Dienstende`, `Dienstbeginn`";

            $result = database_wrapper::instance()->run($sql_query, array('weekday' => $weekday, 'employee_id' => $employee_id));
            $roster_row_iterator = 0;
            while ($row = $result->fetch(PDO::FETCH_OBJ)) {
                $Roster[$date_unix][$roster_row_iterator] = new roster_item($date_sql, (int) $row->VK, $row->Mandant, $row->Dienstbeginn, $row->Dienstende, $row->Mittagsbeginn, $row->Mittagsende);
                $roster_row_iterator++;
            }
            if (0 === $roster_row_iterator) {
                /*
                 * If there is no roster on a given day, we insert one empty roster_item.
                 * This is important for weekly views. Non existent rosters would misalign the tables.
                 */
                $branch_id = $workforce->List_of_employees[$employee_id]->principle_branch_id;
                $Roster[$date_unix][$roster_row_iterator] = new roster_item_empty($date_sql, $branch_id);
            }
        }
        return $Roster;
    }

    /*
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

    public static function transfer_lunch_breaks($Principle_employee_roster, $Principle_roster) {
        foreach ($Principle_employee_roster as $date_unix => $Principle_employee_roster_day_array) {
            foreach ($Principle_employee_roster_day_array as $principle_employee_roster_object) {
                if (NULL === $principle_employee_roster_object->break_start_int and isset($Principle_roster[$date_unix])) {
                    foreach ($Principle_roster[$date_unix] as $principle_roster_object) {
                        if ($principle_roster_object->employee_id === $principle_employee_roster_object->employee_id
                                and $principle_roster_object->duty_start_int === $principle_employee_roster_object->duty_start_int
                                and $principle_roster_object->branch_id === $principle_employee_roster_object->branch_id
                                and NULL !== $principle_roster_object->break_start_int) {
                            $principle_employee_roster_object->break_start_int = $principle_roster_object->break_start_int;
                            $principle_employee_roster_object->break_end_int = $principle_roster_object->break_end_int;
                            $principle_employee_roster_object->break_start_sql = $principle_roster_object->break_start_sql;
                            $principle_employee_roster_object->break_end_sql = $principle_roster_object->break_end_sql;
                            /* The durations are automagically recalculated using roster_intem->__set() which calls roster_item->calculate_durations() */
                        }
                    }
                }
            }
        }
    }

    public static function calculate_changing_times($Roster) {
        if (array() === $Roster) {
            /* No roster, no changing times */
            return FALSE;
        }
        foreach ($Roster as $roster_day) {
            foreach ($roster_day as $roster_item_object) {
                $Changing_times[] = $roster_item_object->duty_start_int;
                $Changing_times[] = $roster_item_object->duty_end_int;
                $Changing_times[] = $roster_item_object->break_start_int;
                $Changing_times[] = $roster_item_object->break_end_int;
            }
        }
        $Clean_changing_times = roster::cleanup_changing_times($Changing_times);
        return $Clean_changing_times;
    }

    public static function cleanup_changing_times($Changing_times) {
        sort($Changing_times);
        $Unique_changing_times = array_unique($Changing_times);
        /*
         * Remove empty and null values from the array:
         */
        $Clean_changing_times = array_filter($Unique_changing_times, 'strlen');
        return $Clean_changing_times;
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

    /**
     *
     * @param array $Roster
     * @return int
     */
    public static function calculate_max_employee_count($Roster) {
        $Employee_count[] = 0;
        foreach ($Roster as $Roster_day_array) {
            $Employee_count[] = (count($Roster_day_array));
        }
        $roster_employee_count = max($Employee_count); //The number of rows is defined by the column (=day) with the most lines
        //$max_employee_count = $roster_employee_count + 1; //One additional empty row will be appended
        return $roster_employee_count;
    }

    /*
     * Calculation of the working hours of the employees:
     */

    public static function calculate_working_hours_weekly_from_branch_roster($Branch_roster) {
        /*
         * CAVE! This function expects an array of the format: $Branch_roster[$branch_id][$date_unix][$roster_item]
         * The standard $Roster array ($Roster[$date_unix][$roster_item]) will not return any usefull information.
         */
        $Working_hours_week = array();
        foreach ($Branch_roster as $Branch_roster_branch_array) {
            foreach ($Branch_roster_branch_array as $Roster_day_array) {
                foreach ($Roster_day_array as $roster_item) {
                    if (!isset($roster_item->working_hours)) {
                        continue 1;
                    }
                    if (!isset($Working_hours_week[$roster_item->employee_id])) {
                        $Working_hours_week[$roster_item->employee_id] = $roster_item->working_hours;
                    } else {
                        $Working_hours_week[$roster_item->employee_id] += $roster_item->working_hours;
                    }
                }
            }
        }
        ksort($Working_hours_week);
        return $Working_hours_week;
    }

    /**
     * Test if a duty roster is completely empty
     *
     * @param array $Roster
     * @return boolean
     */
    public static function is_empty($Roster) {
        foreach ($Roster as $roster_array) {
            foreach ($roster_array as $roster_object) {
                if (NULL !== $roster_object->employee_id) {
                    /*
                     * In most cases we do not have to loop through the whole array.
                     * If the first element is filled, then we allready stop searching.
                     */
                    return FALSE;
                }
            }
        }
        /*
         * In those cases, where there is no actual roster data given, the array is mostly small.
         * Therefore this should also not be a huge load of work.
         */
        return TRUE;
    }

}
