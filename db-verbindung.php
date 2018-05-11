<?php

global $database_connection_mysqli;
$database_connection_mysqli = new mysqli($config['database_host'], $config['database_user'], $config['database_password'], $config['database_name']);
if (mysqli_connect_errno()) {
    error_log("Connect failed: %s\n", mysqli_connect_error() . " in file:" . __FILE__ . " on line:" . __LINE__);
    die("<p>There was an error while connecting to the database. Please see the error log for more details!</p>");
}
$database_connection_mysqli->set_charset('utf8');
try {
    $pdo = new PDO($config['database_management_system'] . ':host=localhost;charset=utf8;dbname=' . $config['database_name'], $config['database_user'], $config['database_password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', PDO::ATTR_EMULATE_PREPARES => false));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $exception) {
    error_log("Error!: " . $exception->getMessage() . " in file:" . __FILE__ . " on line:" . __LINE__);
    die("<p>There was an error while querying the database. Please see the error log for more details!</p>");
}// change character set to utf8
if (!$database_connection_mysqli->set_charset("utf8")) {
    printf("Error loading character set utf8: %s\n", $database_connection_mysqli->error);
}

function mysqli_query_verbose($sql_query, $inside_transaction = FALSE) {
    global $config, $database_connection_mysqli;
    $result = mysqli_query($database_connection_mysqli, $sql_query);
    if (1146 == $database_connection_mysqli->connect_errno) {
        /*
         * The table does not exist.
         * We might just create it:
         */
        database_wrapper::create_table_from_template($table_name);
    }
    if ($result === FALSE) {
        $message = "Error: $sql_query <br>" . mysqli_error($database_connection_mysqli);
        error_log($message);
        if ($inside_transaction !== FALSE) {
            mysqli_query($database_connection_mysqli, "ROLLBACK");
        }
        die("<p>There was an error while querying the database. Please see the error log for more details!</p>");
    } elseif (isset($config['debug_mode']) and TRUE === $config['debug_mode']) {
        //error_log('SQL Query: ' . $sql_query);
    }
    return $result;
}
