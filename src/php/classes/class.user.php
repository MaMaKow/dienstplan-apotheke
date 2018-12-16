<?php

/*
 * Copyright (C) 2018 Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
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

/**
 * Description of class
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class user {

    public $employee_id;
    public $user_name;
    public $email;
    private $password;
    public $status;
    private $failed_login_attempts;
    private $failed_login_attempt_time;
    public $receive_emails_on_changed_roster;
    public $created_at;
    public $updated_at;
    public $privileges;

    /* public $employee_object; */

    public function __construct($employee_id = NULL) {
        if (is_null($employee_id)) {
            /*
             * create empty user object:
             */
            return NULL;
        }
        $this->read_data_from_database($employee_id);
    }

    private function read_data_from_database($employee_id) {
        $sql_query = "SELECT * from `users` WHERE `employee_id` = :employee_id;";
        $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $this->employee_id = $row->employee_id;
            $this->user_name = $row->user_name;
            $this->email = $row->email;
            $this->password = $row->password;
            $this->status = $row->status;
            $this->failed_login_attempts = $row->failed_login_attempts;
            $this->failed_login_attempt_time = $row->failed_login_attempt_time;
            $this->receive_emails_on_changed_roster = (bool) $row->receive_emails_on_changed_roster;
            $this->created_at = $row->created_at;
            $this->updated_at = $row->updated_at;
            $this->read_privileges_from_database();
            return TRUE;
        }
        return FALSE;
    }

    private function read_privileges_from_database() {
        $sql_query = "SELECT * FROM `users_privileges` WHERE `employee_id` = :employee_id";
        $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $this->employee_id));
        $this->privileges = array();
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $this->privileges[] = $row->privilege;
        }
    }

    public function write_new_privileges_to_database($privileges = array()) {
        database_wrapper::instance()->beginTransaction();
        $sql_query = "DELETE FROM `users_privileges` WHERE `employee_id`  = :employee_id";
        database_wrapper::instance()->run($sql_query, array('employee_id' => $this->employee_id));
        foreach ($privileges as $privilege) {
            $sql_query = "INSERT INTO `users_privileges` (`employee_id`, `privilege`) VALUES(:employee_id, :privilege)";
            database_wrapper::instance()->run($sql_query, array('employee_id' => $this->employee_id, 'privilege' => $privilege));
        }
        database_wrapper::instance()->commit();
    }

    public function set_receive_emails_opt_in($receive_emails_opt_in) {
        $sql_query = "UPDATE `users` "
                . "SET `receive_emails_on_changed_roster` = :receive_emails_opt_in "
                . "WHERE `employee_id` = :employee_id";
        $result = database_wrapper::instance()->run($sql_query, array(
            'receive_emails_opt_in' => $receive_emails_opt_in,
            'employee_id' => $this->employee_id,
        ));
        return '00000' === $result->errorCode;
    }

    public function create_new($employee_id, $user_name, $password_hash, $email, $status) {
        $statement = $this->pdo->prepare("INSERT INTO"
                . " users (user_name, employee_id, password, email, status)"
                . " VALUES (:user_name, :employee_id, :password, :email, :status)");
        $result = $statement->execute(array(
            'user_name' => $user_name,
            'employee_id' => $employee_id,
            'password' => $password_hash,
            'email' => $email,
            'status' => $status,
        ));
        if (!$result) {
            /*
             * We were not able to create the user.
             */
            return FALSE;
        }
    }

    public function change_password($old_password, $new_password) {

    }

    public function wants_emails_on_changed_roster() {

    }

    public function activate() {
        return $this->set_status('active');
    }

    public function block() {
        return $this->set_status('blocked');
    }

    public function delete() {
        return $this->set_status('deleted');
    }

    private function set_status($new_status) {
        $sql_query = "UPDATE `users` SET `status` = :status WHERE `employee_id` = :employee_id";
        $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $this->employee_id, 'status' => $new_status));
        return '00000' === $result->errorCode;
    }

    public function exists() {
        $statement = $this->pdo->prepare("SELECT `employee_id` FROM `users` WHERE `employee_id` = :employee_id");
        $result = $statement->execute(array('employee_id' => $this->employee_id));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            return TRUE;
        }
        return FALSE;
    }

    public function guess_user_by_identifier($identifier) {
        $sql_query = "SELECT `employee_id` FROM `users` WHERE `employee_id` = :employee_id OR `email` = :email OR `user_name` = :user_name";
        $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $identifier, 'email' => $identifier, 'user_name' => $identifier));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $this->read_data_from_database($row->employee_id);
            return $row->employee_id;
        }
        return FALSE;
    }

    public function password_verify($user_password) {
        if (empty($user_password)) {
            return FALSE;
        }
        return password_verify($user_password, $this->password);
    }

    public function reset_failed_login_attempts() {
        $sql_query = "UPDATE users "
                . " SET failed_login_attempt_time = NOW(), "
                . " failed_login_attempts = 0 "
                . " WHERE `user_name` = :user_name";
        database_wrapper::instance()->run($sql_query, array('user_name' => $this->user_name));
    }

    public function register_failed_login_attempt() {
        $sql_query = "UPDATE users"
                . " SET failed_login_attempt_time = NOW(),"
                . " failed_login_attempts = IFNULL(failed_login_attempts, 0)+1"
                . " WHERE `user_name` = :user_name";
        database_wrapper::instance()->run($sql_query, array('user_name' => $this->user_name));
    }

}
