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
class employee {

    public $employee_id;
    public $first_name;
    public $last_name;
    public $full_name;
    public $principle_branch_id;
    public $working_week_hours;
    public $working_week_days;
    public $lunch_break_minutes;
    public $start_of_employment;
    public $end_of_employment;
    public $holidays;
    public $Principle_roster;

    public function __construct($employee_id, $last_name, $first_name, $working_week_hours, $lunch_break_minutes, $profession, $branch, $start_of_employment, $end_of_employment, $holidays) {
        $this->employee_id = $employee_id;
        $this->last_name = $last_name;
        $this->first_name = $first_name;
        $this->full_name = $first_name . " " . $last_name;
        $this->working_week_hours = $working_week_hours;
        $this->lunch_break_minutes = $lunch_break_minutes;
        $this->profession = $profession;
        $this->start_of_employment = $start_of_employment;
        $this->end_of_employment = $end_of_employment;
        $this->holidays = $holidays;
        $this->principle_branch_id = $branch;
        $this->Principle_roster = $this->read_principle_roster_from_database();
        $this->working_week_days = $this->read_working_week_days_from_database();
    }

    protected function read_principle_roster_from_database() {
        $Principle_roster = array();
        $sql_query = "SELECT * FROM `Grundplan`"
                . "WHERE `VK` = :employee_id";
        $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $this->employee_id));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $pseudo_date_unix = time() + ($row->Wochentag - date('w')) * PDR_ONE_DAY_IN_SECONDS;
            $pseudo_date_sql = date('Y-m-d', $pseudo_date_unix);
            $Principle_roster[$row->Wochentag][] = new roster_item($pseudo_date_sql, (int) $row->VK, $row->Mandant, $row->Dienstbeginn, $row->Dienstende, $row->Mittagsbeginn, $row->Mittagsende, $row->Kommentar);
        }
        return $Principle_roster;
    }

    protected function read_working_week_days_from_database() {
        $sql_query = "SELECT `VK`, Count(DISTINCT `Wochentag`) as `working_week_days` FROM `Grundplan` WHERE `VK` = :employee_id";
        $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $this->employee_id));
        $row = $result->fetch(PDO::FETCH_OBJ);
        return (int) $row->working_week_days;
    }

}
