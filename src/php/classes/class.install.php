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
    public $Config;
    public $pdr_file_system_application_path;

    public function __construct() {
        $this->pdr_file_system_application_path = dirname(dirname(dirname(__DIR__))) . "/";
        ini_set("error_log", $this->pdr_file_system_application_path . "error.log");
        session_regenerate_id();
        $this->read_config_from_session();
    }

    /*
     * Connect to the given database
     */

    private function connect_to_database($database_management_system, $database_host, $database_port, $database_name, $database_username, $database_password) {
        try {
            $database_connect_string = "$database_management_system:";
            $database_connect_string .= "host=$database_host;";
            $database_connect_string .= $database_port ? "port=$database_port;" : "";
            $database_connect_string .= "charset=utf8;";
            //$database_connect_string .= "dbname=$database_name";
            $database_connect_options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
            $this->pdo = new PDO($database_connect_string, $database_username, $database_password, $database_connect_options);
            //$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //TODO: remove the drop!
            //$this->pdo->exec("DROP DATABASE $database_name");
            /*
              foreach ($this->pdo->query("SHOW DATABASES") as $row) {
              print_r($row);
              echo "<br>";
              }
             */

            $this->pdo->exec("USE $database_name");
            if ($this->pdo->errorInfo()[1] === 1049) {
                /*
                 * Unknown database
                 * maybe we are able to just create that database
                 */
                $this->setup_mysql_database($database_host, $database_name, $database_username, $database_password);
            }
        } catch (PDOException $e) {
            error_log("Error!: " . $e->getMessage() . " in file:" . __FILE__ . " on line:" . __LINE__);
            echo ("Error!: " . $e->getMessage() . " in file:" . __FILE__ . " on line:" . __LINE__);
            $this->Error_message = "<p>There was an error while connecting to the database. Please see the error log for more details!</p>";
        }
    }

    private function setup_mysql_database($database_host, $database_name, $database_username, $database_password) {
        //TODO: Check for every step if it was successfull. Create an alternative or throw an error if there is no option left.

        $database_password_self = bin2hex(openssl_random_pseudo_bytes(16));
        $database_username_self = "pdr_" . bin2hex(openssl_random_pseudo_bytes(5)); //The user name must not be longer than 16 chars in mysql.
        //echo "<br>will register with user $database_username_self and password $database_password_self<br>";
        /*
         * Create the database:
         */
        $this->pdo->exec("CREATE DATABASE $database_name");

        /*
         * Create the user:
         */
        $statement = $this->pdo->prepare("CREATE USER :database_username@:database_host IDENTIFIED BY :database_password");
        $statement->execute(array(
            'database_username' => $database_username_self,
            'database_host' => $database_host,
            'database_password' => $database_password_self
        ));
        /*
         * Grant the user access to the database:
         * TODO: '@':database_host' is probably wrong.
         * Everything is well as long as it is localhost.
         * But this will blow up, once a remote connection is used!
         */
        $privileges = "SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, TRIGGER";
        $this->pdo->exec("GRANT $privileges ON `$database_name`.* TO $database_username_self@$database_host");
        /*
         * Reload the privileges:
         */
        $this->pdo->exec("FLUSH PRIVILEGES");
        return TRUE;
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
        $this->Config["database_management_system"] = $database_management_system;
        $this->Config["database_host"] = $database_host;
        $this->Config["database_name"] = $database_name;
        $this->Config["database_port"] = $database_port;
        $this->Config["database_username"] = $database_username;
        $this->Config["database_password"] = $database_password;
        $this->write_config_to_session();
        if (!in_array($database_management_system, PDO::getAvailableDrivers())) {
            $this->Error_messages[] = "$database_management_system is not available on this server. Please check the configuration!";
        }
        $this->connect_to_database($database_management_system, $database_host, $database_port, $database_name, $database_username, $database_password);
    }

    private function write_config_to_session() {
        $_SESSION["Config"] = $this->Config;
    }

    private function read_config_from_session() {
        $this->Config = $_SESSION["Config"];
    }

    private function write_config_to_file($Config) {
        file_put_contents('./config/config.php', '<?php  $config =' . var_export($Config, true) . ';');
    }

}
