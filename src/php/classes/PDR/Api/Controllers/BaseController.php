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

abstract class BaseController {

    protected $session;

    public function __construct() {
        $this->initializeSession();
    }

    protected function initializeSession() {
        $this->session = new sessions();
        $this->session->verifyAccessToken(); //Beendet die Ausführung des Scripts bei fehlendem Login
    }

    protected function requireAdminPrivileges() {
        if (!$this->session->user_has_privilege(\sessions::PRIVILEGE_ADMINISTRATION)) {
            $this->sendError('You need administrative privileges for this action.', 403);
        }
    }

    protected function sendJson($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function sendError($message, $statusCode = 400) {
        $this->sendJson(['error' => $message], $statusCode);
    }

    protected function sendSuccess($data = null, $message = 'Success') {
        $response = ['message' => $message];
        if ($data !== null) {
            $response['data'] = $data;
        }
        $this->sendJson($response);
    }
}
