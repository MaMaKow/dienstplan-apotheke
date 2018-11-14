<?php

define('PDR_FILE_SYSTEM_APPLICATION_PATH', dirname(__DIR__) . '/');

// Set error reporting pretty high
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1); //Display errors to the end user?
ini_set('log_errors', 1); //Log errors to file?
ini_set('error_log', PDR_FILE_SYSTEM_APPLICATION_PATH . 'tests/test_error.log'); //Which file should errors be logged to?
// Get base, application and tests path
// Load autoloader
spl_autoload_register(function ($class_name) {
    include_once PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/classes/class.' . $class_name . '.php';
});

