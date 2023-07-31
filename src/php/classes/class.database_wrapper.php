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
 * This class is a database-wrapper.
 *
 * @author Your Common Sense @ShrapnelCol
 * @link https://phpdelusions.net/pdo/common_mistakes description
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 *
 */
class database_wrapper {

    protected static $instance;

    /**
     *
     * @var int A counter to prevent a loop in exception handling.
     */
    protected static $unknown_column_iterator;
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
            $port_string = '';
        }
        if (null == $this->database_host) {
            die("database_host ist not set inside the configuration!");
        }
        $dsn = 'mysql:host=' . $this->database_host . ';' . $port_string . 'dbname=' . $this->database_name . ';charset=utf8mb4';
        try {
            $this->pdo = new \PDO($dsn, $this->database_user_name, $this->database_password, $options);
        } catch (PDOException $exception) {
            print_debug_variable($exception);
            $message = gettext('There was an error while connecting to the database.')
                    . " " . gettext('Please see the error log for more details!')
                    . " " . sprintf(gettext('The error log resides in: %1$s'), ini_get('error_log'));
            die("<p>$message</p>");
        } catch (Exception $exception) {
            print_debug_variable($exception);
            $message = gettext('There was an error while connecting to the database.')
                    . " " . gettext('Please see the error log for more details!')
                    . " " . sprintf(gettext('The error log resides in: %1$s'), ini_get('error_log'));
            die("<p>$message</p>");
        }
    }

    /**
     *  A classical static method to make it universally available
     *  @return PDO Object of class PDO
     */
    public static function instance() {
        if (null === self::$instance) {
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
            /*
              if (false !== strpos($sql_query, "DELETE FROM `saturday_rotation_teams`")) {
              print_debug_variable($sql_query, $arguments);
              }
             */
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

    public static function database_table_index_exists($database_name, $table_name, $index_name) {
        $sql_query = "SELECT count(*) as index_exists FROM information_schema.statistics "
                . "WHERE TABLE_SCHEMA = :database_name AND TABLE_NAME = :table_name AND index_name = :index_name";
        $result = self::instance()->run($sql_query, array(
            'database_name' => $database_name,
            'table_name' => $table_name,
            'index_name' => $index_name
        ));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            if ($row->index_exists > 0) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public static function table_has_constraints($table_name) {
        $constraints = self::find_table_constraints($table_name);
        if (array() == $constraints) {
            return FALSE;
        }
        return TRUE;
    }

    private static function find_table_constraints($referenced_table_name) {
        $table_constraints = array();
        $sql_query = "SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_NAME = :referencedTableName AND TABLE_SCHEMA = DATABASE();";
        $result = self::instance()->run($sql_query, array(
            'referencedTableName' => $referenced_table_name,
        ));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            if ($row->index_exists > 0) {
                $table_constraints[] = clone $row;
            }
        }
        return $table_constraints;
    }

    public static function database_table_constraint_exists($table_name, $constraint_name) {
        $table_constraints = self::find_table_constraints($table_name);
        foreach ($table_constraints as $table_constraint_object) {
            $message = "Found constraint: " . $table_constraint_object->CONSTRAINT_NAME;
            error_log($message);
            print_debug_variable_to_screen($table_constraint_object);
            echo $message;
            if ($constraint_name == $table_constraint_object->CONSTRAINT_NAME) {
                return true;
            }
            return false;
        }
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
                    . " " . gettext('Please see the error log for more details!')
                    . " " . sprintf(gettext('The error log resides in: %1$s'), ini_get('error_log'));
            $user_dialog = new user_dialog();
            $user_dialog->add_message($message, E_USER_ERROR);
            throw $exception;
        } elseif ('42S22' == $exception->getCode() and 1054 === $exception->errorInfo[1]) {
            /*
             * Unknown column ... in field list
             * The database table does not match the expected layout.
             * We try to update the database.
             * CAVE: This will not be called on querys, which are inside a transaction.
             */
            if (3 <= self::$unknown_column_iterator++) {
                $message = gettext('There was an error while querying the database.')
                        . " " . gettext('Please see the error log for more details!')
                        . " " . sprintf(gettext('The error log resides in: %1$s'), ini_get('error_log'));
                die("<p>$message</p>");
            }
            new update_database();
            $statement = $this->pdo->prepare($sql_query);
            $statement->execute($arguments);
            return $statement;
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
                    . " " . gettext('Please see the error log for more details!')
                    . " " . sprintf(gettext('The error log resides in: %1$s'), ini_get('error_log'));
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
