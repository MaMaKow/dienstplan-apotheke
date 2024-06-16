<?php

/*
 * Copyright (C) 2023 Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once '../../../../bootstrap.php';
/**
 * We create a session object.
 * Normally this is only possible in a logged in state or on the login page.
 * But in this case we will authorize via access token.
 * Therefore we create the session object and $allowUnauthorized.
 */
$allowUnauthorized = true;
$session = new sessions($allowUnauthorized);
$session->verifyAccessToken();
/**
 * Get input data from GET:
 */
$dateStartString = \user_input::getVariableFromSpecificInput('dateStart', INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS, (new DateTime('now'))->format("Y-m-d"));
$dateEndString = \user_input::getVariableFromSpecificInput('dateEnd', INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS, $dateStartString);
$workforce = new \workforce($dateStartString, $dateEndString);
$defaultEmployeeKey = $workforce->get_default_employee_key();
$defaultEmployeeFullName = $workforce->getEmployeeFullName($defaultEmployeeKey);
$employeeFullName = \user_input::getVariableFromSpecificInput('employeeFullName', INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS, $defaultEmployeeFullName);
$employeeKey = $workforce->getKeyByFullName($employeeFullName);

/**
 * Fetch the roster data from the database or any other source
 */
$startDate = new DateTime($dateStartString);
$endDate = new DateTime($dateEndString);
//$branch_id = 1;

try {
    $roster = new roster($startDate, $endDate, $employeeKey);
    //$roster = roster::read_roster_from_database($branch_id, $startDate->format('Y-m-d'), $endDate->format('Y-m-d'));
    $rosterData = $roster->encodeToJson();
    echo $rosterData;
} catch (Exception $e) {
    /**
     * Handle exceptions, e.g., log the error or send an error response
     */
    PDR\Utility\GeneralUtility::printDebugVariable($e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
