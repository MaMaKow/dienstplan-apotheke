<?php

/*
 * Copyright (C) 2017 Mandelkow
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
 * @var PDR_FILE_SYSTEM_APPLICATION_PATH The full path of the application root as determined by the position of the default.php
 */
define('PDR_FILE_SYSTEM_APPLICATION_PATH', __DIR__ . '/');
/**
 * @var PDR_HTTP_SERVER_APPLICATION_PATH The relative path of the application root on the web server.
 */
$folder_tree_depth_in_chars = strlen(substr(getcwd(), strlen(__DIR__)));
$root_folder = substr(dirname($_SERVER["SCRIPT_NAME"]), 0, strlen(dirname($_SERVER["SCRIPT_NAME"])) - $folder_tree_depth_in_chars) . "/";
define('PDR_HTTP_SERVER_APPLICATION_PATH', $root_folder);
//TODO: This does not work, if the location is a symbolic link.
/**
 * @var PDR_ONE_DAY_IN_SECONDS The amount of seconds in one day.
 */
define('PDR_ONE_DAY_IN_SECONDS', 24 * 60 * 60);

/*
 * Define an autoloader:
 */
spl_autoload_register(function ($class_name) {
    include_once PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/classes/class.' . $class_name . '.php';
});


if (!file_exists(PDR_FILE_SYSTEM_APPLICATION_PATH . '/config/config.php')) {
    header("Location: " . PDR_HTTP_SERVER_APPLICATION_PATH . "src/php/pages/install_page_intro.php");
    die("The application does not seem to be installed. Please see the <a href='" . PDR_HTTP_SERVER_APPLICATION_PATH . "src/php/pages/install_page_intro.php'>installation page</a>!");
} else {
    $config = array();
    global $config; //This has to be explicitly declared in order to work with PHPUnit

    /*
     * Load configuration parameters from the configuration file:
     */
    require_once PDR_FILE_SYSTEM_APPLICATION_PATH . "config/config.php";
    /*
     * Complement the configuration array with the default values for unset parameters:
     */
    foreach (configuration::$List_of_configuration_parameters as $key => $value) {
        if (!isset($config[$key])) {
            $config[$key] = $value;
        }
    }
}
/*
 * Setup if errors should be reported to the user, if to log them, and where:
 */
ini_set('display_errors', $config['display_errors']); //Display errors to the end user?
ini_set('log_errors', $config['log_errors']); //Log errors to file?
if ($config['log_errors'] or $config['display_errors']) {
    /*
     * Debug mode
     */
    ini_set('zend.assertions', 1); //Assertions will be compiled AND executed.
    ini_set('assert.exception', 1); //An exception will be thrown if an assertion fails.
} else {
    ini_set('zend.assertions', -1); //Assertions are not compiled.
    ini_set('assert.exception', 0); //Only warnings would be shown if assertions were to be executed and failed.
}
ini_set('error_log', $config['error_log']); //Which file should errors be logged to?
error_reporting($config['error_reporting']); //Which errors should be reported?

/*
 * We want some functions to be accessible in all scripts.
 */
require_once PDR_FILE_SYSTEM_APPLICATION_PATH . "funktionen.php";

/*
 * Setup the presentation of time values:
  //setlocale(LC_ALL, 'de_DE'); // Leider versteht die Datenbank dann nicht mehr, was die Kommata sollen.
 */
setlocale(LC_TIME, $config['LC_TIME']);
/*
 * Setup default timezone for date()
 */
date_default_timezone_set($config['timezone']);
/*
 * Setup the encoding for multibyte functions:
 * This is necessary for the usage of UTF-8 characters in functions like mb_substr()
 */
mb_internal_encoding($config['mb_internal_encoding']);
require_once PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/localization.php';

/*
 * session management
 */
$session = new sessions;

$List_of_branch_objects = branch::read_branches_from_database();
/*
 * Guess the navigator (=browser) language from HTTP_ACCEPT_LANGUAGE:
 * This is used in the head.php
 */
$navigator_languages = preg_split('/[,;]/', filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE', FILTER_SANITIZE_STRING));
$navigator_language = $navigator_languages[0]; //ignore the other options
