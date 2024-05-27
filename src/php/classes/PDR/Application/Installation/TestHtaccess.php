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

namespace PDR\Application\Installation;

/**
 * @todo Check if user_dialog actually works during installation.
 */
class TestHtaccess {

    /**
     * @var array $listOfInsecureFolders list of all the folders not responding with 403.
     */
    public $listOfInsecureFolders;

    /**
     * @var array A list of folders containing sensitive information
     */
    private $ListOfForbiddenFolders = array(
        "tmp",
        "config",
        "upload",
        "tests",
    );

    /**
     * Public execution function for secret_folder_is_secure
     */
    function __construct() {
        $this->listOfInsecureFolders = $this->tryInsecureFolders($this->ListOfForbiddenFolders);
    }

    /**
     * Call the folder via https:// to see if it is visible
     *
     * @param string $folder the name and position of the folder to call.
     * @return bool TRUE for blocked folders, FALSE for visible folders.
     */
    private function secretFolderIsSecure($folder) { //There MUST NOT be a type hint here! Type hints only worked for objects prior to PHP7.
        $installUtility = new \PDR\Application\Installation\InstallUtility();
        $pdrServerApplicationPath = $installUtility->getPdrServerApplicationPath();
        $user_dialog = new \user_dialog();
        $hostname = filter_input(INPUT_SERVER, "HTTP_HOST", FILTER_SANITIZE_URL);

        $inputServerHttps = filter_input(INPUT_SERVER, "HTTPS", FILTER_SANITIZE_SPECIAL_CHARS);
        $protocol = 'http';
        if (!empty($inputServerHttps) and $inputServerHttps === "on") {
            $protocol = 'https';
        }
        $url = $protocol . "://" . $hostname . $pdrServerApplicationPath . $folder . '/';
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
        $responseArray = get_headers($url, 0, $context);
        $response = $responseArray[0];
        $responseCode = substr($response, strpos($response, " "), (strrpos($response, " ") - strpos($response, " ")));
        if (200 == $responseCode) {
            $error_message = "Warning! The directory <a href='$url'>$url</a> seems to be world visible. Please make sure that the directory is not accessible by the public!";
            $user_dialog->add_message($error_message, E_USER_ERROR, TRUE);
            return FALSE;
        } elseif (403 == $responseCode) {
            //$user_dialog->add_message("$folder is secure.", E_USER_NOTICE);
            return TRUE;
        } elseif (404 == $responseCode) {
            $user_dialog->add_message("The directory $folder is missing.", E_USER_WARNING);
            error_log("The directory $folder is missing.");
            return TRUE;
        } else {
            $error_message = "Error! The result could not be interpreted for the directory $url. Please make sure that the directory is not accessible by the public!<br>The server returned: '$response'. <br>";
            if (!is_array($responseArray)) {
                $user_dialog->add_message($error_message, E_USER_WARNING, TRUE);
                return FALSE;
            }
            foreach ($responseArray as $key => $response_http) {
                $error_message .= $key . ": " . htmlspecialchars($response_http) . "<br>\n";
            }
            $user_dialog->add_message($error_message, E_USER_WARNING, TRUE);
            return FALSE;
        }
    }

    public function getInsecureFoldersList() {//There MUST NOT be a type hint here! We need maximum compatibility during installation.
        return $this->listOfInsecureFolders;
    }

    public function allFoldersAreSecure() {//There MUST NOT be a type hint here! We need maximum compatibility during installation.
        if (array() === $this->listOfInsecureFolders) {
            return TRUE;
        }
        return FALSE;
    }

    private function tryInsecureFolders(array $Folders) {//There MUST NOT be a type hint here! We need maximum compatibility during installation.
        $listOfInsecureFolders = array();
        foreach ($Folders as $folder) {
            $secure = $this->secretFolderIsSecure($folder);
            if (TRUE !== $secure) {
                $listOfInsecureFolders[] = $folder;
            }
        }
        return $listOfInsecureFolders;
    }
}
