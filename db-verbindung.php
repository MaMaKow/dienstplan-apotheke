<?php

global $verbindungi;
$verbindungi = new mysqli($config['database_host'], $config['database_user'], $config['database_password'], $config['database_name']);
if (mysqli_connect_errno()) {
    error_log("Connect failed: %s\n", mysqli_connect_error() . " in file:" . __FILE__ . " on line:" . __LINE__);
    die("<p>There was an error while connecting to the database. Please see the error log for more details!</p>");
}
try {
    $pdo = new PDO($config['database_management_system'] . ':host=localhost;charset=utf8;dbname=' . $config['database_name'], $config['database_user'], $config['database_password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
    /*
     * TODO: It is advised to throw exceptions with PDO. But that also means, that they have to be caught everywhere.
     * $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     */
} catch (PDOException $exception) {
    error_log("Error!: " . $exception->getMessage() . " in file:" . __FILE__ . " on line:" . __LINE__);
    die("<p>There was an error while querying the database. Please see the error log for more details!</p>");
}// change character set to utf8
if (!$verbindungi->set_charset("utf8")) {
    printf("Error loading character set utf8: %s\n", $verbindungi->error);
}

/*
 * This function uses PDO to access the database.
 * If no bind_array is given, then the query will be executed directly.
 * If a bind_array is there, then the sql_query will be interpreted as
 */

function pdo_query($sql_query, $bind_array = null, $inside_transaction = FALSE) {
    try {
        global $pdo;
        if (is_null($bind_array)) {
            $result = $pdo->execute($sql_query);
        } else {
            $statement = $pdo->prepare($sql_query);
            $result = $statement->execute($bind_array);
        }
        if ($result === FALSE) {
            error_log("Error: $sql_query <br>" . $pdo->errorInfo());
            if ($inside_transaction !== FALSE) {
                $pdo->rollBack();
            }
            die("<p>There was an error while querying the database. Please see the error log for more details!</p>");
        }
        return $result;
    } catch (Exception $exc) {
        error_log($exc->getTraceAsString());
        die("<p>There was an error while querying the database. Please see the error log for more details!</p>");
    }
}

function mysqli_query_verbose($sql_query, $inside_transaction = FALSE) {
    global $config;
    $result = mysqli_query($GLOBALS['verbindungi'], $sql_query);
    if ($result === FALSE) {
        $message = "Error: $sql_query <br>" . mysqli_error($GLOBALS['verbindungi']);
        error_log($message);
        if ($inside_transaction !== FALSE) {
            mysqli_query($GLOBALS['verbindungi'], "ROLLBACK");
        }
        die("<p>There was an error while querying the database. Please see the error log for more details!</p>");
    } elseif (TRUE === $config['debug_mode']) {
        //error_log('SQL Query: ' . $sql_query);
    }
    return $result;
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
function pdr_database_table_exists($table_name) {
    // Try a select statement against the table
    // Run it in try/catch in case PDO is in ERRMODE_EXCEPTION.
    global $pdo;
    try {
        $result = $pdo->query("SELECT 1 FROM $table LIMIT 1");
    } catch (Exception $e) {
        // We got an exception == table not found
        return FALSE;
    }
    // Result is either boolean FALSE (no table found) or PDOStatement Object (table found)
    return $result !== FALSE;
}
