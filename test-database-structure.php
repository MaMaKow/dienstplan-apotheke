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

// include the Diff class
require_once 'src/php/class.Diff.php';

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

function echo_table_diff() {
//Then we collect the new data and write it to files:
    $sql_query = "SHOW TABLES";
    $sql_result_with_tables = mysqli_query_verbose($sql_query);
    while ($table_row = mysqli_fetch_array($sql_result_with_tables)) {
        $table_name = $table_row[0];
        $sql_query = "SHOW CREATE TABLE " . $table_name;
        $sql_result = mysqli_query_verbose($sql_query);
        while ($row = mysqli_fetch_array($sql_result)) {
            $table_structure_create_new = $row['Create Table'];
            $file_name = iconv("UTF-8", "ISO-8859-1", $table_name); //This is necessary for Microsoft Windows to recognise special chars.
            $file_name = 'src/sql/' . $file_name . '.sql';
            $table_structure_create_old = file_get_contents($file_name);
            $diff = Diff::compare($table_structure_create_old, $table_structure_create_new);
            if (0 !== array_sum(array_column($diff, 1))) {
                echo $table_name . ":<br>\n";
                echo Diff::toTable($diff, '', '');
            }
            //print_debug_variable($diff);
            //TODO: Is ISO-8859-1 correct for all versions of Windows? Will there be any problems on Linux or Mac?
        }
    }
    return TRUE;
}

// output the result of comparing two files as a table
echo "<HTML><HEAD><STYLE>
    .diff td{
        padding:0 0.667em;
        vertical-align:top;
        white-space:pre;
        white-space:pre-wrap;
        font-family:Consolas,'Courier New',Courier,monospace;
        font-size:0.75em;
        line-height:1.333;
      }

      .diff span{
        display:block;
        min-height:1.333em;
        margin-top:-1px;
        padding:0 3px;
      }

      * html .diff span{
        height:1.333em;
      }

      .diff span:first-child{
        margin-top:0;
      }

      .diffDeleted span{
        border:1px solid rgb(255,192,192);
        background:rgb(255,224,224);
      }
      .diffUnmodified span, td.diffUnmodified {
        font-size: 0.8em;
        /*line-height: 0.5;*/
      }

      .diffInserted span{
        border:1px solid rgb(192,255,192);
        background:rgb(224,255,224);
      }

      #toStringOutput{
        margin:0 2em 2em;
      }
}</STYLE></HEAD><BODY>";
echo_table_diff();

function write_new_trigger_data() {
//Then we collect the new data and write it to files:
    $sql_query = "SHOW TRIGGERS";
    $sql_result_with_triggers = mysqli_query_verbose($sql_query);
    while ($trigger_row = mysqli_fetch_array($sql_result_with_triggers)) {
        $trigger_name = $trigger_row["Trigger"];
        $sql_query = "SHOW CREATE TRIGGER " . $trigger_name;
        $sql_result = mysqli_query_verbose($sql_query);
        while ($row = mysqli_fetch_array($sql_result)) {
            $trigger_structure_create = $row['SQL Original Statement'];
            $file_name = iconv("UTF-8", "ISO-8859-1", $trigger_name); //This is necessary for Microsoft Windows to recognise special chars.
            //TODO: Is ISO-8859-1 correct for all versions of Windows? Will there be any problems on Linux or Mac?
            if (!file_put_contents('src/sql/' . $file_name . '.sql', $trigger_structure_create)) {
                return FALSE;
            }
        }
    }
    return TRUE;
}

/*
if (delete_old_table_data() and write_new_table_data() and write_new_trigger_data()) {
    echo "<p>New sql table structure files have been written.</p>";
} else {
    echo "<p>Error while writing sql table structure files.</p>";
}
 * 
 */
//TODO: Triggers should also be saved.
//Have another look into: https://www.slideshare.net/jonoxer/selfhealing-databases-managing-schema-updates-in-the-field/18-Missing_column_Record_schema_changes