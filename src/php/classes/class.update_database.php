<?php

/*
 * Copyright (C) 2017 Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
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

/**
 * Description of class
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class update_database {

    public function __construct() {
        /*
         * Check if update is necessary
         */
        error_log(date('Y-m-d H:i:s') . PHP_EOL, 3, PDR_FILE_SYSTEM_APPLICATION_PATH . 'maintenance.log');
        $sql_query = 'SELECT `pdr_database_version_hash` FROM pdr_self;';
        $result = database_wrapper::instance()->run($sql_query);
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $pdr_database_version_hash = $row->pdr_database_version_hash;
            error_log("Read pdr_database_version_hash from database: " . $pdr_database_version_hash . PHP_EOL, 3, PDR_FILE_SYSTEM_APPLICATION_PATH . 'maintenance.log');
        }
        require_once PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/database_version_hash.php';
        error_log("Read PDR_DATABASE_VERSION_HASH from file: " . PDR_DATABASE_VERSION_HASH . PHP_EOL, 3, PDR_FILE_SYSTEM_APPLICATION_PATH . 'maintenance.log');
        if (PDR_DATABASE_VERSION_HASH === $pdr_database_version_hash) {
            /*
             * No need to update the database
             */
            $message = date('Y-m-d') . ': ' . 'No need to update the database.' . PHP_EOL;
            error_log($message, 3, PDR_FILE_SYSTEM_APPLICATION_PATH . 'maintenance.log');
            return NULL;
        }
        $message = date('Y-m-d') . ': ' . 'Performing update of the database.' . PHP_EOL;
        error_log($message, 3, PDR_FILE_SYSTEM_APPLICATION_PATH . 'maintenance.log');

        $databaseMigrator = new PDR\Database\Migration\DatabaseMigrator();
        $databaseMigrator->migrate();

        /**
         * Write new pdr_database_version_hash into the database:
         */
        $message = date('Y-m-d') . ': ' . 'Write new pdr_database_version_hash into the database:' . PHP_EOL;
        error_log($message, 3, PDR_FILE_SYSTEM_APPLICATION_PATH . 'maintenance.log');
        $sql_query = 'REPLACE INTO `pdr_self` (`pdr_database_version_hash`) VALUES (:pdr_database_version_hash);';
        $result = database_wrapper::instance()->run($sql_query, array(
            'pdr_database_version_hash' => PDR_DATABASE_VERSION_HASH
        ));
        $message = date('Y-m-d') . ': ' . 'Done with update_database' . PHP_EOL;
        error_log($message, 3, PDR_FILE_SYSTEM_APPLICATION_PATH . 'maintenance.log');
    }
}
