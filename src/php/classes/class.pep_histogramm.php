<?php

/*
 * Copyright (C) 2016 Mandelkow
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

class pep_histogramm {

    /**
     *
     * @param array $Roster
     * @param int $branch_id
     * @param array $Anwesende
     * @param string $date_unix
     * @var float $factor_employee The number of drug packages that can be sold per employee within a certain time.
     * @return string The canvas element
     */
    public function get_expectation_javascript_object($branch_id) {
        $Expectation = $this->get_expectation($branch_id);
        $Expectation_javascripft_object = json_encode($Expectation, JSON_UNESCAPED_UNICODE);
        return $Expectation_javascripft_object;
    }

    private function get_expectation($branch_id) {
        $Packungen = array();
        $Expectation = array();
        $factor_tag_im_monat = 1;
        $factor_monat_im_jahr = 1;
        /*
         * echo roster_image_histogramm::check_timeliness_of_pep_data();
         */
        $sql_weekday = 2;
        $month_day = 1;
        $month = 1;

        $network_of_branch_offices = new \PDR\Pharmacy\NetworkOfBranchOffices;
        $List_of_branch_objects = $network_of_branch_offices->get_list_of_branch_objects();
        $branch_pep_id = $List_of_branch_objects[$branch_id]->getPEP();
        if (empty($branch_pep_id)) {
            return FALSE;
        }
        $result = database_wrapper::instance()->run("SELECT Uhrzeit, Mittelwert FROM `pep_weekday_time`  WHERE Mandant = :branch_pep_id and Wochentag = :weekday",
                array(
                    'branch_pep_id' => $branch_pep_id,
                    'weekday' => $sql_weekday
        ));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $Packungen[$row->Uhrzeit] = $row->Mittelwert;
        }
        $result = database_wrapper::instance()->run("SELECT factor FROM `pep_month_day`  WHERE `branch` = :branch_pep_id and `day` = :month_day",
                array(
                    'branch_pep_id' => $branch_pep_id,
                    'month_day' => $month_day
        ));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $factor_tag_im_monat = $row->factor;
        }

        $result = database_wrapper::instance()->run("SELECT factor FROM `pep_year_month`  WHERE `branch` = :branch_pep_id and `month` = :month",
                array(
                    'branch_pep_id' => $branch_pep_id,
                    'month' => $month
        ));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $factor_monat_im_jahr = $row->factor;
        }
        foreach ($Packungen as $time => $average) {
            $Expectation[$time] = $average * $factor_monat_im_jahr * $factor_tag_im_monat;
        }
        return $Expectation;
    }

    /**
     * @todo Perhaps move this into a separate pep class?
     * @return \DateTime
     */
    private function get_last_update_of_pep_data() {
        //Check if the PEP information is still up-to-date:
        $sql_query = "SELECT max(Datum) as Datum FROM `pep`";
        $result = database_wrapper::instance()->run($sql_query);
        $row = $result->fetch(PDO::FETCH_OBJ);
        if (empty($row->Datum)) {
            return null;
        }

        $date_object = new DateTime($row->Datum);
        return $date_object;
    }

    public function get_last_update_of_pep_data_date_string() {
        $newest_pep_date = $this->get_last_update_of_pep_data();
        if (null == $newest_pep_date) {
            return "<p>" . gettext("There are no entries yet.") . "</p>\n";
        }
        $date_string = "<p>" . gettext("Last entry") . " " . $newest_pep_date->format('d.m.Y') . "</p>\n";
        return $date_string;
    }
}
