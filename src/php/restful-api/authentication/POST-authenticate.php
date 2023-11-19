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
// Set the maximum allowed content length
$maxContentLength = 1024 * 1024; // 1 MB, adjust as needed
// Read the raw JSON data from the request body, limiting the content length
$jsonData = file_get_contents("php://input", false, null, 0, $maxContentLength + 1);

// Check if the content length exceeds the limit
if (strlen($jsonData) > $maxContentLength) {
    // Handle the request with excessive content length
    echo json_encode(['error' => 'Request payload too large']);
    exit;
}
// Decode JSON data
$data = json_decode($jsonData, true);

if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    // Handle JSON decoding error
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

// Validate and sanitize user_name and user_password
$user_name = filter_var($data['user_name'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$user_password = filter_var($data['user_password'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// Assuming you have a class/method for user authentication
$session = new sessions();
$session->login($user_name, $user_password, FALSE);

if ($session->user_is_logged_in()) {
    // Generate and return an access token
    $accessToken = $session->generateAccessToken($session->getUserObject());
    echo json_encode(['access_token' => $accessToken]);
} else {
    // Handle authentication failure
    echo json_encode(['error' => 'Authentication failed']);
}
