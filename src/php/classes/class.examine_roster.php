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

class examine_roster {

    public $Anwesende; //this will also be used by: roster_image_histogramm::draw_image_histogramm
    private $Approbierten_anwesende;
    private $Wareneingang_Anwesende;

    public function __construct($Roster, $date_unix, $branch_id, $workforce) {

        $this->Roster_of_all_employees = $Roster;
        $this->Roster_of_qualified_pharmacist_employees = roster_headcount::get_roster_of_qualified_pharmacist_employees($Roster, $workforce);
        $this->Roster_of_goods_receipt_employees = roster_headcount::get_roster_of_goods_receipt_employees($Roster, $workforce);

        $this->Opening_times = roster_headcount::read_opening_hours_from_database($date_unix, $branch_id);
        $Changing_times = roster::calculate_changing_times($Roster);
        /*
         * Add the opening and closing time to the changing times:
         * This is necessary to allow examine_roster->check_for_sufficient_qualified_pharmacist_count()
         *   to look at the start of the day.
         */
        $Changing_times[] = $this->Opening_times['day_opening_start'];
        $Changing_times[] = $this->Opening_times['day_opening_end'];
        $this->Changing_times = roster::cleanup_changing_times($Changing_times);

        $this->Anwesende = roster_headcount::headcount_roster($this->Roster_of_all_employees, $this->Changing_times);
        $this->Wareneingang_Anwesende = roster_headcount::headcount_roster($this->Roster_of_goods_receipt_employees, $this->Changing_times);
        $this->Approbierten_anwesende = roster_headcount::headcount_roster($this->Roster_of_qualified_pharmacist_employees, $this->Changing_times);
    }

    /**
     * Test for overlaps of scheduling for all the employees on a given date.
     *
     * <p>For example an employee might be scheduled from 08:00 to 16:30 in one branch and from 10:00 to 18:30 in an other branch.
     * Without bilocation this is impossible.</p>
     *
     * @param string $date_sql
     * @param array $List_of_branch_objects
     * @param object $workforce
     */
    public function check_for_overlap($date_sql, $List_of_branch_objects, $workforce) {
        $sql_query = "SELECT `first`.`VK`,"
                . " `first`.`Dienstbeginn` as first_start, `first`.`Dienstende` as first_end, "
                . " `first`.`Mandant` as first_branch,"
                . " `second`.`Dienstbeginn` as second_start, `second`.`Dienstende` as second_end,"
                . " `second`.`Mandant` as second_branch"
                . " FROM `Dienstplan` AS first"
                . " 	INNER JOIN `Dienstplan` as second"
                . " 		ON first.VK = second.VK AND first.datum = second.datum"
                . " WHERE `first`.`Datum` = :date "
                . " 	AND ((`first`.`Dienstbeginn` != `second`.`Dienstbeginn` ) OR (`first`.`mandant` != `second`.`mandant` ))" //eliminate pure self-duplicates, primary key is VK+start+mandant
                . " 	AND (`first`.`Dienstbeginn` > `second`.`Dienstbeginn` AND `first`.`Dienstbeginn` < `second`.`Dienstende`)"; //find overlaping time values!

        $result = database_wrapper::instance()->run($sql_query, array('date' => $date_sql));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $message = sprintf(gettext('Conflict at employee %1s <br>%2s to %3s (%4s) <br>with<br>%5s to %6s (%7s)'), $workforce->List_of_employees[$row->VK]->last_name, $row->first_start, $row->first_end, $List_of_branch_objects[$row->first_branch]->short_name, $row->second_start, $row->second_end, $List_of_branch_objects[$row->second_branch]->short_name
            );
            user_dialog::add_message($message, E_USER_ERROR, TRUE);
        }
        return TRUE;
    }

    /**
     * Check if there are enough employees at the whole day in the chosen branch.
     *
     * <p>
     * The array $this->Anwesende holds the number of present employees.
     * $this->Anwesende is provided by roster_headcount::headcount_roster()
     * The $minimum_number_of_employees is hardcoded to 2. This might change in the future.
     * </p>
     *
     * @return boolean
     */
    public function check_for_sufficient_employee_count() {
        if (FALSE === $this->Anwesende) {
            return FALSE;
        }
        $minimum_number_of_employees = 2;
        foreach ($this->Anwesende as $zeit => $anwesende) {
            if ($anwesende < $minimum_number_of_employees
                    and $zeit < $this->Opening_times['day_opening_end']
                    and $zeit >= $this->Opening_times['day_opening_start']) {
                if (!isset($attendant_error)) {
                    $message = sprintf(gettext('At %1s there are less than %2s employees present.'), roster_item::format_time_integer_to_string($zeit), $minimum_number_of_employees);
                    user_dialog::add_message($message, E_USER_WARNING);
                    $attendant_error = true;
                }
            } else {
                unset($attendant_error);
            }
        }
    }

    /**
     * Check if there is at least one employee capable of goods reciept present the whole day.
     *
     * <p>
     * The applications warns for the full scheduled day.
     * Until now there is no knowledge about the times of goods reciept in the application.
     * This might change.
     * </p>
     *
     * @return boolean
     */
    public function check_for_sufficient_goods_receipt_count() {
        if (FALSE === $this->Wareneingang_Anwesende) {
            return FALSE;
        }
        foreach ($this->Wareneingang_Anwesende as $zeit => $anwesende_wareneingang) {
            // TODO: Die tatsächlichen Termine für den Wareneingang wären sinnvoller, als die Öffnungszeiten. ($Opening_times['day_opening_end'])
            if ($anwesende_wareneingang === 0 and $zeit < $this->Opening_times['day_opening_end'] and $zeit >= $this->Opening_times['day_opening_start']) {
                if (!isset($attendant_error)) {
                    $message = sprintf(gettext('At %1s there is no goods receipt employee present.'), roster_item::format_time_integer_to_string($zeit));
                    user_dialog::add_message($message, E_USER_WARNING);
                    $attendant_error = true;
                }
            } else {
                unset($attendant_error);
            }
        }
    }

    /**
     * Check if there is at least one qualified pharmacist employee present the whole day.
     *
     * <p>
     * The applications warns for the full scheduled day.
     * This application sees "Apotheker" and "PI" (=Pharmazieingenieur) as qualified pharmacist.
     * It does not discriminate between the two.
     * However the ApBetrO in Germany does discriminate between them in several ways.
     * </p>
     *
     * @return boolean
     */
    public function check_for_sufficient_qualified_pharmacist_count() {
        if (FALSE === $this->Approbierten_anwesende) {
            return FALSE;
        }
        foreach ($this->Approbierten_anwesende as $zeit => $anwesende_approbierte) {
            if ($anwesende_approbierte === 0 and $zeit < $this->Opening_times['day_opening_end'] and $zeit >= $this->Opening_times['day_opening_start']) {
                if (!isset($attendant_error)) {
                    $message = sprintf(gettext('At %1s there is no authorized person present.'), roster_item::format_time_integer_to_string($zeit));
                    user_dialog::add_message($message);
                    $attendant_error = true;
                }
            } else {
                unset($attendant_error);
            }
        }
    }

}
