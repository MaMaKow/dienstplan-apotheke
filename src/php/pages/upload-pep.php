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

//TODO: drei Graphen mit den jeweils aktuellen pep Zahlen .;
require "../../../default.php";
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
$user_dialog = new user_dialog();
$session->exit_on_missing_privilege('administration');
if (isset($_FILES['file_to_upload']['name'])) {
    $pepUploadHandler = new PDR\Input\PepUploadHandler;
    $pepUploadHandler->handleFileUpload();
}
?>
<p style=height:2em></p>
<div id=main-area>
    <form method="post" id='pep_upload_form' enctype="multipart/form-data">
        <label for="file_to_upload">Eine PEP-Datei zum Hochladen auswählen:</label><br>
        <input type="file" name="file_to_upload" id="file_to_upload" onchange=" this.form.submit(); document.body.style.cursor = 'wait';" ><br>
    </form>
    <p id=xmlhttpresult class=day_paragraph></p>
    <?php
    echo $user_dialog->build_messages();
    $histogramm = new \pep_histogramm();
    $Expectation_javascripft_object = $histogramm->get_expectation_javascript_object(1);
    echo "<div id='expectation' data-expectation='$Expectation_javascripft_object'>";
    echo "</div>";
    $canvas_width = 650;
    $canvas_height = 300;
    echo $histogramm->get_last_update_of_pep_data_date_string();
    echo "<canvas id='canvas_histogram' width='$canvas_width' height='$canvas_height'>\n Your browser does not support the HTML5 canvas tag.\n </canvas>\n";
    ?>
    <script src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/js/draw_canvas_histogram.js" ></script>

</div>
<?php
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php';
?>
<script type="text/javascript">
            update_pep();
</script>
</body>
</html>
