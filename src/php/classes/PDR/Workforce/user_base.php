<?php

/*
 * Copyright (C) 2022 Mandelkow
 *
 * Dienstplan Apotheke
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * The user_base is the collection of users registered to access this instance of PDR.
 *
 * @author Mandelkow
 */

namespace PDR\Workforce;

class user_base {

    private $user_list;

    function __construct() {
        $this->read_user_list_from_database();
    }

    private function read_user_list_from_database() {
        $this->user_list = array();
        $sql_query = "SELECT `primary_key` FROM `users` ORDER BY `primary_key` ASC";
        $result = \database_wrapper::instance()->run($sql_query);
        while ($row = $result->fetch(\PDO::FETCH_OBJ)) {
            $this->user_list[$row->primary_key] = new \user($row->primary_key);
        }
    }

    public function get_user_list() {
        return $this->user_list;
    }

    /**
     *
     * @param string $identifier
     * @return boolean|int primary_key
     */
    public function guess_user_id_by_identifier($identifier) {
        $sql_query = "SELECT `primary_key` FROM `users` WHERE `email` = :email OR `user_name` = :user_name";
        $result = \database_wrapper::instance()->run($sql_query, array('email' => $identifier, 'user_name' => $identifier));
        while ($row = $result->fetch(\PDO::FETCH_OBJ)) {
            return $row->primary_key;
        }
        return FALSE;
    }

    public function guess_user_by_identifier($identifier) {
        $primary_key = $this->guess_user_id_by_identifier($identifier);
        if (false === $primary_key) {
            return false;
        }
        return $this->user_list[$primary_key];
    }

    public function create_new_user(int $employee_key = null, string $user_name, string $password_hash, string $email, string $status) {
        $user = new \user(null);
        $new_user = $user->create_new($employee_key, $user_name, $password_hash, $email, $status);
        $this->read_user_list_from_database();
        return $new_user;
    }

}
