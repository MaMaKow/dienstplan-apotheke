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

class UserController extends BaseController {

    public function getAllUsers($matches) {
        $this->requireAdminPrivileges();

        try {
            $userBase = new PDR\Workforce\user_base();
            $users = $userBase->getAllUsersAsJson(); // Diese Methode müsste noch implementiert werden
            $this->sendJson(json_decode($users));
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function getUserById($matches) {
        if (($matches[1] ?? null) === 'me') {
            return $this->getMyUserData();
        }

        $this->requireAdminPrivileges();

        try {
            $userId = (int) $matches[1];
            $user = new \user($userId);

            // Sicherheitsrelevante Daten entfernen
            $userData = $user->getSafeUserData();
            $this->sendJson($userData);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    /**
     * Returns the currently authenticated user as safe JSON data.
     */
    public function getMyUserData() {
        try {
            if (!$this->session->user_is_logged_in()) {
                $this->sendError('Not authenticated', 401);
            }

            $currentUser = $this->session->getUserObject();
            if (!$currentUser instanceof \user) {
                $this->sendError('Not authenticated', 401);
            }

            $userData = $currentUser->getSafeUserData();
            $this->sendJson($userData, 200);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

}
