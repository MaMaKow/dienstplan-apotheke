<?php

/*
 * Copyright (C) 2019 Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
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

    public $alternation_id;

    public static function read_current_principle_roster_from_database(int $branch_id, DateTime $date_start_object, DateTime $date_end_object = NULL, array $Options = array()) {
        global $workforce;
        if (NULL === $date_end_object) {
            $date_end_object = $date_start_object;
        }
        if (array() !== $Options and ! is_array($Options)) {
            $Options = (array) $Options;
        }
        $Roster = array();
        for ($date_object = clone $date_start_object; $date_object <= $date_end_object; $date_object->add(new DateInterval('P1D'))) {
            $date_sql = $date_object->format('Y-m-d');
            $Absentees = absence::read_absentees_from_database($date_sql);
            $weekday = $date_object->format('N');
            $sql_query = "SELECT * FROM `principle_roster` "
                    . " WHERE `weekday` = :weekday "
                    . " AND `branch_id` = :branch_id "
                    . " AND `alternation_id` = :alternation_id "
                    . " AND (`valid_from` <= :date1 OR ISNULL(`valid_from`))"
                    . " AND (`valid_until` >= :date2 OR ISNULL(`valid_until`)) "
                    . " ORDER BY `duty_start` + `duty_end`, `duty_start`";
            $alternation_id = alternating_week::get_alternating_week_for_date($date_object);
            $result = database_wrapper::instance()->run($sql_query, array(
                'branch_id' => $branch_id,
                'weekday' => $weekday,
                'alternation_id' => $alternation_id,
                'date1' => $date_sql,
                'date2' => $date_sql,
            ));
            $roster_row_iterator = 0;
            while ($row = $result->fetch(PDO::FETCH_OBJ)) {
                if (in_array(self::OPTION_CONTINUE_ON_ABSENCE, $Options) and isset($Absentees[$row->employee_id])) {
                    /*
                     * Absent employees will be excluded, if an actual roster is built.
                     */
                    continue 1;
                }
                if (isset($workforce->List_of_employees) AND array_search($row->employee_id, array_keys($workforce->List_of_employees)) === false) {
                    /*
                     * Exclude non-existent employees from the principle roster:
                     */
                    continue 1;
                }
                $Roster[$date_object->format('U')][$roster_row_iterator] = new roster_item($date_sql, (int) $row->employee_id, $row->branch_id, $row->duty_start, $row->duty_end, $row->break_start, $row->break_end);
                $roster_row_iterator++;
            }
        }
        self::determine_lunch_breaks($Roster);
        return $Roster;
    }

    public static function get_list_of_change_dates(int $employee_id, int $alternation_id) {
        $List_of_change_dates = array();
        /*
         * Define a valid_from for all the entries in the database. 1970-01-01
         * Read all the valid_until values.
         * Make an array of those values.
         * Make a list of weeks.
         * Make an array of Rosters in those weeks, with the valid_from as key
         */
        $sql_query = "SELECT DISTINCT `valid_from` "
                . " FROM `principle_roster` "
                . " WHERE `employee_id` = :employee_id AND `alternation_id` = :alternation_id ORDER BY `valid_from`;";
        $result = database_wrapper::instance()->run($sql_query, array(
            'employee_id' => $employee_id,
            'alternation_id' => $alternation_id,
        ));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $date_of_change = $row->valid_from;
            if (NULL === $date_of_change) {
                /*
                 * If there is no known start of validity for one entry, we just take the start of employment for this employee.
                 * CAVE: This might not be correct if the employee started working after this change.
                 *   This may happen if an old employee_id is given to someone new.
                 * TODO: Fix this maybe?
                 */
                $date_of_change = workforce::get_first_start_of_employment($employee_id);
            }
            $List_of_change_dates[] = new DateTime($date_of_change);
        }
        if (array() === $List_of_change_dates) {
            /*
             * There has to be at least one entry in the list:
             */
            $date_of_change = workforce::get_first_start_of_employment($employee_id);
            if (FALSE !== $date_of_change) {
                return array(new DateTime($date_of_change));
            }
            return array(new DateTime('1970-01-01'));
        }
        return $List_of_change_dates;
    }

    public static function read_all_principle_employee_rosters_from_database(int $employee_id, int $alternation_id) {
        $List_of_change_dates = self::get_list_of_change_dates();
        foreach ($List_of_change_dates as $date_start_object) {
            $date_end_object = clone $date_start_object;
            $date_end_object->add(new DateInterval('P6D'));
            $List_of_principle_employee_rosters[$date_start_object->format('Y-m-d')] = self::read_current_principle_employee_roster_from_database($employee_id, $date_start_object, $date_end_object);
        }
        return $List_of_principle_employee_rosters;
    }

    public static function read_current_principle_employee_roster_from_database(int $employee_id, DateTime $date_start_object, DateTime $date_end_object = NULL) {
        if ($date_start_object > $date_end_object) {
            throw new Exception('The start cannot be before the end.');
        }
        if (NULL === $date_end_object) {
            $date_end_object = $date_start_object;
        }
        $Roster = array();
        for ($date_object = clone $date_start_object; $date_object <= $date_end_object; $date_object->add(new DateInterval('P1D'))) {
            $date_sql = $date_object->format('Y-m-d');
            $weekday = $date_object->format('w');
            $alternation_id = alternating_week::get_alternating_week_for_date($date_object);
            $sql_query = "SELECT * FROM `principle_roster` "
                    . " WHERE `weekday` = :weekday "
                    . " AND `employee_id` = :employee_id "
                    . " AND `alternation_id` = :alternation_id "
                    . " AND (`valid_from` <= :date1 OR ISNULL(`valid_from`))"
                    . " AND (`valid_until` >= :date2 OR ISNULL(`valid_until`)) "
                    . " ORDER BY `duty_start` + `duty_end`, `duty_start`";

            $result = database_wrapper::instance()->run($sql_query, array(
                'weekday' => $weekday,
                'employee_id' => $employee_id,
                'alternation_id' => $alternation_id,
                'date1' => $date_sql,
                'date2' => $date_sql,
            ));
            $roster_row_iterator = 0;
            while ($row = $result->fetch(PDO::FETCH_OBJ)) {
                $Roster[$date_object->format('U')][$roster_row_iterator] = new roster_item($date_sql, (int) $row->employee_id, $row->branch_id, $row->duty_start, $row->duty_end, $row->break_start, $row->break_end);
                $roster_row_iterator++;
            }
            if (0 === $roster_row_iterator) {
                /*
                 * If there is no roster on a given day, we insert one empty roster_item.
                 * This is important for weekly views. Non existent rosters would misalign the tables.
                 */
                $workforce = new workforce($date_object->format('Y-m-d'));
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

    public static function get_working_hours_should(DateTime $date_object, int $employee_id) {
        $working_hours_should = NULL;
        $sql_query = "SELECT SUM(`working_hours`) as `working_hours_should` FROM `principle_roster` WHERE `weekday` = :weekday AND `employee_id` = :employee_id AND `alternation_id` = :alternation_id";
        $alternation_id = alternating_week::get_alternating_week_for_date($date_object);
        $result = database_wrapper::instance()->run($sql_query, array(
            'weekday' => $date_object->format('w'),
            'employee_id' => $employee_id,
            'alternation_id' => $alternation_id,
        ));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $working_hours_should = $row->working_hours_should;
        }
        return $working_hours_should;
    }

    public static function get_working_week_days($employee_id) {
        $sql_query = "SELECT `employee_id`, Count(DISTINCT `weekday`) as `working_week_days`, Count(DISTINCT `alternation_id`) as `alternations` FROM `principle_roster` WHERE `employee_id` = :employee_id";
        $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            if (0 != $row->alternations) {
                return (int) $row->working_week_days / $row->alternations;
            }
            return (int) $row->working_week_days;
        }
        return NULL;
    }

    public static function guess_opening_times(DateTime $date_object, int $branch_id) {
        $Opening_times = array();
        $sql_query = "SELECT min(`duty_start`) as `day_opening_start`, max(`duty_end`) as `day_opening_end` FROM `principle_roster` WHERE `weekday` = :weekday AND `branch_id` = :branch_id";
        $result = database_wrapper::instance()->run($sql_query, array(
            'branch_id' => $branch_id,
            'weekday' => $date_object->format('N'),
        ));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $Opening_times['day_opening_start'] = $row->day_opening_start;
            $Opening_times['day_opening_end'] = $row->day_opening_end;
        }
        return $Opening_times;
    }

    public static function remove_changed_employee_entries_from_database($branch_id, $Employee_id_list) {
        throw new Exception('We will probably not remove any entries anymore. Just invalidate them!');
        foreach ($Employee_id_list as $date_unix => $Employee_id_list_day) {
            $weekday = date('w', $date_unix);
            $date_object = new DateTime();
            $date_object->setTimestamp($date_unix);
            $alternation_id = alternating_week::get_alternating_week_for_date($date_object);
            if (!empty($Employee_id_list_day)) {
                list($IN_placeholder, $IN_employees_list) = database_wrapper::create_placeholder_for_mysql_IN_function($Employee_id_list_day, TRUE);
                $sql_query = "DELETE FROM `principle_roster`"
                        . " WHERE `weekday` = :weekday"
                        . " AND `employee_id` IN ($IN_placeholder)"
                        . " AND `branch_id` = :branch_id"
                        . " AND `alternation_id` = :alternation_id";
                database_wrapper::instance()->run($sql_query, array_merge($IN_employees_list, array(
                    'weekday' => $weekday,
                    'branch_id' => $branch_id,
                    'alternation_id' => $alternation_id,
                )));
            }
        }
    }

    public static function insert_changed_entries_into_database(array $Roster, array $Changed_roster_employee_id_list, string $valid_from) {
        foreach ($Roster as $date_unix => $Roster_day_array) {
            if (!isset($Changed_roster_employee_id_list[$date_unix])) {
                /* There are no changes. */
                continue;
            }
            $date_object = new DateTime;
            $date_object->setTimestamp($date_unix);
            $alternation_id = alternating_week::get_alternating_week_for_date($date_object);

            foreach ($Roster_day_array as $roster_item) {
                if (!in_array($roster_item->employee_id, $Changed_roster_employee_id_list[$date_unix])) {
                    continue;
                }
                if (NULL === $roster_item->employee_id) {
                    continue;
                }
                database_wrapper::instance()->beginTransaction();

                /*
                 * TODO: Check if an existing entry has to be overwritten:
                 */
                $primary_key_of_existing_entry = self::find_existing_entry_in_db($roster_item, $alternation_id, $valid_from);
                if (FALSE !== $primary_key_of_existing_entry) {
                    self::update_old_entry_into_db($roster_item, $alternation_id, $valid_from, $primary_key_of_existing_entry);
                } else {
                    self::insert_new_entry_into_db($roster_item, $alternation_id, $valid_from);
                }
                self::update_valid_until_values($roster_item->employee_id, $roster_item->branch_id, date('w', $roster_item->date_unix), $alternation_id);
                database_wrapper::instance()->commit();
            }
        }
    }

    private static function update_valid_until_values(int $employee_id, int $branch_id, int $weekday, int $alternation_id) {
        $sql_query = "SELECT * FROM `principle_roster`"
                . " WHERE "
                . " `employee_id` = :employee_id AND "
                . " `branch_id` = :branch_id AND "
                . " `weekday` = :weekday AND "
                . " `alternation_id` = :alternation_id"
                . " ORDER BY ISNULL(`valid_from`) ASC, `valid_from` DESC";
        $result = database_wrapper::instance()->run($sql_query, array(
            'employee_id' => $employee_id,
            'branch_id' => $branch_id,
            'weekday' => $weekday,
            'alternation_id' => $alternation_id,
        ));
        $valid_until_date = NULL;
        $valid_until_sql = $valid_until_date;
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $table_row_identifier = (int) $row->primary_key;
            if (NULL !== $valid_until_date) {
                $valid_until_sql = $valid_until_date->format('Y-m-d');
            }
            $update_result = self::write_valid_until_value_into_db($table_row_identifier, $valid_until_sql);
            if (FALSE === $update_result) {
                database_wrapper::instance()->rollBack();
            }

            /*
             * now set the $valid_until_date for the next iteration:
             */
            $valid_from_date = new DateTime($row->valid_from);
            $valid_until_date = clone $valid_from_date;
            $valid_until_date->sub(new DateInterval('P1D'));
        }
    }

    private static function update_old_entry_into_db(roster_item $roster_item, int $alternation_id, string $valid_from, int $primary_key) {
        $sql_query = "UPDATE `principle_roster` "
                . " SET `employee_id` = :employee_id, "
                . " `branch_id` = :branch_id, "
                . " `weekday` = :weekday, "
                . " `alternation_id` = :alternation_id, "
                . " `duty_start` = :duty_start, `duty_end` = :duty_end, `break_start` = :break_start, `break_end` = :break_end, `working_hours` = :working_hours, "
                . " `valid_from` = :valid_from, "
                . " `comment` = :comment"
                . " WHERE `primary_key` = :primary_key"
                . ";";
        $result = database_wrapper::instance()->run($sql_query, array(
            'employee_id' => $roster_item->employee_id,
            'weekday' => date('w', $roster_item->date_unix),
            'alternation_id' => $alternation_id,
            'duty_start' => $roster_item->duty_start_sql,
            'duty_end' => $roster_item->duty_end_sql,
            'break_start' => $roster_item->break_start_sql,
            'break_end' => $roster_item->break_end_sql,
            'working_hours' => $roster_item->working_hours,
            'branch_id' => $roster_item->branch_id,
            'valid_from' => $valid_from,
            'comment' => $roster_item->comment,
            'primary_key' => $primary_key,
        ));

        return '00000' === $result->errorCode();
    }

    private static function find_existing_entry_in_db(roster_item $roster_item, int $alternation_id, string $valid_from) {
        $sql_query = "SELECT * FROM `principle_roster` "
                . " WHERE "
                . " `employee_id` = :employee_id AND "
                . " `branch_id` = :branch_id AND "
                . " `alternation_id` = :alternation_id AND "
                . " `weekday` = :weekday AND "
                . " `valid_from` = :valid_from "
                . "";

        $result = database_wrapper::instance()->run($sql_query, array(
            'employee_id' => $roster_item->employee_id,
            'weekday' => date('w', $roster_item->date_unix),
            'alternation_id' => $alternation_id,
            'branch_id' => $roster_item->branch_id,
            'valid_from' => $valid_from,
        ));

        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            return (int) $row->primary_key;
        }
        return FALSE;
    }

    private static function insert_new_entry_into_db(roster_item $roster_item, int $alternation_id, string $valid_from) {
        $sql_query = "INSERT INTO `principle_roster` "
                . " SET `employee_id` = :employee_id, "
                . " `branch_id` = :branch_id, "
                . " `weekday` = :weekday, "
                . " `alternation_id` = :alternation_id, "
                . " `duty_start` = :duty_start, `duty_end` = :duty_end, `break_start` = :break_start, `break_end` = :break_end, `working_hours` = :working_hours, "
                . " `valid_from` = :valid_from, "
                . " `comment` = :comment"
                . ";";
        $result = database_wrapper::instance()->run($sql_query, array(
            'employee_id' => $roster_item->employee_id,
            'weekday' => date('w', $roster_item->date_unix),
            'alternation_id' => $alternation_id,
            'duty_start' => $roster_item->duty_start_sql,
            'duty_end' => $roster_item->duty_end_sql,
            'break_start' => $roster_item->break_start_sql,
            'break_end' => $roster_item->break_end_sql,
            'working_hours' => $roster_item->working_hours,
            'branch_id' => $roster_item->branch_id,
            'valid_from' => $valid_from,
            'comment' => $roster_item->comment,
        ));

        return '00000' === $result->errorCode();
    }

    private static function write_valid_until_value_into_db(int $table_row_identifier, string $valid_until = NULL) {
        $sql_query = "UPDATE `principle_roster` SET `valid_until` = :valid_until"
                . " WHERE `primary_key` = :primary_key";
        $result = database_wrapper::instance()->run($sql_query, array(
            'valid_until' => $valid_until,
            'primary_key' => $table_row_identifier,
        ));
        return '00000' === $result->errorCode();
    }

}
