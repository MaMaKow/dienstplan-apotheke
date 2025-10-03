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

class AuthController extends BaseController {

    public function __construct() {
        // Für Authentication keine Session-Überprüfung
    }

    public function login($matches) {
        try {
            // Set the maximum allowed content length
            $maxContentLength = 1024; // 1 KB
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

            // Validate user_name and user_password
            $userName = isset($data['userName']) ? trim($data['userName']) : '';
            $userPassphrase = isset($data['userPassphrase']) ? $data['userPassphrase'] : '';

            // Validiere Username-Format (aber NICHT das Passwort!)
            if (empty($userName)) {
                http_response_code(400);
                echo json_encode(['error' => 'Username is required']);
                exit;
            }

            if (empty($userPassphrase)) {
                http_response_code(400);
                echo json_encode(['error' => 'Password is required']);
                exit;
            }

            // Username-Länge und Format prüfen
            if (strlen($userName) > 255) {
                http_response_code(400);
                echo json_encode(['error' => 'Username too long']);
                exit;
            }

            // Nur alphanumerische Zeichen, Unterstriche und Bindestriche erlauben
            if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $userName)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid username format']);
                exit;
            }

            // Session erstellen mit allowUnauthorized=true für API
            try {
                $session = new sessions();
            } catch (Exception $e) {
                http_response_code(500);
                error_log("Session creation failed: " . $e->getMessage());
                echo json_encode(['error' => 'Internal server error']);
                exit;
            }
            // User authentication
            $session->login($userName, $userPassphrase, FALSE);

            if ($session->user_is_logged_in()) {
                // Generate and return an access token
                $accessToken = $session->generateAccessToken($session->getUserObject());
                echo json_encode(['accessToken' => $accessToken]);
                die();
            } else {
                // Handle authentication failure
                echo json_encode(['error' => 'Authentication failed']);
                die();
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 401);
        }
    }
}
