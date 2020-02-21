<?php

/*
 * Copyright (C) 2018 Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
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
 * An employee is someone, who works on one of the branches. The person can be scheduled into rosters, take vacation and collect overtime hours.
 *   An employee may register as a \user. The user_id will be the same, as the employee_id.
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class employee {

    public $employee_id;
    public $first_name;
    public $last_name;
    public $full_name;
    public $profession;

    /**
     *
     * @var int The branch_id of the typical branch, on which the most working hours are done.
     */
    public $principle_branch_id;

    /**
     *
     * @var float The working hours per week as contracted in the employment contract.
     */
    public $working_week_hours;

    /**
     *
     * @var float The number of days per week, which the employee normally works on.
     *   This can be a float if an employee works different days on alternating weeks.
     */
    public $working_week_days;
    public $lunch_break_minutes;

    /**
     *
     * @var string The first day on which the employee did work.
     *   This might be a day before the start of the actual contract.
     */
    public $start_of_employment;

    /**
     *
     * @var string The last day on which the employee did work.
     *   This might be a day after the start of the actual contract.
     *   This might also be a day without work if the employee was sick or had holidays or overtime left.
     */
    public $end_of_employment;

    /**
     *
     * @var int The number of vacation days per year, which the employee is granted.
     *   This is not a float, at least not in Germany. In Germany the number has to be rounded up [ceil()].
     */
    public $holidays;

    /**
     *
     * @var array  $Principle_roster is a list of unix dates and their associated normal rosters for this single employee.
     */
    private $Principle_roster;

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
        $this->Principle_roster = array();
        $this->working_week_days = principle_roster::get_working_week_days($this->employee_id);
    }

    public function get_principle_roster_on_date(DateTime $date_object) {
        /**
         * @var int $date_unix is the unix timestamp representing the $date_object.
         */
        $date_unix = $date_object->getTimestamp();
        if (empty($this->Principle_roster[$date_unix])) {
            $Principle_roster_on_date = principle_roster::read_current_principle_employee_roster_from_database($this->employee_id, $date_object, $date_object);
            $this->Principle_roster[$date_unix] = $Principle_roster_on_date[$date_unix];
        }
        return $this->Principle_roster[$date_unix];
    }

}
