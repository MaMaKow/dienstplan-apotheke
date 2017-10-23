<?php

/**
 * *    Copyright (C) 2017  Dr. Martin Mandelkow
 * *
 * *    This program is free software: you can redistribute it and/or modify
 * *    **it under the terms of the GNU General Public License as published by
 * *    the Free Software Foundation, either version 3 of the License, or
 * *    (at your option) any later version.
 * *
 * *    This program is distributed in the hope that it will be useful,
 * *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 * *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * *    GNU General Public License for more details.
 * *
 * *    You should have received a copy of the GNU General Public License
 * *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * */
/*
 * This class holds all necessary functions for the installation of PDR.
 *
 */
class install {

    private $pdo;
    public $Error_message;

    public function __construct() {
        echo __FILE__;
        ini_set("error_log", "error.log");
        session_regenerate_id();
    }

    /*
     * Connect to the given database
     */

    private function connect_to_database($database_management_system, $database_host, $database_port, $database_name, $database_username, $database_password) {
        try {
            $this->pdo = new PDO("$database_management_system:host=$database_host;port=$database_port;charset=utf8;dbname=" . $database_name, $database_username, $database_password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
        } catch (PDOException $e) {
            error_log("Error!: " . $e->getMessage() . " in file:" . __FILE__ . " on line:" . __LINE__);
            die("<p>There was an error while querying the database. Please see the error log for more details!</p>");
        }
    }

    private function setup_mysql_database($database_host, $database_name, $user_name, $password) {
        /*
         * Create the database:
         */
        $statement = $this->pdo->prepare("CREATE DATABASE `:database_name`");
        $statement->exec(array(database_name => $database_name));

        /*
         * Create the user:
         */
        $statement = $this->pdo->prepare("CREATE USER ':user_name'@':database_host' IDENTIFIED BY ':password'");
        $statement->exec(array(user_name => $user_name, database_host => $database_host, password => $password));
        /*
         * Grant the user access to the database:
         */
        $statement = $this->pdo->prepare("GRANT ALL PRIVILEGES ON `:database_name` . * TO ':user_name'@':database_host'");
        $statement->exec(array(user_name => $user_name, database_host => $database_host, database_name => $database_name, password => $password));
        /*
         * Reload the privileges:
         */
        $this->pdo->exec("FLUSH PRIVILEGES");
    }

    public function handle_user_input_administration() {

        $user_name = filter_input(INPUT_POST, "user_name", FILTER_SANITIZE_STRING, $options = null);
        $employee_id = filter_input(INPUT_POST, "employee_id", FILTER_SANITIZE_INT, $options = null);
        $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL, $options = null);
        $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING, $options = null);
        $password2 = filter_input(INPUT_POST, "password2", FILTER_SANITIZE_STRING, $options = null);
        if ($password !== $password2) {
            $this->Error_message[] = gettext("The passwords aren't the same!");
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $statement = $this->pdo->prepare("INSERT INTO users (user_name, employee_id, password, email, status) VALUES (:user_name, :employee_id, :password, :email, 'inactive')");
            $result = $statement->execute(array('user_name' => $user_name, 'employee_id' => $employee_id, 'password' => $password_hash, 'email' => $email));
        }
    }

    public function pdr_directories_are_writable() {
        //TODO WHERE ARE WE? WHERE IS THE ROOT?
        print_r(__FILE__);
        $List_of_directories = array("upload", "tmp", "config");
        foreach ($List_of_directories as $directory_name) {
            if (is_writable($directory_name)) {
                //    $List_of_writable_directories[] = $directory_name;
            } else {
                $List_of_non_writable_directories[] = $directory_name;
            }

            //TODO generate some output to the user
        }
        foreach ($List_of_non_writable_directories as $non_writable_directory_name) {
            $this->Error_message[] = "<em>" . $non_writable_directory_name . "is not writable!</em>";
        }
    }

    public function handle_user_input_database() {
        $database_management_system = filter_input(INPUT_POST, "database_management_system", FILTER_SANITIZE_STRING, $options = null);
        $database_host = filter_input(INPUT_POST, "database_host", FILTER_SANITIZE_STRING, $options = null);
        $database_name = filter_input(INPUT_POST, "database_name", FILTER_SANITIZE_STRING, $options = null);
        $database_port = filter_input(INPUT_POST, "database_port", FILTER_SANITIZE_NUMBER_INT, $options = null);
        $database_username = filter_input(INPUT_POST, "database_username", FILTER_SANITIZE_STRING, $options = null);
        $database_password = filter_input(INPUT_POST, "database_password", FILTER_SANITIZE_STRING, $options = null);
        $Config["database_management_system"] = $database_management_system;
        $Config["database_host"] = $database_host;
        $Config["database_name"] = $database_name;
        $Config["database_port"] = $database_port;
        $Config["database_username"] = $database_username;
        $Config["database_password"] = $database_password;
        $this->write_config_to_session($Config);
        if (!in_array($database_management_system, PDO::getAvailableDrivers())) {
            $this->Error_messages[] = "$database_management_system is not available on this server. Please check the configuration!";
        }
        $this->connect_to_database($database_management_system, $database_host, $database_port, $database_name, $database_username, $database_password);
    }

    private function write_config_to_session($Config) {
        $_SESSION["Config"] = $Config;
    }

    private function write_config_to_file($Config) {
        file_put_contents('./config/config.php', '<?php  $config =' . var_export($Config, true) . ';');
    }

}
