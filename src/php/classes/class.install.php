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
    private $Config;
    public $Error_message;
    public $pdr_file_system_application_path;

    function __construct() {
//echo "In class " . __CLASS__ . " in method " . __METHOD__ . " on line " . __LINE__ . "<br>";
        $this->pdr_file_system_application_path = dirname(dirname(dirname(__DIR__))) . "/";
        ini_set("error_log", $this->pdr_file_system_application_path . "error.log");
        session_start();
        session_regenerate_id();
        $this->read_config_from_session();
        /*
          echo "Session:<br>";
          print_r($_SESSION);
          echo "Config:<br>";
          print_r($this->Config);
         */
    }

    function __destruct() {
        $this->write_config_to_session();
    }

    /*
     * Connect to the given database
     */

    private function connect_to_database() {
        $database_connect_string = $this->Config["database_management_system"] . ":";
        $database_connect_string .= "host=" . $this->Config["database_host"] . ";";
        $database_connect_string .= $this->Config["database_port"] ? "port=" . $this->Config["database_port"] . ";" : "";
        $database_connect_string .= "charset=utf8;";
        $database_connect_options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
        try {
            $this->pdo = new PDO($database_connect_string, $this->Config["database_username"], $this->Config["database_password"], $database_connect_options);
        } catch (PDOException $e) {
            error_log("Error!: " . $e->getMessage() . " in file:" . __FILE__ . " on line:" . __LINE__);
            echo ("Error!: " . $e->getMessage() . " in file:" . __FILE__ . " on line:" . __LINE__);
            $this->Error_message = "<p>There was an error while connecting to the database. Please see the error log for more details!</p>";
        }

        $this->pdo->exec("USE " . $this->Config["database_name"]);
        return $this->pdo->errorInfo();
    }

    private function setup_mysql_database() {
//TODO: Check for every step if it was successfull. Create an alternative or throw an error if there is no option left.
        if (FALSE === $this->setup_mysql_database_create_database()) {
            /*
             * We could not create the database.
             * This function is only called by handle_user_input_database(), if the database did not exist. So there is nothing we can do.
             */
            $this->Error_message[] = "Could not connect to the database. Please check the configuration!";
            return FALSE;
        }
        if (FALSE === $this->setup_mysql_database_create_user()) {
            /*
             * We could not create our own user. So we just keep the old one.
             * TODO: We might give a warning to the administrator?
             */
        } else {
            /*
             * We created our own user.
             * It should have a small set of privileges at only the pdr database:
             */
            if (FALSE === $this->setup_mysql_database_grant_privileges()) {
                /*
                 * That is too bad. We have to go back to the given user.
                 */
                unset($this->Config["database_username_self"]);
                unset($this->Config["database_password_self"]);
            } else {
                /*
                 * Change the configuration to the new database user:
                 */
                $this->Config["database_username"] = $this->Config["database_username_self"];
                $this->Config["database_password"] = $this->Config["database_password_self"];
                unset($this->Config["database_username_self"]);
                unset($this->Config["database_password_self"]);
                return TRUE;
            }
        }
        /*
         * Reload the privileges:
         */
        $this->pdo->exec("FLUSH PRIVILEGES");
        return TRUE; //TODO Check if the GRANT did work!
    }

    private function setup_mysql_database_create_database() {
        /*
         * Create the database:
         */
        $result = $this->pdo->exec("CREATE DATABASE " . $this->Config["database_name"]);
        if (FALSE === $result) {
            error_log($this->pdo->errorInfo()[3] . " on line " . __LINE__ . " in method " . __METHOD__ . ".");
            return FALSE;
        } else {
            return TRUE;
        }
    }

    private function setup_mysql_database_create_user() {
        /*
         * Create the user:
         */
        $this->Config["database_username_self"] = "pdr"; //The user name must not be longer than 16 chars in mysql.
        $this->Config["database_password_self"] = bin2hex(openssl_random_pseudo_bytes(16));
        $statement = $this->pdo->prepare("CREATE USER :database_username@:database_host IDENTIFIED BY :database_password");
        $result = $statement->execute(array(
            'database_username' => $this->Config["database_username_self"],
            'database_host' => "localhost",
            'database_password' => $this->Config["database_password_self"],
        ));
        /*
         * PDOStatement::execute will return TRUE on success or FALSE on failure.
         */
        if ($result) {
            if ("localhost" !== $this->Config["database_host"]) {
                /*
                 * Allow access from any remote.
                 * TODO: Should we place a warning to the administrator?
                 */
                $result = $statement->execute(array(
                    'database_username' => $this->Config["database_username_self"],
                    'database_host' => "%",
                    'database_password' => $this->Config["database_password_self"],
                ));
                /*
                 * PDOStatement::execute will return TRUE on success or FALSE on failure.
                 */
                return $result;
            }
        } else {
            return FALSE;
        }
    }

    private function setup_mysql_database_grant_privileges() {
        /*
         * Grant the user access to the database:
         * If the host is not localhost, then the access is allowed from ANY remote client.
         *
         */
        $Privileges = array(
            "SELECT",
            "INSERT",
            "UPDATE",
            "DELETE",
            "CREATE",
            "DROP",
            "INDEX",
            "ALTER",
            "TRIGGER",
        );
        $this->pdo->exec("GRANT " . implode(", ", $Privileges) . " ON `" . $this->Config["database_name"] . "`.* TO " . $this->Config["database_username_self"] . "@localhost");
        if ("localhost" !== $this->Config["database_host"]) {
            /*
             * Allow access from any remote.
             * TODO: Should we place a warning to the administrator?
             */
            $this->pdo->exec("GRANT " . implode(", ", $Privileges) . " ON `" . $this->Config["database_name"] . "`.* TO " . $this->Config["database_username_self"] . "@%");
        }
    }

    public function setup_mysql_database_tables() {
//private function setup_mysql_database_tables() {
        $this->connect_to_database();
        $sql_files = glob($this->pdr_file_system_application_path . "src/sql/*.sql");
        foreach ($sql_files as $sql_file_name) {
            $sql_create_table_statement = file_get_contents($sql_file_name);
            $pattern = "/^.*TRIGGER.*\$/m";
            if (preg_match_all($pattern, $sql_create_table_statement, $matches)) {
                /*
                 * This file contains a CREATE TRIGGER clause.
                 */
                /*
                 * Remove DEFINER clause. MySQL will automatically add the current user.
                 */
                $pattern = "/^(.*)DEFINER[^@][^\s]*(.*)\$/m";
                $sql_create_table_statement = preg_replace($pattern, "$1 $2", $sql_create_table_statement);
            }
            $this->pdo->exec($sql_create_table_statement);
        }
    }

    public function handle_user_input_administration() {

        $this->Config["user_name"] = filter_input(INPUT_POST, "user_name", FILTER_SANITIZE_STRING, $options = null);
        $this->Config["employee_id"] = filter_input(INPUT_POST, "employee_id", FILTER_SANITIZE_NUMBER_INT, $options = null);
        $this->Config["email"] = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL, $options = null);
        $this->Config["password"] = filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING, $options = null);
        $this->Config["password2"] = filter_input(INPUT_POST, "password2", FILTER_SANITIZE_STRING, $options = null);
        if ($this->Config["password"] !== $this->Config["password2"]) {
            $this->Error_message[] = gettext("The passwords aren't the same!");
            return FALSE;
        } else {
            $password_hash = password_hash($this->Config["password"], PASSWORD_DEFAULT);

//TODO: make sure that the database and user table exist

            $statement = $this->pdo->prepare("INSERT INTO users (user_name, employee_id, password, email, status) VALUES (:user_name, :employee_id, :password, :email, 'inactive')");
            $result = $statement->execute(array('user_name' => $this->Config["user_name"], 'employee_id' => $this->Config["employee_id"], 'password' => $password_hash, 'email' => $this->Config["email"]));
        }
        $this->write_config_to_session();
        $this->write_config_to_file();
    }

    public function pdr_directories_are_writable() {
//TODO WHERE ARE WE? WHERE IS THE ROOT?
        print_r(__FILE__);
        $List_of_directories = array(
            "upload",
            "tmp",
            "config",
        );
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
        $this->Config["database_management_system"] = filter_input(INPUT_POST, "database_management_system", FILTER_SANITIZE_STRING, $options = null);
        $this->Config["database_host"] = filter_input(INPUT_POST, "database_host", FILTER_SANITIZE_STRING, $options = null);
        $this->Config["database_name"] = filter_input(INPUT_POST, "database_name", FILTER_SANITIZE_STRING, $options = null);
        $this->Config["database_port"] = filter_input(INPUT_POST, "database_port", FILTER_SANITIZE_NUMBER_INT, $options = null);
        $this->Config["database_username"] = filter_input(INPUT_POST, "database_username", FILTER_SANITIZE_STRING, $options = null);
        $this->Config["database_password"] = filter_input(INPUT_POST, "database_password", FILTER_SANITIZE_STRING, $options = null);
        $this->write_config_to_session();
        if (!in_array($this->Config["database_management_system"], PDO::getAvailableDrivers())) {
            $this->Error_messages[] = $this->Config["database_management_system"] . "is not available on this server. Please check the configuration!";
            return FALSE;
        }
        $connect_error_info = $this->connect_to_database();

        if ($connect_error_info[1] === 1049) {
            /*
             * Unknown database
             * Maybe we are able to just create that database.
             * We are using the $database_name.
             */
            if (FALSE === $this->setup_mysql_database()) {
                /*
                 * There was a serious error while trying to create the database.
                 */
                return FALSE;
            }
        }
        if (empty($this->Error_message)) {
            $this->write_config_to_session();
            /*
             * Success, we move to the next page.
             */
            header("Location: install_page_admin.php");
            die();
        } else {
            return FALSE;
        }
    }

    private function write_config_to_session() {
        $_SESSION["Config"] = $this->Config;
    }

    private function read_config_from_session() {
        $this->Config = $_SESSION["Config"];
    }

    private function write_config_to_file() {
        file_put_contents($this->pdr_file_system_application_path . 'config/config.php', '<?php  $config =' . var_export($this->Config, true) . ';');
    }

}
