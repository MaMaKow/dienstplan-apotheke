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
require "default.php";
$session->exit_on_missing_privilege('administration');

// include the Diff class
require_once 'src/php/classes/class.diff.php';

function echo_table_diff() {
//Then we collect the new data and write it to files:
    $sql_query = "SHOW TABLES";
    $sql_result_with_tables = database_wrapper::instance()->run($sql_query);
    while ($table_name = $sql_result_with_tables->fetch(PDO::FETCH_COLUMN)) {
        $sql_query = "SHOW CREATE TABLE " . database_wrapper::quote_identifier($table_name);
        $sql_result = database_wrapper::instance()->run($sql_query);
        while ($row = $sql_result->fetch(PDO::FETCH_ASSOC)) {
            $table_structure_create_with_increment = preg_replace('/CREATE TABLE/', 'CREATE TABLE IF NOT EXISTS', $row['Create Table']);
            $table_structure_create_new = preg_replace('/AUTO_INCREMENT=[0-9]*/', '', $table_structure_create_with_increment);
            $file_name = iconv("UTF-8", "ISO-8859-1", $table_name); //This is necessary for Microsoft Windows to recognise special chars.
            $file_name = 'src/sql/' . $file_name . '.sql';
            $table_structure_create_old = file_get_contents($file_name);
            $diff = Diff::compare($table_structure_create_old, $table_structure_create_new);
            if (0 !== array_sum(array_column($diff, 1))) {
                echo "<p>Changes in table " . $table_name . ":</p>\n";
                echo Diff::toTable($diff, '', '');
            } else {
                echo "<p>Table " . $table_name . " without changes</p>\n";
            }
            //TODO: Is ISO-8859-1 correct for all versions of Windows? Will there be any problems on Linux or Mac?
        }
    }
    $Triggers = get_new_trigger_data();
    foreach ($Triggers as $trigger_name => $trigger_data) {
        $file_name = iconv("UTF-8", "ISO-8859-1", $trigger_name); //This is necessary for Microsoft Windows to recognise special chars.
        $file_name = 'src/sql/' . $file_name . '.sql';
        $trigger_structure_create_old = file_get_contents($file_name);
        $diff = Diff::compare($trigger_structure_create_old, $trigger_data);
        if (0 !== array_sum(array_column($diff, 1))) {
            echo "<p>Changes in trigger " . $trigger_name . ":</p>\n";
            echo Diff::toTable($diff, '', '');
        } else {
            echo "<p>Trigger " . $trigger_name . " without changes</p>\n";
        }
    }
    return TRUE;
}

function get_new_trigger_data() {
//Then we collect the new data and write it to files:
    $sql_query = "SHOW TRIGGERS";
    $sql_result_with_triggers = database_wrapper::instance()->run($sql_query);
    while ($trigger_row = $sql_result_with_triggers->fetch(PDO::FETCH_ASSOC)) {
        $trigger_name = $trigger_row["Trigger"];
        $sql_query = "SHOW CREATE TRIGGER " . database_wrapper::quote_identifier($trigger_name);
        $sql_result = database_wrapper::instance()->run($sql_query);
        while ($row = $sql_result->fetch(PDO::FETCH_ASSOC)) {
            $trigger_structure_create = $row['SQL Original Statement'];
            $Triggers[$trigger_name] = $trigger_structure_create;
        }
    }
    return $Triggers;
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
