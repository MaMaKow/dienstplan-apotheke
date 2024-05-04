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
$session = new sessions();
$session->verifyAccessToken();

// Fetch the roster data from the database or any other source
// You may need to adjust the parameters based on your implementation
$startDate = new DateTime("monday this week");
$endDate = new DateTime("sunday this week");
$employeeKey = 55; // Replace with the actual employee key

try {
    $roster = new roster($startDate, $endDate, $employeeKey);
    $rosterData = $roster->encodeToJson(); // Use the function you created to encode data to JSON
    echo $rosterData;
} catch (Exception $e) {
// Handle exceptions, e.g., log the error or send an error response
    echo json_encode(['error' => $e->getMessage()]);
}
