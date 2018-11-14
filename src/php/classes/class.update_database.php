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

    public function __construct() {
        /*
         * Check if update is necessary
         */
        $sql_query = 'SELECT `pdr_database_version_hash` FROM pdr_self;';
        $result = database_wrapper::instance()->run($sql_query);
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $pdr_database_version_hash = $row->pdr_database_version_hash;
        }
        require_once PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/database_version_hash.php';
        if (PDR_DATABASE_VERSION_HASH === $pdr_database_version_hash) {
            /*
             * No need to update the database
             */
            return NULL;
        }
        $message = date('Y-m-d') . ': ' . 'Performing update of the database.' . PHP_EOL;
        error_log($message, 3, PDR_FILE_SYSTEM_APPLICATION_PATH . 'maintenance.log');
        $this->refactor_opening_times_special_table();
        $this->refactor_absence_table();
        //$this->refactor_duty_roster_table();
        $this->refactor_receive_emails_on_changed_roster();
        $this->refactor_user_email_notification_cache();
        /*
         * Write new pdr_database_version_hash into the database:
         */
        $sql_query = 'REPLACE INTO `pdr_self` (`pdr_database_version_hash`) VALUES (:pdr_database_version_hash);';
        $result = database_wrapper::instance()->run($sql_query, array(
            'pdr_database_version_hash' => PDR_DATABASE_VERSION_HASH
        ));
    }

    private function rename_database_table($table_name_old, $table_name_new) {
        /*
         * The table will be automatically locked during the command.
         *
         */
        $sql_query = "RENAME TABLE "
                . database_wrapper::quote_identifier($table_name_old)
                . " TO "
                . database_wrapper::quote_identifier($table_name_new);
        database_wrapper::instance()->run($sql_query);
    }

    private function refactor_opening_times_special_table() {
        if (database_wrapper::database_table_exists('Sonderöffnungszeiten') and ! database_wrapper::database_table_exists('opening_times_special')) {
            database_wrapper::instance()->run("RENAME TABLE `Sonderöffnungszeiten` TO `opening_times_special`;");
            $sql_query = "ALTER TABLE `opening_times_special` CHANGE `Datum` `date` DATE NOT NULL, CHANGE `Beginn` `start` TIME NOT NULL, CHANGE `Ende` `end` TIME NOT NULL, CHANGE `Bezeichnung` `event_name` VARCHAR(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;";
            database_wrapper::instance()->run($sql_query);
        }
    }

    private function refactor_absence_table() {
        global $config;
        if (!database_wrapper::database_table_column_exists($config['database_name'], 'absence', 'comment')) {
            $sql_query = "ALTER TABLE `absence` ADD `comment` VARCHAR(64) NULL DEFAULT NULL AFTER `days`;";
            database_wrapper::instance()->run($sql_query);
            $sql_query = "ALTER TABLE `absence` CHANGE `reason` `reason` ENUM('maternity leave','paid leave of absence','parental leave','remaining holiday','sickness','sickness of child','unpaid leave of absence','vacation') CHARACTER SET latin1 COLLATE latin1_german1_ci NOT NULL;";
            database_wrapper::instance()->run($sql_query);
        }
    }

    private function refactor_duty_roster_table() {
        if (database_wrapper::database_table_exists('Dienstplan') and ! database_wrapper::database_table_exists('roster')) {
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
            database_wrapper::instance()->run($sql_query);
            database_wrapper::instance()->run("RENAME TABLE `Dienstplan` TO `roster`;");
        }
    }

    private function refactor_receive_emails_on_changed_roster() {
        $database_name = database_wrapper::get_database_name();
        if (database_wrapper::database_table_exists('users') and ! database_wrapper::database_table_column_exists($database_name, 'users', 'receive_emails_on_changed_roster')) {
            $sql_query = "ALTER TABLE `users`  ADD `receive_emails_on_changed_roster` BOOLEAN NOT NULL DEFAULT FALSE  AFTER `failed_login_attempt_time`;";
            database_wrapper::instance()->run($sql_query);
        }
    }

    private function refactor_user_email_notification_cache() {
        if (!database_wrapper::database_table_exists('user_email_notification_cache')) {
            $sql_query = file_get_contents(PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/sql/user_email_notification_cache.sql');
            database_wrapper::instance()->run($sql_query);
        }
        $database_name = database_wrapper::get_database_name();
        if (database_wrapper::database_table_exists('user_email_notification_cache') and ! database_wrapper::database_table_column_exists($database_name, 'user_email_notification_cache', 'date')) {
            $sql_query = "ALTER TABLE `user_email_notification_cache` ADD `date` DATE NOT NULL AFTER `employee_id`;";
            database_wrapper::instance()->run($sql_query);
        }
    }

}
