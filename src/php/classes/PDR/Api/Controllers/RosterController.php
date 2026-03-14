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

            // Debug
            //\PDR\Utility\GeneralUtility::printDebugVariable($employeeKey);
            //\PDR\Utility\GeneralUtility::printDebugVariable($branchId);
            //\PDR\Utility\GeneralUtility::printDebugVariable($startDate);
            //\PDR\Utility\GeneralUtility::printDebugVariable($endDate);
            // Je nach Kombination der Filter passende Funktion aufrufen:
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
}
