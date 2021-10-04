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

    public $alternating_week_id;

    public static function read_current_principle_roster_from_database(int $branch_id, DateTime $date_start_object, DateTime $date_end_object = NULL, array $Options = array()) {
        if (NULL === $date_end_object) {
            $date_end_object = $date_start_object;
        }
        $workforce = new workforce($date_start_object->format('Y-m-d'), $date_end_object->format('Y-m-d'));
        if (array() !== $Options and!is_array($Options)) {
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
                    . " AND `alternating_week_id` = :alternating_week_id "
                    . " ORDER BY `duty_start` + `duty_end`, `duty_start`";
            $alternating_week_id = alternating_week::get_alternating_week_for_date($date_object);
            $result = database_wrapper::instance()->run($sql_query, array(
                'weekday' => $weekday,
                'branch_id' => $branch_id,
                'alternating_week_id' => $alternating_week_id,
            ));
            $roster_row_iterator = 0;
            while ($row = $result->fetch(PDO::FETCH_OBJ)) {
                if (in_array(self::OPTION_CONTINUE_ON_ABSENCE, $Options) and isset($Absentees[$row->employee_id])) {
                    /*
                     * Absent employees will be excluded, if an actual roster is built.
                     */
                    continue 1;
                }
                if (isset($workforce->List_of_employees) AND false === array_search($row->employee_id, array_keys($workforce->List_of_employees))) {
                    /*
                     * Exclude non-existent employees from the principle roster:
                     */
                    continue 1;
                }
                try {
                    $Roster[$date_object->format('U')][$roster_row_iterator] = new \principle_roster_item((int) $row->primary_key, $date_sql, (int) $row->employee_id, $row->branch_id, $row->duty_start, $row->duty_end, $row->break_start, $row->break_end, $row->comment);
                } catch (Exception $exception) {
                    error_log($exception->getTraceAsString());
                    throw new Exception('There was an error while reading the current principle roster from the database. Please see the error log file for details!');
                }
                $roster_row_iterator++;
            }
        }
        /*
         * TODO: Build a function instead,
         *   that gives a warning or information, if a required lunch break is not scheduled.
         */
        //self::determine_lunch_breaks($Roster);
        return $Roster;
    }

    public static function read_current_principle_employee_roster_from_database(int $employee_id, DateTime $date_start_object, DateTime $date_end_object = NULL) {
        if (NULL === $date_end_object) {
            $date_end_object = clone $date_start_object;
        }
        if ($date_start_object > $date_end_object) {
            throw new Exception('The start cannot be before the end.');
        }
        $Roster = array();
        for ($date_object = clone $date_start_object; $date_object <= $date_end_object; $date_object->add(new DateInterval('P1D'))) {
            $date_sql = $date_object->format('Y-m-d');
            $weekday = $date_object->format('w');
            $alternating_week_id = alternating_week::get_alternating_week_for_date($date_object);
            $sql_query = "SELECT * FROM `principle_roster` "
                    . " WHERE `weekday` = :weekday "
                    . " AND `employee_id` = :employee_id "
                    . " AND `alternating_week_id` = :alternating_week_id "
                    . " ORDER BY `duty_start` + `duty_end`, `duty_start`";

            $result = database_wrapper::instance()->run($sql_query, array(
                'weekday' => $weekday,
                'employee_id' => $employee_id,
                'alternating_week_id' => $alternating_week_id,
            ));
            $roster_row_iterator = 0;
            while ($row = $result->fetch(PDO::FETCH_OBJ)) {
                try {
                    $Roster[$date_object->format('U')][$roster_row_iterator] = new \principle_roster_item((int) $row->primary_key, $date_sql, (int) $row->employee_id, $row->branch_id, $row->duty_start, $row->duty_end, $row->break_start, $row->break_end, $row->comment);
                    $roster_row_iterator++;
                } catch (Exception $exception) {
                    error_log($exception->getTraceAsString());
                    throw new Exception('There was an error while reading the current principle employee roster from the database. Please see the error log file for details!');
                }
            }
            if (0 === $roster_row_iterator) {
                /*
                 * If there is no roster on a given day, we insert one empty roster_item.
                 * This is important for weekly views. Non existent rosters would misalign the tables.
                 */
                $workforce = new workforce($date_object->format('Y-m-d'));
                if (isset($workforce->List_of_employees[$employee_id])) {
                    $branch_id = $workforce->List_of_employees[$employee_id]->principle_branch_id;
                } else {
                    /*
                     * In case, the employee does not exist on this day we fall back to using the first branch.
                     * This can happen if an employee will start shortly, but not on a monday.
                     */
                    $network_of_branch_offices = new \PDR\Pharmacy\NetworkOfBranchOffices();
                    $List_of_branch_objects = $network_of_branch_offices->get_list_of_branch_objects();
                    $branch_id = min(array_keys($List_of_branch_objects));
                }
                $Roster[$date_object->format('U')][$roster_row_iterator] = new roster_item_empty($date_sql, $branch_id);
            }
        }
        return $Roster;
    }

    /**
     * This function determines the optimal lunch breaks.
     *
     * It considers the principle lunch breaks.
     * @deprecated No longer used by internal code and not recommended.
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
                if (!empty($workforce->List_of_employees[$employee_id]->lunch_break_minutes) AND!($roster_item_object->break_start_int > 0) AND!($roster_item_object->break_end_int > 0)) {
                    /* <p lang="de">Zunächst berechnen wir die Stunden, damit wir wissen, wer überhaupt eine Mittagspause bekommt.</p> */
                    $duty_seconds_with_a_break = $roster_item_object->duty_end_int - $roster_item_object->duty_start_int - $workforce->List_of_employees[$employee_id]->lunch_break_minutes * 60;
                    if ($duty_seconds_with_a_break >= 6 * 3600) {
                        /* <p lang="de">Wer länger als 6 Stunden Arbeitszeit hat, bekommt eine Mittagspause.</p> */
                        $lunch_break_end = $lunch_break_start + $workforce->List_of_employees[$employee_id]->lunch_break_minutes * 60;
                        for ($number_of_trys = 0; $number_of_trys < 3; $number_of_trys++) {
                            if (FALSE !== array_search($lunch_break_start, $break_start_taken_int) OR FALSE !== array_search($lunch_break_end, $break_end_taken_int)) {
                                /* <p lang="de">Zu diesem Zeitpunkt startet schon jemand sein Mittag. Wir warten 30 Minuten (1800 Sekunden)</p> */
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
                } elseif (!empty($employee_id) AND!empty($roster_item_object->break_start_int) AND empty($roster_item_object->break_end_int)) {
                    $roster_item_object->break_end_int = $roster_item_object->break_start_int + $workforce->List_of_employees[$employee_id]->lunch_break_minutes;
                    $roster_item_object->break_end_sql = roster_item::format_time_integer_to_string($roster_item_object->break_end_int);
                } elseif (!empty($employee_id) AND empty($roster_item_object->break_start_int) AND!empty($roster_item_object->break_end_int)) {
                    $roster_item_object->break_start_int = $roster_item_object->break_end_int - $workforce->List_of_employees[$employee_id]->lunch_break_minutes;
                    $roster_item_object->break_start_sql = roster_item::format_time_integer_to_string($roster_item_object->break_start_int);
                }
            }
        }
        return NULL;
    }

    public static function get_working_hours_should(DateTime $date_object, int $employee_id) {
        $working_hours_should = NULL;
        $sql_query = "SELECT SUM(`working_hours`) as `working_hours_should` FROM `principle_roster` WHERE `weekday` = :weekday AND `employee_id` = :employee_id AND `alternating_week_id` = :alternating_week_id";
        $alternating_week_id = alternating_week::get_alternating_week_for_date($date_object);
        $result = database_wrapper::instance()->run($sql_query, array(
            'weekday' => $date_object->format('w'),
            'employee_id' => $employee_id,
            'alternating_week_id' => $alternating_week_id,
        ));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $working_hours_should = $row->working_hours_should;
        }
        return $working_hours_should;
    }

    public static function get_working_week_days($employee_id) {
        /*
         * TODO: Die Funktion könnte in die employee Klasse übergeben werden, wenn diese einen Zugriff auf den Grundplan hätte.
         * Sie wäre dort vermutlich private und nicht static.
         * Ein employee Objekt enthät bereits einen Grundplan. Allerdings ist die vorhandene Instanz nur für eine Alternierung vorhanden.
         * Sonderfall: Wenn jemand im Wechsel 2 und 3 Tage pro Woche arbeitet,
         *   so kann nur hier mit Zugriff auf den kompletten Grundplan und den kompletten Alternierungen auch der korrekte Wert von 2,5 gefunden werden.
         */
        $sql_query = "SELECT COUNT(*) AS `working_week_days`, COUNT(DISTINCT `alternating_week_id`) AS `alternations` FROM (SELECT `alternating_week_id` FROM `principle_roster` WHERE `employee_id` = :employee_id GROUP BY `alternating_week_id`, `weekday`) AS q1;";
        $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            if (0 != $row->alternations) {
                return (float) $row->working_week_days / $row->alternations;
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
            $Opening_times['day_opening_start'] = roster_item::convert_time_to_seconds($row->day_opening_start);
            $Opening_times['day_opening_end'] = roster_item::convert_time_to_seconds($row->day_opening_end);
        }
        return $Opening_times;
    }

    /**
     *
     * @param array $List_of_deleted_roster_primary_keys
     * @return void
     */
    public static function invalidate_removed_entries_in_database(array $List_of_deleted_roster_primary_keys) {
        $sql_query_insert = "INSERT INTO `principle_roster_archive` (SELECT *, NOW() FROM `principle_roster` WHERE `primary_key` = :primary_key)";
        $sql_query_delete = "DELETE FROM `principle_roster` WHERE `primary_key` = :primary_key";
        database_wrapper::instance()->beginTransaction();
        $statement_insert = database_wrapper::instance()->prepare($sql_query_insert);
        $statement_delete = database_wrapper::instance()->prepare($sql_query_delete);
        foreach ($List_of_deleted_roster_primary_keys as $primary_key) {
            $arguments = array('primary_key' => $primary_key);
            $statement_delete->execute($arguments);
            $statement_insert->execute($arguments);
        }
        database_wrapper::instance()->commit();
        return;
    }

    public static function insert_changed_entries_into_database(array $Roster, array $Changed_roster_employee_id_list) {
        foreach ($Roster as $date_unix => $Roster_day_array) {
            if (!isset($Changed_roster_employee_id_list[$date_unix])) {
                /**
                 * There are no changes.
                 */
                continue;
            }
            /**
             * There is some change on $date_unix
             */
            $date_object = new DateTime;
            $date_object->setTimestamp($date_unix);
            $alternating_week_id = alternating_week::get_alternating_week_for_date($date_object);

            foreach ($Roster_day_array as $roster_item) {
                if (!in_array($roster_item->employee_id, $Changed_roster_employee_id_list[$date_unix])) {
                    /**
                     * <p lang=de>Dieser Mitarbeiter wurde nicht geändert.</p>
                     */
                    continue;
                }
                if (NULL === $roster_item->employee_id) {
                    /**
                     * <p lang=de>Dies ist der Pseudomitarbeiter.
                     * Er wird nur aus optischen/technischen Gründen mitgeführt.
                     * </p>
                     */
                    continue;
                }
                database_wrapper::instance()->beginTransaction();
                /*
                 * TODO: Do we also have to delete entries in some cases?
                 */
                $primary_key_of_existing_entry = self::find_existing_entry_in_db($roster_item, $alternating_week_id);
                if (FALSE !== $primary_key_of_existing_entry) {
                    /**
                     * <p lang=de>Diesen Eintrag gibt es schon so ähnlich:</p>
                     */
                    self::update_old_entry_into_db($roster_item, $alternating_week_id, $primary_key_of_existing_entry);
                } else {
                    /**
                     * <p lang=de>Dieser Eintrag ist komplett neu:</p>
                     */
                    self::insert_new_entry_into_db($roster_item, $alternating_week_id);
                }
                database_wrapper::instance()->commit();
            }
        }
    }

    /**
     *
     * @param roster_item $roster_item
     * @param int $alternating_week_id
     * @param int $primary_key
     * @return type<p>TODO: Es muss immer erst einmal der alte Eintrag archiviert werden, bevor der neue gesetzt werden kann.</p>
     */
    private static function update_old_entry_into_db(roster_item $roster_item, int $alternating_week_id, int $primary_key) {
        $sql_query = "UPDATE `principle_roster` "
                . " SET `employee_id` = :employee_id, "
                . " `branch_id` = :branch_id, "
                . " `weekday` = :weekday, "
                . " `alternating_week_id` = :alternating_week_id, "
                . " `duty_start` = :duty_start, `duty_end` = :duty_end, `break_start` = :break_start, `break_end` = :break_end, `working_hours` = :working_hours, "
                . " `comment` = :comment"
                . " WHERE `primary_key` = :primary_key"
                . ";";
        $result = database_wrapper::instance()->run($sql_query, array(
            'employee_id' => $roster_item->employee_id,
            'weekday' => date('w', $roster_item->date_unix),
            'alternating_week_id' => $alternating_week_id,
            'duty_start' => $roster_item->duty_start_sql,
            'duty_end' => $roster_item->duty_end_sql,
            'break_start' => $roster_item->break_start_sql,
            'break_end' => $roster_item->break_end_sql,
            'working_hours' => $roster_item->working_hours,
            'branch_id' => $roster_item->branch_id,
            'comment' => $roster_item->comment,
            'primary_key' => $primary_key,
        ));

        return '00000' === $result->errorCode();
    }

    /**
     *
     * @param roster_item $roster_item
     * @param int $alternating_week_id
     * @return boolean
     * <p>TODO: Es sollte möglichst immer der primary_key übergeben werden.
     * Der sollte Bestandteil des roster_item werden!
     * $alternating_week_id sollte da auch rein. Im Zweifel null, wenn das feature nicht benutzt wird.
     * </p>
     */
    public static function find_existing_entry_in_db(roster_item $roster_item, int $alternating_week_id) {
        $sql_query = "SELECT * FROM `principle_roster` "
                . " WHERE "
                . " `employee_id` = :employee_id AND "
                . " `branch_id` = :branch_id AND "
                . " `alternating_week_id` = :alternating_week_id AND "
                . " `weekday` = :weekday ";

        $result = database_wrapper::instance()->run($sql_query, array(
            'employee_id' => $roster_item->employee_id,
            'branch_id' => $roster_item->branch_id,
            'alternating_week_id' => $alternating_week_id,
            'weekday' => date('w', $roster_item->date_unix),
        ));

        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            return (int) $row->primary_key;
        }
        return FALSE;
    }

    private static function insert_new_entry_into_db(roster_item $roster_item, int $alternating_week_id) {
        $sql_query = "INSERT INTO `principle_roster` "
                . " SET `employee_id` = :employee_id, "
                . " `branch_id` = :branch_id, "
                . " `weekday` = :weekday, "
                . " `alternating_week_id` = :alternating_week_id, "
                . " `duty_start` = :duty_start, `duty_end` = :duty_end, `break_start` = :break_start, `break_end` = :break_end, `working_hours` = :working_hours, "
                . " `comment` = :comment"
                . ";";
        $result = database_wrapper::instance()->run($sql_query, array(
            'employee_id' => $roster_item->employee_id,
            'weekday' => date('w', $roster_item->date_unix),
            'alternating_week_id' => $alternating_week_id,
            'duty_start' => $roster_item->duty_start_sql,
            'duty_end' => $roster_item->duty_end_sql,
            'break_start' => $roster_item->break_start_sql,
            'break_end' => $roster_item->break_end_sql,
            'working_hours' => $roster_item->working_hours,
            'branch_id' => $roster_item->branch_id,
            'comment' => $roster_item->comment,
        ));

        return '00000' === $result->errorCode();
    }

}
