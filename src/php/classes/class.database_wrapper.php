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
 * This class is a database-wrapper.
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
    private $database_port;
    private $database_name;
    private $database_user_name;
    private $database_password;

    const ERROR_MESSAGE_DUPLICATE_ENTRY_FOR_KEY = 'Duplicate entry for key';

    protected function __construct() {
        global $config;
        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => FALSE,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        );
        $this->database_host = $config['database_host'];
        $this->database_name = $config['database_name'];
        $this->database_port = $config['database_port'];
        $this->database_user_name = $config['database_user'];
        $this->database_password = $config['database_password'];
        if (!empty($this->database_port) and 3306 != $this->database_port) {
            $port_string = 'port=' . $this->database_port . ';';
        } else {
            /*
             * TODO: Should we add special options for access thru the unix socket?
             * Note: Unix only:
             * When the host name is set to "localhost", then the connection to the server is made thru a domain socket. If PDO_MYSQL is compiled against libmysqlclient then the location of the socket file is at libmysqlclient's compiled in location. If PDO_MYSQL is compiled against mysqlnd a default socket can be set thru the pdo_mysql.default_socket setting.
             * dsn examples:
             * mysql:host=localhost;port=3306;dbname=testdb
             * mysql:unix_socket=/tmp/mysql.sock;dbname=testdb
             */
            $port_string = '';
        }
        $dsn = 'mysql:host=' . $this->database_host . ';' . $port_string . 'dbname=' . $this->database_name . ';charset=utf8';
        $this->pdo = new PDO($dsn, $this->database_user_name, $this->database_password, $options);
    }

    /**
     *  A classical static method to make it universally available
     *  @return object Object of class PDO
     */
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     *  Get the database name
     *  @return string database_name
     */
    public static function get_database_name() {
        if (self::$instance === null) {
            return FALSE;
        }
        $sql_query = "SELECT DATABASE() as `database_name` FROM DUAL;"; //On all databases except Oracle FROM DUAL can be omitted.
        $result = self::$instance->run($sql_query);
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $database_name = $row->database_name;
        }
        return $database_name;
    }

    /**
     *  A proxy to native PDO methods
     *  @param string $method Name of a method of the class PDO
     *  @param misc $arguments Description arguments passed to the PDO method
     */
    public function __call($method, $arguments) {
        return call_user_func_array(array($this->pdo, $method), $arguments);
    }

    /**
     *  A helper function to run prepared statements smoothly
     *  @return object Object of the class PDOStatement
     *  @param string $sql_query An SQL string to be queried against the database. It may contain placeholders which will be replaced by $arguments.
     *  @param array $arguments An array of values to be replaced into the placeholders.
     */
    public function run($sql_query, $arguments = []) {
        try {
            $statement = $this->pdo->prepare($sql_query);
            $statement->execute($arguments);
            return $statement;
        } catch (Exception $exception) {
            return $this->handle_exceptions($exception, $sql_query, $arguments);
        }
    }

    /**
     * Create a table inside the database.
     *
     * In the folder src/sql/ there are the create statements for all the database tables,
     * which should exist in the current version of the program.
     *
     * @param string $filename A fully qualified filename consisting of the folder, filename and the extension.
     */
    protected static function create_table_from_template($filename) {
        $create_statement = file_get_contents($filename);
        self::instance()->query($create_statement);
    }

    /**
     * Fill the newly created database table with values from an old table.
     *
     * In specific cases database tables might have been simply be renamed and/or their column names changed.
     * In those cases this function can make those changes.
     *
     * @before create_table_from_template
     * @param string $table_name
     */
    protected static function create_table_insert_from_old_table($table_name) {
        switch ($table_name) {
            case 'opening_times':
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
     * @link https://stackoverflow.com/a/14355475/2323627 source
     *
     * @param PDO $pdo PDO instance connected to a database.
     * @param string $table Table to search for.
     * @return bool TRUE if table exists, FALSE if no table found.
     */
    public static function database_table_exists($table_name) {
        /*
         *  Try a select statement against the table.
         *  Run it in try/catch in case PDO is in ERRMODE_EXCEPTION.
         */
        try {
            $table_name_clean = self::quote_identifier($table_name);
            $result = self::instance()->query("SELECT 1 FROM $table_name_clean LIMIT 1");
        } catch (Exception $exception) {
            /*
             *  We got an exception == table not found
             */
            return FALSE;
        }
        /*
         *  Result is either boolean FALSE (no table found) or PDOStatement Object (table found)
         */
        return $result !== FALSE;
    }

    public static function database_table_column_exists($database_name, $table_name, $column_name) {
        $sql_query = "SELECT * FROM information_schema.COLUMNS "
                . "WHERE TABLE_SCHEMA = :database_name AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name";
        $result = self::instance()->run($sql_query, array(
            'database_name' => $database_name,
            'table_name' => $table_name,
            'column_name' => $column_name
        ));
        while ($result->fetch()) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     *
     * @param string $field database identifier (i.e. database name, table name, column name)
     * @return string securely quoted identifier
     */
    public static function quote_identifier($field) {
        return "`" . str_replace("`", "``", $field) . "`";
    }

    /**
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

    /**
     * Handle exception thrown by self::run()
     *
     * Exceptions are supposed to be of the class PDOStatement.
     * If a transaction is active no further actions are taken. We simply rollBack() and die().
     *
     * @param Exception $exception
     * @param string $sql_query for the case of a repetition
     * @param type $arguments for the case of a repetition
     * @throws Exception If the error could not be resolved an exception is thrown or rethrown.
     */
    protected function handle_exceptions($exception, $sql_query, $arguments) {
        print_debug_variable($exception);
        if (TRUE === $this->pdo->inTransaction()) {
            $this->pdo->rollBack();
            $message = gettext('There was an error while querying the database.')
                    . " " . gettext('Please see the error log for more details!');
            die("<p>$message</p>");
        } elseif ('42S02' == $exception->getCode() and 1146 === $exception->errorInfo[1]) {
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
                    try {
                        unset($exception);
                        if (self::database_table_exists($table_name)) {
                            /*
                             * If we had success with creating the table,
                             * retry the query to the database with the newly created table:
                             */
                            $statement = $this->pdo->prepare($sql_query);
                            $statement->execute($arguments);
                            return $statement;
                        }
                    } catch (Exception $exception) {
                        /*
                         * This catch is superfluous.
                         * But we might want to add another layer of complication here sometime :-)
                         */
                        throw $exception;
                    }
                    break;
                }
            }
        } elseif ('23000' == $exception->getCode() and 1062 === $exception->errorInfo[1]) {
            /*
             * 23000 = Integrity constraint violation
             * 1062 = Duplicate entry for key
             */
            throw new Exception(self::ERROR_MESSAGE_DUPLICATE_ENTRY_FOR_KEY);
        } else {
            /*
             * Every exception is logged.
             * If that ever changes, then here is the last chance to do so for anything that we did not think of.
             * print_debug_variable($exception);
             */
            $message = gettext('There was an error while querying the database.')
                    . " " . gettext('Please see the error log for more details!');
            die("<p>$message</p>");
        }
    }

    public static function null_from_post_to_mysql($value) {
        if ('' === $value) {
            return NULL;
        } else {
            return $value;
        }
    }

}
