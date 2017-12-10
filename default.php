<?php

/*
 * @var PDR_FILE_SYSTEM_APPLICATION_PATH The full path of the application root as determined by the position of the default.php
 */
define('PDR_FILE_SYSTEM_APPLICATION_PATH', __DIR__ . '/');
/*
 * @var PDR_HTTP_SERVER_APPLICATION_PATH The relative path of the application root on the web server.
 */
$folder_tree_depth_in_chars = strlen(substr(getcwd(), strlen(__DIR__)));
$root_folder = substr(dirname($_SERVER["SCRIPT_NAME"]), 0, strlen(dirname($_SERVER["SCRIPT_NAME"])) - $folder_tree_depth_in_chars) . "/";
define('PDR_HTTP_SERVER_APPLICATION_PATH', $root_folder);
//TODO: This does not work, if the location is a symbolic link.
/*
 * @var PDR_ONE_DAY_IN_SECONDS The amount of seconds in one day.
 */
define('PDR_ONE_DAY_IN_SECONDS', 24 * 60 * 60);


if (!file_exists(PDR_FILE_SYSTEM_APPLICATION_PATH . '/config/config.php')) {
    header("Location: " . PDR_HTTP_SERVER_APPLICATION_PATH . "src/php/pages/install_page_intro.php");
    die("The application does not seem to be installed. Please see the <a href='" . PDR_HTTP_SERVER_APPLICATION_PATH . "src/php/pages/install_page_intro.php'>installation page</a>!");
}

require "config/config.php";
//	file_put_contents('config/config.php', '<?php  $config =' . var_export($config, true) . ';');
//Setup if errors should be reorted to the user:
if (isset($config['error_reporting'])) {
    error_reporting($config['error_reporting']);
} else {
    error_reporting('E_ALL'); //debugging
}
ini_set("display_errors", 1); //debugging
ini_set("error_log", PDR_FILE_SYSTEM_APPLICATION_PATH . "/error.log");

//We want some functions to be accessable in all scripts.
require_once "funktionen.php";
//For development and debugging:
require_once 'src/php/classes/class.dBug.php';
//Setup the presentation of time values:
if (isset($config['LC_TIME'])) {
    setlocale(LC_TIME, $config['LC_TIME']);
} else {
    setlocale(LC_TIME, 'de_DE.utf8', 'de_DE@euro', 'de_DE', 'de', 'ge', 'deu-deu');
    //setlocale(LC_ALL, 'de_DE'); // Leider versteht die Datenbank dann nicht mehr, was die Kommata sollen.
}
//Setup the encoding for multibyte functions:
if (isset($config['mb_internal_encoding'])) {
    mb_internal_encoding($config['mb_internal_encoding']); //Dies ist notwendig für die Verarbeitung von UTF-8 Zeichen mit einigen funktionen wie mb_substr
} else {
    mb_internal_encoding('UTF-8'); //Dies ist notwendig für die Verarbeitung von UTF-8 Zeichen mit einigen funktionen wie mb_substr
}
require_once 'src/php/localization.php';
//Setup the default for hiding the duty roster before approval:
//We set it up to false in order not to disconcert new users.
if (!isset($config['hide_disapproved'])) {
    $config['hide_disapproved'] = false;
}

//Create a connection to the database:
require_once 'db-verbindung.php';

//session management
require_once 'src/php/classes/class.sessions.php';
$session = new sessions;

require_once 'src/php/build-warning-messages.php';


$navigator_languages = preg_split('/[,;]/', filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE', FILTER_SANITIZE_STRING));
$navigator_language = $navigator_languages[0]; //ignore the other options
