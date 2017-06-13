<?php

/*
 * Copyright (C) 2017 Mandelkow
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once '../../default.php';

$sql_query = "SELECT TABLE_NAME FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = '" . $config['database_name'] . "'";
$result = mysqli_query_verbose($sql_query);
while ($row = mysqli_fetch_object($result)) {
    $Table_names[] = $row->TABLE_NAME;
}

foreach ($Table_names as $key => $table_name) {
    $dirname = __DIR__;
    //This script lies within /src/php/ so therefore we have to move up by two levels:
    $dirname = str_replace('\\', '/', $dirname);
    $dir_above1 = substr($dirname, 0, strrpos($dirname, '/'));
    $dir_above2 = substr($dirname, 0, strrpos($dir_above1, '/'));

    $backup_file = $dir_above2 . "/tmp/$table_name.sql";
    $backup_file = iconv("UTF-8", "ISO-8859-1", $backup_file); //This is necessary for Microsoft Windows to recognise special chars.

    $sql_query = "SELECT * INTO OUTFILE '$backup_file' FROM $table_name";
    $result = mysqli_query_verbose($sql_query);
}