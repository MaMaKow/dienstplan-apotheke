<?php

/*
 * Copyright (C) 2018 Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
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
 * A user is an employee, who has registered a user account in PDR.
 * Users can login into the program and view data.
 * Some possibilities reading / writing / editing are restricted to users with specific privileges.
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class user {

    /**
     *
     * @var int <p>The employee_id is the primary key of the `users` table.
     *  It refers to the primary key `id` in the `employees` table of the database.</p>
     */
    public $employee_id;

    /**
     *
     * @var string <p>The user_name can be freely chosen by the employee (FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW).
     * It is a UNIQUE KEY in the `users` table.</p>
     */
    public $user_name;

    /**
     *
     * @var string <p>An email address of the user.
     * It is a UNIQUE KEY in the `users` table.</p>
     */
    public $email;

    /**
     *
     * @var string a hash of the password created by password_hash($password, PASSWORD_DEFAULT);
     */
    private $password;

    /**
     *
     * @var string <p>A user can be either 'deleted', 'blocked', 'inactive' or 'active'.
     * Only 'active' users can login into PDR.
     * </p>
     * @todo <p>There is no gui for managing the status. 'inactive' (new) users can be activated or blocked.
     * But no other transitions are possible.</p>
     */
    public $status;

    /**
     *
     * @var int <p>The number of failed login attempts since the last successfull login.
     * If there are more than 5 failed attempts, then any login to this user will be blocked for 5 minutes</p>
     */
    public $failed_login_attempts;

    /**
     *
     * @var string <p>A MySQL time string of the last login attempt.
     *  This value does not disciminate between successfull and failed attempts.</p>
     */
    public $failed_login_attempt_time;

    /**
     *
     * @var bool <p>A user may choose (opt-in) to receive email notifications when his roster is changed.
     * Emails will be sent at maximum once a day and only for future rosters up to 2 weeks in advance.</p>
     */
    public $receive_emails_on_changed_roster;

    /**
     *
     * @var string <p>A MySQL time string of the creation of the user.</p>
     */
    public $created_at;

    /**
     *
     * @var string <p>A MySQL time string of the last change to the `user` in the database.</p>
     */
    public $updated_at;

    /**
     *
     * @var array <p>Some possibilities reading / writing / editing are restricted to users with specific privileges.
     * This array is a list of all the given privileges for the user.
     * </p>
     */
    public $privileges;

    /* public $employee_object; */

    /**
     * <p>It is possible to create an empty user.</p>
     *
     * @param int $employee_id
     * @return void
     */
    public function __construct($employee_id = NULL) {
        if (is_null($employee_id)) {
            /*
             * create empty user object:
             */
            return NULL;
        }
        $this->read_data_from_database($employee_id);
    }

    /**
     * Read the user data from the database and store it in the object.
     *
     * @param int $employee_id
     * @return boolean <p>success of the database calls.</p>
     */
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

    /**
     * Read the privileges of the user from the database and store them in the object.
     */
    private function read_privileges_from_database() {
        $sql_query = "SELECT * FROM `users_privileges` WHERE `employee_id` = :employee_id";
        $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $this->employee_id));
        $this->privileges = array();
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $this->privileges[] = $row->privilege;
        }
    }

    /**
     *
     * <p>All privileges which are inside the array will be added to the users privileges.
     * All absent privileges will be removed from the user.</p>
     *
     * @param array $privileges
     */
    public function write_new_privileges_to_database($privileges = array()) {
        database_wrapper::instance()->beginTransaction();
        $sql_query = "DELETE FROM `users_privileges` WHERE `employee_id`  = :employee_id";
        database_wrapper::instance()->run($sql_query, array('employee_id' => $this->employee_id));
        foreach ($privileges as $privilege) {
            $sql_query = "INSERT INTO `users_privileges` (`employee_id`, `privilege`) VALUES(:employee_id, :privilege)";
            database_wrapper::instance()->run($sql_query, array('employee_id' => $this->employee_id, 'privilege' => $privilege));
        }
        database_wrapper::instance()->commit();
        $this->read_privileges_from_database();
    }

    /**
     *
     * Store a new decision on `receive_emails_on_changed_roster` in the database
     *
     * @param bool $receive_emails_opt_in
     * @return bool success of the database call.
     */
    public function set_receive_emails_opt_in($receive_emails_opt_in) {
        $sql_query = "UPDATE `users` "
                . "SET `receive_emails_on_changed_roster` = :receive_emails_opt_in "
                . "WHERE `employee_id` = :employee_id";
        $result = database_wrapper::instance()->run($sql_query, array(
            'receive_emails_opt_in' => $receive_emails_opt_in,
            'employee_id' => $this->employee_id,
        ));
        return '00000' === $result->errorCode();
    }

    /**
     *
     * @param int $employee_id
     * @param string $user_name
     * @param string $password_hash
     * @param string $email
     * @param string $status
     * @return boolean
     */
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

    /**
     * Change the password hash of the user to a new value.
     *
     * @param type $old_password
     * @param type $new_password
     */
    public function change_password($old_password, $new_password) {
        $user_dialog = new \user_dialog();
        if (!$this->password_verify($old_password)) {
            $user_dialog->add_message(gettext('The password was not correct.'));
            return FALSE;
        }
        if (8 > strlen($new_password)) {
            $user_dialog->add_message(gettext('The password is to short.'));
            $user_dialog->add_message(gettext('A secure password should be at least 8 characters long and not listed in any dictionary.'), E_USER_NOTICE);
            return FALSE;
        }
        try {
            $have_i_been_pwned = new \have_i_been_pwned();
            if (!$have_i_been_pwned->password_is_secure($new_password)) {
                $user_dialog->add_message($have_i_been_pwned->get_user_information_string());
                return FALSE;
            }
        } catch (Exception $exception) {
            /*
             * Well I am sad. But we will be fine.
             * TODO: Perhaps send a mail to pdr@martin-mandelkow.de to make me check if anything is wrong with the api.
             * No, better: Build a test against the API. The user does not have to be botherd sending messages to the developer.
             */
        }

        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $sql_query = "UPDATE `users` SET `password` = :password WHERE `employee_id` = :employee_id";
        $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $this->employee_id, 'password' => $password_hash));
        return '00000' === $result->errorCode();
    }

    /**
     * @todo not implemented yet
     */
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
        return '00000' === $result->errorCode();
    }

    /**
     * Test if a user exists.
     * @return boolean
     * @todo should this be static? Is it working?
     */
    public function exists() {
        $statement = $this->pdo->prepare("SELECT `employee_id` FROM `users` WHERE `employee_id` = :employee_id");
        $result = $statement->execute(array('employee_id' => $this->employee_id));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     *
     * @param string $identifier
     * @return boolean|int
     */
    public function guess_user_by_identifier($identifier) {
        $sql_query = "SELECT `employee_id` FROM `users` WHERE `employee_id` = :employee_id OR `email` = :email OR `user_name` = :user_name";
        $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $identifier, 'email' => $identifier, 'user_name' => $identifier));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $this->read_data_from_database($row->employee_id);
            return $row->employee_id;
        }
        return FALSE;
    }

    /**
     * Verify if the correct password was given without telling the password_hash to the $session.
     * @param type $user_password
     * @return boolean
     */
    public function password_verify($user_password) {
        if (empty($user_password)) {
            return FALSE;
        }
        return password_verify($user_password, $this->password);
    }

    /**
     * Reset the number of failed login attempts to 0.
     */
    public function reset_failed_login_attempts() {
        $sql_query = "UPDATE users "
                . " SET failed_login_attempt_time = NOW(), "
                . " failed_login_attempts = 0 "
                . " WHERE `user_name` = :user_name";
        database_wrapper::instance()->run($sql_query, array('user_name' => $this->user_name));
    }

    /**
     * Increase the number of failed login attempts by 1.
     */
    public function register_failed_login_attempt() {
        $sql_query = "UPDATE users"
                . " SET failed_login_attempt_time = NOW(),"
                . " failed_login_attempts = IFNULL(failed_login_attempts, 0)+1"
                . " WHERE `user_name` = :user_name";
        database_wrapper::instance()->run($sql_query, array('user_name' => $this->user_name));
    }

}
