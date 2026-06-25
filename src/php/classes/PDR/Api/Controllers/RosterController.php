<?php

/*
 * Copyright (C) 2015 Mandelkow
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
require_once 'BaseController.php';

use PDR\Utility\GeneralUtility;

class RosterController extends BaseController {

    public function getRosters($matches) {
        try {
            $employeeKey = user_input::getVariableFromSpecificInput('employeeKey', INPUT_GET, FILTER_VALIDATE_INT, null);
            $branchId = user_input::getVariableFromSpecificInput('branchId', INPUT_GET, FILTER_VALIDATE_INT, null);
            $startParam = user_input::getVariableFromSpecificInput('dateStart', INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS, null);
            $endParam = user_input::getVariableFromSpecificInput('dateEnd', INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS, null);
            if ($employeeKey === false) {
                $employeeKey = null;
            }
            if ($branchId === false) {
                $branchId = null;
            }
            try {
                $startDate = $startParam ? new DateTime($startParam) : new DateTime('Monday this week');
                $endDate = $endParam ? new DateTime($endParam) : new DateTime('Sunday this week');
            } catch (Exception $exception) {
                throw new InvalidArgumentException('Invalid date format. Expected YYYY-MM-DD.');
            }

            if (null !== $employeeKey or null !== $branchId) {
                $roster = new roster($startDate, $endDate, $employeeKey, $branchId);
                $this->sendJson(json_decode($roster->encodeToJson(), true));
            } else {
                throw new \Exception("You must provide either a 'branch' or an 'employee' parameter.");
            }
        } catch (Exception $exception) {
            PDR\Utility\GeneralUtility::printDebugVariable($exception);
            $this->sendError($exception->getMessage());
        }
    }

    /**
     * Updates a roster in the database.
     *
     * @param array $matches
     * @return void
     */
    public function updateRoster($matches) {
        $this->requireRosterEditPrivileges(); // Will exit if the user does not have the required privileges.
        $branchIdInput = $matches['0'];
        $dateStartInput = $matches['1'];
        $dateEndInput = $matches['2'];

        try {
            $rosterFromPut = user_input::getRosterFromPutSecure();
            $firstRosterDay = reset($rosterFromPut);
            $firstRosterItem = reset($firstRosterDay);
            $lastRosterDay = end($rosterFromPut);
            $lastRosterItem = end($lastRosterDay);
            if ($firstRosterItem->get_branch_id() != $branchIdInput) {
                throw new InvalidArgumentException('The provided branch ID does not match the URL parameters.');
            }
            if ($firstRosterItem->get_date_start() != $dateStartInput) {
                GeneralUtility::printDebugVariable($firstRosterItem->get_date_sql());
                GeneralUtility::printDebugVariable($dateStartInput);
                throw new InvalidArgumentException('The provided start date does not match the URL parameters.');
            }
            if ($lastRosterItem->get_date_sql() != $dateEndInput) {
                GeneralUtility::printDebugVariable($lastRosterItem->get_date_sql());
                GeneralUtility::printDebugVariable($dateEndInput);
                throw new InvalidArgumentException('The provided end date does not match the URL parameters.');
            }
            user_input::roster_write_user_input_to_database($rosterFromPut);
            $this->sendJson(['message' => 'Roster updated successfully'], 200);
        } catch (Exception $exception) {
            PDR\Utility\GeneralUtility::printDebugVariable($exception);
            $this->sendError($exception->getMessage());
        }
    }

    /**
     * Deletes a roster day from the database.
     *
     * @param array $matches
     * @return void
     */
    public function deleteRoster($matches) {
        $this->requireRosterEditPrivileges(); // Will exit if the user does not have the required privileges.
        try {
            $branchId = $matches['0'];
            $date = $matches['1'];
            user_input::deleteRosterDayFromDatabase($branchId, $date);
            $this->sendJson(['message' => 'Roster day deleted successfully'], 200);
        } catch (Exception $exception) {
            PDR\Utility\GeneralUtility::printDebugVariable($exception);
            $this->sendError($exception->getMessage());
        }
    }
}
