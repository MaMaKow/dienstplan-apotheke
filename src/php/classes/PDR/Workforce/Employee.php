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

namespace PDR\Workforce;

/**
 * An employee is someone, who works on one of the branches. The person can be scheduled into rosters, take vacation and collect overtime hours.
 *   An employee may register as a \user. The user_key WILL NOT be the the employee_key.
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class Employee {

    private $primary_key;
    private $first_name;
    private $last_name;
    private $full_name;
    private $profession;
    private $goods_receipt;
    private $compounding;

    /**
     *
     * @var int The branch_id of the typical branch, on which the most working hours are done.
     */
    private $principle_branch_id;

    /**
     *
     * @var float The working hours per week as contracted in the employment contract.
     */
    private $working_week_hours;

    /**
     *
     * @var float The number of days per week, which the employee normally works on.
     *   This can be a float if an employee works different days on alternating weeks.
     */
    private $working_week_days;
    private $lunch_break_minutes;

    /**
     *
     * @var string The first day on which the employee did work.
     *   This might be a day before the start of the actual contract.
     */
    private $start_of_employment;

    /**
     *
     * @var string The last day on which the employee did work.
     *   This might be a day after the start of the actual contract.
     *   This might also be a day without work if the employee was sick or had holidays or overtime left.
     */
    private $end_of_employment;

    /**
     *
     * @var int The number of vacation days per year, which the employee is granted.
     *   This is not a float, at least not in Germany. In Germany the number has to be rounded up [ceil()].
     */
    private $holidays;

    /**
     *
     * @var array  $Principle_roster is a list of unix dates and their associated normal rosters for this single employee.
     */
    private $Principle_roster;

    public function __construct($primaryKey, $lastName, $firstName, $workingWeekHours, $lunchBreakMinutes, $profession, $compounding, $goodsReceipt, $branch, $startOfEmployment, $endOfEmployment, $holidays) {
        $this->primary_key = $primaryKey;
        $this->last_name = $lastName;
        $this->first_name = $firstName;
        $this->full_name = $firstName . " " . $lastName;
        $this->working_week_hours = $workingWeekHours;
        $this->lunch_break_minutes = $lunchBreakMinutes;
        $this->profession = $profession;
        $this->compounding = $compounding;
        $this->goods_receipt = $goodsReceipt;
        $this->start_of_employment = $startOfEmployment;
        $this->end_of_employment = $endOfEmployment;
        $this->holidays = $holidays;
        $this->principle_branch_id = $branch;
        $this->Principle_roster = array();
        $this->working_week_days = \principle_roster::get_working_week_days($this->primary_key);
    }

    public function get_principle_roster_on_date(\DateTime $date_object): array {
        /**
         * @var int $date_unix is the unix timestamp representing the $date_object.
         */
        $date_unix = $date_object->getTimestamp();
        if (empty($this->Principle_roster[$date_unix])) {
            $Example_roster = \principle_roster::read_current_principle_employee_roster_from_database($this->primary_key, clone $date_object, clone $date_object);
            $this->Principle_roster[$date_unix] = $Example_roster[$date_unix];
        }
        return $this->Principle_roster[$date_unix];
    }

    /**
     * Calculates the principle working hours for the employee on a specified date.
     *
     * This function retrieves the employee's roster for the given date, calculates the principle working hours
     * for that day, and returns the sum. If the roster for the specified date is not already loaded, it fetches
     * the roster from the database and caches it for future use.
     *
     * @param DateTime $date_object The date for which to calculate the employee's principle working hours.
     * @return int The sum of the employee's working hours on the specified date.
     */
    public function getPrincipleHoursOnDate(\DateTime $date_object): float {
        /**
         * @var int $date_unix Unix timestamp representing the $date_object.
         */
        $date_unix = $date_object->getTimestamp();

        // Check if the roster for this date is already cached; if not, load it from the database.
        if (empty($this->Principle_roster[$date_unix])) {
            $Example_roster = \principle_roster::read_current_principle_employee_roster_from_database($this->primary_key, clone $date_object, clone $date_object);
            $this->Principle_roster[$date_unix] = $Example_roster[$date_unix];
        }

        // Sum the working hours for the employee in the roster for the specified date.
        $sumOfHours = 0;
        foreach ($this->Principle_roster[$date_unix] as $principleRosterItem) {
            $sumOfHours += $principleRosterItem->working_hours;
        }

        return $sumOfHours;
    }

    public function getEmployeeKey(): ?int {
        return $this->primary_key;
    }

    public function getFullName(): ?string {
        return $this->first_name . " " . $this->last_name;
    }

    public function getLastName(): ?string {
        return $this->last_name;
    }

    public function getFirstName(): ?string {
        return $this->first_name;
    }

    public function getPrincipleBranchId(): ?int {
        return $this->principle_branch_id;
    }

    public function getProfession(): ?string {
        return $this->profession;
    }

    public function getStartOfEmployment(): ?string {
        return $this->start_of_employment;
    }

    public function getEndOfEmployment(): ?string {
        return $this->end_of_employment;
    }

    public function canDoGoodsReceipt(): ?bool {
        return $this->goods_receipt;
    }

    public function canDoCompounding(): ?bool {
        return $this->compounding;
    }

    public function getWorkingWeekHours(): ?float {
        return $this->working_week_hours;
    }

    public function getWorkingWeekDays(): ?float {
        return $this->working_week_days;
    }

    public function getLunchBreakMinutes(): ?float {
        return $this->lunch_break_minutes;
    }

    public function getHolidays(): ?int {
        return $this->holidays;
    }
}
