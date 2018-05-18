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
 * CAVE: This is an experiment!
 *
 * This class might become a database-wrapper.
 * But I will not actively use it, until I find a deeper understanding of what it does and does not!
 *
 * @author Your Common Sense @ShrapnelCol
 * @link https://phpdelusions.net/pdo/common_mistakes description
 * @author Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
 *
 */
class database_wrapper {

    protected static $instance;
    protected $pdo;
    private $database_host;
    //private $database_port;
    private $database_name;
    private $database_user_name;
    private $database_password;

    protected function __construct() {
        global $config;
        //throw new Exception('This class is work in progress. Do not use it!');
        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => FALSE,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        );
        $this->database_host = $config['database_host'];
        $this->database_name = $config['database_name'];
        $this->database_user_name = $config['database_user'];
        $this->database_password = $config['database_password'];

        $dsn = 'mysql:host=' . $this->database_host . ';dbname=' . $this->database_name . ';charset=utf8';
        $this->pdo = new PDO($dsn, $this->database_user_name, $this->database_password, $options);
    }

    /*
     *  a classical static method to make it universally available
     */

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /*
     *  a proxy to native PDO methods
     */

    public function __call($method, $args) {
        return call_user_func_array(array($this->pdo, $method), $args);
    }

    /*
     *  a helper function to run prepared statements smoothly
     */

    public function run($sql_query, $arguments = []) {
        try {
            print_debug_variable($sql_query, $arguments);
            $stmt = $this->pdo->prepare($sql_query);
            $stmt->execute($arguments);
        } catch (Exception $exception) {
            $this->handle_exceptions($exception, $sql_query, $arguments);
        }
        return $stmt;
    }

    protected static function create_table_from_template($filename) {
        $create_statement = file_get_contents($filename);
        self::instance()->query($create_statement);
    }

    protected static function create_table_insert_from_old_table($table_name) {
        switch ($table_name) {
            case opening_times:
                /*
                 * renamed table after commit 3b03e70f991208313eed872bfda3da273bd2c7ec
                 */
                $sql_query = "INSERT INTO opening_times (weekday, start, end, branch_id) "
                        . "SELECT Wochentag, Beginn, Ende, Mandant FROM Öffnungszeiten";
                self::instance()->run($sql_query);
                break;

            default:
                break;
        }
    }

    /**
     * Check if a table exists in the current database.
     *
     * https://stackoverflow.com/a/14355475/2323627
     *
     * @param PDO $pdo PDO instance connected to a database.
     * @param string $table Table to search for.
     * @return bool TRUE if table exists, FALSE if no table found.
     */
    public static function database_table_exists($table_name) {
        // Try a select statement against the table
        // Run it in try/catch in case PDO is in ERRMODE_EXCEPTION.
        try {
            $table_name_clean = self::quote_identifier($table_name);
            $result = self::instance()->query("SELECT 1 FROM $table_name_clean LIMIT 1");
        } catch (Exception $e) {
            error_log(var_export($e, TRUE));
            // We got an exception == table not found
            return FALSE;
        }
        // Result is either boolean FALSE (no table found) or PDOStatement Object (table found)
        return $result !== FALSE;
    }

    protected static function quote_identifier($field) {
        return "`" . str_replace("`", "``", $field) . "`";
    }

    /*
     * Enable the usage of prepared statements for IN clauses
     *
     * This methods helps to prevent sql injection.
     *
     * @param array an array of values to be queryed in an IN clause
     * @return array the placeholders and the fitting bind array
     * @return string $in_placeholder_trimmed the placeholder string
     * @return array $in_parameters the array items appended with ":in_placeholder"
     */

    public static function create_placeholder_for_mysql_IN_function($input_array, $named_placeholders = FALSE) {
        if (FALSE === $named_placeholders) {
            $in_placeholder = str_repeat('?,', count($input_array) - 1) . '?';
            return array($in_placeholder, $input_array);
        } else {
            $in = "";
            $in_parameters = array();
            foreach ($input_array as $iterator => $item) {
                $key = ":in_placeholder" . $iterator;
                $in .= "$key,";
                $in_parameters[$key] = $item; // collecting values into key-value array
            }
            $in_placeholder_trimmed = rtrim($in, ","); // :id0,:id1,:id2
            return array($in_placeholder_trimmed, $in_parameters);
        }
    }

    protected function handle_exceptions($exception, $sql_query, $arguments) {
        if (TRUE === $this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        } elseif ('42S02' == $exception->getCode()) {
            /*
             * Base table or view not found: 1146 Table doesn't exist
             * We try to create the table from the template.
             * Some refactored tables will be populated from their old versions.
             * CAVE: This will not be called on querys, which are inside a transaction.
             */
            $message = $exception->getMessage();
            foreach (glob(PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/sql/*.sql') as $filename_with_extension_and_path) {
                $filename_with_extension = basename($filename_with_extension_and_path);
                $table_name = substr($filename_with_extension, 0, strlen($filename_with_extension) - 4);
                if (FALSE !== strpos($message, ".$table_name'")) { //the dot (.) and the single quotation mark (') are part of the string: 'database_name.table_name'
                    self::create_table_from_template($filename_with_extension_and_path);
                    self::create_table_insert_from_old_table($table_name);
                }
            }
            try {
                unset($exception);
                /*
                 * Retry the query to the database with the newly created table:
                 */
                $stmt = $this->pdo->prepare($sql_query);
                $stmt->execute($arguments);
            } catch (Exception $exception) {
                /*
                 * This catch is superfluous.
                 * But we might want to add another layer of complication here sometime :-)
                 */
                throw $exception;
            }
        } else {
            throw $exception;
        }
    }

}
