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
require "../../../default.php";
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
$session->exit_on_missing_privilege('administration');
if (isset($_FILES['file_to_upload']['name'])) {
    handle_user_input();
}

/**
 * public static
 */
function handle_user_input() {
    $target_file = PDR_FILE_SYSTEM_APPLICATION_PATH . 'upload/' . uniqid() . "_pep";
    $upload_file_name = basename($_FILES['file_to_upload']['name']);
    $upload_ok = 1;
    $file_type = pathinfo($upload_file_name, PATHINFO_EXTENSION);

    if (UPLOAD_ERR_OK != $_FILES['file_to_upload']['error']) {
        user_dialog::add_message(gettext('There was an error while trying to upload the file.'));
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
                user_dialog::add_message($message, E_USER_WARNING);
                $message = gettext('Make sure, that your webserver allows overwriting of settings and reads .htaccess files!');
                user_dialog::add_message($message, E_USER_NOTICE);
                $message = gettext('For apache2 locate the apache configuration file e.g. /etc/apache2/apache2.conf');
                $message .= ' ';
                $message .= gettext('Insert the following ruleset:');
                user_dialog::add_message($message, E_USER_NOTICE);
                $message = gettext($apache_conf_example);
                user_dialog::add_message($message, E_USER_NOTICE, TRUE);
                break;
            case 2:
                $message = gettext('UPLOAD_ERR_FORM_SIZE: The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.');
                user_dialog::add_message($message, E_USER_NOTICE);
                break;
            case 3:
                $message = gettext('UPLOAD_ERR_PARTIAL: The uploaded file was only partially uploaded.');
                user_dialog::add_message($message, E_USER_NOTICE);
                break;
            case 4:
                $message = gettext('UPLOAD_ERR_NO_FILE: No file was uploaded.');
                user_dialog::add_message($message, E_USER_NOTICE);
                break;
            case 6:
                $message = gettext('UPLOAD_ERR_NO_TMP_DIR: Missing a temporary folder.');
                user_dialog::add_message($message, E_USER_NOTICE);
                break;
            case 7:
                $message = gettext('UPLOAD_ERR_CANT_WRITE: Failed to write file to disk');
                user_dialog::add_message($message, E_USER_NOTICE);
                break;
            case 8:
                $message = gettext('UPLOAD_ERR_EXTENSION: A PHP extension stopped the file upload.');
                user_dialog::add_message($message, E_USER_NOTICE);
                break;
            default:
                $message = gettext('Unknown upload error.');
                print_debug_variable('Unknown upload error.');
                user_dialog::add_message($message, E_USER_NOTICE);
                break;
        }
        return FALSE;
    }

    if ($file_type != "asy") {
        /*
         * Allow certain file formats
         */
        user_dialog::add_message(gettext('Sorry, only ASYS PEP files are allowed.'));
        user_dialog::add_message(sprintf(gettext('You tried to upload: %1s'), $upload_file_name), E_USER_NOTICE);
        user_dialog::add_message(gettext('Please upload a valid ASYS PEP file!'), E_USER_NOTICE);
        $upload_ok = 0;
        return FALSE;
    }
    if (FALSE /* Add other checks here: */) {
        /*
         * TODO: Check if file is in the correct format.
         */
        user_dialog::add_message(gettext('Sorry, your file was not uploaded.'));
        return FALSE;
    }
    if (!move_uploaded_file($_FILES["file_to_upload"]["tmp_name"], $target_file)) {
        $message = gettext('Sorry, there was an error uploading your file');
        user_dialog::add_message($message, E_USER_ERROR);
        return FALSE;
    }
    $message = sprintf(gettext("The file %1s has been uploaded."), htmlentities($upload_file_name));
    $message .= ' ' . gettext('It will be processed in the background.');
    user_dialog::add_message($message, E_USER_NOTICE);
    echo "<input hidden type=text id=filename value='upload/" . htmlentities($_FILES["file_to_upload"]["name"]) . "'>\n";
    echo "<input hidden type=text id=targetfilename value='$target_file'>\n";
}
?>
<p style=height:2em></p>
<div id=main-area>
    <form method="post" id='pep_upload_form' enctype="multipart/form-data">
        <label for="file_to_upload">Eine PEP-Datei zum Hochladen auswählen:</label><br>
        <input type="file" name="file_to_upload" id="file_to_upload" onchange="this.form.submit()" ><br>
    </form>
</div>
<?php
echo user_dialog::build_messages();

echo "<p id=xmlhttpresult></p>\n";
echo "<p id=javascriptmessage></p>\n";
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php';
?>
<script type="text/javascript">
    update_pep();
</script>
</body>
</html>
