<?php

global $verbindungi;
$verbindungi = new mysqli("localhost", $config['database_user'], $config['database_password'], $config['database_name']);
if (mysqli_connect_errno()) {
    error_log("Connect failed: %s\n", mysqli_connect_error() . " in file:" . __FILE__ . " on line:" . __LINE__);
    die("<p>There was an error while connecting to the database. Please see the error log for more details!</p>");
}
try {
    $pdo = new PDO('mysql:host=localhost;charset=utf8;dbname=' . $config['database_name'], $config['database_user'], $config['database_password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
} catch (PDOException $e) {
    error_log("Error!: " . $e->getMessage() . " in file:" . __FILE__ . " on line:" . __LINE__);
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
        print_debug_variable($exc->getTraceAsString());
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
