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
        return $default_value;
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

    public static function convert_post_null_to_mysql_null($value) {
        if ('' === $value) {
            return 'NULL';
        } else {
            return "'" . $value . "'";
        }
    }

    public static function principle_roster_write_user_input_to_database($mandant) {
        global $List_of_employee_lunch_break_minutes, $List_of_employees;
        $Grundplan = filter_input(INPUT_POST, 'Grundplan', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);

        foreach (array_keys($Grundplan) as $wochentag) {
            //First, the old values are deleted.
            $sql_query = "DELETE FROM `Grundplan` WHERE Wochentag='$wochentag' AND Mandant='$mandant'";
            $result = mysqli_query_verbose($sql_query);
            //New values are composed from the Grundplan from $_POST.
            foreach ($Grundplan[$wochentag]['VK'] as $key => $VK) {
                //Die einzelnen Zeilen im Grundplan
                if (!empty($VK)) {
                    //Wir ignorieren die nicht ausgefüllten Felder
                    list($VK) = explode(' ', $VK); //Wir brauchen nur die VK Nummer. Die steht vor dem Leerzeichen.
                    $dienstbeginn = user_input::convert_post_null_to_mysql_null($Grundplan[$wochentag]['Dienstbeginn'][$key]);
                    $dienstende = user_input::convert_post_null_to_mysql_null($Grundplan[$wochentag]['Dienstende'][$key]);
                    $mittagsbeginn = user_input::convert_post_null_to_mysql_null($Grundplan[$wochentag]['Mittagsbeginn'][$key]);
                    $mittagsende = user_input::convert_post_null_to_mysql_null($Grundplan[$wochentag]['Mittagsende'][$key]);
                    $kommentar = user_input::convert_post_null_to_mysql_null($Grundplan[$wochentag]['Kommentar'][$key]);
                    if (!empty($mittagsbeginn) && !empty($mittagsende)) {
                        $sekunden = (strtotime($dienstende) - strtotime($dienstbeginn)) - (strtotime($mittagsende) - strtotime($mittagsbeginn));
                        $stunden = round($sekunden / 3600, 1);
                    } else {
                        $sekunden = strtotime($dienstende) - strtotime($dienstbeginn);
                        //Wer länger als 6 Stunden Arbeitszeit hat, bekommt eine Mittagspause.
                        if (!isset($List_of_employees)) {
                            require 'db-lesen-mitarbeiter.php';
                        }

                        if ($sekunden - $List_of_employee_lunch_break_minutes[$VK] * 60 >= 6 * 3600) {
                            $mittagspause = $List_of_employee_lunch_break_minutes[$VK] * 60;
                            $sekunden = $sekunden - $mittagspause;
                        } else {
                            //Keine Mittagspause
                        }
                        $stunden = round($sekunden / 3600, 1);
                    }
                    //The new values are stored inside the database.
                    $sql_query = "REPLACE INTO `Grundplan` (VK, Wochentag, Dienstbeginn, Dienstende, Mittagsbeginn, Mittagsende, Stunden, Kommentar, Mandant)
			             VALUES ('$VK', '$wochentag', $dienstbeginn, $dienstende, $mittagsbeginn, $mittagsende, '$stunden', $kommentar, '$mandant')";
                    $result = mysqli_query_verbose($sql_query);
                }
            }
        }
    }

    public static function get_Roster_from_POST_secure() {
        $Roster_from_post = filter_input(INPUT_POST, 'Roster', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
        $Roster = array();
        foreach ($Roster_from_post as $date_unix => $Roster_day_array) {
            foreach ($Roster_day_array as $roster_row_iterator => $Roster_row_array) {
                $date_sql = filter_var($Roster_row_array['date_sql'], FILTER_SANITIZE_STRING);
                $employee_id = filter_var($Roster_row_array['employee_id'], FILTER_SANITIZE_NUMBER_INT);
                $branch_id = filter_var($Roster_row_array['branch_id'], FILTER_SANITIZE_NUMBER_INT);
                $duty_start_sql = filter_var($Roster_row_array['duty_start_sql'], FILTER_SANITIZE_STRING);
                $duty_end_sql = filter_var($Roster_row_array['duty_end_sql'], FILTER_SANITIZE_STRING);
                $break_start_sql = filter_var($Roster_row_array['break_start_sql'], FILTER_SANITIZE_STRING);
                $break_end_sql = filter_var($Roster_row_array['break_end_sql'], FILTER_SANITIZE_STRING);
                $comment = filter_var($Roster_row_array['comment'], FILTER_SANITIZE_STRING);
                if ('' !== $employee_id) {
                    /*
                     * TODO: This test might be a bit more complex.
                     * This might even be a good place to insert some sanity checks.
                     * e.g. Is the end after the beginning? Is the break within the duty time? Are there no overlapping duties for the same employee?
                     * roster_item::check_roster_item_sequence();
                     */
                    $Roster[$date_unix][$roster_row_iterator] = new roster_item($date_sql, $employee_id, $branch_id, $duty_start_sql, $duty_end_sql, $break_start_sql, $break_end_sql, $comment);
                }
            }
        }
        return $Roster;
    }

    private static function remove_changed_entries_from_database($branch_id, $Employee_id_list) {
        foreach ($Employee_id_list as $date_unix => $Employee_id_list_day) {
            $date_sql = date('Y-m-d', $date_unix);
            if (!empty($Employee_id_list_day)) {
                $sql_query = "DELETE FROM `Dienstplan`"
                        . " WHERE `Datum` = '$date_sql'"
                        . " AND `VK` IN (" . implode(', ', $Employee_id_list_day) . ")"
                        . " AND `Mandant` = '$branch_id';";
                mysqli_query_verbose($sql_query, TRUE);
            }
        }
    }

    private static function insert_changed_entries_into_database($Roster, $Changed_roster_employee_id_list) {
        foreach ($Roster as $date_unix => $Roster_day_array) {
            foreach ($Roster_day_array as $roster_row_object) {
                if (!in_array($roster_row_object->employee_id, $Changed_roster_employee_id_list[$date_unix])) {
                    continue;
                }
                /*
                 * TODO: Should we use an INSERT ON DUPLICATE UPDATE here instead of the REPLACE?
                 * Are there any advantages to that?
                 */
                $sql_query = "REPLACE INTO `Dienstplan` (VK, Datum, Dienstbeginn, Dienstende, Mittagsbeginn, Mittagsende, Stunden, Mandant, Kommentar, user) VALUES ("
                        . user_input::escape_sql_value($roster_row_object->employee_id)
                        . ", " . user_input::escape_sql_value($roster_row_object->date_sql)
                        . ", " . user_input::escape_sql_value($roster_row_object->duty_start_sql)
                        . ", " . user_input::escape_sql_value($roster_row_object->duty_end_sql)
                        . ", " . user_input::escape_sql_value($roster_row_object->break_start_sql)
                        . ", " . user_input::escape_sql_value($roster_row_object->break_end_sql)
                        . ", " . user_input::escape_sql_value($roster_row_object->working_hours)
                        . ", " . user_input::escape_sql_value($roster_row_object->branch_id)
                        . ", " . user_input::escape_sql_value($roster_row_object->comment)
                        . ", " . user_input::escape_sql_value($_SESSION['user_name'])
                        . ")";
                mysqli_query_verbose($sql_query, TRUE);
            }
        }
    }

    private static function insert_new_approval_into_database($date_sql, $branch_id) {
        //TODO: We should manage situations, where an entry already exists better.
        $sql_query = "INSERT IGNORE INTO `approval` (date, state, branch, user)
			VALUES ('$date_sql', 'not_yet_approved', '$branch_id', " . user_input::escape_sql_value($_SESSION['user_name']) . ")";
        $result = mysqli_query_verbose($sql_query);
        return $result;
    }

    public static function old_write_approval_to_database($mandant) {

        $Dienstplan = filter_input(INPUT_POST, 'Dienstplan', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
        foreach (array_keys($Dienstplan) as $tag) {
            if (empty($Dienstplan[$tag]['Datum'][0])) {
                continue;
            }
            $date = $Dienstplan[$tag]['Datum'][0];
            if (filter_has_var(INPUT_POST, 'submit_approval')) {
                $state = "approved";
            } elseif (filter_has_var(INPUT_POST, 'submit_disapproval')) {
                $state = "disapproved";
            } else {
                //no state is given.
                // TODO: This is an Exception. Should we fail fast and loud?
                die("An Error has occurred during approval!");
            }
            //The variable $user is set within the default.php
            $sql_query = "INSERT INTO `approval` (date, branch, state, user) values ('$date', '$mandant', '$state', '" . $_SESSION['user_employee_id'] . "') ON DUPLICATE KEY UPDATE date='$date', branch='$mandant', state='$state', user='" . $_SESSION['user_employee_id'] . "'";
            $result = mysqli_query_verbose($sql_query);
            return $result;
        }
    }

    private static function get_changed_roster_employee_id_list($Roster, $Roster_old) {
        $Changed_roster_employee_id_list = array();
        foreach ($Roster as $date_unix => $Roster_day_array) {
            foreach ($Roster_day_array as $roster_row_object) {
                foreach ($Roster_old[$date_unix] as $roster_row_object_old) {
                    if ($roster_row_object->employee_id === $roster_row_object->employee_id and $roster_row_object === $roster_row_object_old) {
                        /*
                         * There is an old entry for this employee, which does not exactly match the newly sent entry.
                         * CAVE: This will also put any employee on the list, who is on the roster more than once.
                         */
                        $Changed_roster_employee_id_list[$date_unix][] = $roster_row_object->employee_id;
                    }
                }
            }
        }
        return $Changed_roster_employee_id_list;
    }

    private static function get_deleted_roster_employee_id_list($Roster, $Roster_old) {
        foreach ($Roster as $date_unix => $Roster_day_array) {

            if (empty($Roster_day_array)) {
                foreach ($Roster_old[$date_unix] as $roster_row_object) {
                    $Deleted_roster_employee_id_list[$date_unix][] = $roster_row_object->employee_id;
                }
            } else {
                foreach ($Roster_old[$date_unix] as $roster_row_object) {
                    $List_of_employees_in_Roster_old[] = $roster_row_object->employee_id;
                }
                foreach ($Roster[$date_unix] as $roster_row_object) {
                    $List_of_employees_in_Roster[] = $roster_row_object->employee_id;
                }
                $Deleted_roster_employee_id_list[$date_unix] = array_diff($List_of_employees_in_Roster_old, $List_of_employees_in_Roster);
            }
        }
        return $Deleted_roster_employee_id_list;
    }

    private static function get_inserted_roster_employee_id_list($Roster, $Roster_old) {
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

    public static function old_roster_write_user_input_to_database($Roster, $branch_id) {
        foreach (array_keys($Roster) as $date_unix) {
            $date_sql = date('Y-m-d', $date_unix);
            //The following line will add an entry for every day in the table approval.
            user_input::insert_new_approval_into_database($date_sql, $branch_id);
            $Roster_old = roster::read_roster_from_database($branch_id, $date_sql);

            /*
             * Remove deleted data rows:
             * TODO: Find the changed or the deleted rows:
             */
            $Changed_roster_employee_id_list = user_input::get_changed_roster_employee_id_list($Roster, $Roster_old, $date_unix);
            $Deleted_roster_employee_id_list = user_input::get_deleted_roster_employee_id_list($Roster, $Roster_old, $date_unix);
            $Inserted_roster_employee_id_list = user_input::get_inserted_roster_employee_id_list($Roster, $Roster_old, $date_unix);

            mysqli_query_verbose("START TRANSACTION");
            user_input::remove_changed_entries_from_database($branch_id, $Deleted_roster_employee_id_list);
            user_input::remove_changed_entries_from_database($branch_id, $Changed_roster_employee_id_list);
            user_input::insert_changed_entries_into_database($Roster, $Changed_roster_employee_id_list);
            user_input::insert_changed_entries_into_database($Roster, $Inserted_roster_employee_id_list);
            mysqli_query_verbose("COMMIT");
        }
    }

}
