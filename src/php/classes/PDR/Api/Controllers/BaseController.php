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

    /**
     * @var user object holding a user instance of the currently authenticated user.
     *  Will be null if no valid JWT token is provided in the request.
     */
    protected $userObject;

    public function __construct() {
        $this->initializeSession();
    }

    protected function initializeSession() {
        $jwtHandler = new \PDR\Security\JwtHandler();
        $this->userObject = $jwtHandler->verifyAccessToken(); //Beendet die Ausführung des Scripts bei fehlendem Login
    }

    protected function requireAdminPrivileges() {
        if (!$this->userObject->has_privilege(\sessions::PRIVILEGE_ADMINISTRATION)) {
            $this->sendError('You need administrative privileges for this action.', 403);
            exit;
        }
    }
    protected function requireRosterEditPrivileges() {
        if (!$this->userObject->has_privilege(\sessions::PRIVILEGE_CREATE_ROSTER)) {
            $this->sendError('You need roster edit privileges for this action.', 403);
            exit;
        }
    }
    /**
     * @param mixed $data
     * @param int $statusCode
     */
    protected function sendJson($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function sendError(string $message, $statusCode = 400) {
        $this->sendJson(['error' => $message], $statusCode);
        exit;
    }
}
