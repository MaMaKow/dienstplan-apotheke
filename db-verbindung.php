<?php

global $verbindungi;
$verbindungi = new mysqli("localhost", $config['database_user'], $config['database_password'], $config['database_name']);
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}
try {
    $pdo = new PDO('mysql:host=localhost;charset=utf8;dbname=' . $config['database_name'], $config['database_user'], $config['database_password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
} catch (PDOException $e) {
    error_log("Error!: " . $e->getMessage() . " in file:" . __FILE__ . " on line:" . __LINE__);
    die("<p>There was an error while querying the database. Please see the error log for more details!</p>");
}// change character set to utf8
if (!$verbindungi->set_charset("utf8")) {
    printf("Error loading character set utf8: %s\n", $verbindungi->error);
} else {
//    printf("Current character set: %s\n", $verbindungi->character_set_name());
}

function mysqli_query_verbose($sql_query, $inside_transaction = FALSE) {
    //print_debug_variable($sql_query);
    $result = mysqli_query($GLOBALS['verbindungi'], $sql_query);
    if ($result === FALSE) {
        $message = "Error: $sql_query <br>" . \mysqli_error($GLOBALS['verbindungi']);
        error_log($message);
        if ($inside_transaction !== FALSE) {
            mysqli_query($GLOBALS['verbindungi'], "ROLLBACK");
        }
        die("<p>There was an error while querying the database. Please see the error log for more details!</p>");
    }
    return $result;
}
