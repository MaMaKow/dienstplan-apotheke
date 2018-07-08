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
        <SCRIPT type="text/javascript" src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/js/translations.json" ></SCRIPT>
        <SCRIPT type="text/javascript" src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/js/javascript.js" ></SCRIPT>
        <SCRIPT type="text/javascript" src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/js/roster-day-edit.js" ></SCRIPT>
        <SCRIPT type="text/javascript" src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/js/overtime.js" ></SCRIPT>
        <SCRIPT type="text/javascript" src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/js/datepicker.js"></SCRIPT>
        <SCRIPT type="text/javascript" src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>js/collaborative-vacation.js" ></SCRIPT>
        <LINK rel="stylesheet" type="text/css" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/css/style.css" media="all">
        <LINK rel="stylesheet" type="text/css" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/css/print.css" media="print">
        <LINK rel="stylesheet" type="text/css" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/css/overtime.css" media="all">
        <LINK rel="stylesheet" type="text/css" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/css/datepicker.css" />
        <LINK rel="stylesheet" type="text/css" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>css/collaborative-vacation.css" media="all">
    </HEAD>
    <BODY>
