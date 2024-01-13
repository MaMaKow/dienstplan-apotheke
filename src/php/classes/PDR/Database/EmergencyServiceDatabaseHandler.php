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

namespace PDR\Database;

/**
 * Handles database operations related to pharmacy emergency services.
 *
 * This class provides methods for interacting with the database to retrieve information
 * about pharmacy emergency services, such as checking if the pharmacy has an active emergency
 * service on a specific date, retrieving emergency service details for a given date, and determining
 * if the emergency service was active until the dawn of a provided date.
 *
 * @author Mandelkow
 */
class EmergencyServiceDatabaseHandler {

    /**
     * Retrieves and returns the pharmacy's emergency service information for the specified date from the database.
     *
     * This method queries the `emergency_services` table in the database to fetch the emergency service details
     * for the given date. If an entry is found, it creates and returns an instance of the \PDR\Roster\EmergencyService
     * class representing the emergency service on that date.
     *
     * @param \DateTime $dateObject The date for which to retrieve emergency service information.
     * @return \PDR\Roster\EmergencyService Returns an instance of the \PDR\Roster\EmergencyService class if the service exists, otherwise throws an exception.
     * @throws \Exception Throws an exception if the pharmacy does not have an emergency service on the specified date.
     */
    public static function readEmergencyServiceOnDate(\DateTime $dateObject): \PDR\Roster\EmergencyService {
        $sqlQuery = "SELECT * FROM `emergency_services` WHERE `date` = :date";
        $result = \database_wrapper::instance()->run($sqlQuery, array('date' => $dateObject->format('Y-m-d')));
        while ($row = $result->fetch(\PDO::FETCH_OBJ)) {
            $employeeKey = $row->employee_key;
            $branchId = $row->branch_id;
            return new \PDR\Roster\EmergencyService($dateObject, $branchId, $employeeKey);
        }
        throw new \Exception("We don't have the pharmacy emergency service today.");
    }

    public static function readEmergencyServiceOnDawn(\DateTime $today): \PDR\Roster\EmergencyService {
        $yesterday = (clone $today)->sub(new \DateInterval('P1D'));
        return self::readEmergencyServiceOnDate($yesterday);
    }

    /**
     * Checks if the pharmacy has an active emergency service on the provided date.
     *
     * This method queries the database to determine if there is an entry for the given date
     * in the `emergency_services` table, indicating that the pharmacy has an active emergency service on that day.
     *
     * @param \DateTime $dateObject The date for which to check the pharmacy's emergency service.
     * @return bool Returns true if the pharmacy has an active emergency service on the provided date, false otherwise.
     */
    public static function isOurServiceDay(\DateTime $dateObject): bool {
        $sqlQuery = "SELECT * FROM `emergency_services` WHERE `date` = :date";
        $result = \database_wrapper::instance()->run($sqlQuery, array('date' => $dateObject->format('Y-m-d')));
        while ($row = $result->fetch(\PDO::FETCH_OBJ)) {
            return true;
        }
        return false;
    }

    /**
     * Checks if the pharmacy emergency service was active during the dawn (early morning) of the given date.
     *
     * This method calculates the dawn of the provided date and then checks if the emergency service
     * was active on the previous day and night until 8:00, which is when it ends at the dawn of the current date.
     *
     * @param \DateTime $today The date for which to check the dawn emergency service.
     * @return bool Returns true if the emergency service was active until 8:00 on the dawn of the provided date, false otherwise.
     */
    public static function isOurServiceDawn(\DateTime $today): bool {
        $yesterday = (clone $today)->sub(new \DateInterval('P1D'));
        return self::isOurServiceDay($yesterday);
    }

    /**
     * Adds a new emergency service entry to the database.
     *
     * @param string $dateNew The new date for the emergency service.
     * @param int $branchId The branch ID for the emergency service.
     */
    public static function add_emergency_service_entry(string $dateNew, int $branchId): void {
        $sqlQueryInsert = "INSERT INTO `emergency_services` (`date`, `branch_id`) VALUES(:date_new, :branch_id)";
        \database_wrapper::instance()->run($sqlQueryInsert, array(
            'branch_id' => $branchId,
            'date_new' => $dateNew,
        ));
    }

    /**
     * Updates an existing emergency service entry in the database.
     *
     * @param int $employeeKey The employee key for the emergency service.
     * @param string $dateNew The new date for the emergency service.
     * @param string $dateOld The old date for the emergency service.
     * @param int $branchId The branch ID for the emergency service.
     */
    public static function update_emergency_service_entry(int $employeeKey, string $dateNew, string $dateOld, int $branchId): void {
        $sqlQueryUpdate = "UPDATE `emergency_services` SET `employee_key` = :employee_key, `date` = :date_new WHERE `date` = :date_old AND branch_id = :branch_id";
        \database_wrapper::instance()->run($sqlQueryUpdate, array(
            'employee_key' => \user_input::convert_post_empty_to_php_null($employeeKey),
            'branch_id' => $branchId,
            'date_new' => $dateNew,
            'date_old' => $dateOld,
        ));
    }

    /**
     * Deletes an existing emergency service entry from the database.
     *
     * @param string $dateOld The old date for the emergency service.
     * @param int $branchId The branch ID for the emergency service.
     */
    public static function delete_emergency_service_entry(string $dateOld, int $branchId): void {
        $sqlQueryDelete = "DELETE FROM `emergency_services` WHERE `date` = :date_old AND branch_id = :branch_id";
        \database_wrapper::instance()->run($sqlQueryDelete, array(
            'branch_id' => $branchId,
            'date_old' => $dateOld,
        ));
    }

    public static function getListOfEmergencyServicesInYear(int $year, int $branchId): array {
        $listOfEmergencyServicesInYear = array();
        $sqlQuerySelect = "SELECT * FROM emergency_services WHERE YEAR(date) = :year AND branch_id = :branch_id";
        $result = \database_wrapper::instance()->run($sqlQuerySelect, array('year' => $year, 'branch_id' => $branchId));
        while ($row = $result->fetch(\PDO::FETCH_OBJ)) {
            $dateObject = new \DateTime($row->date);
            $emergencyService = new \PDR\Roster\EmergencyService($dateObject, $row->branch_id, $row->employee_key);
            $listOfEmergencyServicesInYear[] = $emergencyService;
        }
        return $listOfEmergencyServicesInYear;
    }
}
