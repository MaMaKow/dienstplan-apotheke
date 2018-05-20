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
require "default.php";
$Fehlermeldung = array();
$Warnmeldung = array();
require 'head.php';
require 'src/php/pages/menu.php';
if (!$session->user_has_privilege('administration')) {
    echo build_warning_messages("", [gettext("Die notwendige Berechtigung zur Administration fehlt. Bitte wenden Sie sich an einen Administrator.")]);
    die();
}

if (filter_has_var(INPUT_POST, "submit")) {
    $target_dir = "/upload/";
    $target_file = PDR_FILE_SYSTEM_APPLICATION_PATH . $target_dir . uniqid() . "_pep";
    $upload_file_name = basename($_FILES["fileToUpload"]["name"]);
    $upload_ok = 1;
    $file_type = pathinfo($upload_file_name, PATHINFO_EXTENSION);

    if ($file_type != "asy") {
        // Allow certain file formats
        $Fehlermeldung[] = "Sorry, only ASYS PEP files are allowed.";
        $upload_ok = 0;
    } elseif ($upload_ok == 0) {
        // Check if $upload_ok is set to 0 by an error
        $Fehlermeldung[] = "Sorry, your file was not uploaded.";
        // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            $Message[] = "The file " . htmlentities(basename($_FILES["fileToUpload"]["name"])) . " has been uploaded.";
            $Message[] = "It will be processed in the background.";
            echo "<input hidden type=text id=filename value=upload/" . htmlentities($_FILES["fileToUpload"]["name"]) . ">\n";
            echo "<input hidden type=text id=targetfilename value=" . htmlentities($target_file) . ">\n";
        } else {
            $Fehlermeldung[] = "Sorry, there was an error uploading your file.<br>\n";
        }
    }
}
?>
<p style=height:2em></p>
<div id=main-area>
    <form action="upload-in.php" method="post" enctype="multipart/form-data">
        Eine PEP-Datei zum Hochladen auswählen:<br>
        <input type="file" name="fileToUpload" id="fileToUpload" onchange="reset_update_pep()"><br>
        <input type="submit" value="Upload" name="submit"><br>
    </form>
</div>
<?php
//Hier beginnt die Fehlerausgabe. Es werden alle Fehler angezeigt, die wir in $Fehlermeldung gesammelt haben.


echo build_warning_messages($Fehlermeldung, $Warnmeldung);

echo "<p id=xmlhttpresult></p>\n";
echo "<p id=javascriptmessage></p>\n";
echo "</div>";
require 'contact-form.php';
?>
<script type="text/javascript">
    update_pep();
</script>
<!-- The following lines might be an alternative to using javascript with ajax.

function do_post_request($url, $data, $optional_headers = null,$getresponse = false) {
$params = array('http' => array(
       'method' => 'POST',
       'content' => $data
    ));
if ($optional_headers !== null) {
$params['http']['header'] = $optional_headers;
}
$ctx = stream_context_create($params);
$fp = @fopen($url, 'rb', false, $ctx);
if (!$fp) {
return false;
}
if ($getresponse){
$response = stream_get_contents($fp);
return $response;
}
return true;
}-->
</body>
</html>
