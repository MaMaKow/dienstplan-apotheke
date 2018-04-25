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
    public $lunch_break_minutes;

    public function __construct($employee_id, $last_name, $first_name, $working_week_hours, $lunch_break_minutes, $profession, $branch) {
        $this->employee_id = $employee_id;
        $this->last_name = $last_name;
        $this->first_name = $first_name;
        $this->full_name = $first_name . " " . $last_name;
        $this->working_week_hours = $working_week_hours;
        $this->lunch_break_minutes = $lunch_break_minutes;
        $this->profession = $profession;
        $this->principle_branch_id = $branch;
        $this->read_principle_roster_from_database();
    }

    protected function read_principle_roster_from_database() {
        $sql_query = "SELECT * FROM `Grundplan`"
                . "WHERE `VK` = " . $this->employee_id;
        $result = mysqli_query_verbose($sql_query);
        while ($row = mysqli_fetch_object($result)) {
            /*
             * The primary key of the table `Grundplan` is VK + Wochentag + Mandant.
             * This is reflected by the keys in this array:
             */
            $this->Principle_roster[$row->Wochentag][$row->Mandant]['duty_start'] = $row->Dienstbeginn;
            $this->Principle_roster[$row->Wochentag][$row->Mandant]['duty_end'] = $row->Dienstende;
            $this->Principle_roster[$row->Wochentag][$row->Mandant]['break_start'] = $row->Mittagsbeginn;
            $this->Principle_roster[$row->Wochentag][$row->Mandant]['break_end'] = $row->Mittagsende;
            $this->Principle_roster[$row->Wochentag][$row->Mandant]['comment'] = $row->Kommentar;
        }
    }

}
