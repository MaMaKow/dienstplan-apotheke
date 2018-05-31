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
    <HEAD<?php
    if (!empty($navigator_language)) {
        echo " lang=$navigator_language";
    }
    if (isset($config['application_name'])) {
        $application_name = $config['application_name'];
    } else {
        $application_name = 'PDR';
    }
    ?>>
        <META charset=UTF-8>
        <TITLE><?= $application_name ?></TITLE>
        <SCRIPT type="text/javascript" src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>javascript.js" ></SCRIPT>
        <LINK rel="stylesheet" type="text/css" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>datepicker.css" />
        <SCRIPT type="text/javascript" src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>datepicker.js"></SCRIPT>
        <LINK rel="stylesheet" type="text/css" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>style.css" media="all">
        <LINK rel="stylesheet" type="text/css" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>print.css" media="print">
        <?php
        /* The following two files are relevant only to collaborative-vacation-*.php
         * TODO: Maybe we should load them only where necessary.
         */
        ?>
        <SCRIPT type="text/javascript" src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>js/collaborative-vacation.js" ></SCRIPT>
        <LINK rel="stylesheet" type="text/css" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>css/collaborative-vacation.css" media="all">
    </HEAD>
    <BODY>
