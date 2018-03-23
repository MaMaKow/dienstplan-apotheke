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
class user_input {
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
        global $List_of_employee_lunch_break_minutes;
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

    public static function old_get_Roster_from_POST_secure() {
        global $Columns; //Will be needed to slice out empty rows later.
        //The following statement requires PHP >= 7.0.0
        //define("TIME_COLUMNS", array("Dienstbeginn", "Dienstende"));
        $time_columns = array("Dienstbeginn", "Dienstende", "Mittagsbeginn", "Mittagsende");

        $Roster_from_post = filter_input(INPUT_POST, 'Dienstplan', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
        $Roster = array();

        foreach ($Roster_from_post as $day_number => $inhalt_tag) {
            $day_number = filter_var($day_number, FILTER_SANITIZE_NUMBER_INT);
            foreach ($inhalt_tag as $column_name => $Lines) {
                $column_name = filter_var($column_name, FILTER_SANITIZE_STRING);
                $Columns[$column_name] = $column_name; //Will be needed to slice out empty rows later.
                foreach ($Lines as $line_number => $line) {
                    $line = filter_var($line, FILTER_SANITIZE_STRING);
                    if (!empty($line) and in_array($column_name, $time_columns)) {
                        $line = strftime('%H:%M:%S', strtotime($line));
                    }
                    $line_number = filter_var($line_number, FILTER_SANITIZE_NUMBER_INT);
                    if ('' === $line) {
                        //Empty fields should be inserted as null values inside the database.
                        //TODO: Should we make an exeption for Comments?
                        //$line = 'null';
                        $line = NULL;
                    }
                    $Roster[$day_number][$column_name][$line_number] = $line;
                }
            }
        }
        return $Roster;
    }

    public static function old_get_old_Roster_from_database($date_sql, $branch_id) {
        $query = "SELECT * FROM `Dienstplan`
			WHERE `Datum` = '$date_sql'
                            AND `Mandant` = '$branch_id'
			;"; //Der Mandant wird entweder als default gesetzt oder per POST übergeben und dann im vorherigen if-clause übeschrieben.
        $result = mysqli_query_verbose($query);
        $Roster_old_day = array();
        while ($row = mysqli_fetch_object($result)) {
            $Roster_old_day["Datum"][] = $row->Datum;
            $Roster_old_day["VK"][] = $row->VK;
            $Roster_old_day["Dienstbeginn"][] = $row->Dienstbeginn;
            $Roster_old_day["Dienstende"][] = $row->Dienstende;
            $Roster_old_day["Mittagsbeginn"][] = $row->Mittagsbeginn;
            $Roster_old_day["Mittagsende"][] = $row->Mittagsende;
            $Roster_old_day["Mandant"][] = $row->Mandant;
            $Roster_old_day["Kommentar"][] = $row->Kommentar;
        }
        return $Roster_old_day;
    }

    public static function old_remove_changed_entries_from_database($date_sql, $branch_id, $Employee_id_list) {
        if (!empty($Employee_id_list)) {
            $sql_query = "DELETE FROM `Dienstplan`"
                    . " WHERE `Datum` = '$date_sql'"
                    . " AND `VK` IN (" . implode(', ', $Employee_id_list) . ")"
                    . " AND `Mandant` = '$branch_id';"; //Der Mandant wird entweder als default gesetzt oder per POST übergeben und dann im vorherigen if-clause übeschrieben.
            mysqli_query_verbose($sql_query, TRUE);
        }
    }

    public static function old_insert_changed_entries_into_database($date_sql, $day_number, $branch_id, $Dienstplan, $Changed_roster_employee_id_list) {
        foreach ($Dienstplan[$day_number]['VK'] as $key => $employee_id) { //Die einzelnen Zeilen im Dienstplan
            if (!in_array($employee_id, $Changed_roster_employee_id_list)) {
                continue;
            }
            if (isset($Dienstplan[$day_number]["Mittagsbeginn"][$key]) && isset($Dienstplan[$day_number]["Mittagsende"][$key])) {
                $lunch_break = strtotime($Dienstplan[$day_number]["Mittagsende"][$key]) - strtotime($Dienstplan[$day_number]["Mittagsbeginn"][$key]);
            } else {
                $lunch_break = 0;
            }
            $working_seconds = strtotime($Dienstplan[$day_number]["Dienstende"][$key]) - strtotime($Dienstplan[$day_number]["Dienstbeginn"][$key]) - $lunch_break;
            $working_hours = $working_seconds / 3600;
            $sql_query = "REPLACE INTO `Dienstplan` (VK, Datum, Dienstbeginn, Dienstende, Mittagsbeginn, Mittagsende, Stunden, Mandant, Kommentar, user)
            VALUES ($employee_id"
                    . ", " . user_input::escape_sql_value($date_sql)
                    . ", " . user_input::escape_sql_value($Dienstplan[$day_number]["Dienstbeginn"][$key])
                    . ", " . user_input::escape_sql_value($Dienstplan[$day_number]["Dienstende"][$key])
                    . ", " . user_input::escape_sql_value($Dienstplan[$day_number]["Mittagsbeginn"][$key])
                    . ", " . user_input::escape_sql_value($Dienstplan[$day_number]["Mittagsende"][$key])
                    . ", " . $working_hours
                    . ", " . $branch_id
                    . ", " . user_input::escape_sql_value($Dienstplan[$day_number]["Kommentar"][$key])
                    . ", " . user_input::escape_sql_value($_SESSION['user_name'])
                    . ")";
            mysqli_query_verbose($sql_query, TRUE);
        }
    }

    public static function old_remove_empty_rows($Roster, $day_number, $Columns) {
        //Slice out empty rows in all columns:
        foreach ($Roster[$day_number]["VK"] as $line_number => $employee_id) {
            if (NULL === $employee_id) {
                foreach ($Columns as $column_name) {
                    unset($Roster[$day_number][$column_name][$line_number]);
                }
            }
        }
        return $Roster;
    }

    public static function old_insert_new_approval_into_database($date_sql, $branch_id) {
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

    public static function old_roster_write_user_input_to_database($Dienstplan, $Columns, $mandant) {
        foreach (array_keys($Dienstplan) as $day_number) { //Hier sollte eigentlich nur ein einziger Tag ankommen.
            $Dienstplan = user_input::old_remove_empty_rows($Dienstplan, $day_number, $Columns);
            $roster_first_key = min(array_keys($Dienstplan[$day_number]['Datum']));
            if (!empty($Dienstplan[$day_number]['Datum'][$roster_first_key])) {
                $date_sql = $Dienstplan[$day_number]['Datum'][$roster_first_key];
            } else {
                $date_sql = filter_input(INPUT_POST, 'date_sql', FILTER_SANITIZE_STRING);
            }
            //The following line will add an entry for every day in the table approval.
            user_input::old_insert_new_approval_into_database($date_sql, $mandant);
            $Roster_old[$day_number] = user_input::old_get_old_Roster_from_database($date_sql, $mandant);

            /*
             * Remove deleted data rows:
             * TODO: Find the changed or the deleted rows:
             */
            foreach ($Dienstplan[$day_number]["VK"] as $key => $employee_id) {
                $Comparison_keys = array_keys($Roster_old[$day_number]["VK"], $employee_id);
                foreach ($Comparison_keys as $comparison_key) {
                    foreach ($Dienstplan[$day_number] as $column_name => $Column) {
                        if ($Roster_old[$day_number][$column_name][$comparison_key] !== $Dienstplan[$day_number][$column_name][$key]) {
                            $Changed_roster_employee_id_list[] = $employee_id;
                        }
                    }
                }
            }
            $Changed_roster_employee_id_list = array_unique($Changed_roster_employee_id_list);
            if (empty($Dienstplan[$day_number]["VK"])) {
                $Deleted_roster_employee_id_list = $Roster_old[$day_number]["VK"];
            } else {
                $Deleted_roster_employee_id_list = array_diff($Roster_old[$day_number]["VK"], $Dienstplan[$day_number]["VK"]);
            }
            if (empty($Roster_old[$day_number]["VK"])) {
                $Inserted_employee_id_list = $Dienstplan[$day_number]["VK"];
            } else {
                $Inserted_employee_id_list = array_diff($Dienstplan[$day_number]["VK"], $Roster_old[$day_number]["VK"]);
            }

            //TODO: There should be a transaction here:
            mysqli_query_verbose("START TRANSACTION");
            user_input::old_remove_changed_entries_from_database($date_sql, $mandant, $Deleted_roster_employee_id_list);
            user_input::old_remove_changed_entries_from_database($date_sql, $mandant, $Changed_roster_employee_id_list);
            user_input::old_insert_changed_entries_into_database($date_sql, $day_number, $mandant, $Dienstplan, $Changed_roster_employee_id_list);
            user_input::old_insert_changed_entries_into_database($date_sql, $day_number, $mandant, $Dienstplan, $Inserted_employee_id_list);
            mysqli_query_verbose("COMMIT");
        }
    }

}
