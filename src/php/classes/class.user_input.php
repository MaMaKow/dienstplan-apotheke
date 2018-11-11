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
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of class
 *
 * @author Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
abstract class user_input {
    /*
     * TODO: all methods in this class which start with "old_" have to be reviewed.
     */

    public static function get_variable_from_any_input($variable_name, $filter = FILTER_SANITIZE_STRING, $default_value = null) {
        $List_of_input_sources = array(INPUT_POST, INPUT_GET, INPUT_COOKIE);
        foreach ($List_of_input_sources as $input_source) {
            if (filter_has_var($input_source, $variable_name)) {
                return filter_input($input_source, $variable_name, $filter);
            }
        }
        return filter_var($default_value, $filter);
    }

    public static function escape_sql_value($value) {
        if ('NULL' === $value or 'null' === $value) {
            return $value;
        } elseif (NULL === $value) {
            return 'NULL';
        } else {
            return "'" . $value . "'";
        }
    }

    public static function convert_post_empty_to_php_null($value) {
        if ('' === $value) {
            return NULL;
        } else {
            return $value;
        }
    }

    public static function principle_employee_roster_write_user_input_to_database($employee_id) {
        $Principle_employee_roster_new = user_input::get_Roster_from_POST_secure();
        database_wrapper::instance()->beginTransaction();
        $sql_query = "DELETE FROM `Grundplan` WHERE `VK` = :employee_id";
        database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id));
        foreach ($Principle_employee_roster_new as $Principle_employee_roster_new_day_array) {
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
                    error_log('$employee_id is not equal to $principle_employee_roster_new_object->employee_id ' . "in " . __METHOD__ . " " . $employee_id . "!=" . $principle_employee_roster_new_object->employee_id);
                    return FALSE;
                }
                //$pseudo_date_sql_start = $principle_employee_roster_new_object->date_sql;
                //$pseudo_date_sql_end = $principle_employee_roster_new_object->date_sql;
                //$Principle_employee_roster_old = roster::read_principle_employee_roster_from_database($employee_id, $pseudo_date_sql_start, $pseudo_date_sql_end);
                $sql_query = "INSERT INTO `Grundplan` "
                        . "(VK, Wochentag, Dienstbeginn, Dienstende, Mittagsbeginn, Mittagsende, Stunden, Mandant, Kommentar) "
                        . "VALUES (:employee_id, :weekday, :duty_start_sql, :duty_end_sql, :break_start_sql, :break_end_sql, :working_hours, :branch_id, :comment)";
                database_wrapper::instance()->run($sql_query, array(
                    'employee_id' => $principle_employee_roster_new_object->employee_id,
                    'weekday' => date('w', $principle_employee_roster_new_object->date_unix),
                    'duty_start_sql' => $principle_employee_roster_new_object->duty_start_sql,
                    'duty_end_sql' => $principle_employee_roster_new_object->duty_end_sql,
                    'break_start_sql' => $principle_employee_roster_new_object->break_start_sql,
                    'break_end_sql' => $principle_employee_roster_new_object->break_end_sql,
                    'working_hours' => $principle_employee_roster_new_object->working_hours,
                    'branch_id' => $principle_employee_roster_new_object->branch_id,
                    'comment' => $principle_employee_roster_new_object->comment,
                ));
            }
        }
        database_wrapper::instance()->commit();
    }

    public static function principle_roster_write_user_input_to_database($branch_id) {
        $Principle_roster_new = user_input::get_Roster_from_POST_secure();
        $pseudo_date_sql_start = date('Y-m-d', min(array_keys($Principle_roster_new)));
        $pseudo_date_sql_end = date('Y-m-d', max(array_keys($Principle_roster_new)));
        $Principle_roster_old = roster::read_principle_roster_from_database($branch_id, $pseudo_date_sql_start, $pseudo_date_sql_end);
        $Changed_roster_employee_id_list = user_input::get_changed_roster_employee_id_list($Principle_roster_new, $Principle_roster_old);
        $Deleted_roster_employee_id_list = user_input::get_deleted_roster_employee_id_list($Principle_roster_new, $Principle_roster_old);
        $Inserted_roster_employee_id_list = user_input::get_inserted_roster_employee_id_list($Principle_roster_new, $Principle_roster_old);
        database_wrapper::instance()->beginTransaction();
        user_input::remove_changed_entries_from_database_principle_roster($branch_id, $Deleted_roster_employee_id_list);
        user_input::remove_changed_entries_from_database_principle_roster($branch_id, $Changed_roster_employee_id_list);
        user_input::insert_changed_entries_into_database_principle_roster($Principle_roster_new, $Changed_roster_employee_id_list);
        user_input::insert_changed_entries_into_database_principle_roster($Principle_roster_new, $Inserted_roster_employee_id_list);
        database_wrapper::instance()->commit();
    }

    public static function get_Roster_from_POST_secure() {
        $Roster_from_post = filter_input(INPUT_POST, 'Roster', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
        $Roster = array();
        foreach ($Roster_from_post as $date_unix => $Roster_from_post_day_array) {
            foreach ($Roster_from_post_day_array as $roster_row_iterator => $Roster_row_array) {
                $date_sql = filter_var($Roster_row_array['date_sql'], FILTER_SANITIZE_STRING);
                $employee_id = filter_var($Roster_row_array['employee_id'], FILTER_SANITIZE_NUMBER_INT);
                $branch_id = filter_var($Roster_row_array['branch_id'], FILTER_SANITIZE_NUMBER_INT);
                $duty_start_sql = user_input::convert_post_empty_to_php_null(filter_var($Roster_row_array['duty_start_sql'], FILTER_SANITIZE_STRING));
                $duty_end_sql = user_input::convert_post_empty_to_php_null(filter_var($Roster_row_array['duty_end_sql'], FILTER_SANITIZE_STRING));
                $break_start_sql = user_input::convert_post_empty_to_php_null(filter_var($Roster_row_array['break_start_sql'], FILTER_SANITIZE_STRING));
                $break_end_sql = user_input::convert_post_empty_to_php_null(filter_var($Roster_row_array['break_end_sql'], FILTER_SANITIZE_STRING));
                $comment = user_input::convert_post_empty_to_php_null(filter_var($Roster_row_array['comment'], FILTER_SANITIZE_STRING));
                if ('' !== $employee_id) {
                    /*
                     * TODO: This test might be a bit more complex.
                     * This might even be a good place to insert some sanity checks.
                     * e.g. Is the end after the beginning? Is the break within the duty time? Are there no overlapping duties for the same employee?
                     * roster_item::check_roster_item_sequence();
                     */
                    $Roster[$date_unix][$roster_row_iterator] = new roster_item($date_sql, $employee_id, $branch_id, $duty_start_sql, $duty_end_sql, $break_start_sql, $break_end_sql, $comment);
                } else {
                    $Roster[$date_unix][$roster_row_iterator] = new roster_item_empty($date_sql, $branch_id);
                }
            }
        }
        return $Roster;
    }

    private static function remove_changed_entries_from_database($branch_id, $Employee_id_list) {
        foreach ($Employee_id_list as $date_unix => $Employee_id_list_day) {
            $date_sql = date('Y-m-d', $date_unix);
            if (!empty($Employee_id_list_day)) {
                list($IN_placeholder, $IN_employees_list) = database_wrapper::create_placeholder_for_mysql_IN_function($Employee_id_list_day, TRUE);
                $sql_query = "DELETE FROM `Dienstplan`"
                        . " WHERE `Datum` = :date"
                        . " AND `VK` IN ($IN_placeholder)"
                        . " AND `Mandant` = :branch_id";
                database_wrapper::instance()->run($sql_query, array_merge($IN_employees_list, array('date' => $date_sql, 'branch_id' => $branch_id)));
            }
        }
    }

    private static function remove_changed_entries_from_database_principle_roster($branch_id, $Employee_id_list) {
        foreach ($Employee_id_list as $date_unix => $Employee_id_list_day) {
            $weekday = date('w', $date_unix);
            if (!empty($Employee_id_list_day)) {
                list($IN_placeholder, $IN_employees_list) = database_wrapper::create_placeholder_for_mysql_IN_function($Employee_id_list_day, TRUE);
                $sql_query = "DELETE FROM `Grundplan`"
                        . " WHERE `Wochentag` = :weekday"
                        . " AND `VK` IN ($IN_placeholder)"
                        . " AND `Mandant` = :branch_id";
                database_wrapper::instance()->run($sql_query, array_merge($IN_employees_list, array('weekday' => $weekday, 'branch_id' => $branch_id)));
            }
        }
    }

    private static function insert_changed_entries_into_database($Roster, $Changed_roster_employee_id_list) {
        foreach ($Roster as $date_unix => $Roster_day_array) {
            if (!isset($Changed_roster_employee_id_list[$date_unix])) {
                /* There are no changes. */
                continue;
            }
            foreach ($Roster_day_array as $roster_row_object) {
                if (!in_array($roster_row_object->employee_id, $Changed_roster_employee_id_list[$date_unix])) {
                    continue;
                }
                if (NULL === $roster_row_object->employee_id) {
                    /*
                     * This is the case, if there is an empty roster_item produced.
                     * e.g. inside user_input::get_Roster_from_POST_secure() if ('' !== $employee_id)
                     *
                     */
                    continue;
                }
                /*
                 * TODO: Should we use an INSERT ON DUPLICATE UPDATE here instead of the REPLACE?
                 * Are there any advantages to that?
                 */
                $sql_query = 'REPLACE INTO `Dienstplan` (VK, Datum, Dienstbeginn, Dienstende, Mittagsbeginn, Mittagsende, Stunden, Mandant, Kommentar, user) VALUES (:employee_id, :date_sql, :duty_start_sql, :duty_end_sql, :break_start_sql, :break_end_sql, :working_hours, :branch_id, :comment, :user_name)';
                database_wrapper::instance()->run($sql_query, array(
                    'employee_id' => $roster_row_object->employee_id,
                    'date_sql' => $roster_row_object->date_sql,
                    'duty_start_sql' => $roster_row_object->duty_start_sql,
                    'duty_end_sql' => $roster_row_object->duty_end_sql,
                    'break_start_sql' => $roster_row_object->break_start_sql,
                    'break_end_sql' => $roster_row_object->break_end_sql,
                    'working_hours' => $roster_row_object->working_hours,
                    'branch_id' => $roster_row_object->branch_id,
                    'comment' => $roster_row_object->comment,
                    'user_name' => $_SESSION['user_name']
                ));
            }
        }
    }

    private static function insert_changed_entries_into_database_principle_roster($Roster, $Changed_roster_employee_id_list) {
        foreach ($Roster as $date_unix => $Roster_day_array) {
            if (!isset($Changed_roster_employee_id_list[$date_unix])) {
                /* There are no changes. */
                continue;
            }
            foreach ($Roster_day_array as $roster_row_object) {
                if (!in_array($roster_row_object->employee_id, $Changed_roster_employee_id_list[$date_unix])) {
                    continue;
                }
                if (NULL === $roster_row_object->employee_id) {
                    continue;
                }
                /*
                 * TODO: Should we use an INSERT ON DUPLICATE UPDATE here instead of the REPLACE?
                 * Are there any advantages to that?
                 */
                $sql_query = "REPLACE INTO `Grundplan` (VK, Wochentag, Dienstbeginn, Dienstende, Mittagsbeginn, Mittagsende, Stunden, Mandant, Kommentar) VALUES (:employee_id, :weekday, :duty_start_sql, :duty_end_sql, :break_start_sql, :break_end_sql, :working_hours, :branch_id, :comment)";
                database_wrapper::instance()->run($sql_query, array(
                    'employee_id' => $roster_row_object->employee_id,
                    'weekday' => date('w', $roster_row_object->date_unix),
                    'duty_start_sql' => $roster_row_object->duty_start_sql,
                    'duty_end_sql' => $roster_row_object->duty_end_sql,
                    'break_start_sql' => $roster_row_object->break_start_sql,
                    'break_end_sql' => $roster_row_object->break_end_sql,
                    'working_hours' => $roster_row_object->working_hours,
                    'branch_id' => $roster_row_object->branch_id,
                    'comment' => $roster_row_object->comment,
                ));
            }
        }
    }

    private static function insert_new_approval_into_database($date_sql, $branch_id) {
        /*
         * TODO: We should manage situations, where an entry already exists better.
         */
        $sql_query = "INSERT IGNORE INTO `approval` (date, state, branch, user)
			VALUES (:date, 'not_yet_approved', :branch_id, :user)";
        database_wrapper::instance()->run($sql_query, array('date' => $date_sql, 'branch_id' => $branch_id, 'user' => $_SESSION['user_name']));
    }

    public static function old_write_approval_to_database($branch_id, $Roster) {
        foreach (array_keys($Roster) as $date_unix) {
            $date_sql = date('Y-m-d', $date_unix);
            if (filter_has_var(INPUT_POST, 'submit_approval')) {
                $state = "approved";
            } elseif (filter_has_var(INPUT_POST, 'submit_disapproval')) {
                $state = "disapproved";
            } else {
//no state is given.
// TODO: This is an Exception. Should we fail fast and loud?
                die("An Error has occurred during approval!");
            }
            $sql_query = "INSERT INTO `approval` (date, branch, state, user) "
                    . "values (:date, :branch_id, :state, :user) "
                    . "ON DUPLICATE KEY "
                    . "UPDATE date = :date2, branch = :branch_id2, state = :state2, user = :user2";
            $result = database_wrapper::instance()->run($sql_query, array(
                'date' => $date_sql, 'branch_id' => $branch_id, 'state' => $state, 'user' => $_SESSION['user_employee_id'],
                'date2' => $date_sql, 'branch_id2' => $branch_id, 'state2' => $state, 'user2' => $_SESSION['user_employee_id']
            ));
            return $result;
        }
    }

    public static function get_changed_roster_employee_id_list($Roster, $Roster_old) {
        $Changed_roster_employee_id_list = array();
        foreach ($Roster as $date_unix => $Roster_day_array) {
            if (!isset($Roster_old[$date_unix])) {
                /*
                 * There is no old roster every entry is new:
                 */
                foreach ($Roster_day_array as $roster_row_object) {
                    if (NULL === $roster_row_object->employee_id) {
                        continue;
                    }
                    $Changed_roster_employee_id_list[$date_unix][] = $roster_row_object->employee_id;
                }
                return $Changed_roster_employee_id_list;
            } else {
                foreach ($Roster_day_array as $roster_row_object) {
                    foreach ($Roster_old[$date_unix] as $roster_row_object_old) {
                        if ($roster_row_object->employee_id !== $roster_row_object_old->employee_id) {
                            /*
                             * TODO: Make sure, that both employee ids are integers.
                              Also make sure, that all of the other values are properly formated!
                             */
                            continue;
                        }
                        if ($roster_row_object != $roster_row_object_old) {
                            /*
                             * There is an old entry for this employee, which does not exactly match the newly sent entry.
                             * CAVE: This only works with the comparison operator != while !== will allways return FALSE if the objects are not references to the SAME object
                             * CAVE: This will also put any employee on the list, who is on the roster more than once.
                             */
                            $Changed_roster_employee_id_list[$date_unix][] = $roster_row_object->employee_id;
                        } else {
                            /*
                             * Everything stayed the same for this employee there is nothing to add to the list.
                             */
                        }
                    }
                }
            }
        }
        return $Changed_roster_employee_id_list;
    }

    private static function get_deleted_roster_employee_id_list($Roster, $Roster_old) {
        $List_of_employees_in_Roster = array();
        $Deleted_roster_employee_id_list = array();
        foreach ($Roster as $date_unix => $Roster_day_array) {
            if (empty($Roster_day_array)) {
                foreach ($Roster_old[$date_unix] as $roster_row_object) {
                    $Deleted_roster_employee_id_list[$date_unix][] = $roster_row_object->employee_id;
                }
            } else {
                if (!isset($Roster_old[$date_unix])) {
                    /* There is no old roster */
                    $List_of_employees_in_Roster_old = array();
                } else {
                    foreach ($Roster_old[$date_unix] as $roster_row_object) {
                        $List_of_employees_in_Roster_old[] = $roster_row_object->employee_id;
                    }
                }
                foreach ($Roster[$date_unix] as $roster_row_object) {
                    if (NULL === $roster_row_object->employee_id) {
                        continue;
                    }
                    $List_of_employees_in_Roster[] = $roster_row_object->employee_id;
                }
                $Deleted_roster_employee_ids = array_diff($List_of_employees_in_Roster_old, $List_of_employees_in_Roster);
                if (array(0 => NULL) !== $Deleted_roster_employee_ids) {
                    $Deleted_roster_employee_id_list[$date_unix] = $Deleted_roster_employee_ids;
                }
            }
        }
        return $Deleted_roster_employee_id_list;
    }

    private static function get_inserted_roster_employee_id_list($Roster, $Roster_old) {
        $Inserted_roster_employee_id_list = array();
        foreach ($Roster_old as $date_unix => $Roster_old_day_array) {
            if (empty($Roster_old_day_array)) {
                foreach ($Roster[$date_unix] as $roster_row_object) {
                    $Inserted_roster_employee_id_list[$date_unix][] = $roster_row_object->employee_id;
                }
            } else {
                foreach ($Roster_old[$date_unix] as $roster_row_object) {
                    $List_of_employees_in_Roster_old[] = $roster_row_object->employee_id;
                }
                foreach ($Roster[$date_unix] as $roster_row_object) {
                    $List_of_employees_in_Roster[] = $roster_row_object->employee_id;
                }
                $Inserted_roster_employee_id_list[$date_unix] = array_diff($List_of_employees_in_Roster, $List_of_employees_in_Roster_old);
            }
        }
        return $Inserted_roster_employee_id_list;
    }

    public static function roster_write_user_input_to_database($Roster, $branch_id) {
        foreach (array_keys($Roster) as $date_unix) {
            $date_sql = date('Y-m-d', $date_unix);
            /*
             * The following line will add an entry for every day in the table approval.
             */
            user_input::insert_new_approval_into_database($date_sql, $branch_id);
            $Roster_old = roster::read_roster_from_database($branch_id, $date_sql);

            /*
             * Remove deleted data rows:
             * TODO: Find the changed or the deleted rows:
             */
            $Changed_roster_employee_id_list = user_input::get_changed_roster_employee_id_list($Roster, $Roster_old);
            $Deleted_roster_employee_id_list = user_input::get_deleted_roster_employee_id_list($Roster, $Roster_old);
            $Inserted_roster_employee_id_list = user_input::get_inserted_roster_employee_id_list($Roster, $Roster_old);
            database_wrapper::instance()->beginTransaction();
            user_input::remove_changed_entries_from_database($branch_id, $Deleted_roster_employee_id_list);
            user_input::remove_changed_entries_from_database($branch_id, $Changed_roster_employee_id_list);
            user_input::insert_changed_entries_into_database($Roster, $Changed_roster_employee_id_list);
            user_input::insert_changed_entries_into_database($Roster, $Inserted_roster_employee_id_list);
            database_wrapper::instance()->commit();
            $user_dialog_email = new user_dialog_email();
            $user_dialog_email->create_email_about_changed_roster_to_employees($Roster, $Roster_old, $Inserted_roster_employee_id_list, $Changed_roster_employee_id_list, $Deleted_roster_employee_id_list);
        }
    }

}
