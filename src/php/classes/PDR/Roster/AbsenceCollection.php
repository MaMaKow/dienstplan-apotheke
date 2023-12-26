<?php

/*
 * Copyright (C) 2023 Mandelkow
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

class AbsenceCollection implements \IteratorAggregate {

    /**
     * Collection of Absence objects.
     *
     * This array holds instances of the Absence class, representing
     * different periods of absence for employees. The AbsenceCollection
     * manages and provides operations for these absence records.
     *
     * @var array
     */
    private $absences = array();

    public function addAbsence(\PDR\Roster\absence $absence): void {
        $this->absences[] = $absence;
    }

    // Implement the IteratorAggregate interface
    public function getIterator(): \ArrayIterator {
        return new \ArrayIterator($this->absences);
    }

    /**
     * Check if the collection contains an absence for a specific employee.
     *
     * This method iterates through the absences in the collection and checks if there
     * is an absence entry for the specified employee key.
     *
     * @param int $employeeKey The unique identifier for the employee.
     *
     * @return bool True if the collection contains an absence for the specified employee, false otherwise.
     */
    public function containsEmployeeKey(int $employeeKey): bool {
        foreach ($this->absences as $currentAbsence) {
            if ($currentAbsence->getEmployeeKey() === $employeeKey) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the AbsenceCollection is empty.
     *
     * @return bool True if the collection is empty, false otherwise.
     */
    public function isEmpty(): bool {
        // Check if the absences array is an empty array
        if (array() === $this->absences) {
            // If it is, return true indicating that the collection is empty
            return true;
        }
        // If the absences array is not empty, return false indicating that the collection is not empty
        return false;
    }

    /**
     * Retrieve the absence entry for a specific employee from the collection.
     *
     * This method iterates through the absences in the collection and returns the first
     * absence entry that corresponds to the specified employee key.
     * @CAVE! There might be multiple Absence objects of the same employee. This function only finds the first one.
     *
     * @param int $employeeKey The unique identifier for the employee.
     * @return Absence|null The Absence object for the specified employee, or null if not found.
     */
    public function getAbsenceByEmployeeKey(int $employeeKey): ?Absence {
        foreach ($this->absences as $currentAbsence) {
            if ($currentAbsence->getEmployeeKey() === $employeeKey) {
                return $currentAbsence;
            }
        }
        return null;
    }

    /**
     * Get the list of employee keys from the absences.
     *
     * @return array - An array of employee keys.
     */
    public function getListOfEmployeeKeys(): array {
        $listOfEmployeeKeys = array();
        foreach ($this->absences as $currentAbsence) {
            $listOfEmployeeKeys[] = $currentAbsence->getEmployeeKey();
        }
        return $listOfEmployeeKeys;
    }
}
