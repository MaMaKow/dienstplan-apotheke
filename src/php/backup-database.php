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

require_once '../../default.php';

$sql_query = "SELECT TABLE_NAME FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = :database_name";
$result = database_wrapper::instance()->run($sql_query, array('database_name' => $config['database_name']));
while ($row = $result->fetch(PDO::FETCH_OBJ)) {
    $Table_names[] = $row->TABLE_NAME;
}

foreach ($Table_names as $key => $table_name) {
    //This script lies within /src/php/ so therefore we have to move up by two levels:
    $dirname = str_replace('\\', '/', __DIR__);
    //$dir_above1 = substr($dirname, 0, strrpos($dirname, '/'));
    $dir_above2 = dirname(dirname($dirname));

    $backup_file = $dir_above2 . "/tmp/$table_name.sql";
    $file_name = iconv("UTF-8", "ISO-8859-1", $backup_file); //This is necessary for Microsoft Windows to recognise special chars.

    $sql_query = "SELECT * INTO OUTFILE :file_name FROM :table_name";
    $result = database_wrapper::instance()->run($sql_query, array('file_name' => $file_name, 'table_name' => $table_name));
}
