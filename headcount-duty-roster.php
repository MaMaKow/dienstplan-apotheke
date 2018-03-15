<?php

if (!empty($Roster)) {

    $Roster_of_all_employees = $Roster;
    $Roster_of_qualified_pharmacist_employees = roster_headcount::get_roster_of_qualified_pharmacist_employees($Roster);
    $Roster_of_goods_receipt_employees = roster_headcount::get_roster_of_goods_receipt_employees($Roster);

    $Changing_times = roster::calculate_changing_times($Roster);
    $Anwesende = roster_headcount::headcount_roster($Roster_of_all_employees, $Changing_times);
    $Wareneingang_Anwesende = roster_headcount::headcount_roster($Roster_of_goods_receipt_employees, $Changing_times);
    $Approbierten_anwesende = roster_headcount::headcount_roster($Roster_of_qualified_pharmacist_employees, $Changing_times);
} else {
    global $Warnmeldung;
    $Warnmeldung[] = "Kein Dienstplan gefunden beim Zeichnen des Histogramms.";
}

class roster_headcount {

    function __construct() {

    }

    public function get_roster_of_qualified_pharmacist_employees($Roster) {
        global $Approbierte_mitarbeiter;
        foreach ($Roster as $roster_day) {
            foreach ($roster_day as $roster_item_object) {
                if (in_array($roster_item_object->employee_id, $Approbierte_mitarbeiter)) {
                    $Roster_of_qualified_pharmacist_employees[] = $roster_item_object;
                }
            }
        }
        return $Roster_of_qualified_pharmacist_employees;
    }

    public function get_roster_of_goods_receipt_employees($Roster) {
        global $Wareneingang_Mitarbeiter;
        foreach ($Roster as $roster_day) {
            foreach ($roster_day as $roster_item_object) {
                if (in_array($roster_item_object->employee_id, $Wareneingang_Mitarbeiter)) {
                    $Roster_of_goods_receipt_employees[] = $roster_item_object;
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
            $Opening_times['day_opening_start'] = strtotime($row->Beginn);
            $Opening_times['day_opening_end'] = strtotime($row->Ende);
        } else {
            /*
             * TODO: Make an exception handler for error- and warning-messages!
             * throw new Exception_warning_message("Es wurden keine Öffnungszeiten hinterlegt. Bitte konfigurieren Sie den Mandanten.");
             */
            $Opening_times['day_opening_start'] = strtotime('1:00');
            $Opening_times['day_opening_end'] = strtotime('23:00');
        }
        return $Opening_times;
    }

}
