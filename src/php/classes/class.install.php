<?php

/**
 *     Copyright (C) 2017  Dr. Martin Mandelkow
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * This class holds all necessary functions for the installation of PDR.
 *
 */
class install {

    private $pdo;
    private $Config;
    private $pdr_supported_database_management_systems;
    public $Error_message;
    public $pdr_file_system_application_path;

    /**
     * @var int PHP_VERSION_ID_REQUIRED
     * The requirements have been calculated by phpcompatinfo-5.0.12
     * for commit cd2423025433eeedf8d504c5fdeb05602ce71c24
     */
    const PHP_VERSION_ID_REQUIRED = 70002;

    function __construct() {
        $this->pdr_supported_database_management_systems = array("mysql");
        $this->pdr_file_system_application_path = dirname(dirname(dirname(__DIR__))) . "/";
        ini_set('log_errors', 1);
        ini_set("error_log", $this->pdr_file_system_application_path . "error.log");
        error_reporting(E_ALL);
        session_start();
        session_regenerate_id();
        if ($this->config_exists_in_file()) {
            $this->Error_message[] = gettext("There already is a configuration file."); //Nobody will ever read this.
            echo $this->build_error_message_div();
            header("Location: ../pages/configuration.php");
            die();
        }

        if (!is_callable('random_bytes')) {
            /*
             * Before PHP 7.0 this function did not exist.
             * PDR does not work with PHP below 7.0 anyways.
             * But at least the installer should be able to run as far as needed to inform the user about that.
             * This installer requires PHP 5.5.0 to compile (password_hash() and PASSWORD_DEFAULT were not known before).
             */

            function random_bytes($number) {
                $random_digits = rand(1, 9);
                for ($index = 0; $index < $number; $index++) {
                    $random_digits .= rand(0, 9);
                }
                $random_number = intval("0" . $random_digits);
                return pack('C', $random_number);
            }

        }


        $this->read_config_from_session();
    }

    function __destruct() {
        $this->write_config_to_session();
    }

    /**
     * Connect to the given database
     */
    private function connect_to_database() {
        $database_connect_string = $this->Config["database_management_system"] . ":";
        $database_connect_string .= "host=" . $this->Config["database_host"] . ";";
        $database_connect_string .= $this->Config["database_port"] ? "port=" . $this->Config["database_port"] . ";" : "";
        $database_connect_string .= "charset=utf8;";
        $database_connect_options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
        try {
            $this->pdo = new PDO($database_connect_string, $this->Config["database_user"], $this->Config["database_password"], $database_connect_options);
        } catch (PDOException $e) {
            error_log("Error!: " . $e->getMessage() . " in file:" . __FILE__ . " on line:" . __LINE__);
            $this->Error_message = "<p>There was an error while connecting to the database. Please see the error log for more details!</p>";
            echo $this->build_error_message_div();
        }

        $this->pdo->exec("USE " . $this->Config["database_name"]);
        return $this->pdo->errorInfo();
    }

    private function setup_mysql_database() {
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
            unset($this->Config["database_user_self"]);
            unset($this->Config["database_password_self"]);
        } else {
            /*
             * We created our own user.
             * It should have a small set of privileges at only the pdr database:
             */
            if (FALSE === $this->setup_mysql_database_grant_privileges()) {
                /*
                 * That is too bad. We have to go back to the given user.
                 * TODO: We should DROP USER the created user. If we do not use it, we should delete it.
                 * But before we have to be sure, that the user did not exist in the first place.
                 * It would be a bad idea to delete some pre-existing user.
                 */
                unset($this->Config["database_user_self"]);
                unset($this->Config["database_password_self"]);
            } else {
                /*
                 * Change the configuration to the new database user:
                 */
                $this->Config["database_user"] = $this->Config["database_user_self"];
                $this->Config["database_password"] = $this->Config["database_password_self"];
                unset($this->Config["database_user_self"]);
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
            /*
             * CAVE: Avoid $this->pdo->errorInfo()[3] in order to allow PHP below 5.4 to at least see, that the minimum version of PHP required is above 7.0.
             */
            $Error_array = $this->pdo->errorInfo();
            error_log($Error_array[3] . " on line " . __LINE__ . " in method " . __METHOD__ . ".");
            return FALSE;
        } else {
            return TRUE;
        }
    }

    private function setup_mysql_database_create_user() {
        /*
         * Create the user:
         */
        $this->Config["database_user_self"] = "pdr"; //The user name must not be longer than 16 chars in mysql.
        $this->Config["database_password_self"] = bin2hex(openssl_random_pseudo_bytes(16));
        $statement = $this->pdo->prepare("CREATE USER :database_user@:database_host IDENTIFIED BY :database_password");
        $result = $statement->execute(array(
            'database_user' => $this->Config["database_user_self"],
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
                    'database_user' => $this->Config["database_user_self"],
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
        $this->pdo->exec("GRANT " . implode(", ", $Privileges) . " ON `" . $this->Config["database_name"] . "`.* TO " . $this->Config["database_user_self"] . "@localhost");
        if ("localhost" !== $this->Config["database_host"]) {
            /*
             * Allow access from any remote (@%).
             * TODO: Should we place a warning to the administrator?
             */
            $this->pdo->exec("GRANT " . implode(", ", $Privileges) . " ON `" . $this->Config["database_name"] . "`.* TO " . $this->Config["database_user_self"] . "@%");
        }
    }

    private function setup_mysql_database_tables() {
        /*
         * TODO: Do we need a specific order of table creation?
         * Some tables have contraints. Do we have to create the referenced tables first?
         */
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

        $this->Config["admin"]["user_name"] = filter_input(INPUT_POST, "user_name", FILTER_SANITIZE_STRING, $options = null);
        $this->Config["admin"]["last_name"] = filter_input(INPUT_POST, "last_name", FILTER_SANITIZE_STRING, $options = null);
        $this->Config["admin"]["employee_id"] = filter_input(INPUT_POST, "employee_id", FILTER_SANITIZE_NUMBER_INT, $options = null);
        $this->Config["admin"]["email"] = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL, $options = null);
        $this->Config["admin"]["password"] = filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING, $options = null);
        $this->Config["admin"]["password2"] = filter_input(INPUT_POST, "password2", FILTER_SANITIZE_STRING, $options = null);
        if ($this->Config["admin"]["password"] !== $this->Config["admin"]["password2"]) {
            $this->Error_message[] = gettext("The passwords aren't the same.");
            unset($this->Config["admin"]["password"], $this->Config["admin"]["password2"]); //We get rid of this values as fast as possible.
            return FALSE;
        } else {
            $password_hash = password_hash($this->Config["admin"]["password"], PASSWORD_DEFAULT);
            unset($this->Config["admin"]["password"]);
            unset($this->Config["admin"]["password2"]);
        }


        if (empty($this->Config["database_name"])) {
            header("Location: install_page_database.php");
            die("The database connection needs to be setup first.");
        }
        $this->connect_to_database();

        /*
         * The table users has a constraint:
         * CONSTRAINT `users_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
         * This means, that only existing employes can have an account to login.
         * It follows, that we have to create an employee first, before we can create a user:
         */
        $statement = $this->pdo->prepare("INSERT INTO `employees` (`id`, `last_name`) VALUES (:employee_id, :last_name);");
        $statement->execute(array(
            'employee_id' => $this->Config["admin"]["employee_id"],
            'last_name' => $this->Config["admin"]["last_name"]
        ));
        $user = new user($this->Config["admin"]["employee_id"]);
        if ($user->exists()) {
            $user_creation_result = $user->create_new($this->Config["admin"]["employee_id"], $this->Config["admin"]["user_name"], $password_hash, $this->Config["admin"]["email"], 'active');
            if (!$user_creation_result) {
                /*
                 * We were not able to create the administrative user.
                 */
                $this->Error_message[] = gettext("Error while trying to create administrative user.");
                return FALSE;
            }
        } else {
            /*
             * The administrative user already exists.
             * We will not delete it.
             */
            $this->Error_message[] = gettext("Administrative user already exists.");
        }
        /*
         * Grant all privileges to the administrative user:
         */
        $statement = $this->pdo->prepare("INSERT IGNORE INTO `users_privileges` (`employee_id`, `privilege`) VALUES (:employee_id, :privilege)");
        require_once $this->pdr_file_system_application_path . 'src/php/classes/class.sessions.php';
        /*
         * The __construct method already calls session_regenerate_id();
         * As long as that stays this way, we do not have to repeat that here.
         * session_regenerate_id(); //To prevent session fixation attacks we regenerate the session id right before setting up login details.
         */
        /*
         * Brute force method of login:
         * TODO: Which parts of the following lines are relevant?
         */
        //$_SESSION['user_name'] = $this->Config["admin"]["user_name"];
        //$_SESSION['user_employee_id'] = $this->Config["admin"]["employee_id"];
        //$_SESSION['user_email'] = $this->Config["admin"]["email"];
        $_SESSION['user_object'] = new user($this->Config["admin"]["employee_id"]);


        foreach (sessions::$Pdr_list_of_privileges as $privilege) {
            $result = $statement->execute(array(
                "employee_id" => $this->Config["admin"]["employee_id"],
                "privilege" => $privilege
            ));
            if (!$result) {
                /*
                 * We were not able to create the administrative user.
                 */

                $this->Error_message[] = gettext("Error while trying to create administrative user privileges.");
                print_r($statement->ErrorInfo());
                echo "<br>\n";
                return FALSE;
            }
        }

        $this->write_config_to_session();
        if (FALSE === $this->write_config_to_file()) {
            echo $this->build_error_message_div();
        } else {
            header("Location: ../../../src/php/pages/user-management.php");
            die("Please move on to the <a href=../../../src/php/pages/user-management.php>user management</a>");
            return TRUE;
        }
    }

    public function webserver_supports_https() {
        require_once $this->pdr_file_system_application_path . 'src/php/classes/class.sessions.php';
        $this->try_https();
        $https = filter_input(INPUT_SERVER, "HTTPS", FILTER_SANITIZE_STRING);

        if (!empty($https) and $https === "on") {
            return TRUE;
        } else {
            $this->Error_message[] = "This webserver does not seem to support HTTPS. Please enable Hypertext Transfer Protocol Secure (HTTPS)!";
            return FALSE;
        }
    }

    private function try_https() {
        if (!isset($_SESSION['number_of_times_redirected'])) {
            $_SESSION['number_of_times_redirected'] = 0;
        }
        $https_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
            if (!headers_sent() and ( ++$_SESSION['number_of_times_redirected'] ) < 3) {
                header("Status: 301 Moved Permanently");
                header("Location: $https_url");
            } elseif (( ++$_SESSION['number_of_times_redirected'] ) < 3) {
                echo '<script type="javascript">document.location.href="' . $https_url . '";</script>';
            }
            return FALSE;
        }
        return TRUE;
    }

    public function database_driver_is_installed() {
        if (empty(array_intersect(PDO::getAvailableDrivers(), $this->pdr_supported_database_management_systems))) {
            $this->Error_message[] = "No compatible database driver found. Please install one of the following database management systems and the corresponding PHP driver!";
            $this->Error_message[] = explode(", ", $this->pdr_supported_database_management_systems);
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function php_extension_requirements_are_fulfilled() {
        /*
         * The requirements have been calculated by phpcompatinfo-5.0.12
         * for commit cd2423025433eeedf8d504c5fdeb05602ce71c24
         */
        $Loaded_extensions = get_loaded_extensions();
        $Required_extensions = array(
            'Core', 'PDO', 'calendar', 'date', 'filter', 'gettext', 'hash', 'iconv', 'json', 'mbstring',
            'openssl', 'pcre', 'posix', 'session', 'standard', 'xml',
        );
        $success = TRUE;
        foreach ($Required_extensions as $required_extension) {
            if (!in_array($required_extension, $Loaded_extensions)) {
                $this->Error_message[] = "PHP extension $required_extension is missing.";
                $success = FALSE;
            }
        }
        return $success;
    }

    public function php_version_requirement_is_fulfilled($version_required = self::PHP_VERSION_ID_REQUIRED) {
        $version_required_string_major = round($version_required / 10000, 0);
        $version_required_string_minor = round($version_required % 10000 / 100, 0);
        $version_required_string_release = round($version_required % 100, 0);
        $version_required_string = $version_required_string_major
                . "." . $version_required_string_minor
                . "." . $version_required_string_release;
        /*
         * The requirements have been calculated by phpcompatinfo-5.0.12
         * for commit cd2423025433eeedf8d504c5fdeb05602ce71c24
         * usage:
         * php phpcompatinfo-5.0.12.phar analyser:run ..
         * with phpcompatinfo-5.0.12.phar lying in the folder tests/
         */
        /*
         *  PHP_VERSION_ID is available as of PHP 5.2.7,
         *  if our version is lower than that, then emulate it:
         */
        if (!defined('PHP_VERSION_ID')) {
            $version = explode('.', PHP_VERSION);
            //define('PHP_MAJOR_VERSION',   $version[0]);
            //define('PHP_MINOR_VERSION',   $version[1]);
            //define('PHP_RELEASE_VERSION', $version[2]);

            define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
        }

        /* PHP_VERSION_ID is defined as a number, where the higher the number is,
         * the newer a PHP version is used. It's defined as used in the above expression:
         *
         * $version_id = $major_version * 10000 + $minor_version * 100 + $release_version;
         *
         * Now with PHP_VERSION_ID we can check for features this PHP version
         * may have, this doesn't require to use version_compare() everytime
         * you check if the current PHP version may not support a feature.
         *
         */
        if (PHP_VERSION_ID < $version_required) {
            $this->Error_message[] = "The PHP version running on this webserver is " . PHP_VERSION
                    . " but the application requires at least version " . $version_required_string . ".";
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function pdr_directories_are_writable() {
        $List_of_directories = array(
            "upload",
            "tmp",
            "config",
        );
        foreach ($List_of_directories as $directory_name) {
            if (!is_writable($this->pdr_file_system_application_path . $directory_name)) {
                $List_of_non_writable_directories[] = $directory_name;
            }
        }
        if (!empty($List_of_non_writable_directories)) {
            $this->Error_message[] = sprintf(ngettext("The directory %1s is not writable.", "The directories %1s are not writable.", count($List_of_non_writable_directories)), $this->fancy_implode($List_of_non_writable_directories, ", "));

            $this->Error_message[] = gettext("Make sure that the directories are writable by pdr!");
            $current_www_user = posix_getpwuid(posix_geteuid())["name"];
            $this->Error_message[] = "<pre class='install_cli'>sudo chown -R " . $current_www_user . ":" . $current_www_user . " " . $this->pdr_file_system_application_path . "</pre>\n";
            //$this->Error_message[] = gettext("Adapt the above code to the user and folder of your webserver!");
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function handle_user_input_database() {
        $this->Config["database_management_system"] = filter_input(INPUT_POST, "database_management_system", FILTER_SANITIZE_STRING, $options = null);
        $this->Config["database_host"] = filter_input(INPUT_POST, "database_host", FILTER_SANITIZE_STRING, $options = null);
        $this->Config["database_name"] = filter_input(INPUT_POST, "database_name", FILTER_SANITIZE_STRING, $options = null);
        $this->Config["database_port"] = filter_input(INPUT_POST, "database_port", FILTER_SANITIZE_NUMBER_INT, $options = null);
        $this->Config["database_user"] = filter_input(INPUT_POST, "database_user", FILTER_SANITIZE_STRING, $options = null);
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
                $this->Error_message[] = gettext("Error while trying to create the database.");
                return FALSE;
            }
        }
        if (FALSE === $this->setup_mysql_database_tables()) {
            /*
             * There was a serious error while trying to create the database tables.
             */
            $this->Error_message[] = gettext("Error while trying to create the database tables.");
            return FALSE;
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
        /*
         * Just in case we are interrupted and/or the session is lost, we write the values to a temporary installation file:
         */
        file_put_contents($this->pdr_file_system_application_path . 'config/config_temp_install.php', '<?php' . PHP_EOL . '$config =' . var_export($this->Config, true) . ';');
    }

    private function read_config_from_session() {
        if (!empty($_SESSION["Config"])) {
            $this->Config = $_SESSION["Config"];
        }
        /*
         * Just in case we are interrupted and/or the session is lost, we read the values from a temporary installation file:
         */
        $config = array();
        if (file_exists($this->pdr_file_system_application_path . 'config/config_temp_install.php')) {
            include_once $this->pdr_file_system_application_path . 'config/config_temp_install.php';
        }
        if (!empty($config)) {
            foreach ($config as $key => $value) {
                if (!isset($this->Config[$key])) {
                    $this->Config[$key] = $value;
                }
            }
        }
        unset($config);
    }

    private function write_config_to_file() {
        $this->Config["contact_email"] = $this->Config["admin"]["email"];
        $this->Config["session_secret"] = bin2hex(random_bytes(8)); //In case there are several instances of the program on the same machine
        unset($this->Config["admin"]);
        $dirname = $this->pdr_file_system_application_path . 'config';
        $result = file_put_contents($this->pdr_file_system_application_path . 'config/config.php', '<?php' . PHP_EOL . '$config =' . var_export($this->Config, true) . ';');
        if (FALSE === $result) {
            $this->Error_message[] = gettext("Error while writing the configuration to the filesystem.");
            return FALSE;
        }
        if (file_exists($this->pdr_file_system_application_path . 'config/config_temp_install.php')) {
            unlink($this->pdr_file_system_application_path . 'config/config_temp_install.php');
        }
        return TRUE;
    }

    private function config_exists_in_file() {
        $config_filename = $this->pdr_file_system_application_path . 'config/config.php';
        if (file_exists($config_filename)) {
            include $config_filename;
            if (empty($config)) {
                /*
                 * No config file was written yet.
                 * Or the config array is empty.
                 */
                return FALSE;
            } else {
                return TRUE;
            }
        } else {

            return FALSE;
        }
    }

    public function build_error_message_div() {
        if (empty($this->Error_message)) {
            return FALSE;
        }
        $text_html = "<div id='error_message_div'>\n";
        foreach ($this->Error_message as $error_message) {
            $text_html .= "<p>" . $error_message . "</p>\n";
        }
        $text_html .= "</div>\n";
        unset($this->Error_message); //Unsetting makes it possible to refill the array and build the new contents in another place.
        return $text_html;
    }

    private function fancy_implode($input_array, $delimiter = ", ") {
        /*
         * This also works for just one element in the array:
         */
        $last = array_pop($input_array);
        return count($input_array) ? implode($delimiter, $input_array) . " " . gettext("and") . " " . $last : $last;
    }

}
