<?php

abstract class roster_headcount {

    public function get_roster_of_qualified_pharmacist_employees($Roster) {
        global $Approbierte_mitarbeiter;
        foreach ($Roster as $roster_day) {
            foreach ($roster_day as $roster_item_object) {
                if (in_array($roster_item_object->employee_id, array_keys($Approbierte_mitarbeiter))) {
                    $Roster_of_qualified_pharmacist_employees[$roster_item_object->date_unix][] = $roster_item_object;
                }
            }
        }
        return $Roster_of_qualified_pharmacist_employees;
    }

    public function get_roster_of_goods_receipt_employees($Roster) {
        global $Wareneingang_Mitarbeiter;
        foreach ($Roster as $roster_day) {
            foreach ($roster_day as $roster_item_object) {
                if (in_array($roster_item_object->employee_id, array_keys($Wareneingang_Mitarbeiter))) {
                    $Roster_of_goods_receipt_employees[$roster_item_object->date_unix][] = $roster_item_object;
                }
            }
        }
        return $Roster_of_goods_receipt_employees;
    }

    public static function headcount_roster($Roster, $Changing_times) {
        /* @var $Anwesende array */
        $Anwesende = array();
        foreach ($Roster as $roster_day) {
            foreach ($roster_day as $roster_item_object) {
                $Duty_start_times[] = $roster_item_object->duty_start_int;
                $Duty_end_times[] = $roster_item_object->duty_end_int;
                $Break_start_times[] = $roster_item_object->break_start_int;
                $Break_end_times[] = $roster_item_object->break_end_int;
            }
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
        $sql_query = "SELECT * FROM Öffnungszeiten WHERE Wochentag = " . date('N', $date_unix) . " AND Mandant = " . $branch_id;
        $result = mysqli_query_verbose($sql_query);
        $row = mysqli_fetch_object($result);
        if (!empty($row->Beginn) and ! empty($row->Ende)) {
            $Opening_times['day_opening_start'] = roster_item::convert_time_to_seconds($row->Beginn);
            $Opening_times['day_opening_end'] = roster_item::convert_time_to_seconds($row->Ende);
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
