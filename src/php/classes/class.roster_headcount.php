<?php

/*
 * Copyright (C) 2017 Mandelkow
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

abstract class roster_headcount {

    public static function get_roster_of_qualified_pharmacist_employees($Roster) {
        $Roster_of_qualified_pharmacist_employees = array();
        global $workforce;
        foreach ($Roster as $roster_day) {
            foreach ($roster_day as $roster_item_object) {
                if (in_array($roster_item_object->employee_id, $workforce->List_of_qualified_pharmacist_employees)) {
                    $Roster_of_qualified_pharmacist_employees[$roster_item_object->date_unix][] = $roster_item_object;
                }
            }
        }
        return $Roster_of_qualified_pharmacist_employees;
    }

    public static function get_roster_of_goods_receipt_employees($Roster) {
        $Roster_of_goods_receipt_employees = array();
        global $workforce;
        foreach ($Roster as $roster_day) {
            foreach ($roster_day as $roster_item_object) {
                if (in_array($roster_item_object->employee_id, $workforce->List_of_goods_receipt_employees)) {
                    $Roster_of_goods_receipt_employees[$roster_item_object->date_unix][] = $roster_item_object;
                }
            }
        }
        return $Roster_of_goods_receipt_employees;
    }

    public static function headcount_roster($Roster, $Changing_times) {
        if (FALSE === $Changing_times) {
            return FALSE;
        }
        /* @var $Anwesende array */
        $Anwesende = array();
        $Duty_start_times = array();
        $Duty_end_times = array();
        $Break_start_times = array();
        $Break_end_times = array();
        foreach ($Roster as $roster_day) {
            foreach ($roster_day as $roster_item_object) {
                $Duty_start_times[] = $roster_item_object->duty_start_int;
                $Duty_end_times[] = $roster_item_object->duty_end_int;
                $Break_start_times[] = $roster_item_object->break_start_int;
                $Break_end_times[] = $roster_item_object->break_end_int;
            }
        }
        if (array() === $Duty_start_times) {
            foreach ($Changing_times as $time) {
                $Anwesende[$time] = 0;
            }
            return $Anwesende;
        }
        foreach ($Changing_times as $time) {
            $Anwesende[$time] = 0;
            foreach ($Duty_start_times as $dienstbeginn) {
                if ($dienstbeginn <= $time) {
                    //$Gekommene[$time] ++;
                    $Anwesende[$time] ++;
                }
            }
            foreach ($Duty_end_times as $dienstende) {
                if ($dienstende <= $time) {
                    //$Gegangene[$time] ++;
                    $Anwesende[$time] --;
                }
            }
            foreach ($Break_start_times as $lunch_break_start) {
                if ($lunch_break_start <= $time) {
                    //$Mittagende[$time] ++;
                    $Anwesende[$time] --;
                }
            }
            foreach ($Break_end_times as $lunch_break_end) {
                if ($lunch_break_end <= $time) {
                    //$Gemittagte[$time] ++;
                    $Anwesende[$time] ++;
                }
            }
        }
        return $Anwesende;
    }

    public static function read_opening_hours_from_database($date_unix, $branch_id) {
        $weekday = date('N', $date_unix);
        $sql_query = "SELECT * FROM opening_times WHERE weekday = :weekday AND branch_id = :branch_id";
        $result = database_wrapper::instance()->run($sql_query, array('branch_id' => $branch_id, 'weekday' => $weekday));
        $row = $result->fetch(PDO::FETCH_OBJ);
        if (!empty($row->start) and ! empty($row->end)) {
            $Opening_times['day_opening_start'] = roster_item::convert_time_to_seconds($row->start);
            $Opening_times['day_opening_end'] = roster_item::convert_time_to_seconds($row->end);
        } else {
            /*
             * TODO: Make an exception handler for error- and warning-messages!
             * throw new Exception_warning_message("Es wurden keine Öffnungszeiten hinterlegt. Bitte konfigurieren Sie den Mandanten.");
             */
            $Opening_times['day_opening_start'] = roster_item::convert_time_to_seconds('1:00');
            $Opening_times['day_opening_end'] = roster_item::convert_time_to_seconds('23:00');
        }
        return $Opening_times;
    }

}
