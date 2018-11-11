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
if ('cli' !== PHP_SAPI) {
    /*
     * see https://stackoverflow.com/a/25967493/2323627 for more options to test this.
     */
    die('This file may only be run from the command line. You tried to run from: ' . PHP_SAPI . '.');
}
session_start();
$_SESSION['user_employee_id'] = 999;
$_SESSION['user_name'] = 'internal_non_user';
require "default.php";
session_destroy();
/**
 * In order to follow updates to the database structure, a hash on all the tables and triggers is made and stored.
 *
 * @global string $GLOBALS['text_to_be_hashed']
 * @name $text_to_be_hashed
 */
$GLOBALS['text_to_be_hashed'] = '';

//We delete the old files:
function delete_old_table_data() {
    // This is to make sure, that any deleted tables in the database do not reappear.
    $Files_to_be_deleted = glob('src/sql/*.sql'); // get all file names
    foreach ($Files_to_be_deleted as $file) { // iterate files
        if (is_file($file)) {
            if (!unlink($file)) { // delete file
                return FALSE;
            }
        }
    }
    return TRUE;
}

function write_new_table_data() {
    //Collect the new data and write it to files:
    global $New_table_files;
    $sql_query = "SHOW TABLES";
    $sql_result_with_tables = database_wrapper::instance()->run($sql_query);
    while ($table_name = $sql_result_with_tables->fetch(PDO::FETCH_COLUMN)) {
        $sql_query = "SHOW CREATE TABLE " . $table_name;
        $sql_result = database_wrapper::instance()->run($sql_query);
        while ($row = $sql_result->fetch(PDO::FETCH_ASSOC)) {
            $table_structure_create_with_increment = preg_replace('/CREATE TABLE/', 'CREATE TABLE IF NOT EXISTS', $row['Create Table']);
            $table_structure_create = preg_replace('/AUTO_INCREMENT=[0-9]*/', '', $table_structure_create_with_increment);
            $GLOBALS['text_to_be_hashed'] .= $table_structure_create;
            if (TRUE === running_on_windows()) {
                $file_name = iconv("UTF-8", "ISO-8859-1", $table_name); //This is necessary for Microsoft Windows to recognise special chars.
            } else {
                //$file_name = iconv("ISO-8859-15", "UTF-8", $table_name);
                //$file_name = iconv("ISO-8859-1", "UTF-8", $table_name);
                $file_name = $table_name;
            }
            echo "Writing file: " . $file_name . PHP_EOL;
            $file_name = PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/sql/' . $file_name . '.sql';
            //TODO: Is ISO-8859-1 correct for all versions of Windows? Will there be any problems on Linux or Mac?
            $New_table_files[] = $file_name;
            if (!file_put_contents($file_name, $table_structure_create)) {
                user_dialog::add_message(gettext('Error while writing the file to disk.'));
                return FALSE;
            }
        }
    }
    return TRUE;
}

function write_new_trigger_data() {
    //Collect the new data and write it to files:
    global $New_table_files;
    $sql_query = "SHOW TRIGGERS";
    $sql_result_with_triggers = database_wrapper::instance()->run($sql_query);
    while ($trigger_row = $sql_result_with_triggers->fetch(PDO::FETCH_ASSOC)) {
        $trigger_name = $trigger_row["Trigger"];
        $sql_query = "SHOW CREATE TRIGGER " . $trigger_name;
        $sql_result = database_wrapper::instance()->run($sql_query);
        while ($row = $sql_result->fetch(PDO::FETCH_ASSOC)) {
            /*
             * get rid of the definer clause e.g. DEFINER=`root`@`localhost`
             */
            $trigger_structure_create_without_definer = preg_replace('/DEFINER.*TRIGGER/', 'TRIGGER', $row['SQL Original Statement']);

            $GLOBALS['text_to_be_hashed'] .= $trigger_structure_create_without_definer;
            $file_name = iconv("UTF-8", "ISO-8859-1", $trigger_name); //This is necessary for Microsoft Windows to recognise special chars.
            //TODO: Is ISO-8859-1 correct for all versions of Windows? Will there be any problems on Linux or Mac?
            $file_name = PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/sql/trigger.' . $file_name . '.sql';
            $New_table_files[] = $file_name;
            if (!file_put_contents($file_name, $trigger_structure_create_without_definer)) {
                user_dialog::add_message(gettext('Error while writing the trigger file to disk.'));
                return FALSE;
            }
        }
    }
    return TRUE;
}

if (delete_old_table_data() and write_new_trigger_data() and write_new_table_data()) {
    echo "New sql table structure files have been written." . PHP_EOL;
    $database_hash = sha1($GLOBALS['text_to_be_hashed']);
    $database_version_hash_text = '<?php ' . PHP_EOL . PHP_EOL . "const PDR_DATABASE_VERSION_HASH = '" . $database_hash . "';" . PHP_EOL;
    if (!file_put_contents(PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/database_version_hash.php', $database_version_hash_text)) {
        user_dialog::add_message(gettext('Error while writing the database hash file to disk.'));
    }
} else {
    echo "Error while writing sql table structure files." . PHP_EOL;
}
//Have another look into: https://www.slideshare.net/jonoxer/selfhealing-databases-managing-schema-updates-in-the-field/18-Missing_column_Record_schema_changes
echo user_dialog::build_messages_for_cli();
