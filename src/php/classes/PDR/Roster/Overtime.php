<?php

/*
 * Copyright (C) 2024 Mandelkow
 *
 * Dienstplan Apotheke
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace PDR\Roster;

/**
 * An Overtime is the difference between actual hours worked and
 * expected hours for a given period, such as a day, week or month.
 *
 * In the context of PDR, an overtime is allways tied to a specific date.
 * In most cases, this is the last day of the week for which the sums of
 * actual hours worked and expected hours are compared.
 *
 * @author Mandelkow
 */
class Overtime {

    private int $employeeKey;
    private \DateTime $dateObject;
    private float $hours;
    private float $balance;

    public function __construct(int $employeeKey, \DateTime $dateObject, float $hours, float $balance = null) {
        $this->employeeKey = $employeeKey;
        $this->dateObject = $dateObject;
        $this->hours = $hours;
        $this->balance = $balance;
    }

    public function getEmployeeKey(): int {
        return $this->employeeKey;
    }

    public function getDate(): \DateTime {
        return $this->dateObject;
    }

    public function getHours(): float {
        return $this->hours;
    }

    public function getBalance(): float {
        return $this->balance;
    }
}
