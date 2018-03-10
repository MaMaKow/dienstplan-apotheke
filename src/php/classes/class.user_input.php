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

    public static function principle_roster_write_user_input_to_database() {
        $Grundplan = filter_input(INPUT_POST, 'Grundplan', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);

        foreach ($Grundplan as $wochentag => $value) {
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
                        $sekunden = strtotime($dienstende) - strtotime($dienstbeginn);
                        $mittagspause = strtotime($mittagsende) - strtotime($mittagsbeginn);
                        $sekunden = $sekunden - $mittagspause;
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

}
