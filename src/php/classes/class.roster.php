<?php

/*
 * Copyright (C) 2018 Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
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
 * A roster is an array of information about when which employee will start and end to work.
 * The elements of that array are roster_item objects.
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class roster {

    /**
     * @var array $array_of_days_of_roster_items is the roster array of the instantiated object.
     */
    public $array_of_days_of_roster_items = array();

    function __construct(DateTime $date_start_object, DateTime $date_end_object = NULL, int $employee_key = NULL, int $branch_id = NULL, int $other_branch_id = NULL) {
        if (NULL === $date_start_object) {
            throw new Exception('A start date must be given for ' . __METHOD__);
        }
        if (NULL === $date_end_object) {
            $date_end_object = clone $date_start_object;
        }
        if (NULL !== $employee_key) {
            $this->array_of_days_of_roster_items = $this->read_employee_roster_from_database($employee_key, clone $date_start_object, clone $date_end_object);
            return TRUE;
        }
        throw new Exception('The object of the class ' . __CLASS__ . ' was not correctly constructed. Please check the parameters.');
    }

    /**
     * Read the roster data from the database.
     * @param DateTime $date_start_object The first day, that is to be read.
     * @param DateTime $date_end_object The last day, that is to be read.
     */
    protected function read_employee_roster_from_database(int $employee_key, DateTime $date_start_object, DateTime $date_end_object) {
        /*
         * TODO: unify this with read_roster_from_database
         * Make them both one function perhaps.
         */
        $Roster = array();
        for ($date_object = $date_start_object; $date_object <= $date_end_object; $date_object->add(new DateInterval('P1D'))) {
            $date_sql = $date_object->format('Y-m-d');
            $sql_query = 'SELECT * '
                    . 'FROM `Dienstplan` '
                    . "WHERE `Datum` = :date and `employee_key` = :employee_key "
                    . 'ORDER BY `Dienstbeginn` ASC, `Dienstende` ASC, `Mittagsbeginn` ASC;';
            $result = database_wrapper::instance()->run($sql_query, array('date' => $date_sql, 'employee_key' => $employee_key));

            $roster_row_iterator = 0;
            while ($row = $result->fetch(PDO::FETCH_OBJ)) {
                $Roster[$date_object->format('U')][$roster_row_iterator] = new roster_item($row->Datum, (int) $row->employee_key, $row->Mandant, $row->Dienstbeginn, $row->Dienstende, $row->Mittagsbeginn, $row->Mittagsende, $row->Kommentar);
                $roster_row_iterator++;
            }
            if (0 === $roster_row_iterator) {
                /*
                 * If there is no roster on a given day, we insert one empty roster_item.
                 * This is important for weekly views. Non existent rosters would misalign the tables.
                 */
                $Roster[$date_object->format('U')][$roster_row_iterator] = new roster_item_empty($date_sql, NULL);
            }
        }
        return $Roster;
    }

    /**
     * Read the roster data from the database.
     * @param $start_date_sql string A string representation in the form of 'Y-m-d'. The first day, that is to be read.
     * @param $end_date_sql string A string representation in the form of 'Y-m-d'. The last day, that is to be read.
     */
    public static function read_roster_from_database(int $branch_id, string $date_sql_start, string $date_sql_end = NULL) {
        /*
         * TODO: unify this with read_branch_roster_from_database
         * Make them both one function perhaps.
         */
        if (NULL === $date_sql_end) {
            $date_sql_end = $date_sql_start;
        }
        $date_end_object = new DateTime($date_sql_end);
        $Roster = array();
        $the_whole_roster_is_empty = TRUE;
        for ($date_object = new DateTime($date_sql_start); $date_object <= $date_end_object; $date_object->add(new DateInterval('P1D'))) {
            $date_sql = $date_object->format('Y-m-d');
            $sql_query = 'SELECT * '
                    . 'FROM `Dienstplan` '
                    . 'WHERE Mandant = :branch_id AND `Datum` = :date '
                    . 'ORDER BY `Dienstbeginn` ASC, `Dienstende` ASC, `Mittagsbeginn` ASC;';
            $result = database_wrapper::instance()->run($sql_query, array('branch_id' => $branch_id, 'date' => $date_sql));
            $roster_row_iterator = 0;
            while ($row = $result->fetch(PDO::FETCH_OBJ)) {
                $Roster[$date_object->format('U')][$roster_row_iterator] = new roster_item($row->Datum, (int) $row->employee_key, $row->Mandant, $row->Dienstbeginn, $row->Dienstende, $row->Mittagsbeginn, $row->Mittagsende, $row->Kommentar);
                $the_whole_roster_is_empty = FALSE;
                $roster_row_iterator++;
            }
            if (0 === $roster_row_iterator) {
                /*
                 * If there is no roster on a given day, we insert one empty roster_item.
                 * This is important for weekly views. Non existent rosters would misalign the tables.
                 */
                $Roster[$date_object->format('U')][$roster_row_iterator] = new roster_item_empty($date_sql, $branch_id);
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

    public static function read_branch_roster_from_database(int $branch_id, int $other_branch_id, string $date_sql_start, string $date_sql_end = NULL) {
        if (NULL === $date_sql_end) {
            $date_sql_end = $date_sql_start;
        }
        $date_end_object = new DateTime($date_sql_end);
        $Roster = array();
        $the_whole_roster_is_empty = TRUE;
        for ($date_object = new DateTime($date_sql_start); $date_object <= $date_end_object; $date_object->add(new DateInterval('P1D'))) {
            $date_sql = $date_object->format('Y-m-d');
            $sql_query = 'SELECT DISTINCT Dienstplan.* '
                    . ' FROM `Dienstplan` LEFT JOIN employees ON Dienstplan.employee_key = employees.primary_key '
                    . ' WHERE Dienstplan.Mandant = :other_branch_id AND `Datum` = :date AND employees.branch = :branch_id '
                    . ' ORDER BY `Dienstbeginn` ASC, `Dienstende` ASC, `Mittagsbeginn` ASC;';
            $result = database_wrapper::instance()->run($sql_query, array('branch_id' => $branch_id, 'other_branch_id' => $other_branch_id, 'date' => $date_sql));

            $roster_row_iterator = 0;
            while ($row = $result->fetch(PDO::FETCH_OBJ)) {
                $Roster[$date_object->getTimestamp()][$roster_row_iterator] = new roster_item($row->Datum, (int) $row->employee_key, $row->Mandant, $row->Dienstbeginn, $row->Dienstende, $row->Mittagsbeginn, $row->Mittagsende, $row->Kommentar);
                $the_whole_roster_is_empty = FALSE;
                $roster_row_iterator++;
            }
            if (0 === $roster_row_iterator) {
                /*
                 * If there is no roster on a given day, we insert one empty roster_item.
                 * This is important for weekly views. Non existent rosters would misalign the tables.
                 */
                $Roster[$date_object->getTimestamp()][$roster_row_iterator] = new roster_item_empty($date_sql, $branch_id);
            }
        }
        if (TRUE === $the_whole_roster_is_empty) {
            /* reset the roster to be completely empty */
            $Roster = array();
        }
        return $Roster;
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
        $Clean_changing_times = array_filter($Unique_changing_times, function ($value) {
            return $value !== null && strlen($value) > 0;
        });
        return $Clean_changing_times;
    }

    /**
     *
     * @param type $Roster
     * @param type $day_iterator
     * @param type $roster_row_iterator
     * @return type
     * @todo Are these functions used somewhere?
     */
    public static function get_employee_key_from_roster($Roster, $day_iterator, $roster_row_iterator) {
        return $Roster[$day_iterator][$roster_row_iterator]->employee_key;
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

    public static function get_working_hours_from_roster(array $Roster, int $day_iterator, int $roster_row_iterator) {
        return $Roster[$day_iterator][$roster_row_iterator]->working_hours;
    }

    public static function get_comment_from_roster($Roster, $day_iterator, $roster_row_iterator) {
        return $Roster[$day_iterator][$roster_row_iterator]->comment;
    }

    public static function get_working_hours_in_all_branches(string $date_string, int $employee_key) {
        $working_hours = 0;
        $sql_query = "SELECT sum(`Stunden`) as `working_hours` FROM `Dienstplan` WHERE `Datum` = :date and `employee_key` = :employee_key";
        $result = database_wrapper::instance()->run($sql_query, array(
            'date' => $date_string,
            'employee_key' => $employee_key,
        ));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $working_hours = $row->working_hours;
        }
        return $working_hours;
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

    /**
     * Calculation of the working hours of the employees:
     */
    public static function calculate_working_weekly_hours_from_branch_roster($Branch_roster) {
        /*
         * CAVE! This function expects an array of the format: $Branch_roster[$branch_id][$date_unix][$roster_item]
         * The standard $Roster array ($Roster[$date_unix][$roster_item]) will not return any usefull information.
         */
        $Working_week_hours = array();
        foreach ($Branch_roster as $Branch_roster_branch_array) {
            foreach ($Branch_roster_branch_array as $Roster_day_array) {
                foreach ($Roster_day_array as $roster_item) {
                    if (!isset($roster_item->working_hours)) {
                        continue 1;
                    }
                    if (!isset($Working_week_hours[$roster_item->employee_key])) {
                        $Working_week_hours[$roster_item->employee_key] = $roster_item->working_hours;
                    } else {
                        $Working_week_hours[$roster_item->employee_key] += $roster_item->working_hours;
                    }
                }
            }
        }
        ksort($Working_week_hours);
        return $Working_week_hours;
    }

    /**
     * Test if a duty roster is completely empty
     *
     * @param array $Roster
     * @return boolean
     */
    public static function is_empty($Roster) {
        foreach ($Roster as $Roster_day_array) {
            foreach ($Roster_day_array as $roster_object) {
                if (NULL !== $roster_object->employee_key) {
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

    public static function is_empty_roster_day_array($Roster_day_array) {
        foreach ($Roster_day_array as $roster_object) {
            if (NULL !== $roster_object->employee_key) {
                /*
                 * In most cases we do not have to loop through the whole array.
                 * If the first element is filled, then we allready stop searching.
                 */
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * Codiert die Dienstplan-Daten in das JSON-Format.
     *
     * @return string JSON-codierter Dienstplan
     */
    public function encodeToJson() {
        $jsonArray = array();

        foreach ($this->array_of_days_of_roster_items as $dateUnix => $rosterDayArray) {
            $jsonDayArray = array();

            foreach ($rosterDayArray as $rosterItem) {
                if ($rosterItem instanceof roster_item_empty) {
                    continue;
                }
                $jsonDayArray[] = array(
                    'date' => $rosterItem->date_object->format('Y-m-d'),
                    'employee_key' => $rosterItem->employee_key,
                    'branch_id' => $rosterItem->branch_id,
                    'duty_start' => $rosterItem->get_dutyStartDateTime()->format('c'),
                    'duty_end' => $rosterItem->get_dutyEndDateTime()->format('c'),
                    'break_start' => $rosterItem->getBreakStartDateTime()->format('c'),
                    'break_end' => $rosterItem->getBreakStartDateTime()->format('c'),
                    'comment' => $rosterItem->comment,
                    'working_hours' => $rosterItem->working_hours,
                );
            }
            if (isset($jsonDayArray[0]) and isset($jsonDayArray[0]['date']))
                $jsonArray[] = array(
                    'date' => $jsonDayArray[0]['date'],
                    'roster' => $jsonDayArray,
                );
        }

        return json_encode($jsonArray);
    }
}
