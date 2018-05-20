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

require_once '../../default.php';
if (!$session->user_has_privilege('administration')) {
    echo build_warning_messages("", ["Die notwendige Berechtigung zum Erstellen von Abwesenheiten fehlt. Bitte wenden Sie sich an einen Administrator."]);
    die();
}
echo test_folders(array("/tmp/", "/config/", "/upload/", "/tests/"));

/*
 * Call the folder via http:// to see if it is visible
 *
 * @param string $folder the name and position of the folder to call.
 * @return bool | string TRUE for blocked folders, an error message for visible folders.
 */

function secret_folder_is_secure($folder = "/tmp/") {
    $dirname = dirname($_SERVER["PHP_SELF"]);
    //This script lies within /src/php/ so therefore we have to move up by two levels:
    $dir_above1 = substr($dirname, 0, strrpos($dirname, "/"));
    $dir_above2 = substr($dirname, 0, strrpos($dir_above1, "/"));
    $hostname = filter_input(INPUT_SERVER, "HTTP_HOST", FILTER_SANITIZE_URL);
    /*
     * TODO: $hostname is not 100% secure. It might be possible to make requests in our name to different servers.
     * The page can only be used/misused by logged in users with administration privileges.
     * If there is the need to prevent this, a whitelist has to be put into the configuration file.
     * Any given hostname then has to be checked against it then.
     */
    $url = "https://" . $hostname . $dir_above2 . $folder;

    $Response = get_headers($url);
    $response = $Response[0];
    $response_code = substr($response, strpos($response, " "), (strrpos($response, " ") - strpos($response, " ")));
    if (200 == $response_code) {
        $error_message = "Warning! The directory <a href='$url'>$url</a> seems to be world visible. Please make sure that the directory is not accessible by the public!";
        return $error_message;
    } elseif (403 == $response_code) {
        return TRUE;
    } elseif (404 == $response_code) {
        //TODO: This is an error. Some directory is missing. But we do not have a good place to report it.
        error_log("The directory $folder is missing.");
        return TRUE;
    } else {
        $error_message = "Error! The result could not be interpreted for the directory $url. The server returned: '$response'. Please make sure that the directory is not accessible by the public!<br>";
        foreach ($Response as $key => $response_http) {
            $error_message .= $key . ": " . $response_http . "<br>\n";
        }
        return $error_message;
    }
}

/*
 * Public execution function for secret_folder_is_secure
 */

function test_folders($Folders) {
    $error_message_html = "<div class=error_container>\n";
    foreach ((array) $Folders as $folder) {
        $secure = secret_folder_is_secure($folder);
        if (TRUE !== $secure) {
            $error_message_html .= build_error_message($secure);
            $error_message_exists = TRUE;
        }
    }
    $error_message_html .= "</div>\n";
    if ($error_message_exists) {
        require_once get_root_folder() . 'head.php';
        return $error_message_html;
    } else {
        return FALSE;
    }
}

function build_error_message($error_message_text) {
    $error_message_html = "<div class=warningmsg>" . $error_message_text . "</div>\n";
    return $error_message_html;
}

?>
