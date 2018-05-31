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

/*
 * @var PDR_FILE_SYSTEM_APPLICATION_PATH The full path of the application root as determined by the position of the default.php
 */
define('PDR_FILE_SYSTEM_APPLICATION_PATH', __DIR__ . '/');
/*
 * @var PDR_HTTP_SERVER_APPLICATION_PATH The relative path of the application root on the web server.
 */
$folder_tree_depth_in_chars = strlen(substr(getcwd(), strlen(__DIR__)));
/*
 * TODO:
 * For some weird reason, the following line is wrong now. It appands one to many slash /
 * But that was neseccary before.
 */
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

require_once PDR_FILE_SYSTEM_APPLICATION_PATH . "config/config.php";
//	file_put_contents('config/config.php', '<?php  $config =' . var_export($config, true) . ';');
//Setup the default for hiding the duty roster before approval:
//We set it up to false in order not to disconcert new administrators.
if (!isset($config['hide_disapproved'])) {
    $config['hide_disapproved'] = false;
}
//Setup if errors should be reorted to the user:
ini_set("display_errors", 1); //debugging
ini_set('log_errors', 1);
ini_set("error_log", PDR_FILE_SYSTEM_APPLICATION_PATH . "error.log");
if (isset($config['error_reporting'])) {
    error_reporting($config['error_reporting']);
} else {
    error_reporting(E_ALL); //debugging
}

spl_autoload_register(function ($class_name) {
    include_once PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/classes/class.' . $class_name . '.php';
});
//We want some functions to be accessable in all scripts.
require_once PDR_FILE_SYSTEM_APPLICATION_PATH . "funktionen.php";
//For development and debugging:
//require_once PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/classes/class.dBug.php';
//Setup the presentation of time values:
if (isset($config['LC_TIME'])) {
    setlocale(LC_TIME, $config['LC_TIME']);
} else {
    setlocale(LC_TIME, 'de_DE.utf8', 'de_DE@euro', 'de_DE', 'de', 'ge', 'deu-deu');
    //setlocale(LC_ALL, 'de_DE'); // Leider versteht die Datenbank dann nicht mehr, was die Kommata sollen.
}
/*
 * TODO: Make the timezone a configuration parameter
 */
date_default_timezone_set('Europe/Berlin');
//Setup the encoding for multibyte functions:
if (isset($config['mb_internal_encoding'])) {
    mb_internal_encoding($config['mb_internal_encoding']); //Dies ist notwendig für die Verarbeitung von UTF-8 Zeichen mit einigen funktionen wie mb_substr
} else {
    mb_internal_encoding('UTF-8'); //Dies ist notwendig für die Verarbeitung von UTF-8 Zeichen mit einigen funktionen wie mb_substr
}
require_once PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/localization.php';


//session management
//require_once PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/classes/class.sessions.php';
$session = new sessions;

require_once PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/build-warning-messages.php';
//require_once PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/classes/class.branch.php';
$List_of_branch_objects = branch::read_branches_from_database();

$navigator_languages = preg_split('/[,;]/', filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE', FILTER_SANITIZE_STRING));
$navigator_language = $navigator_languages[0]; //ignore the other options
