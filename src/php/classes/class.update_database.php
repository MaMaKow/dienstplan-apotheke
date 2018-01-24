<?php

/*
 * Copyright (C) 2017 Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
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
 * @author Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class update_database {

    private $pdr_list_of_refactored_tables = array(
        'mandant' => 'branch',
        'Mandant' => 'branch',
        'dienstplan' => 'roster',
        'Dienstplan' => 'roster',
    );

    function __construct() {
        require_once PDR_FILE_SYSTEM_APPLICATION_PATH . 'db-verbindung.php';
        if (pdr_database_table_exists("Dienstplan") and ! pdr_database_table_exists("roster")) {
            $this->refactor_roster_table();
        }
    }

    public function rename_database_table($table_name_old, $table_name_new) {
        /*
         * The table will be automatically locked during the command.
         *
         */
        global $pdo;
        $sql_query = "RENAME TABLE `$table_name_old` TO `$table_name_new`";
        $pdo->execute($sql_query);
    }

    private function refactor_roster_table() {
        $sql_query = "ALTER TABLE `Dienstplan` "
                . "CHANGE `VK` `employee_id` TINYINT UNSIGNED NOT NULL, "
                . "CHANGE `Datum` `date` DATE NOT NULL, "
                . "CHANGE `Dienstbeginn` `start_of_shift` TIME NOT NULL DEFAULT '00:00:00', "
                . "CHANGE `Dienstende` `end_of_shift` TIME NULL DEFAULT NULL, "
                . "CHANGE `Mittagsbeginn` `start_of_lunch_break` TIME NULL DEFAULT NULL, "
                . "CHANGE `Mittagsende` `end_of_lunch_break` TIME NULL DEFAULT NULL, "
                . "CHANGE `Kommentar` `comment` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, "
                . "CHANGE `Stunden` `working_hours` FLOAT NULL DEFAULT NULL, "
                . "CHANGE `Mandant` `branch` TINYINT UNSIGNED NOT NULL DEFAULT '1', "
                . "CHANGE `timestamp` `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;";
        $pdo->execute($sql_query);
        $pdo->execute("RENAME TABLE `Dienstplan` TO `roster`;");
    }

}
