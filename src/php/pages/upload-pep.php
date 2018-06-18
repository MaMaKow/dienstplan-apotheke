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

if (filter_has_var(INPUT_POST, "submit")) {
    $target_dir = "/upload/";
    $target_file = PDR_FILE_SYSTEM_APPLICATION_PATH . $target_dir . uniqid() . "_pep";
    $upload_file_name = basename($_FILES["fileToUpload"]["name"]);
    $upload_ok = 1;
    $file_type = pathinfo($upload_file_name, PATHINFO_EXTENSION);

    if ($file_type != "asy") {
        /*
         * Allow certain file formats
         */
        user_dialog::add_message(gettext('Sorry, only ASYS PEP files are allowed.'));
        $upload_ok = 0;
    } elseif (0 == $upload_ok) {
        /*
         * Check if $upload_ok is set to 0 by an error
         */
        user_dialog::add_message(gettext('Sorry, your file was not uploaded.'));
        // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            $message = sprintf(gettext("The file %1s has been uploaded."), htmlentities(basename($_FILES["fileToUpload"]["name"])));
            $message .= ' ' . gettext('It will be processed in the background.');
            user_dialog::add_message($message, E_USER_NOTICE);
            echo "<input hidden type=text id=filename value=upload/" . htmlentities($_FILES["fileToUpload"]["name"]) . ">\n";
            echo "<input hidden type=text id=targetfilename value=" . htmlentities($target_file) . ">\n";
        } else {
            $message = gettext('Sorry, there was an error uploading your file');
            user_dialog::add_message($message, E_USER_ERROR);
        }
    }
}
?>
<p style=height:2em></p>
<div id=main-area>
    <form action="upload-in.php" method="post" enctype="multipart/form-data">
        <label for="fileToUpload">Eine PEP-Datei zum Hochladen auswählen:</label><br>
        <input type="file" name="fileToUpload" id="fileToUpload" onchange="reset_update_pep()"><br>
        <input type="submit" value="Upload" name="submit"><br>
    </form>
</div>
<?php
echo user_dialog::build_messages();

echo "<p id=xmlhttpresult></p>\n";
echo "<p id=javascriptmessage></p>\n";
echo "</div>";
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'contact-form.php';
?>
<script type="text/javascript">
    update_pep();
</script>
</body>
</html>
