<?php

/*
 * Copyright (C) 2024 Mandelkow
 *
 * Dienstplan Apotheke
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace PDR\Input;

/**
 * Description of PepUpoadHandler
 *
 * @author Mandelkow
 */
class PepUploadHandler {

    public function handleFileUpload() {
        $userDialog = new \user_dialog();
        $targetFile = PDR_FILE_SYSTEM_APPLICATION_PATH . 'upload/' . uniqid() . "_pep";
        $uploadFileName = basename($_FILES['file_to_upload']['name']);
        if (UPLOAD_ERR_OK != $_FILES['file_to_upload']['error']) {
            $this->handleUploadErrors($userDialog);
            return FALSE;
        }
        $fileFormat = $this->determineFileFormat($userDialog);
        if (FALSE === $fileFormat) {
            $userDialog->add_message(sprintf(gettext('You tried to upload: %1$s.'), $uploadFileName), E_USER_NOTICE);
            return FALSE;
        }
        if (TRUE !== move_uploaded_file($_FILES["file_to_upload"]["tmp_name"], $targetFile)) {
            $message = gettext('Sorry, there was an error uploading your file.');
            $userDialog->add_message($message, E_USER_ERROR);
            return FALSE;
        }
        $message = sprintf(gettext('The file %1$s has been uploaded.'), htmlspecialchars($uploadFileName));
        $message .= ' ' . gettext('It will be processed in the background.');
        $userDialog->add_message($message, E_USER_NOTICE);
        echo "<input hidden type=text id=filename value='upload/" . htmlspecialchars($_FILES["file_to_upload"]["name"]) . "'>\n";
        echo "<input hidden type=text id=targetfilename value='$targetFile'>\n";
    }

    private function determineFileFormat(\user_dialog $userDialog) {
        $uploadFileName = basename($_FILES['file_to_upload']['name']);
        $fileExtension = pathinfo($uploadFileName, PATHINFO_EXTENSION);
        if ($fileExtension == "asy") {
            if (FALSE === $this->testFileContentPatternAsys($_FILES["file_to_upload"]["tmp_name"])) {
                $userDialog->add_message(gettext('Sorry, your file does not have the correct format.'));
                return FALSE;
            }
            return "awinta";
        }
        if (str_contains($fileExtension, "ADG_")) {
            if (FALSE === testFileContentPatternADG($_FILES["file_to_upload"]["tmp_name"])) {
                $userDialog->add_message(gettext('Sorry, your file does not have the correct format.'));
                return FALSE;
            }
            return "ADG";
        }
        /*
         * Allow certain file formats
         */
        $userDialog->add_message(gettext('Sorry, only awinta and ADG PEP files are allowed.'));
        $userDialog->add_message(gettext('Please upload a valid PEP file!'), E_USER_NOTICE);
        return FALSE;
    }

    /**
     *
     * Test if the file content matches the expected pattern for asys pep files.
     *
     * @param type $file_name
     * @return boolean
     */
    function testFileContentPatternAsys($fileName) {
        $pattern = '/[0-3][0-9]\.[0-1][0-9]\.[0-9][0-9][0-9][0-9];[0-2][0-9]:[0-5][0-9];[0-9]*,[0-9][0-9];[0-9]*;[0-9]*;[0-9]*/';
        $this->testFileContentPattern($fileName, $pattern);
    }

    /**
     *
     * Test if the file content matches the expected pattern for ADG pep files.
     *
     * @param type $fileName
     * @return boolean
     */
    function testFileContentPatternADG($fileName) {
        $pattern = '/[0-3][0-9]\.[0-1][0-9]\.[0-9][0-9][0-9][0-9];[0-2][0-9]:[0-5][0-9];[0-9]*\.[0-9][0-9];[0-9]*;[0-9]*;[0-9]*/';
        $this->testFileContentPattern($fileName, $pattern);
    }

    /**
     *
     * Test if the file content matches the expected pattern for ADG pep files.
     *
     * @param type $fileName
     * @return boolean
     */
    function testFileContentPattern($fileName, $pattern) {
        $handle = fopen($fileName, "r");
        if ($handle) {
            $numberOfMatches = 0;
            for ($index = 0; $index < 5; $index++) {
                $line = fgets($handle);
                $matches = array();
                if (1 === preg_match($pattern, $line, $matches)) {
                    $numberOfMatches++;
                }
                if ($numberOfMatches >= 4) {
                    /*
                     * Allow for one line in the test set to not match the pattern.
                     *   This might be the heading in the first line.
                     */
                    fclose($handle);
                    return TRUE;
                }
            }
            fclose($handle);
            return FALSE;
        } else {
            /*
             *  error opening the file
             */
            $user_dialog = new user_dialog;
            $user_dialog->add_message(gettext('Sorry, your file could not be opened.'));
            return FALSE;
        }
    }

    private function handleUploadErrors(\user_dialog $user_dialog) {
        if (UPLOAD_ERR_OK != $_FILES['file_to_upload']['error']) {
            $user_dialog->add_message(gettext('There was an error while trying to upload the file.'));
            switch ($_FILES['file_to_upload']['error']) {
                case 1:
                    $path = PDR_FILE_SYSTEM_APPLICATION_PATH;
                    $apache_conf_example = <<<EOT
&lt;Directory $path&gt;
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
&lt;/Directory&gt;
EOT;
                    $message = gettext('UPLOAD_ERR_INI_SIZE: The uploaded file exceeds the upload_max_filesize directive in php.ini:');
                    $message .= ' ' . ini_get('upload_max_filesize');
                    $user_dialog->add_message($message, E_USER_WARNING);
                    $message = gettext('Make sure, that your webserver allows overwriting of settings and reads .htaccess files!');
                    $user_dialog->add_message($message, E_USER_NOTICE);
                    $message = gettext('For apache2 locate the apache configuration file e.g. /etc/apache2/apache2.conf');
                    $message .= ' ';
                    $message .= gettext('Insert the following ruleset:');
                    $user_dialog->add_message($message, E_USER_NOTICE);
                    $message = gettext($apache_conf_example);
                    $user_dialog->add_message($message, E_USER_NOTICE, TRUE);
                    break;
                case 2:
                    $message = gettext('UPLOAD_ERR_FORM_SIZE: The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.');
                    $user_dialog->add_message($message, E_USER_NOTICE);
                    break;
                case 3:
                    $message = gettext('UPLOAD_ERR_PARTIAL: The uploaded file was only partially uploaded.');
                    $user_dialog->add_message($message, E_USER_NOTICE);
                    break;
                case 4:
                    $message = gettext('UPLOAD_ERR_NO_FILE: No file was uploaded.');
                    $user_dialog->add_message($message, E_USER_NOTICE);
                    break;
                case 6:
                    $message = gettext('UPLOAD_ERR_NO_TMP_DIR: Missing a temporary folder.');
                    $user_dialog->add_message($message, E_USER_NOTICE);
                    break;
                case 7:
                    $message = gettext('UPLOAD_ERR_CANT_WRITE: Failed to write file to disk');
                    $user_dialog->add_message($message, E_USER_NOTICE);
                    break;
                case 8:
                    $message = gettext('UPLOAD_ERR_EXTENSION: A PHP extension stopped the file upload.');
                    $user_dialog->add_message($message, E_USER_NOTICE);
                    break;
                default:
                    $message = gettext('Unknown upload error.');
                    \PDR\Utility\GeneralUtility::printDebugVariable('Unknown upload error.');
                    $user_dialog->add_message($message, E_USER_NOTICE);
                    break;
            }
            return FALSE;
        }
    }
}
