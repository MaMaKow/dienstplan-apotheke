<?php

/*
 * TODO: insert license header in this file and every other file!
 */

class examine_roster {

    public $Anwesende; //this will also be used by: roster_image_histogramm::draw_image_histogramm
    private $Approbierten_anwesende;
    private $Wareneingang_Anwesende;

    public function __construct($Roster, $date_unix, $branch_id) {
        $this->Roster_of_all_employees = $Roster;
        $this->Roster_of_qualified_pharmacist_employees = roster_headcount::get_roster_of_qualified_pharmacist_employees($Roster);
        $this->Roster_of_goods_receipt_employees = roster_headcount::get_roster_of_goods_receipt_employees($Roster);

        $this->Changing_times = roster::calculate_changing_times($Roster);
        $this->Anwesende = roster_headcount::headcount_roster($this->Roster_of_all_employees, $this->Changing_times);
        $this->Wareneingang_Anwesende = roster_headcount::headcount_roster($this->Roster_of_goods_receipt_employees, $this->Changing_times);
        $this->Approbierten_anwesende = roster_headcount::headcount_roster($this->Roster_of_qualified_pharmacist_employees, $this->Changing_times);
        $this->Opening_times = roster_headcount::read_opening_hours_from_database($date_unix, $branch_id);
    }

    public function check_for_overlap($date_sql, &$Error_message) {
        global $List_of_branch_objects, $workforce;
        $sql_query = "SELECT `first`.`VK`,"
                . " `first`.`Dienstbeginn` as first_start, `first`.`Dienstende` as first_end, "
                . " `first`.`Mandant` as first_branch,"
                . " `second`.`Dienstbeginn` as second_start, `second`.`Dienstende` as second_end,"
                . " `second`.`Mandant` as second_branch"
                . " FROM `Dienstplan` AS first"
                . " 	INNER JOIN `Dienstplan` as second"
                . " 		ON first.VK = second.VK AND first.datum = second.datum" //compare multiple different rows together
                . " WHERE `first`.`Datum` = '$date_sql'" //some real date here
                . " 	AND ((`first`.`Dienstbeginn` != `second`.`Dienstbeginn` ) OR (`first`.`mandant` != `second`.`mandant` ))" //eliminate pure self-duplicates, primary key is VK+start+mandant
                . " 	AND (`first`.`Dienstbeginn` > `second`.`Dienstbeginn` AND `first`.`Dienstbeginn` < `second`.`Dienstende`)"; //find overlaping time values!

        $result = mysqli_query_verbose($sql_query);
        while ($row = mysqli_fetch_array($result)) {
            $Error_message[] = "Konflikt bei Mitarbeiter " . $workforce->List_of_employees[$row['VK']]->last_name . "<br>"
                    . $row['first_start'] . " bis " . $row['first_end'] . " (" . $List_of_branch_objects[$row['first_branch']]->short_name . ") "
                    . "mit <br>" . $row['second_start'] . " bis " . $row['second_end'] . " (" . $List_of_branch_objects[$row['second_branch']]->short_name . ")!";
        }
    }

    public function check_for_sufficient_employee_count(&$Fehlermeldung, $minimum_number_of_employees = 2) {
        /*
         * TODO: Write a more general version of this, maybe?
         * This might obsolete the function examine_roster::check_for_sufficient_goods_receipt_count
         * There are different types of employees to check for.
         * THere are different grades of severity.
         *
         */
        if (FALSE === $this->Anwesende) {
            return FALSE;
        }
        foreach ($this->Anwesende as $zeit => $anwesende) {
            if ($anwesende < $minimum_number_of_employees and $zeit < $this->Opening_times['day_opening_end'] and $zeit >= $this->Opening_times['day_opening_start']) {
                if (!isset($attendant_error)) {
                    $Fehlermeldung[] = 'Um ' . date('H:i', $zeit) . " Uhr sind weniger als $minimum_number_of_employees Mitarbeiter anwesend.";
                    $attendant_error = true;
                }
            } else {
                unset($attendant_error);
            }
        }
    }

    public function check_for_sufficient_goods_receipt_count(&$Warning_message) {
        if (FALSE === $this->Wareneingang_Anwesende) {
            return FALSE;
        }
        foreach ($this->Wareneingang_Anwesende as $zeit => $anwesende_wareneingang) {
            // TODO: Die tatsächlichen Termine für den Wareneingang wären sinnvoller, als die Öffnungszeiten. ($Opening_times['day_opening_end'])
            if ($anwesende_wareneingang === 0 and $zeit < $this->Opening_times['day_opening_end'] and $zeit >= $this->Opening_times['day_opening_start']) {
                if (!isset($attendant_error)) {
                    $Warning_message[] = 'Um ' . date('H:i', $zeit) . ' Uhr ist niemand für den Wareneingang anwesend.';
                    $attendant_error = true;
                }
            } else {
                unset($attendant_error);
            }
        }
    }

    public function check_for_sufficient_qualified_pharmacist_count(&$Error_message) {
        if (FALSE === $this->Approbierten_anwesende) {
            return FALSE;
        }
        foreach ($this->Approbierten_anwesende as $zeit => $anwesende_approbierte) {
            if ($anwesende_approbierte === 0 and $zeit < $this->Opening_times['day_opening_end'] and $zeit >= $this->Opening_times['day_opening_start']) {
                if (!isset($attendant_error)) {
                    $Error_message[] = sprintf(gettext('At %1s there is no authorized person present.'), date('H:i', $zeit));
                    $attendant_error = true;
                }
            } else {
                unset($attendant_error);
            }
        }
    }

}
