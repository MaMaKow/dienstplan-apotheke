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

    public static function read_principle_roster_from_database(int $branch_id, DateTime $date_start_object, DateTime $date_end_object = NULL, array $Options = array()) {
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
                    . " ORDER BY `duty_start` + `duty_end`, `duty_start`";
            $alternation_id = alternating_week::get_alternating_week_for_date($date_object);
            $result = database_wrapper::instance()->run($sql_query, array(
                'branch_id' => $branch_id,
                'weekday' => $weekday,
                'alternation_id' => $alternation_id,
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

    public static function read_principle_employee_roster_from_database(int $employee_id, DateTime $date_start_object, DateTime $date_end_object = NULL) {
        if ($date_start_object > $date_end_object) {
            throw new Exception('The start cannot be before the end.');
        }
        global $workforce;
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
                    . " ORDER BY `duty_start` + `duty_end`, `duty_start`";

            $result = database_wrapper::instance()->run($sql_query, array(
                'weekday' => $weekday,
                'employee_id' => $employee_id,
                'alternation_id' => $alternation_id,
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

    public static function insert_changed_entries_into_database($Roster, $Changed_roster_employee_id_list) {
        foreach ($Roster as $date_unix => $Roster_day_array) {
            if (!isset($Changed_roster_employee_id_list[$date_unix])) {
                /* There are no changes. */
                continue;
            }
            $date_object = new DateTime;
            $date_object->setTimestamp($date_unix);
            $alternation_id = alternating_week::get_alternating_week_for_date($date_object);

            foreach ($Roster_day_array as $roster_row_object) {
                if (!in_array($roster_row_object->employee_id, $Changed_roster_employee_id_list[$date_unix])) {
                    continue;
                }
                if (NULL === $roster_row_object->employee_id) {
                    continue;
                }
                $sql_query = "INSERT INTO `principle_roster` "
                        . " SET `employee_id` = :employee_id, "
                        . " `branch_id` = :branch_id, "
                        . " `weekday` = :weekday, "
                        . " `alternation_id` = :alternation_id, "
                        . " `duty_start` = :duty_start, `duty_end` = :duty_end, `break_start` = :break_start, `break_end` = :break_end, `working_hours` = :working_hours, "
                        . " `comment` = :comment"
                        . " ON DUPLICATE KEY UPDATE "
                        . " `employee_id` = :employee_id2, "
                        . " `branch_id` = :branch_id2, "
                        . " `weekday` = :weekday2, "
                        . " `alternation_id` = :alternation_id2, "
                        . " `duty_start` = :duty_start2, `duty_end` = :duty_end2, `break_start` = :break_start2, `break_end` = :break_end2, "
                        . " `working_hours` = :working_hours2, "
                        . " `comment` = :comment2"
                        . ";";
                database_wrapper::instance()->run($sql_query, array(
                    'employee_id' => $roster_row_object->employee_id,
                    'weekday' => date('w', $roster_row_object->date_unix),
                    'alternation_id' => $alternation_id,
                    'duty_start' => $roster_row_object->duty_start_sql,
                    'duty_end' => $roster_row_object->duty_end_sql,
                    'break_start' => $roster_row_object->break_start_sql,
                    'break_end' => $roster_row_object->break_end_sql,
                    'working_hours' => $roster_row_object->working_hours,
                    'branch_id' => $roster_row_object->branch_id,
                    'comment' => $roster_row_object->comment,
                    'employee_id2' => $roster_row_object->employee_id,
                    'weekday2' => date('w', $roster_row_object->date_unix),
                    'alternation_id2' => $alternation_id,
                    'duty_start2' => $roster_row_object->duty_start_sql,
                    'duty_end2' => $roster_row_object->duty_end_sql,
                    'break_start2' => $roster_row_object->break_start_sql,
                    'break_end2' => $roster_row_object->break_end_sql,
                    'working_hours2' => $roster_row_object->working_hours,
                    'branch_id2' => $roster_row_object->branch_id,
                    'comment2' => $roster_row_object->comment,
                ));
            }
        }
    }

    public static function write_employee_user_input_to_database(int $employee_id, array $Principle_employee_roster_new) {
        database_wrapper::instance()->beginTransaction();
        foreach (array_keys($Principle_employee_roster_new) as $date_unix) {
            $date_object = new DateTime;
            $date_object->setTimestamp($date_unix);
            $alternation_id = alternating_week::get_alternating_week_for_date($date_object);
            $sql_query = "DELETE FROM `principle_roster` WHERE `employee_id` = :employee_id AND `alternation_id` = :alternation_id";
            database_wrapper::instance()->run($sql_query, array(
                'employee_id' => $employee_id,
                'alternation_id' => $alternation_id,
            ));
            /*
             * The $alternation_id will be used in the next foreach loop.
             * Therefore we store it here for further use:
             */
            $List_of_alternation_ids[$date_unix] = $alternation_id;
        }
        foreach ($Principle_employee_roster_new as $date_unix => $Principle_employee_roster_new_day_array) {
            foreach ($Principle_employee_roster_new_day_array as $principle_employee_roster_new_object) {
                if (NULL === $principle_employee_roster_new_object->employee_id) {
                    /*
                     * Just an empty row.
                     */
                    continue;
                }
                if ($employee_id != $principle_employee_roster_new_object->employee_id) {
                    /*
                     * The input must not contain any other employee.
                     * We roll back the transaction here.
                     */
                    database_wrapper::instance()->rollBack();
                    throw new Exception('$employee_id != $principle_employee_roster_new_object->employee_id '
                    . "in " . __METHOD__ . " "
                    . $employee_id . " != " . $principle_employee_roster_new_object->employee_id);
                    /* return FALSE; */
                }
                if (NULL === $principle_employee_roster_new_object->duty_start_sql) {
                    /*
                     * The input must contain at least a starting time and and end of duty.
                     */
                    continue;
                }
                if (NULL === $principle_employee_roster_new_object->duty_end_sql) {
                    /*
                     * The input must contain at least a starting time and and end of duty.
                     */
                    continue;
                }
                $sql_query = "INSERT INTO `principle_roster` "
                        . " SET `employee_id` = :employee_id, "
                        . " `weekday` = :weekday, "
                        . " `branch_id` = :branch_id, "
                        . " `alternation_id` = :alternation_id, "
                        . " `duty_start` = :duty_start, `duty_end` = :duty_end, `break_start` = :break_start, `:break_end` = :break_end, "
                        . " `working_hours` = :working_hours, "
                        . " `comment` = :comment"
                        . ";";
                database_wrapper::instance()->run($sql_query, array(
                    'employee_id' => $principle_employee_roster_new_object->employee_id,
                    'alternation_id' => $List_of_alternation_ids[$date_unix],
                    'weekday' => date('w', $principle_employee_roster_new_object->date_unix),
                    'duty_start' => $principle_employee_roster_new_object->duty_start_sql,
                    'duty_end' => $principle_employee_roster_new_object->duty_end_sql,
                    'break_start' => $principle_employee_roster_new_object->break_start_sql,
                    'break_end' => $principle_employee_roster_new_object->break_end_sql,
                    'working_hours' => $principle_employee_roster_new_object->working_hours,
                    'branch_id' => $principle_employee_roster_new_object->branch_id,
                    'comment' => $principle_employee_roster_new_object->comment,
                ));
            }
        }
        database_wrapper::instance()->commit();
    }

}
