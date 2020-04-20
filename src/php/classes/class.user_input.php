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

    public static function principle_roster_write_user_input_to_database($branch_id, $valid_from) {
        global $session;
        $session->exit_on_missing_privilege('create_roster');
        $Principle_roster_new = user_input::get_Roster_from_POST_secure();
        $pseudo_date_start_object = new DateTime();
        $pseudo_date_start_object->setTimestamp(min(array_keys($Principle_roster_new)));
        $pseudo_date_end_object = new DateTime();
        $pseudo_date_end_object->setTimestamp(max(array_keys($Principle_roster_new)));
        $Principle_roster_old = principle_roster::read_principle_roster_from_database($branch_id, $pseudo_date_start_object, $pseudo_date_end_object);
        $Changed_roster_employee_id_list = user_input::get_changed_roster_employee_id_list($Principle_roster_new, $Principle_roster_old);
        $Deleted_roster_employee_id_list = user_input::get_deleted_roster_employee_id_list($Principle_roster_new, $Principle_roster_old);
        $Inserted_roster_employee_id_list = user_input::get_inserted_roster_employee_id_list($Principle_roster_new, $Principle_roster_old);
        database_wrapper::instance()->beginTransaction();
        principle_roster::remove_changed_employee_entries_from_database($branch_id, $Deleted_roster_employee_id_list);
        principle_roster::remove_changed_employee_entries_from_database($branch_id, $Changed_roster_employee_id_list);
        principle_roster::insert_changed_entries_into_database($Principle_roster_new, $Changed_roster_employee_id_list, $valid_from);
        principle_roster::insert_changed_entries_into_database($Principle_roster_new, $Inserted_roster_employee_id_list, $valid_from);
        database_wrapper::instance()->commit();
    }

    public static function get_Roster_from_POST_secure() {
        $Roster_from_post = filter_input(INPUT_POST, 'Roster', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
        $Roster = array();
        if (empty($Roster_from_post)) {
            return FALSE;
        }
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
                if ('' === $employee_id) {
                    $Roster[$date_unix][$roster_row_iterator] = new roster_item_empty($date_sql, $branch_id);
                    continue;
                }
                if (NULL === $duty_start_sql) {
                    $Roster[$date_unix][$roster_row_iterator] = new roster_item_empty($date_sql, $branch_id);
                    continue;
                }
                if (NULL === $duty_end_sql) {
                    $Roster[$date_unix][$roster_row_iterator] = new roster_item_empty($date_sql, $branch_id);
                    continue;
                }
                $Roster[$date_unix][$roster_row_iterator] = new roster_item($date_sql, $employee_id, $branch_id, $duty_start_sql, $duty_end_sql, $break_start_sql, $break_end_sql, $comment);
                $Roster[$date_unix][$roster_row_iterator]->check_roster_item_sequence();
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

    private static function insert_changed_roster_into_database($Roster, $Changed_roster_employee_id_list) {
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
                $sql_query = 'REPLACE INTO `Dienstplan` '
                        . ' (VK, Datum, Dienstbeginn, Dienstende, Mittagsbeginn, Mittagsende, Stunden, Mandant, Kommentar, user) '
                        . ' VALUES (:employee_id, :date_sql, :duty_start_sql, :duty_end_sql, :break_start_sql, :break_end_sql, :working_hours, :branch_id, :comment, :user_name)';
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
                    'user_name' => $_SESSION['user_object']->user_name,
                ));
            }
        }
    }

    public static function get_changed_roster_employee_id_list($Roster, $Roster_old) {
        $Changed_roster_employee_id_list = array();
        foreach ($Roster as $date_unix => $Roster_day_array) {
            if (!isset($Roster_old[$date_unix]) or roster::is_empty_roster_day_array($Roster_old[$date_unix])) {
                /*
                 * There is no old roster. Every entry is new:
                 */
                foreach ($Roster_day_array as $roster_item) {
                    if (NULL === $roster_item->employee_id) {
                        continue;
                    }
                    $Changed_roster_employee_id_list[$date_unix][] = $roster_item->employee_id;
                }
            } else {
                foreach ($Roster_day_array as $roster_item) {
                    if (NULL === $roster_item->employee_id) {
                        continue;
                    }
                    if (self::roster_item_has_changed($roster_item, $Roster_old)) {
                        $Changed_roster_employee_id_list[$date_unix][] = $roster_item->employee_id;
                    }
                }
            }
        }
        return $Changed_roster_employee_id_list;
    }

    /**
     * This function aims to determine, if a roster_item has changed.
     *     It compares it to ALL the old elements.
     *     If ANY element in the old roster is the same, then no change has been made to this item.
     *
     * @param type $roster_item
     * @param type $Roster_old
     * @return boolean
     */
    private static function roster_item_has_changed($roster_item, $Roster_old) {

        foreach ($Roster_old[$roster_item->date_unix] as $roster_item_old) {
            if ($roster_item == $roster_item_old) {
                return FALSE;
            }
        }
        return TRUE;
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
            user_input::insert_changed_roster_into_database($Roster, $Changed_roster_employee_id_list);
            user_input::insert_changed_roster_into_database($Roster, $Inserted_roster_employee_id_list);
            database_wrapper::instance()->commit();
            $user_dialog_email = new user_dialog_email();
            $user_dialog_email->create_notification_about_changed_roster_to_employees($Roster, $Roster_old, $Inserted_roster_employee_id_list, $Changed_roster_employee_id_list, $Deleted_roster_employee_id_list);
        }
    }

}
