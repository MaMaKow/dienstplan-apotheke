<?php

if ('cli' !== PHP_SAPI) {
    /*
     * see https://stackoverflow.com/a/25967493/2323627 for more options to test this.
     */
    die('This file may only be run from the command line. You tried to run from: ' . PHP_SAPI . '.');
}

session_start();
$_SESSION['user_employee_id'] = 999;
$_SESSION['user_name'] = 'internal_non_user';
//require_once dirname(__DIR__) . '/default.php';
require_once dirname(__DIR__) . '/default.php';

// Set error reporting pretty high
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1); //Display errors to the end user?
ini_set('log_errors', 1); //Log errors to file?
ini_set('error_log', PDR_FILE_SYSTEM_APPLICATION_PATH . 'tests/test_error.log'); //Which file should errors be logged to?
