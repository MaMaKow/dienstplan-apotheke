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
require "default.php";

//First we delete the old files:
function delete_old_table_data() {
// This is to make sure, that any deleted tables in the database do not reappear.
    $files_to_be_deleted = glob('src/sql/*.sql'); // get all file names
    foreach ($files_to_be_deleted as $file) { // iterate files
        if (is_file($file)) {
            if (!unlink($file)) { // delete file
                return FALSE;
            }
        }
    }
    return TRUE;
}

function write_new_table_data() {
//Then we collect the new data and write it to files:
    $sql_query = "SHOW TABLES";
    $sql_result_with_tables = mysqli_query_verbose($sql_query);
    while ($table_row = mysqli_fetch_array($sql_result_with_tables)) {
        $table_name = $table_row[0];
        $sql_query = "SHOW CREATE TABLE " . $table_name;
        $sql_result = mysqli_query_verbose($sql_query);
        while ($row = mysqli_fetch_array($sql_result)) {
            $table_structure_create = $row['Create Table'];
            $file_name = iconv("UTF-8", "ISO-8859-1", $table_name); //This is necessary for Microsoft Windows to recognise special chars.
            //TODO: Is ISO-8859-1 correct for all versions of Windows? Will there be any problems on Linux or Mac?
            if (!file_put_contents('src/sql/' . $file_name . '.sql', $table_structure_create)) {
                return FALSE;
            }
        }
    }
    return TRUE;
}

if (delete_old_table_data() and write_new_table_data()) {
    echo "<p>New sql table structure files have been written.</p>";
} else {
    echo "<p>Error while writing sql table structure files.</p>";
}