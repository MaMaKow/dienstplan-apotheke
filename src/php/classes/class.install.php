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
    private $database_existed_before_installation;
    private $database_user_self_existed_before_installation;
    public $Error_message;
    public $pdr_file_system_application_path;

    /**
     * @var int PHP_VERSION_ID_REQUIRED
     * The requirements have been calculated by phpcompatinfo-5.0.12
     * for commit cd2423025433eeedf8d504c5fdeb05602ce71c24
     */
    const PHP_VERSION_ID_REQUIRED = 70002;

    function __construct() {
        $this->Error_message = array();
        $this->Config["database_user_self"] = "pdr_" . bin2hex(openssl_random_pseudo_bytes(5)); //The user name must not be longer than 16 chars in mysql.
        $this->Config["database_password_self"] = bin2hex(openssl_random_pseudo_bytes(16));
        $this->pdr_file_system_application_path = dirname(dirname(dirname(__DIR__))) . "/";
        define('PDR_FILE_SYSTEM_APPLICATION_PATH', $this->pdr_file_system_application_path);
        $folder_tree_depth_in_chars = strlen(substr(getcwd(), strlen(__DIR__)));
        $root_folder = dirname(dirname(dirname(substr(dirname($_SERVER["SCRIPT_NAME"]), 0, strlen(dirname($_SERVER["SCRIPT_NAME"])) - $folder_tree_depth_in_chars)))) . "/";
        define('PDR_HTTP_SERVER_APPLICATION_PATH', $root_folder);
        require_once PDR_FILE_SYSTEM_APPLICATION_PATH . 'funktionen.php';
        /*
         * Define an autoloader:
         */
        spl_autoload_register(function ($class_name) {
            $base_dir = $this->pdr_file_system_application_path . '/src/php/classes/';
            $file = $base_dir . 'class.' . $class_name . '.php';
            if (file_exists($file)) {
                include_once $file;
            }
            /**
             * <p lang="de">
             * Wir wollen die Files der Klassen besser sortieren.
             * Der Autoloader muss so lange bis das abgeschlossen ist, beide Varianten beherrschen.
             * </p>
             */
            $file = $base_dir . str_replace('\\', '/', $class_name) . '.php';
            if (file_exists($file)) {
                include_once $file;
            }
        });
        $this->pdr_supported_database_management_systems = array("mysql");
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
            die("Could not connect to the database.");
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
        $this->database_user_self_existed_before_installation = $this->database_user_exists($this->Config["database_user_self"]);

        if (TRUE === $this->database_user_self_existed_before_installation or TRUE === $this->setup_mysql_database_create_user()) {
            /*
             * We created our own user.
             * It should have a small set of privileges at only the pdr database:
             */
            if (TRUE === $this->setup_mysql_database_grant_privileges()) {
                /*
                 * Change the configuration to the new database user:
                 */
                $this->pdo->exec("FLUSH PRIVILEGES");
                $this->Config["database_user"] = $this->Config["database_user_self"];
                $this->Config["database_password"] = $this->Config["database_password_self"];
                unset($this->Config["database_user_self"]);
                unset($this->Config["database_password_self"]);
                return TRUE;
            } else {
                /*
                 * We created our own user. But we could not grant privileges to it.
                 * Therefore we will delete the user now.
                 * But only, if it did not exist in the first place.
                 */
                if (FALSE === $this->database_user_self_existed_before_installation) {
                    $statement = $this->pdo->prepare("DROP USER :database_user");
                    $result = $statement->execute(array(
                        "database_user" => $this->Config["database_user_self"],
                    ));
                }
                /*
                 * That is too bad. We have to go back to the given user.
                 */
                unset($this->Config["database_user_self"]);
                unset($this->Config["database_password_self"]);
            }
        } else {
            /*
             * The user could not be created.
             */
            unset($this->Config["database_user_self"]);
            unset($this->Config["database_password_self"]);
            /*
             * We still return TRUE.
             * This user is not the ideal case. But it will work.
             * At least it was able to create the database and the tables.
             */
            return TRUE;
        }
    }

    private function setup_mysql_database_create_database() {
        /**
         * Test if the database exists:
         */
        $this->database_existed_before_installation = $this->database_exists($this->Config["database_name"]);
        if (TRUE === $this->database_existed_before_installation) {
            /*
             * The database already exists.
             * There is nothing more to do here.
             */
            return TRUE;
        }
        /**
         * Create the database:
         */
        $statement = $this->pdo->prepare("CREATE DATABASE " . database_wrapper::quote_identifier($this->Config["database_name"]) . ";");
        $result = $statement->execute();
        if (FALSE === $result) {
            /*
             * CAVE: Avoid $this->pdo->errorInfo()[3] in order to allow PHP below 5.4 to at least see, that the minimum version of PHP required is above 7.0.
             */
            error_log("Could not CREATE DATABASE with name:");
            error_log($this->Config["database_name"]);
            return FALSE;
        } else {
            return TRUE;
        }
    }

    private function setup_mysql_database_create_user() {
        /*
         * Create the user:
         */
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
            error_log("The database user " . $this->Config["database_user_self"] . " was created with the password: " . $this->Config["database_password_self"]);
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
                error_log("The database host is " . $this->Config["database_host"] . ", therefore we tried to create the user a second time with the host set to %");
                error_log("The result was: " . $result);
                return $result;
            }
            return TRUE;
        } else {
            error_log("The database user " . $this->Config["database_user_self"] . " could not be created with the password: " . $this->Config["database_password_self"]);
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
        $client_host = "localhost";
        if ("localhost" !== $this->Config["database_host"] and "127.0.0.1" !== $this->Config["database_host"] and "::1" !== $this->Config["database_host"]) {
            $client_host = "%";
        }
        $statement = $this->pdo->prepare("GRANT " . implode(", ", $Privileges) . " ON " . database_wrapper::quote_identifier($this->Config["database_name"]) . ".* TO :database_user@:client_host");
        $result = $statement->execute(array(
            'database_user' => $this->Config["database_user_self"],
            'client_host' => $client_host,
        ));

        error_log("GRANT all neccessary privileges to the database user on the client host: $client_host.");
        return $result;
    }

    public function handle_user_input_administration() {

        $this->Config["admin"]["user_name"] = filter_input(INPUT_POST, "user_name", FILTER_SANITIZE_STRING, $options = null);
        $this->Config["admin"]["last_name"] = filter_input(INPUT_POST, "last_name", FILTER_SANITIZE_STRING, $options = null);
        $this->Config["admin"]["first_name"] = filter_input(INPUT_POST, "first_name", FILTER_SANITIZE_STRING, $options = null);
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
        $statement = $this->pdo->prepare("INSERT INTO `employees` (`id`, `last_name`, `first_name`, `profession`) VALUES (:employee_id, :last_name, :first_name, :profession);");
        $result = $statement->execute(array(
            'employee_id' => $this->Config["admin"]["employee_id"],
            'last_name' => $this->Config["admin"]["last_name"],
            'first_name' => $this->Config["admin"]["first_name"],
            'profession' => "Apotheker", //Employees need a profession. We just take "Apotheker". The user can change this later on, if necessary.
        ));
        if (false === $result) {
            $this->Error_message[] = gettext("Error while trying to create employee.");
            return false;
        }
        //error_log("Created the employee " . $this->Config["admin"]["last_name"] . ", " . $this->Config["admin"]["first_name"] . " with the id " . $this->Config["admin"]["employee_id"]);
        global $config;
        $config['database_host'] = $this->Config['database_host'];
        $config['database_name'] = $this->Config['database_name'];
        $config['database_port'] = $this->Config['database_port'];
        $config['database_user'] = $this->Config['database_user'];
        $config['database_password'] = $this->Config['database_password'];
        $user = new user($this->Config["admin"]["employee_id"]);
        if (!$user->exists()) {
            $user_creation_result = $user->create_new($this->Config["admin"]["employee_id"], $this->Config["admin"]["user_name"], $password_hash, $this->Config["admin"]["email"], 'active');
            if (FALSE === $user_creation_result) {
                /*
                 * We were not able to create the administrative user.
                 */
                error_log("Error while trying to create administrative user.");
                $this->Error_message[] = gettext("Error while trying to create administrative user.");
                return FALSE;
            }
        } else {
            /*
             * The administrative user already exists.
             * We will not delete it.
             */
            error_log("Administrative user already exists.");
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
            if (FALSE === $result) {
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
        /**
         * 'posix' is not required.
         * Windows does not and can not have it. Linux has it by default.
         */
        $Required_extensions = array(
            'Core', 'PDO', 'calendar', 'date', 'filter', 'gettext', 'hash', 'iconv', 'json', 'mbstring',
            'openssl', 'pcre', 'session', 'standard', 'xml',
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
            $this->Error_message[] = sprintf(ngettext('The directory %1$s is not writable.', 'The directories %1$s are not writable.', count($List_of_non_writable_directories)), $this->fancy_implode($List_of_non_writable_directories, ", "));

            $this->Error_message[] = gettext("Make sure that the directories are writable by pdr!");
            if (function_exists('posix_getpwuid')) {
                /*
                 * Unix / Linux / MacOS:
                 */
                $current_www_user = posix_getpwuid(posix_geteuid())["name"];
            } else {
                /*
                 * Windows:
                 */
                $current_www_user = getenv('USERNAME');
            }
            $this->Error_message[] = "<pre class='install_cli'>sudo chown -R " . $current_www_user . ":" . $current_www_user . " " . $this->pdr_file_system_application_path . "</pre>\n";
            //$this->Error_message[] = gettext("Adapt the above code to the user and folder of your webserver!");
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function pdr_secret_directories_are_not_visible() {
        require_once $this->pdr_file_system_application_path . 'src/php/classes/class.test_htaccess.php';
        $test_htaccess = new test_htaccess();
        foreach (user_dialog::$Messages as $Message) {
            $this->Error_message[] = $Message['text'];
        }
        /*
         * This is a fix for the error in PHP 5.6:
         * PHP Fatal error:  Attempt to unset static property
         * TODO: This fix is far from perfect. We should let the class user_dialog handle this.
         *  Or we SHOULD just not use the user_dialog class for this task.
         */
        user_dialog::$Messages = array();
        return $test_htaccess->all_folders_are_secure;
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
        if (FALSE === $this->fill_mysql_database_tables()) {
            /*
             * There was a serious error while trying to fill the database tables.
             */
            $this->Error_message[] = gettext("Error while trying to fill the database tables.");
            return FALSE;
        }
        /**
         * After creating all the tables, we store the state of the table structure in form of a hash inside the database:
         */
        require_once PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/database_version_hash.php';
        $statement = $this->pdo->prepare("REPLACE INTO `pdr_self` (`pdr_database_version_hash`) VALUES (:pdr_database_version_hash);");
        $result = $statement->execute(array(
            'pdr_database_version_hash' => PDR_DATABASE_VERSION_HASH
        ));

        if (empty($this->Error_message)) {
            $this->write_config_to_session();
            /*
             * Success, we move to the next page.
             */
            header("Location: install_page_admin.php");
            die("<a href='install_page_admin.php'>Please move on to administrative user configuration!</a>");
        } else {
            return FALSE;
        }
    }

    private function write_config_to_session() {
        $_SESSION["Config"] = $this->Config;
        /*
         * Just in case we are interrupted and/or the session is lost, we write the values to a temporary installation file:
         */
        file_put_contents($this->pdr_file_system_application_path . 'config/config_temp_install.php', '<?php' . PHP_EOL . '$config =' . var_export($this->Config, true) . ';' . "\n");
    }

    private function read_config_from_session() {

        if (!empty($_SESSION["Config"])) {
            foreach ($_SESSION["Config"] as $key => $value) {
                if (!isset($this->Config[$key])) {
                    $this->Config[$key] = $value;
                }
            }
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
        $this->Error_message = array(); //Unsetting makes it possible to refill the array and build the new contents in another place.
        return $text_html;
    }

    private function fancy_implode($input_array, $delimiter = ", ") {
        /*
         * This also works for just one element in the array:
         */
        $last = array_pop($input_array);
        return count($input_array) ? implode($delimiter, $input_array) . " " . gettext("and") . " " . $last : $last;
    }

    private function setup_mysql_database_tables() {
        /**
         * Some tables have contraints.
         * In theory there would be a specific perfect order of table creation.
         * The referenced tables have to be created first.
         * As a workaround we store the tables, which could not be created.
         * After all tables have been tried to create the array of failed statements is executed again.
         * This is repeated, until no failed statements are left.
         */
        $this->connect_to_database();
        $sql_files = glob($this->pdr_file_system_application_path . "src/sql/*.sql");
        $list_of_failed_statements = array();
        $number_of_executions = 0;
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
            $statement = $this->pdo->prepare($sql_create_table_statement);
            $result = $statement->execute();
            if (TRUE !== $result) {
                $list_of_failed_statements[] = $statement;
            }
        }
        while (array() !== $list_of_failed_statements) {
            if (5 <= $number_of_executions++) {
                /*
                 * This loop will try to install all the tables.
                 * But it will only try 5 iterations of the whole array.
                 */
                error_log(print_r($statement->errorInfo(), TRUE));
                error_log("Error while creating the database tables. Not all tables could be created.");
                //TODO: Report also to the administrator on the screen.
                break;
            }
            foreach ($list_of_failed_statements as $key => $failed_statement) {
                $result = $failed_statement->execute();
                if (TRUE === $result) {
                    unset($list_of_failed_statements[$key]);
                }
            }
        }
        return $result;
    }

    private function fill_mysql_database_tables() {
        /**
         * Fill some tables with necessary data:
         */
        $Sql_query_array[] = "INSERT INTO `absence_reasons` (`id`, `reason_string`) VALUES
                (1, 'vacation'),
                (2, 'remaining vacation'),
                (3, 'sickness'),
                (4, 'sickness of child'),
                (5, 'taken overtime'),
                (6, 'paid leave of absence'),
                (7, 'maternity leave'),
                (8, 'parental leave');";
        foreach ($Sql_query_array as $sql_query) {
            $result = $this->pdo->query($sql_query);
            if ('00000' !== $result->errorCode()) {
                $this->pdo->rollBack();
                return FALSE;
            }
        }
    }

    /**
     * <p lang=de>
     * Wenn die Datenbank nicht korrekt erstellt werden konnte, so sollte sie m�glichst wieder entfernt werden.
     * Das sollte aber nur passieren, wenn es sie vorher noch nicht gegeben hat.
     * </p>
     */
    public function remove_database() {
        throw new Exception("Not implemented yet!");
        if (TRUE === $this->database_existed_before_installation) {
            return FALSE;
        }
        $statement = $this->pdo->prepare("DROP DATABASE :database_name");
        $result = $statement->execute(array(
            "database_name" => $this->Config["database_name"],
        ));
        return $result;
    }

    /**
     * Test if the database exists:
     */
    private function database_exists($database_name) {
        $statement = $this->pdo->prepare("SHOW DATABASES LIKE :database_name;");
        $result = $statement->execute(array(
            "database_name" => $database_name,
        ));
        while ($row = $statement->fetch(PDO::FETCH_NUM)) {
            if (!empty($row[0])) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Test if some database user exists:
     */
    private function database_user_exists($database_user_name) {
        $statement = $this->pdo->prepare("SELECT EXISTS (SELECT 1 FROM mysql.user WHERE user = :database_user_name) AS `exists`");
        $result = $statement->execute(array(
            "database_user_name" => $database_user_name,
        ));
        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
            if (1 == $row->exists) {
                return TRUE;
            }
        }
        return FALSE;
    }

}
