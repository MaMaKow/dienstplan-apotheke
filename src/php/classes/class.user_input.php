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
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of class
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 * @todo Create a specific roster input class. Leave generic user input here
 */
abstract class user_input {

    const EXCEPTION_CODE_DUTY_START_INVALID = 1001;
    const EXCEPTION_CODE_DUTY_END_INVALID = 1002;

    /**
     *
     * @param type $variable_name as given from POST/GET/COOKIE
     * @param type $filter e.g. FILTER_SANITIZE_SPECIAL_CHARS
     * @param type $default_value
     * @return type
     */
    public static function get_variable_from_any_input($variable_name, $filter = FILTER_SANITIZE_SPECIAL_CHARS, $default_value = null) {
        $List_of_input_sources = array(INPUT_POST, INPUT_GET, INPUT_COOKIE);
        foreach ($List_of_input_sources as $input_source) {
            if (filter_has_var($input_source, $variable_name)) {
                return filter_input($input_source, $variable_name, $filter);
            }
        }
        return filter_var($default_value, $filter);
    }

    public static function convert_post_empty_to_php_null($value) {
        if ('' === $value) {
            return NULL;
        } else {
            return $value;
        }
    }

    public static function principle_roster_copy_from($principle_roster_copy_from) {
        global $session;
        $session->exit_on_missing_privilege(sessions::PRIVILEGE_CREATE_ROSTER);
        alternating_week::create_alternation_copy_from_principle_roster($principle_roster_copy_from);
    }

    public static function principle_roster_delete($principle_roster_delete) {
        global $session;
        $session->exit_on_missing_privilege(sessions::PRIVILEGE_CREATE_ROSTER);
        alternating_week::delete_alternation($principle_roster_delete);
    }

    /**
     *
     * @return boolean|\roster_item
     * @throws Exception
     */
    public static function get_Roster_from_POST_secure() {
        $Roster_from_post = filter_input(INPUT_POST, 'Roster', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY);
        $Roster = array();
        if (empty($Roster_from_post)) {
            return FALSE;
        }
        foreach ($Roster_from_post as $date_unix => $Roster_from_post_day_array) {
            if (!is_numeric($date_unix)) {
                throw new Exception('$date_unix must be an integer representing a unix timestamp!');
            }
            foreach ($Roster_from_post_day_array as $roster_row_iterator => $Roster_row_array) {
                if (!is_numeric($roster_row_iterator)) {
                    throw new Exception('$roster_row_iterator must be an integer!');
                }
                $date_sql = filter_var($Roster_row_array['date_sql'], FILTER_SANITIZE_SPECIAL_CHARS);
                $employee_key = filter_var($Roster_row_array['employee_key'], FILTER_SANITIZE_NUMBER_INT);
                $branch_id = filter_var($Roster_row_array['branch_id'], FILTER_SANITIZE_NUMBER_INT);
                $duty_start_sql = user_input::convert_post_empty_to_php_null(filter_var($Roster_row_array['duty_start_sql'], FILTER_SANITIZE_SPECIAL_CHARS));
                $duty_end_sql = user_input::convert_post_empty_to_php_null(filter_var($Roster_row_array['duty_end_sql'], FILTER_SANITIZE_SPECIAL_CHARS));
                $break_start_sql = user_input::convert_post_empty_to_php_null(filter_var($Roster_row_array['break_start_sql'], FILTER_SANITIZE_SPECIAL_CHARS));
                $break_end_sql = user_input::convert_post_empty_to_php_null(filter_var($Roster_row_array['break_end_sql'], FILTER_SANITIZE_SPECIAL_CHARS));
                $comment = user_input::convert_post_empty_to_php_null(filter_var($Roster_row_array['comment'], FILTER_SANITIZE_SPECIAL_CHARS));
                if (!is_numeric($branch_id)) {
                    throw new \Exception('$branch_id must be an integer!');
                }
                if (!self::isValidDateInFormat($date_sql, 'Y-m-d')) {
                    throw new \Exception('$date_sql must be a valid date in the format "Y-m-d"!');
                }
                if ('' === $employee_key) {
                    $Roster[$date_unix][$roster_row_iterator] = new \roster_item_empty($date_sql, $branch_id);
                    continue;
                }
                if (!self::isValidDateInFormat($duty_start_sql, 'H:i')) {
                    /**
                     * <p lang=de>
                     * Bei der Übertragung von roster items können leere Items übertragen werden.
                     * Diese haben aber IMMER eine leere employee_key.
                     * Daher wird diese Zeile in diesem Fall nicht erreicht.
                     * </p>
                     */
                    if (NULL === $duty_end_sql OR !self::isValidDateInFormat($duty_end_sql, 'H:i')) {
                        /**
                         * <p lang=de>
                         * Sowohl Beginn als auch Ende wurden als leer übertragen. Dieses roster item wurde also gelöscht.
                         * </p>
                         */
                        $Roster[$date_unix][$roster_row_iterator] = new roster_item_empty($date_sql, $branch_id);
                        continue;
                    }
                    throw new Exception('duty_start_sql MUST be a valid time!', SELF::EXCEPTION_CODE_DUTY_START_INVALID);
                }
                if (NULL === $duty_end_sql OR !self::isValidDateInFormat($duty_end_sql, 'H:i')) {
                    throw new Exception('duty_end_sql MUST be a valid time!', SELF::EXCEPTION_CODE_DUTY_END_INVALID);
                }
                $Roster[$date_unix][$roster_row_iterator] = new roster_item($date_sql, $employee_key, $branch_id, $duty_start_sql, $duty_end_sql, $break_start_sql, $break_end_sql, $comment);
                $Roster[$date_unix][$roster_row_iterator]->check_roster_item_sequence();
            }
        }
        return $Roster;
    }

    /**
     * Read a week of principle roster from the INPUT_POST.
     *
     * This function is only applicable for principle-roster-employee.php, where a whole week for a predefined employee is sent.
     * When using this function for rinciple-roster-day.php data loss might occur in situations, where duty_start is sent as "null".
     *
     * @return boolean|\roster_item
     * @throws Exception
     */
    public static function get_principle_employee_roster_from_POST_secure() {
        $Principle_roster_from_post = filter_input(INPUT_POST, 'Roster', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY);
        $Principle_roster = array();
        if (empty($Principle_roster_from_post)) {
            return FALSE;
        }
        if (7 !== sizeof($Principle_roster_from_post)) {
            throw new Exception("\$Principle_roster_from_post must contain a whole week sent from principle-roster-employee.php");
        }
        foreach ($Principle_roster_from_post as $date_unix => $Principle_roster_from_post_day_array) {
            if (!is_numeric($date_unix)) {
                throw new Exception('$date_unix must be an integer representing a unix timestamp!');
            }
            foreach ($Principle_roster_from_post_day_array as $roster_row_iterator => $Principle_roster_row_array) {
                if (!is_numeric($roster_row_iterator)) {
                    throw new Exception('$roster_row_iterator must be an integer!');
                }
                $date_sql = filter_var($Principle_roster_row_array['date_sql'], FILTER_SANITIZE_SPECIAL_CHARS);
                $employee_key = filter_var($Principle_roster_row_array['employee_key'], FILTER_SANITIZE_NUMBER_INT);
                $branch_id = filter_var($Principle_roster_row_array['branch_id'], FILTER_SANITIZE_NUMBER_INT);
                $duty_start_sql = user_input::convert_post_empty_to_php_null(filter_var($Principle_roster_row_array['duty_start_sql'], FILTER_SANITIZE_SPECIAL_CHARS));
                $duty_end_sql = user_input::convert_post_empty_to_php_null(filter_var($Principle_roster_row_array['duty_end_sql'], FILTER_SANITIZE_SPECIAL_CHARS));
                $break_start_sql = user_input::convert_post_empty_to_php_null(filter_var($Principle_roster_row_array['break_start_sql'], FILTER_SANITIZE_SPECIAL_CHARS));
                $break_end_sql = user_input::convert_post_empty_to_php_null(filter_var($Principle_roster_row_array['break_end_sql'], FILTER_SANITIZE_SPECIAL_CHARS));
                $comment = user_input::convert_post_empty_to_php_null(filter_var($Principle_roster_row_array['comment'], FILTER_SANITIZE_SPECIAL_CHARS));
                if (!is_numeric($branch_id)) {
                    throw new Exception('$branch_id must be an integer!');
                }
                if (!self::isValidDateInFormat($date_sql, 'Y-m-d')) {
                    throw new Exception('$date_sql must be a valid date in the format "Y-m-d"!');
                }
                /*
                  if ('' === $employee_key) {
                  $Principle_roster[$date_unix][$roster_row_iterator] = new roster_item_empty($date_sql, $branch_id);
                  continue;
                  }
                 *
                 */
                if (!self::isValidDateInFormat($duty_start_sql, 'H:i')) {
                    /**
                     * <p lang=de>
                     * Bei der Woche gibt es immer auch Tage, an denen nicht gearbeitet wird.
                     * Die überspringen wir hier.
                     * Außerdem gibt es Tage, an denen bisher gearbeitet wurde,
                     * an denen aber in Zukunft nicht mehr gearbeitet wird.
                     * Die werden als roster_item_empty markiert.
                     * </p>
                     */
                    if (isset($Principle_roster_row_array['primary_key'])) {
                        /**
                         * <p lang=de>
                         * Dienstzeit wurde gerade gelöscht:
                         * </p>
                         */
                        $primary_key = user_input::convert_post_empty_to_php_null(filter_var($Principle_roster_row_array['primary_key'], FILTER_SANITIZE_NUMBER_INT));
                        $Principle_roster[$date_unix][$roster_row_iterator] = new roster_item_empty($date_sql, $branch_id);
                    }
                    /**
                     * <p lang=de>
                     * An diesem Tag wird grundsätzlich nicht gearbeitet:
                     * </p>
                     */
                    continue;
                }
                if (NULL === $duty_end_sql OR !self::isValidDateInFormat($duty_end_sql, 'H:i')) {
                    throw new Exception('duty_end_sql MUST be a valid time!', SELF::EXCEPTION_CODE_DUTY_END_INVALID);
                }
                if (!isset($Principle_roster_row_array['primary_key']) or !is_numeric($Principle_roster_row_array['primary_key'])) {
                    /**
                     * <p lang=de>Wenn an einem Tag bisher kein Grundplan hinterlegt war,
                     *  dann wird dort auch kein primary key übertragen.
                     *  Wenn an diesem Tag nun aber doch ein Datum übergeben wird,
                     *  so müssen wir zunächst einen primary key vergeben.
                     *  Da die Datenbank final zu entscheiden hat,
                     *  welcher primary key zu verwenden ist, nehmen wir in PHP einfach NULL.</p>
                     */
                    $Principle_roster_row_array['primary_key'] = null;
                }
                $primary_key = user_input::convert_post_empty_to_php_null(filter_var($Principle_roster_row_array['primary_key'], FILTER_SANITIZE_NUMBER_INT));
                $Principle_roster[$date_unix][$roster_row_iterator] = new principle_roster_item($primary_key, $date_sql, $employee_key, $branch_id, $duty_start_sql, $duty_end_sql, $break_start_sql, $break_end_sql, $comment);
                $Principle_roster[$date_unix][$roster_row_iterator]->check_roster_item_sequence();
            }
        }
        return $Principle_roster;
    }

    public static function get_principle_roster_day_from_POST_secure() {
        $Principle_roster_from_post = filter_input(INPUT_POST, 'Roster', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY);
        $Principle_roster = array();
        if (empty($Principle_roster_from_post)) {
            return FALSE;
        }
        if (1 !== sizeof($Principle_roster_from_post)) {
            throw new Exception("\$Principle_roster_from_post must contain a whole week sent from principle-roster-employee.php");
        }
        foreach ($Principle_roster_from_post as $date_unix => $Principle_roster_day) {
            if (!is_numeric($date_unix)) {
                throw new Exception('$date_unix must be an integer representing a unix timestamp!');
            }
            foreach ($Principle_roster_day as $roster_row_iterator => $Principle_roster_row_array) {
                if (!is_numeric($roster_row_iterator)) {
                    throw new Exception('$roster_row_iterator must be an integer!');
                }
                $date_sql = filter_var($Principle_roster_row_array['date_sql'], FILTER_SANITIZE_SPECIAL_CHARS);
                $employee_key = user_input::convert_post_empty_to_php_null(filter_var($Principle_roster_row_array['employee_key'], FILTER_SANITIZE_NUMBER_INT));
                $branch_id = filter_var($Principle_roster_row_array['branch_id'], FILTER_SANITIZE_NUMBER_INT);
                $duty_start_sql = user_input::convert_post_empty_to_php_null(filter_var($Principle_roster_row_array['duty_start_sql'], FILTER_SANITIZE_SPECIAL_CHARS));
                $duty_end_sql = user_input::convert_post_empty_to_php_null(filter_var($Principle_roster_row_array['duty_end_sql'], FILTER_SANITIZE_SPECIAL_CHARS));
                $break_start_sql = user_input::convert_post_empty_to_php_null(filter_var($Principle_roster_row_array['break_start_sql'], FILTER_SANITIZE_SPECIAL_CHARS));
                $break_end_sql = user_input::convert_post_empty_to_php_null(filter_var($Principle_roster_row_array['break_end_sql'], FILTER_SANITIZE_SPECIAL_CHARS));
                $comment = user_input::convert_post_empty_to_php_null(filter_var($Principle_roster_row_array['comment'], FILTER_SANITIZE_SPECIAL_CHARS));
                if (!is_numeric($branch_id)) {
                    throw new Exception('$branch_id must be an integer!');
                }
                if (!self::isValidDateInFormat($date_sql, 'Y-m-d')) {
                    throw new Exception('$date_sql must be a valid date in the format "Y-m-d"!');
                }
                if (NULL === $employee_key OR !is_numeric($employee_key)) {
                    /**
                     * Es wurde kein Mitarbeiter übergeben.
                     * Dieses Item ist ungültig und wird übersprungen:
                     */
                    continue;
                }
                if (!self::isValidDateInFormat($duty_start_sql, 'H:i')) {
                    /**
                     * <p lang=de>
                     * Einträge sind nur gültig, wenn sie einen Start und ein Ende haben.
                     * Entweder hier wurde ein bereits existierender Eintrag gelöscht,
                     * oder es wurde ein neuer Eintrag nicht korrekt übergeben.
                     * </p>
                     */
                    if (isset($Principle_roster_row_array['primary_key']) and !self::isValidDateInFormat($duty_end_sql, 'H:i')) {
                        /**
                         * <p lang=de>
                         * Dienstzeit wurde gerade gelöscht.
                         * Wir erstellen einen leeren Eintrag ohne Inhalt:
                         * </p>
                         */
                        $primary_key = user_input::convert_post_empty_to_php_null(filter_var($Principle_roster_row_array['primary_key'], FILTER_SANITIZE_NUMBER_INT));
                        $Principle_roster[$date_unix][$roster_row_iterator] = new roster_item_empty($date_sql, $branch_id);
                        continue;
                    }
                    /**
                     * <p lang=de>
                     * Der Eintrag ist komplett ungültig.
                     * Wir geben das als Feher an den User zurück:
                     * </p>
                     */
                    throw new Exception('duty_start_sql MUST be a valid time!', SELF::EXCEPTION_CODE_DUTY_START_INVALID);
                }
                if (NULL === $duty_end_sql OR !self::isValidDateInFormat($duty_end_sql, 'H:i')) {
                    throw new Exception('duty_end_sql MUST be a valid time!', SELF::EXCEPTION_CODE_DUTY_END_INVALID);
                }
                if (!isset($Principle_roster_row_array['primary_key']) or !is_numeric($Principle_roster_row_array['primary_key'])) {
                    /**
                     * <p lang=de>Wenn für diesen Mitarbeiter bisher kein Grundplan hinterlegt war,
                     *  dann wird dort auch kein primary key übertragen.
                     *  Wenn für diesen Mitarbeiter nun aber doch ein Eintrag übergeben wird,
                     *  so müssen wir zunächst einen primary key vergeben.
                     *  Da die Datenbank final zu entscheiden hat,
                     *  welcher primary key zu verwenden ist, nehmen wir in PHP einfach NULL.</p>
                     */
                    $Principle_roster_row_array['primary_key'] = null;
                }

                $primary_key = user_input::convert_post_empty_to_php_null(filter_var($Principle_roster_row_array['primary_key'], FILTER_SANITIZE_NUMBER_INT));
                $Principle_roster[$date_unix][$roster_row_iterator] = new principle_roster_item($primary_key, $date_sql, $employee_key, $branch_id, $duty_start_sql, $duty_end_sql, $break_start_sql, $break_end_sql, $comment);
                $Principle_roster[$date_unix][$roster_row_iterator]->check_roster_item_sequence();
            }
        }
        return $Principle_roster;
    }

    private static function remove_changed_entries_from_database($branch_id, $Employee_key_list) {
        $sql_query = "DELETE FROM `Dienstplan`"
                . " WHERE `Datum` = :date"
                . " AND `employee_key` = :employee_key"
                . " AND `Mandant` = :branch_id";
        $statement = database_wrapper::instance()->prepare($sql_query);
        foreach ($Employee_key_list as $date_unix => $Employee_key_list_day) {
            $date_sql = date('Y-m-d', $date_unix);
            foreach ($Employee_key_list_day as $employee_key) {
                $statement->execute(array('employee_key' => $employee_key, 'date' => $date_sql, 'branch_id' => $branch_id));
            }
        }
    }

    /**
     * Removes a roster item from the database based on the specified criteria.
     *
     * This function performs a DELETE operation on the 'Dienstplan' table,
     * removing the entry for a specific employee on a given date and branch.
     *
     * @param roster_item $rosterItem The roster item to be removed from the database.
     */
    public static function removeRosterItemFromDatabase(roster_item $rosterItem): void {
        $sqlQuery = "DELETE FROM `Dienstplan`"
                . " WHERE `Datum` = :date"
                . " AND `employee_key` = :employee_key"
                . " AND `Mandant` = :branch_id";
        $statement = database_wrapper::instance()->prepare($sqlQuery);
        $statement->execute(array('employee_key' => $rosterItem->employee_key, 'date' => $rosterItem->date_sql, 'branch_id' => $rosterItem->branch_id));
    }

    private static function insert_changed_roster_into_database($Roster, $Changed_roster_employee_key_list) {
        foreach ($Roster as $date_unix => $Roster_day_array) {
            if (!isset($Changed_roster_employee_key_list[$date_unix])) {
                /* There are no changes. */
                continue;
            }
            foreach ($Roster_day_array as $roster_row_object) {
                if (!in_array($roster_row_object->employee_key, $Changed_roster_employee_key_list[$date_unix])) {
                    continue;
                }
                if (NULL === $roster_row_object->employee_key) {
                    /*
                     * This is the case, if there is an empty roster_item produced.
                     * e.g. inside user_input::get_Roster_from_POST_secure() if ('' !== $employee_key)
                     *
                     */
                    continue;
                }
                /*
                 * TODO: Should we use an INSERT ON DUPLICATE UPDATE here instead of the REPLACE?
                 * Are there any advantages to that?
                 */
                $sql_query = 'REPLACE INTO `Dienstplan` '
                        . ' (employee_key, Datum, Dienstbeginn, Dienstende, Mittagsbeginn, Mittagsende, Stunden, Mandant, Kommentar, user) '
                        . ' VALUES (:employee_key, :date_sql, :duty_start_sql, :duty_end_sql, :break_start_sql, :break_end_sql, :working_hours, :branch_id, :comment, :user_name)';
                database_wrapper::instance()->run($sql_query, array(
                    'employee_key' => $roster_row_object->employee_key,
                    'date_sql' => $roster_row_object->date_sql,
                    'duty_start_sql' => $roster_row_object->duty_start_sql,
                    'duty_end_sql' => $roster_row_object->duty_end_sql,
                    'break_start_sql' => $roster_row_object->break_start_sql,
                    'break_end_sql' => $roster_row_object->break_end_sql,
                    'working_hours' => $roster_row_object->working_hours,
                    'branch_id' => $roster_row_object->branch_id,
                    'comment' => $roster_row_object->comment,
                    'user_name' => $_SESSION['user_object']->user_name,
                ));
            }
        }
    }

    /**
     * Finde geänderte aber noch existente Einträge im neuen Plan
     *
     * <p lang="de">
     * CAVE! Gelöschte Einträge fehlen hier.
     *   Wenn ein Tag im neuen $Roster nicht mehr existiert, so wird er auch hier nicht mit erscheinen.
     *   get_deleted_roster_employee_key_list ist für die Aufgabe gedacht.
     * </p>
     *
     * @param type $Principle_roster_new
     * @param type $Principle_roster_old
     * @return type
     */
    public static function get_changed_principle_roster_primary_key_list($Principle_roster_new, $Principle_roster_old) {
        $Changed_roster_primary_key_list = array();
        foreach ($Principle_roster_new as $date_unix => $Roster_day_array) {
            if (!isset($Principle_roster_old[$date_unix]) or roster::is_empty_roster_day_array($Principle_roster_old[$date_unix])) {
                /**
                 * There is no old roster. Every entry for this day is new:
                 */
                foreach ($Roster_day_array as $roster_item) {
                    if ($roster_item instanceof roster_item_empty) {
                        continue;
                    }
                    if (NULL === $roster_item->get_primary_key()) {
                        continue;
                    }
                    $Changed_roster_primary_key_list[$date_unix][] = $roster_item->get_primary_key();
                }
            } else {
                /**
                 * There is an old roster and we can do a comparison:
                 */
                foreach ($Roster_day_array as $roster_item) {
                    if ($roster_item instanceof roster_item_empty) {
                        continue;
                    }
                    if (NULL === $roster_item->get_primary_key()) {
                        continue;
                    }
                    if (self::principle_roster_item_has_changed($roster_item, $Principle_roster_old)) {
                        /**
                         * The roster for the employee has changed for this day.
                         * The employee_key will be added to Changed_roster_employee_key_list
                         */
                        $Changed_roster_primary_key_list[$date_unix][] = $roster_item->get_primary_key();
                    }
                }
            }
        }
        return $Changed_roster_primary_key_list;
    }

    /**
     * Finde geänderte aber noch existente Einträge im neuen Plan
     *
     * <p lang="de">
     * CAVE! Gelöschte Einträge fehlen hier.
     *   Wenn ein Tag im neuen $Roster nicht mehr existiert, so wird er auch hier nicht mit erscheinen.
     *   get_deleted_roster_employee_key_list ist für die Aufgabe gedacht.
     * </p>
     *
     * @param type $Roster
     * @param type $Roster_old
     * @return type
     */
    public static function get_changed_roster_employee_key_list($Roster, $Roster_old) {
        $Changed_roster_employee_key_list = array();
        foreach ($Roster as $date_unix => $Roster_day_array) {
            if (!isset($Roster_old[$date_unix]) or roster::is_empty_roster_day_array($Roster_old[$date_unix])) {
                /**
                 * There is no old roster. Every entry is new:
                 */
                foreach ($Roster_day_array as $roster_item) {
                    if (NULL === $roster_item->employee_key) {
                        continue;
                    }
                    $Changed_roster_employee_key_list[$date_unix][] = $roster_item->employee_key;
                }
            } else {
                foreach ($Roster_day_array as $roster_item) {
                    if (NULL === $roster_item->employee_key) {
                        continue;
                    }
                    if (self::roster_item_has_changed($roster_item, $Roster_old)) {
                        /**
                         * The roster for the employee has changed for this day.
                         * The employee_key will be added to Changed_roster_employee_key_list
                         */
                        $Changed_roster_employee_key_list[$date_unix][] = $roster_item->employee_key;
                    }
                }
            }
        }
        return $Changed_roster_employee_key_list;
    }

    /**
     * This function aims to determine, if a roster_item has changed.
     *     It compares it to ALL the old elements.
     *     If ANY element in the old roster is the same, then no change has been made to this item.
     *
     * @param type $principle_roster_item_new
     * @param type $Principle_roster_old
     * @return boolean
     */
    private static function principle_roster_item_has_changed(principle_roster_item $principle_roster_item_new, array $Principle_roster_old) {
        /**
         * Searching for the same roster item in the old roster:
         */
        foreach ($Principle_roster_old[$principle_roster_item_new->date_unix] as $roster_item_old) {
            if ($principle_roster_item_new->get_primary_key() != $roster_item_old->get_primary_key()) {
                continue;
            }
            if ($principle_roster_item_new->get_employee_key() != $roster_item_old->get_employee_key()) {
                continue;
            }
            if ($principle_roster_item_new->get_branch_id() != $roster_item_old->get_branch_id()) {
                continue;
            }
            if ($principle_roster_item_new->get_duty_start_sql() != $roster_item_old->get_duty_start_sql()) {
                continue;
            }
            if ($principle_roster_item_new->get_duty_end_sql() != $roster_item_old->get_duty_end_sql()) {
                continue;
            }
            if ($principle_roster_item_new->get_break_start_sql() != $roster_item_old->get_break_start_sql()) {
                continue;
            }
            if ($principle_roster_item_new->get_break_end_sql() != $roster_item_old->get_break_end_sql()) {
                continue;
            }
            if ($principle_roster_item_new->get_comment() != $roster_item_old->get_comment()) {
                continue;
            }
            /**
             * All values are equal. The item was not changed:
             */
            return FALSE;
        }
        /**
         * We found none. return true because the item was changed:
         */
        return TRUE;
    }

    private static function roster_item_has_changed(roster_item $roster_item, array $Roster_old) {

        /**
         * Searching for the same roster item in the old roster:
         */
        foreach ($Roster_old[$roster_item->date_unix] as $roster_item_old) {
            if ($roster_item == $roster_item_old) {
                return FALSE;
            }
        }
        /**
         * We found none. return true because the item was changed:
         */
        return TRUE;
    }

    public static function get_deleted_roster_employee_key_list($Roster, $Roster_old) {
        $Deleted_roster_employee_key_list = array();
        foreach ($Roster as $date_unix => $Roster_day_array) {
            $List_of_employees_in_Roster = array();
            $List_of_employees_in_Roster_old = array();
            if (empty($Roster_day_array) or roster::is_empty_roster_day_array($Roster_day_array)) {
                /*
                 * Es steht kein einziger Eintrag in diesem Tag.
                 * Alle alten Einträge sind gelöschte Einträge.
                 */
                foreach ($Roster_old[$date_unix] as $roster_row_object) {
                    if (NULL === $roster_row_object->employee_key) {
                        continue;
                    }
                    $Deleted_roster_employee_key_list[$date_unix][] = $roster_row_object->employee_key;
                }
            } else {
                if (!isset($Roster_old[$date_unix])) {
                    /* There is no old roster */
                    $List_of_employees_in_Roster_old = array();
                } else {
                    foreach ($Roster_old[$date_unix] as $roster_row_object) {
                        if (NULL === $roster_row_object->employee_key) {
                            continue;
                        }
                        $List_of_employees_in_Roster_old[] = $roster_row_object->employee_key;
                    }
                }
                foreach ($Roster[$date_unix] as $roster_row_object) {
                    if (NULL === $roster_row_object->employee_key) {
                        continue;
                    }
                    $List_of_employees_in_Roster[] = $roster_row_object->employee_key;
                }
                $Deleted_roster_employee_keys = array_diff($List_of_employees_in_Roster_old, $List_of_employees_in_Roster);
                if (array(0 => NULL) === $Deleted_roster_employee_keys) {
                    continue;
                }
                if (array() === $Deleted_roster_employee_keys) {
                    continue;
                }
                $Deleted_roster_employee_key_list[$date_unix] = $Deleted_roster_employee_keys;
            }
        }
        return $Deleted_roster_employee_key_list;
    }

    /**
     * <p lang=de>
     * Wenn im Grundplan mit dem SELECT ein anderer Mitarbeiter ausgewählt wird,
     * dann muss man dies feststellen.
     * Es gibt zwei Funktionen, die Änderungen am Grundplan finden sollen:
     * - get_deleted_roster_primary_key_list()
     * - roster_item_has_changed()
     * Diese beiden Funktionen können diesen Fall aber nicht erkennen.
     * Der primary_key wird vom alten employee übertragen.
     * Der alte employee taucht aber nicht mehr auf, um ihn mit dem alten Plan zu vergleichen.
     * </p>
     * @param array $Roster_new The newly submitted roster
     * @param array $Roster_old The old roster stored in the database
     */
    public static function get_changed_roster_item_list(array $Roster_new, array $Roster_old) {
        $Changed_roster_item_list = array();
        foreach ($Roster_old as $date_unix => $Roster_day_array_old) {
            foreach ($Roster_day_array_old as $roster_row_iterator => $roster_item) {
                if (!array_key_exists($roster_row_iterator, $Roster_new[$date_unix])) {
                    continue;
                }
                if ($Roster_new[$date_unix][$roster_row_iterator] instanceof roster_item_empty) {
                    continue;
                }
                if ($roster_item->primary_key !== $Roster_new[$date_unix][$roster_row_iterator]->primary_key) {
                    throw new Exception("<p lang=de>Ich erwarte, dass der primary key zwischen den Plänen unverändert bleibt.</p>");
                }
                if ($roster_item->employee_key !== $Roster_new[$date_unix][$roster_row_iterator]->employee_key) {
                    $Changed_roster_item_list[] = $roster_item->primary_key;
                }
            }
        }
        return $Changed_roster_item_list;
    }

    public static function get_deleted_roster_primary_key_list(array $Roster_new, array $Roster_old) {
        $List_of_primary_keys_in_old_roster = array();
        $List_of_primary_keys_in_new_roster = array();
        /*
         * TODO: <p lang="de">Sobald es eine Klasse \PDR\Roster\Roster mit dem Inhalt \PDR\Roster\RosterDayArray gibt, sollte dies eine feste funktion werden:
         *  function get_primary_keys() {}
         *   Die "Mutterklasse" \PDR\Roster\Roster kann dann die gleichnamige Funktion bei ihren \PDR\Roster\RosterDayArray aufrufen.
         *   Und die können das aus ihren items abrufen.
         *   Die Werte in den items können private gestellt werden und zukünftig über funktionen ungleich dem magischen __get() angefordert werden.
         * </p>
         */
        foreach ($Roster_old as $Roster_old_day_array) {
            foreach ($Roster_old_day_array as $roster_old_item) {
                if (isset($roster_old_item->employee_key)) {
                    $List_of_primary_keys_in_old_roster[] = $roster_old_item->primary_key;
                }
            }
        }
        foreach ($Roster_new as $Roster_new_day_array) {
            foreach ($Roster_new_day_array as $roster_new_item) {
                if (isset($roster_new_item->employee_key) and isset($roster_new_item->primary_key)) {
                    $List_of_primary_keys_in_new_roster[] = $roster_new_item->primary_key;
                }
            }
        }
        $Deleted_roster_primary_key_list = array_diff($List_of_primary_keys_in_old_roster, $List_of_primary_keys_in_new_roster);
        return $Deleted_roster_primary_key_list;
    }

    private static function get_inserted_roster_employee_key_list($Roster, $Roster_old) {
        $Inserted_roster_employee_key_list = array();
        foreach ($Roster_old as $date_unix => $Roster_old_day_array) {
            if (empty($Roster_old_day_array)) {
                foreach ($Roster[$date_unix] as $roster_row_object) {
                    $Inserted_roster_employee_key_list[$date_unix][] = $roster_row_object->employee_key;
                }
            } else {
                foreach ($Roster_old[$date_unix] as $roster_row_object) {
                    $List_of_employees_in_Roster_old[] = $roster_row_object->employee_key;
                }
                foreach ($Roster[$date_unix] as $roster_row_object) {
                    $List_of_employees_in_Roster[] = $roster_row_object->employee_key;
                }
                $Inserted_roster_employee_key_list[$date_unix] = array_diff($List_of_employees_in_Roster, $List_of_employees_in_Roster_old);
            }
        }
        return $Inserted_roster_employee_key_list;
    }

    public static function get_inserted_principle_roster_item_list($Principle_roster_new) {
        $Inserted_principle_roster_item_list = array();
        foreach ($Principle_roster_new as $date_unix => $Principle_roster_new_day_array) {
            foreach ($Principle_roster_new_day_array as $roster_row_iterator => $principle_roster_item) {
                if ($principle_roster_item instanceof roster_item_empty) {
                    /**
                     * This is not a roster item with data inside:
                     */
                    continue;
                }
                if (null !== $principle_roster_item->get_primary_key()) {
                    /**
                     * This item is not new:
                     */
                    continue;
                }
                $Inserted_principle_roster_item_list[$date_unix][] = $principle_roster_item;
            }
        }
        return $Inserted_principle_roster_item_list;
    }

    public static function roster_write_user_input_to_database($Roster, $branch_id) {
        foreach (array_keys($Roster) as $date_unix) {
            $date_sql = date('Y-m-d', $date_unix);
            $Roster_old = roster::read_roster_from_database($branch_id, $date_sql);

            /*
             * Remove deleted data rows:
             * TODO: Find the changed or the deleted rows:
             */
            $Changed_roster_employee_key_list = user_input::get_changed_roster_employee_key_list($Roster, $Roster_old);
            $Deleted_roster_employee_key_list = user_input::get_deleted_roster_employee_key_list($Roster, $Roster_old);
            $Inserted_roster_employee_key_list = user_input::get_inserted_roster_employee_key_list($Roster, $Roster_old);
            database_wrapper::instance()->beginTransaction();
            user_input::remove_changed_entries_from_database($branch_id, $Deleted_roster_employee_key_list);
            user_input::remove_changed_entries_from_database($branch_id, $Changed_roster_employee_key_list);
            user_input::insert_changed_roster_into_database($Roster, $Changed_roster_employee_key_list);
            user_input::insert_changed_roster_into_database($Roster, $Inserted_roster_employee_key_list);
            database_wrapper::instance()->commit();
            //$user_dialog_email = new user_dialog_email();
            //$user_dialog_email->create_notification_about_changed_roster_to_employees($Roster, $Roster_old, $Inserted_roster_employee_key_list, $Changed_roster_employee_key_list, $Deleted_roster_employee_key_list);
        }
    }

    /**
     * Checks if the input string is a valid date in the given format.
     *
     * @param string $date
     * @param string $format
     * @return bool true if the input is a date of the given format
     */
    private static function isValidDateInFormat(string $date = null, string $format = 'Y-m-d'): bool {
        if (null === $date) {
            return false;
        }
        $dateObject = DateTime::createFromFormat($format, $date);
        return $dateObject && $dateObject->format($format) == $date;
    }
}
