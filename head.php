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
        <LINK rel="stylesheet" type="text/css" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/css/style.css" media="all">
        <LINK rel="stylesheet" type="text/css" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/css/print.css" media="print">
        <LINK rel="stylesheet" type="text/css" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/css/overtime.css" media="all">
        <LINK rel="stylesheet" type="text/css" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/css/roster-day-edit.css" media="all">
        <LINK rel="stylesheet" type="text/css" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/css/principle-roster-employee.css" media="all">
        <LINK rel="stylesheet" type="text/css" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/css/saturday_list.css" media="all">
        <LINK rel="stylesheet" type="text/css" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/css/emergency_service.css">
        <LINK rel="stylesheet" type="text/css" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/css/user_dialog.css">
        <LINK rel="stylesheet" type="text/css" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/css/form_and_input.css">
        <LINK rel="stylesheet" type="text/css" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/css/collaborative-vacation.css" media="all">
    </HEAD>
    <BODY>
        <?php
        $user_dialog = new user_dialog();
        echo $user_dialog->build_contact_form();
        $user_dialog->contact_form_send_mail();
        ?>
