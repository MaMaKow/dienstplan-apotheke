<?php header('Content-Type: text/html; charset=utf-8'); ?>
<!DOCTYPE html>
<!--
Copyright (C) 2017 Mandelkow

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
-->
<HTML lang='<?= strstr($config["language"], '_', TRUE) ?>'>
    <HEAD lang="<?= $config["language"] ?>">
        <META charset=UTF-8>
        <TITLE><?= $config['application_name'] ?></TITLE>
        <SCRIPT src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/js/class.roster_item.js" ></SCRIPT>
        <SCRIPT src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/js/translations.js" ></SCRIPT>
        <SCRIPT src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/js/javascript.js" ></SCRIPT>
        <SCRIPT src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/js/keyboard_navigation.js" ></SCRIPT>
        <SCRIPT src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/js/roster-day-edit.js" ></SCRIPT>
        <SCRIPT src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/js/overtime.js" ></SCRIPT>
        <SCRIPT src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/js/user_dialog.js"></SCRIPT>
        <SCRIPT src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/js/collaborative-vacation.js" ></SCRIPT>
        <SCRIPT src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/js/emergency-service-list.js" ></SCRIPT>
        <SCRIPT src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/js/saturday-rotation.js" ></SCRIPT>
        <SCRIPT src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/js/drag-and-drop.js" ></SCRIPT>
        <?= includeSpecificJSForPage(); ?>
        <LINK rel="stylesheet" type="text/css" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/css/style.css" media="all">
        <LINK rel="stylesheet" type="text/css" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/css/print.css" media="print">
        <LINK rel="stylesheet" type="text/css" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/css/form_and_input.css">
        <LINK rel = "stylesheet" type = "text/css" href = "<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/css/user_dialog.css">
        <?= includeSpecificCSSForPage(); ?>
    </HEAD>
    <BODY>
        <?php
        $user_dialog = new user_dialog();
        if ($session->user_is_logged_in()) {
            echo $user_dialog->build_contact_form();
            $user_dialog->contact_form_send_mail();
        }

        /**
         * Include specific CSS files based on the current page.
         *
         * @return string The HTML code to include the specified CSS files.
         */
        function includeSpecificCSSForPage(): string {
            $cssText = '<!-- Electively include specific CSS files: -->' . PHP_EOL;
            // Initialize the $cssFiles array
            $cssFiles = array();
            // Determine the current page's file name
            $currentFile = basename($_SERVER['SCRIPT_FILENAME']);
            switch ($currentFile) {
                case 'overtime-overview.php':
                    $cssFiles[] = 'overtime.css';
                    $cssFiles[] = 'printOrientationPortrait.css';
                    break;
                case 'remaining-vacation-overview.php':
                    $cssFiles[] = 'printOrientationPortrait.css';
                    break;
                case 'saturday-list.php':
                    $cssFiles[] = 'saturday_list.css';
                    $cssFiles[] = 'printOrientationPortrait.css';
                    break;
                case 'emergency-service-list.php':
                    $cssFiles[] = 'emergency_service.css';
                    $cssFiles[] = 'printOrientationPortrait.css';
                    break;
                case 'collaborative-vacation-month.php':
                    $cssFiles[] = 'collaborative-vacation.css';
                    break;
                case 'collaborative-vacation-year.php':
                    $cssFiles[] = 'collaborative-vacation.css';
                    break;
                case 'principle-roster-employee.php':
                    $cssFiles[] = 'principle-roster-employee.css';
                    break;
                case 'roster-day-edit.php':
                    $cssFiles[] = 'roster-day-edit.css';
                    $cssFiles[] = 'printOrientationPortrait.css';
                    break;
            }
            foreach ($cssFiles as $cssFile) {
                $cssText .= '       <LINK rel="stylesheet" type="text/css" href="' . PDR_HTTP_SERVER_APPLICATION_PATH . "src/css/" . $cssFile . '" media="all">' . PHP_EOL;
            }
            return $cssText;
        }

        /**
         * Include specific JavaScript files based on the current page.
         *
         * @return string The HTML code to include the specified JavaScript files.
         */
        function includeSpecificJSForPage(): string {
            $jsText = '<!-- Electively include specific JS files: -->' . PHP_EOL;
            // Initialize the $jsFiles array
            $jsFiles = array();
            // Determine the current page's file name
            $currentFile = basename($_SERVER['SCRIPT_FILENAME']);
            switch ($currentFile) {
                case 'user-management.php':
                    $jsFiles[] = 'unsaved-changes-prompt.js';
                    break;
            }

            // Include JavaScript files
            foreach ($jsFiles as $jsFile) {
                $jsText .= '        <script src="' . PDR_HTTP_SERVER_APPLICATION_PATH . "src/js/" . $jsFile . '"></script>' . PHP_EOL;
            }
            return $jsText;
        }
        ?>
