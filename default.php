<?php

if (!file_exists('./config/config.php') and ! file_exists('../config/config.php')) {
    die("The application does not seem to be installed. Please see the <a href=install.php>installation page</a>!");
}

require "config/config.php";
//	file_put_contents('config/config.php', '<?php  $config =' . var_export($config, true) . ';');
//We want some functions to be accessable in all scripts.
require_once "funktionen.php";

//Setup the presentation of time values:
if (isset($config['LC_TIME'])) {
    setlocale(LC_TIME, $config['LC_TIME']);
} else {
    setlocale(LC_TIME, 'de_DE.utf8');
    //setlocale(LC_ALL, 'de_DE'); // Leider versteht die Datenbank dann nicht mehr, was die Kommata sollen.
}

//Setup the encoding for multibyte functions:
//This is currently only needed by anwesenheitsliste.php
if (isset($config['mb_internal_encoding'])) {
    mb_internal_encoding($config['mb_internal_encoding']); //Dies ist notwendig für die Verarbeitung von UTF-8 Zeichen mit einigen funktionen wie mb_substr
} else {
    mb_internal_encoding('UTF-8'); //Dies ist notwendig für die Verarbeitung von UTF-8 Zeichen mit einigen funktionen wie mb_substr
}

//Setup if errors should be reorted to the user:
if (isset($config['error_reporting'])) {
    error_reporting($config['error_reporting']);
} else {
    error_reporting('E_ALL'); //debugging
}
ini_set("display_errors", 1); //debugging
ini_set("error_log", "error.log");
//Setup the default for hiding the duty roster before approval:
//We set it up to false in order not to disconcert new users.
if (!isset($config['hide_disapproved'])) {
    $config['hide_disapproved'] = false;
}


//Define a value for the variable $user:
//Currently we are working with htaccess file on the production server.
//On the development server there is NO user management.
if (isset($_SERVER['REMOTE_USER'])) {
    $user = $_SERVER['REMOTE_USER'];
} else {
    $user = "IP " . $_SERVER['REMOTE_ADDR'];
}

//Create a connection to the database:
require_once 'db-verbindung.php';

/*TODO: The following lines are a form of tracking.
 * They HAVE TO be removed in a future version!
 * Collecting user data will only happen for specific reasons!
 * I am very interested in the browsers that are used for viewing the application.
 */
//echo $_SERVER['HTTP_USER_AGENT'] . "\n\n";
$browser = get_browser(null, true);
error_log('User: '.$user.", IP: ". $_SERVER['REMOTE_ADDR'].', Browser: '.$browser['browser']." = ".$_SERVER['HTTP_USER_AGENT']);

$navigator_languages = preg_split('/[,;]/', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
$navigator_language = $navigator_languages[0]; //ignore the other options