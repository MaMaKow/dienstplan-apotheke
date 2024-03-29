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

class test_htaccess {

    /**
     *
     * @var bool $all_folders_are_secure is TRUE if all the hidden folders respond with 403.
     */
    public $all_folders_are_secure;

    /**
     *
     * @var array A list of folders containing sensitive information
     */
    private $List_of_forbidden_folders = array(
        "tmp",
        "config",
        "upload",
        "tests",
    );

    /**
     * Public execution function for secret_folder_is_secure
     */
    function __construct() {
        $this->all_folders_are_secure = $this->test_folders($this->List_of_forbidden_folders);
    }

    /**
     * Call the folder via https:// to see if it is visible
     *
     * @param string $folder the name and position of the folder to call.
     * @return bool TRUE for blocked folders, FALSE for visible folders.
     */
    private function secret_folder_is_secure($folder) { //There MUST NOT be a type hint here! Type hints only worked for objects prior to PHP7.
        $user_dialog = new user_dialog();
        $hostname = filter_input(INPUT_SERVER, "HTTP_HOST", FILTER_SANITIZE_URL);

        $input_server_https = filter_input(INPUT_SERVER, "HTTPS", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $protocol = 'http';
        if (!empty($input_server_https) and $input_server_https === "on") {
            $protocol = 'https';
        }
        $url = $protocol . "://" . $hostname . PDR_HTTP_SERVER_APPLICATION_PATH . $folder . '/';
        /**
         * This is just a simple test.
         * We do not need https here.
         * We will enforce it on other points.
         */
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ]);
        $Response = get_headers($url, 0, $context);
        $response = $Response[0];
        $response_code = substr($response, strpos($response, " "), (strrpos($response, " ") - strpos($response, " ")));
        if (200 == $response_code) {
            $error_message = "Warning! The directory <a href='$url'>$url</a> seems to be world visible. Please make sure that the directory is not accessible by the public!";
            $user_dialog->add_message($error_message, E_USER_ERROR, TRUE);
            return FALSE;
        } elseif (403 == $response_code) {
            //$user_dialog->add_message("$folder is secure.", E_USER_NOTICE);
            return TRUE;
        } elseif (404 == $response_code) {
            $user_dialog->add_message("The directory $folder is missing.", E_USER_WARNING);
            error_log("The directory $folder is missing.");
            return TRUE;
        } else {
            $error_message = "Error! The result could not be interpreted for the directory $url. Please make sure that the directory is not accessible by the public!<br>The server returned: '$response'. <br>";
            if (!is_array($Response)) {
                $user_dialog->add_message($error_message, E_USER_WARNING, TRUE);
                return FALSE;
            }
            foreach ($Response as $key => $response_http) {
                $error_message .= $key . ": " . htmlspecialchars($response_http) . "<br>\n";
            }
            $user_dialog->add_message($error_message, E_USER_WARNING, TRUE);
            return FALSE;
        }
    }

    private function test_folders(array $Folders) {
        $user_dialog = new user_dialog();
        $all_folders_are_secure = TRUE;
        foreach ($Folders as $folder) {
            $secure = $this->secret_folder_is_secure($folder);
            if (TRUE !== $secure) {
                $user_dialog->add_message("$folder is not secure.", E_USER_ERROR);
                $all_folders_are_secure = FALSE;
            }
        }
        return $all_folders_are_secure;
    }

}
