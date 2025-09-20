<?php

/*
 * Copyright (C) 2025 Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
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
 * @todo Write a better way to manage the sessions.
 */
$allowUnauthorized = true;
$session = new sessions($allowUnauthorized);
$session->verifyAccessToken();

/**
 * Get input data from GET:
 */
$networkOfBranchOffices = new \PDR\Pharmacy\NetworkOfBranchOffices();
$defaultBranchId = $networkOfBranchOffices->get_main_branch_id();
$branchId = \user_input::getVariableFromSpecificInput('branchId', INPUT_GET, FILTER_SANITIZE_NUMBER_INT, $defaultBranchId);

try {
    if (false === $networkOfBranchOffices->branch_exists($branchId)) {
        throw new Exception("Branch with that ID does not exist.");
    }
    $branchObject = new PDR\Pharmacy\Branch($branchId);
    $branchData = $branchObject->encodeToJson();
    echo $branchData;
} catch (Exception $e) {
    /**
     * Handle exceptions
     */
    PDR\Utility\GeneralUtility::printDebugVariable($e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
