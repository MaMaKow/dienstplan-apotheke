<?php

if ('cli' !== PHP_SAPI) {
    /*
     * see https://stackoverflow.com/a/25967493/2323627 for more options to test this.
     */
    die('This file may only be run from the command line. You tried to run from: ' . PHP_SAPI . '.');
}
/*
 * Anyone has access to the test database `pdr_test`
 * CREATE USER ''@'%';GRANT USAGE ON *.* TO ''@'%' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;GRANT ALL PRIVILEGES ON `pdr_test`.* TO ''@'%';
 */
define('PDR_FILE_SYSTEM_APPLICATION_PATH', dirname(__DIR__) . '/');
define('PDR_HTTP_SERVER_APPLICATION_PATH', 'FAKE_TEST_PATH_VAR_WWW' . '/');
define('PDR_ONE_DAY_IN_SECONDS', 24 * 60 * 60);
spl_autoload_register(function ($class_name) {
    include_once PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/classes/class.' . $class_name . '.php';
});
$config = array(
    'application_name' => 'PDR_TEST',
    'database_name' => 'pdr_test',
    'session_secret' => '40a8e06346471e26',
    'error_reporting' => E_ALL | E_STRICT,
    'display_errors' => 1,
    'log_errors' => 1,
    'error_log' => PDR_FILE_SYSTEM_APPLICATION_PATH . 'tests/test_error.log',
    'LC_TIME' => 'de_DE',
    'contact_email' => 'pdr-development-server@martin-mandelkow.de',
);
foreach (configuration::$List_of_configuration_parameters as $key => $value) {
    if (!isset($config[$key])) {
        $config[$key] = $value;
    }
}
ini_set('display_errors', $config['display_errors']); //Display errors to the end user?
ini_set('log_errors', $config['log_errors']); //Log errors to file?
ini_set('zend.assertions', 1); //Assertions will be compiled AND executed.
ini_set('assert.exception', 1); //An exception will be thrown if an assertion fails.
require_once PDR_FILE_SYSTEM_APPLICATION_PATH . "funktionen.php";
setlocale(LC_TIME, $config['LC_TIME']);
date_default_timezone_set($config['timezone']);
mb_internal_encoding($config['mb_internal_encoding']);
require_once PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/localization.php';
$navigator_language = 'de-de';


exec("mysql < " . PDR_FILE_SYSTEM_APPLICATION_PATH . 'tests/pdr_test.sql', $exec_result);
echo "mysql result: " . var_export($exec_result, TRUE) . PHP_EOL;


