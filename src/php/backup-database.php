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

require_once 'default.php';

$sql_query = "SELECT TABLE_NAME FROM `TABLES` WHERE `TABLE_SCHEMA` = " . $config['database_name'];
mysqli_query_verbose($sql_query);
while ($row = mysqli_fetch_object($ergebnis)) {
    $Table_names[] = $row->TABLE_NAME;
    echo "Found table $row->TABLE_NAME<br>\n";
}

foreach ($Table_names as $key => $table_name){
    echo "Working on $table_name...<br>\n";
    $backup_file = get_root_folder() . "tmp/$table_name.sql";
    $sql = "SELECT * INTO OUTFILE '$backup_file' FROM $table_name";
}