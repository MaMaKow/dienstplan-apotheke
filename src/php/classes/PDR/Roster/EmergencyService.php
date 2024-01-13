<?php

/*
 * Copyright (C) 2021 Mandelkow
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
 * <p lang=de>
 *   Ein Notdienst ist eine Öffnung der Apotheke nachts, an Wochenenden oder Feiertagen.
 *   Die Apotheke bleibt bis zum folgenden Tag um 08:00 Uhr geöffnet.
 * </p>
 *
 * @author Mandelkow
 */
class EmergencyService {

    private $dateObject;
    private $employeeKey;
    private $branchId;

    public function __construct(\DateTime $dateObject, int $branchId, ?int $employeeKey) {
        $this->dateObject = $dateObject;
        $this->employeeKey = $employeeKey;
        $this->branchId = $branchId;
    }

    public function getBranchId(): int {
        return $this->branchId;
    }

    public function getEmployeeShortDescriptor() {
        $workforce = new \workforce();
        return $workforce->get_employee_short_descriptor($this->employeeKey);
    }

    public function getEmployeeLastName(): string {
        $workforce = new \workforce($this->dateObject->format('Y-m-d'));
        if (is_integer($this->employeeKey)) {
            $employeeLastName = $workforce->get_employee_last_name($this->employeeKey);
            return $employeeLastName;
        }
        return '???';
    }

    public function getEmployeeKey(): ?int {
        return $this->employeeKey;
    }

    public function getBranchNameShort(): string {
        $networkOfBranchOffices = new \PDR\Pharmacy\NetworkOfBranchOffices();
        $ListOfBranchObjects = $networkOfBranchOffices->get_list_of_branch_objects();
        if (is_integer($this->branchId) and isset($ListOfBranchObjects[$this->branchId])) {
            $branchNameShort = $ListOfBranchObjects[$this->branchId]->getShortName();
            return $branchNameShort;
        }
        return '???';
    }

    public function getDateObject(): \DateTime {
        return $this->dateObject;
    }
}
