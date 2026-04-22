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
 * Description of class
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class Workforce {

    /**
     * @var array List_of_workforce_objects <p>is an array of known workforce objects</p>
     * @todo <p lang=de>Sobald alle existierenden und ehemaligen employees mit ihrem eigenen primary_key in der Tabelle stehen,
     *  gibt es wahrscheinlich keinen Grund mehr, hier zu unterscheiden.
     * Dann können wir alle Mitarbeiter in eine Instanz dieses Objektes laden.
     * Diese Liste von verschiedenen workforces braucht es dann nicht mehr.</p>
     */
    static private $ListOfWorkforceObjects = array();

    /**
     *
     * @var string $date_start_sql is the date string with which the object was instantiated. It is only stored for debugging purposes.
     */
    private $dateStartSql;

    /**
     *
     * @var string $date_end_sql is an optional date string with which the object was instantiated. It is only stored for debugging purposes.
     */
    private $dateEndSql;
    private $ListOfEmployees;
    private $ListOfQualifiedPharmacistEmployees;
    private $ListOfGoodsReceiptEmployees;
    private $ListOfCompoundingEmployees;
    private static $ListOfShortDescriptors;
    private static $ListOfAllEmployees;

    /**
     * @todo Use \DateTime in the constructor.
     * @param string $dateStartSql
     * @param string $dateEndSql
     */
    public function __construct(string $dateStartSql = NULL, string $dateEndSql = NULL) {
        $this->dateStartSql = $dateStartSql;
        $this->dateEndSql = $dateEndSql;
        if (isset(self::$ListOfWorkforceObjects[$this->dateStartSql][$this->dateEndSql])) {
            /**
             * If this exact workforce is known already, we do not have to repeat that queries.
             */
            $this->ListOfEmployees = self::$ListOfWorkforceObjects[$this->dateStartSql][$this->dateEndSql]->ListOfEmployees;
            $this->ListOfQualifiedPharmacistEmployees = self::$ListOfWorkforceObjects[$this->dateStartSql][$this->dateEndSql]->ListOfQualifiedPharmacistEmployees;
            $this->ListOfGoodsReceiptEmployees = self::$ListOfWorkforceObjects[$this->dateStartSql][$this->dateEndSql]->ListOfGoodsReceiptEmployees;
            $this->ListOfCompoundingEmployees = self::$ListOfWorkforceObjects[$this->dateStartSql][$this->dateEndSql]->ListOfCompoundingEmployees;
            return TRUE;
        }
        if (NULL === $dateStartSql) {
            $sqlQuery = 'SELECT * FROM `employees` '
                    . 'ORDER BY `last_name`, `first_name` ASC;';
            $result = \database_wrapper::instance()->run($sqlQuery);
        } else {
            if (NULL === $dateEndSql) {
                $dateEndSql = $dateStartSql;
            }
            $sqlQuery = 'SELECT * FROM `employees` '
                    . 'WHERE  (`end_of_employment` >= :date_start OR `end_of_employment` IS NULL) '
                    . 'AND  (`start_of_employment` <= :date_end OR `start_of_employment` IS NULL) '
                    . 'ORDER BY `last_name`, `first_name` ASC;';
            $result = \database_wrapper::instance()->run($sqlQuery, array('date_end' => $dateEndSql, 'date_start' => $dateStartSql));
        }
        $this->ListOfEmployees = array();
        $this->ListOfQualifiedPharmacistEmployees = array();
        $this->ListOfGoodsReceiptEmployees = array();
        $this->ListOfCompoundingEmployees = array();
        while ($row = $result->fetch(\PDO::FETCH_OBJ)) {
            $this->ListOfEmployees[$row->primary_key] = new \PDR\Workforce\employee((int) $row->primary_key, $row->last_name, $row->first_name, (float) $row->working_week_hours, (float) $row->lunch_break_minutes, $row->profession, $row->compounding, $row->goods_receipt, (int) $row->branch, $row->start_of_employment, $row->end_of_employment, $row->holidays);
            if (in_array($row->profession, array('Apotheker', 'PI'))) {
                $this->ListOfQualifiedPharmacistEmployees[] = $row->primary_key;
            }
            if (TRUE == $row->goods_receipt) {
                $this->ListOfGoodsReceiptEmployees[] = $row->primary_key;
            }
            if (TRUE == $row->compounding) {
                $this->ListOfCompoundingEmployees[] = $row->primary_key;
            }
        }
        self::$ListOfAllEmployees = $this->getListOfAllEmployees();
        $this->createListOfShortDescriptors();
        self::$ListOfWorkforceObjects[$this->dateStartSql][$this->dateEndSql] = $this;
    }

    public function getListOfEmployees(): array {
        return $this->ListOfEmployees;
    }

    public function getListOfQualifiedPharmacistEmployees(): array {
        return $this->ListOfQualifiedPharmacistEmployees;
    }

    public function getListOfGoodsReceiptEmployees(): array {
        return $this->ListOfGoodsReceiptEmployees;
    }

    public function getListOfCompoundingEmployees(): array {
        return $this->ListOfCompoundingEmployees;
    }

    /**
     * Get the last name of an employee
     *
     * @param int $employeeKey
     * @return string <p>last name of chosen employee or '???' if the employee is not known.
     * For example if an emergency service is not yet chosen ($employee_key = NULL)</p>
     */
    public function getEmployeeLastName(int $employeeKey): string {
        if (isset($this->ListOfEmployees[$employeeKey])) {
            return $this->ListOfEmployees[$employeeKey]->getLastName();
        }
        return $employeeKey . '???';
    }

    public function getEmployeeFirstName(int $employeeKey): string {
        if (isset($this->ListOfEmployees[$employeeKey])) {
            return $this->ListOfEmployees[$employeeKey]->getFirstName();
        }
        return $employeeKey . '???';
    }

    /**
     * Retrieve the full name of an employee based on their employee key.
     *
     * This function attempts to retrieve the last name and first name of an employee
     * using their unique employee key. If the last name is found, it constructs and
     * returns the full name in the format "FirstName LastName". If the last name is
     * not found, it returns the employee key concatenated with '???' as a string.
     *
     * @param int $employeeKey The unique identifier for the employee.
     * @return string The full name of the employee or the employee key followed by '???' if not found.
     */
    public function getEmployeeFullName(int $employeeKey): string {
        if (isset($this->ListOfEmployees[$employeeKey])) {
            return $this->ListOfEmployees[$employeeKey]->getFullName();
        }
        return $employeeKey . '???';
    }

    private function getListOfAllEmployees(): array {
        $ListOfAllEmployees = array();
        $sqlQuery = 'SELECT * FROM `employees` ORDER BY `last_name`, `first_name` ASC;';
        $result = \database_wrapper::instance()->run($sqlQuery);
        while ($row = $result->fetch(\PDO::FETCH_OBJ)) {
            $ListOfAllEmployees[$row->primary_key] = new \PDR\Workforce\employee((int) $row->primary_key, $row->last_name, $row->first_name, (float) $row->working_week_hours, (float) $row->lunch_break_minutes, $row->profession, $row->compounding, $row->goods_receipt, (int) $row->branch, $row->start_of_employment, $row->end_of_employment, $row->holidays);
        }
        return $ListOfAllEmployees;
    }

    /**
     * Get the profession of an employee
     *
     * @param int $employeeKey
     * @return string profession of the chosen employee
     */
    public function getEmployeeProfession($employeeKey): string {
        if (isset($this->ListOfEmployees[$employeeKey])) {
            return $this->ListOfEmployees[$employeeKey]->getProfession();
        }
        return $employeeKey . '???';
    }

    public function getEmployeeObject(?int $employeeKey): \PDR\Workforce\employee {
        if (isset(self::$ListOfAllEmployees[$employeeKey])) {
            if (self::$ListOfAllEmployees[$employeeKey] instanceof \PDR\Workforce\employee) {
                return self::$ListOfAllEmployees[$employeeKey];
            }
        }
        throw new \Exception('This employee does not exist!');
    }

    public function employeeExists(?int $employeeKey): bool {
        if (isset($this->ListOfEmployees[$employeeKey]) and $this->ListOfEmployees[$employeeKey] instanceof \PDR\Workforce\employee) {
            return TRUE;
        }
        return FALSE;
    }

    public function getListOfEmployeeNames(): array {
        $ListOfEmployeeLastNames = array();
        foreach ($this->ListOfEmployees as $employeeKey => $employee) {
            $ListOfEmployeeLastNames[$employeeKey] = $employee->getLastName();
        }
        return $ListOfEmployeeLastNames;
    }

    public function getListOfEmployeeProfessions(): array {
        $ListOfEmployeeProfessions = array();
        foreach ($this->ListOfEmployees as $employeeKey => $employee) {
            $ListOfEmployeeProfessions[$employeeKey] = $employee->getProfession();
        }
        return $ListOfEmployeeProfessions;
    }

    /**
     * <p lang=de>Ich hätte gerne einen sehr kurzen Deskriptor für die Mitarbeiter. Er sollte aber eindeutig sein.
     * Wie kann ich das ereichen?
     * Ich muss auf jeden Fall eine vollständige Liste der aktuellen Mitarbeiter haben.
     * Dann kann ich versuchen, ob ein kurzer String aus dem ersten und zweiten Buchstaben des Vor- und Nachnamen ausreicht.
     * Wenn nicht, muss ich weitere Buchstaben ergänzen.
     * Das sollte möglichst nicht ständig erfolgen.
     * Das Ergebnis sollte also static gespeichert werden.
     * </p>
     */
    public function getEmployeeShortDescriptor(int $employeeKey): string {
        if (empty(self::$ListOfShortDescriptors)) {
            $this->createListOfShortDescriptors();
        }
        return self::$ListOfShortDescriptors[$employeeKey];
    }

    /**
     * @todo <p>maybe write a test with very specific employee names
     * "Albert Polk",
     * "Alex Parbs",
     * "Alexandra Probst",
     * "Alexandra Prokoviev",</p>
     */
    private function createListOfShortDescriptors(): void {
        self::$ListOfShortDescriptors = array();
        foreach (self::$ListOfAllEmployees as $employeeKey => $employee) {
            $numberOfCharactersOfFirstName = 2;
            $numberOfCharactersOfLastName = 2;
            /**
             * Try to add into the array: 2+2
             */
            $shortDescriptor = $this->createShortDescriptor($employee, $numberOfCharactersOfFirstName, $numberOfCharactersOfLastName);
            $searchResult = array_search($shortDescriptor, self::$ListOfShortDescriptors, FALSE);
            if (FALSE === $searchResult) {
                self::$ListOfShortDescriptors[$employeeKey] = $shortDescriptor;
                continue;
            }
            $foundEmployeeObject = self::$ListOfAllEmployees[$searchResult];
            /**
             * Second try: 1+3
             */
            $numberOfCharactersOfFirstName = 1;
            $numberOfCharactersOfLastName = 3;
            $this->changeShortDescriptorByChars($foundEmployeeObject, $numberOfCharactersOfFirstName, $numberOfCharactersOfLastName);
            $shortDescriptor = $this->createShortDescriptor($employee, $numberOfCharactersOfFirstName, $numberOfCharactersOfLastName);
            $searchResult = array_search($shortDescriptor, self::$ListOfShortDescriptors, FALSE);
            if (FALSE === $searchResult) {
                self::$ListOfShortDescriptors[$employeeKey] = $shortDescriptor;
                continue;
            }
            /**
             * Third try: 0+4
             */
            $numberOfCharactersOfFirstName = 0;
            $numberOfCharactersOfLastName = 4;
            $this->changeShortDescriptorByChars($foundEmployeeObject, $numberOfCharactersOfFirstName, $numberOfCharactersOfLastName);
            $shortDescriptor = $this->createShortDescriptor($employee, $numberOfCharactersOfFirstName, $numberOfCharactersOfLastName);
            $searchResult = array_search($shortDescriptor, self::$ListOfShortDescriptors, FALSE);
            if (FALSE === $searchResult) {
                self::$ListOfShortDescriptors[$employeeKey] = $shortDescriptor;
                continue;
            }
            /**
             * Fourth try: 3+1
             */
            $numberOfCharactersOfFirstName = 3;
            $numberOfCharactersOfLastName = 1;
            $this->changeShortDescriptorByChars($foundEmployeeObject, $numberOfCharactersOfFirstName, $numberOfCharactersOfLastName);
            $shortDescriptor = $this->createShortDescriptor($employee, $numberOfCharactersOfFirstName, $numberOfCharactersOfLastName);
            $searchResult = array_search($shortDescriptor, self::$ListOfShortDescriptors, FALSE);
            if (FALSE === $searchResult) {
                self::$ListOfShortDescriptors[$employeeKey] = $shortDescriptor;
                continue;
            }
            /**
             * Last try: 1+1+primaryKey
             */
            $numberOfCharactersOfFirstName = 1;
            $numberOfCharactersOfLastName = 1;
            $this->changeShortDescriptorWithKey($foundEmployeeObject->getEmployeeKey(), $numberOfCharactersOfFirstName, $numberOfCharactersOfLastName);
            $this->changeShortDescriptorWithKey($employeeKey, $numberOfCharactersOfFirstName, $numberOfCharactersOfLastName);
        }
    }

    /**
     * @param type $employee
     * @param type $numberOfCharactersOfFirstName
     * @param type $numberOfCharactersOfLastName
     */
    private function changeShortDescriptorByChars(\PDR\Workforce\employee $employee, int $numberOfCharactersOfFirstName, int $numberOfCharactersOfLastName): void {
        $shortDescriptor = $this->createShortDescriptor($employee, $numberOfCharactersOfFirstName, $numberOfCharactersOfLastName);
        /**
         * Only add this variant, if it does not create another duplicate:
         */
        $searchResult = array_search($shortDescriptor, self::$ListOfShortDescriptors, FALSE);
        if (FALSE === $searchResult) {
            self::$ListOfShortDescriptors[$employee->getEmployeeKey()] = $shortDescriptor;
        }
    }

    /**
     * @param type $employeeKey
     * @param int $numberOfCharactersOfFirstName
     * @param int $numberOfCharactersOfLastName
     */
    private function changeShortDescriptorWithKey(\PDR\Workforce\employee $employeeKey, int $numberOfCharactersOfFirstName, int $numberOfCharactersOfLastName): void {
        $employee = $this->getEmployeeObject($employeeKey);
        $shortDescriptor = $this->createShortDescriptor($employee, $numberOfCharactersOfFirstName, $numberOfCharactersOfLastName);
        $shortDescriptor .= $employee->getEmployeeKey();
        self::$ListOfShortDescriptors[$employeeKey] = $shortDescriptor;
    }

    private function createShortDescriptor(\PDR\Workforce\employee $employee, int $numberOfCharactersOfFirstName, int $numberOfCharactersOfLastName): string {
        $shortDescriptor = "";
        $shortDescriptor .= mb_substr($employee->getFirstName(), 0, $numberOfCharactersOfFirstName);
        $shortDescriptor .= mb_substr($employee->getLastName(), 0, $numberOfCharactersOfLastName);
        return $shortDescriptor;
    }

    /**
     * We just return some random employee
     */
    public function getDefaultEmployeeKey(): ?int {
        if (isset($_SESSION['user_object']) and $_SESSION['user_object'] instanceof \user) {
            /**
             * Try to guess the employeeKey from the logged in user:
             */
            $employeeKey = $_SESSION['user_object']->get_employee_key();
            if ($this->employeeExists($employeeKey)) {
                return $employeeKey;
            }
        }
        if (!empty($this->ListOfEmployees and min($this->ListOfEmployees) instanceof \PDR\Workforce\employee)) {
            $employee = min($this->ListOfEmployees);
            $employeeKey = $employee->getEmployeeKey();
            return $employeeKey;
        }
        /**
         * If there is no employee at all in the workforce, we return NULL:
         */
        return NULL;
    }

    public function getEmptyEmployee(): \PDR\Workforce\employee {
        $privateKey = null;
        $lastName = null;
        $firstName = null;
        $workingWeekHours = 40;
        $lunchBreakMinutes = 30;
        $profession = null;
        $compounding = false;
        $goodsReceipt = false;
        $networkOfBranchOffices = new \PDR\Pharmacy\NetworkOfBranchOffices();
        $branchId = $networkOfBranchOffices->get_main_branch_id();
        $startOfEmployment = null;
        $endOfEmployment = null;
        $holidays = 28;
        $employee = new \PDR\Workforce\employee($privateKey, $lastName, $firstName, $workingWeekHours, $lunchBreakMinutes, $profession, $compounding, $goodsReceipt, $branchId, $startOfEmployment, $endOfEmployment, $holidays);
        return $employee;
    }

    public function getKeyByFullName(String $employeeFullName): int {
        foreach (self::$ListOfAllEmployees as $employeeKey => $employee) {
            if ($employee->getFullName() === $employeeFullName) {
                return $employeeKey;
            }
        }
    }

    public function getEmployeesAsJson(): string {
        $employees = [];
        foreach ($this->ListOfEmployees as $employeeKey => $employee) {
            $employees[] = [
                'id' => $employeeKey,
                'last_name' => $employee->getLastName(),
                'first_name' => $employee->getFirstName(),
                'profession' => $employee->getProfession(),
                'branch' => $employee->getPrincipleBranchId(),
                'start_of_employment' => $employee->getStartOfEmployment(),
                'end_of_employment' => $employee->getEndOfEmployment(),
            ];
        }
        return json_encode($employees, JSON_PRETTY_PRINT);
    }
}
