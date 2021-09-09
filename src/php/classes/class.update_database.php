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
            $message = date('Y-m-d') . ': ' . 'No need to update the database.' . PHP_EOL;
            error_log($message, 3, PDR_FILE_SYSTEM_APPLICATION_PATH . 'maintenance.log');
            return NULL;
        }
        $message = date('Y-m-d') . ': ' . 'Performing update of the database.' . PHP_EOL;
        error_log($message, 3, PDR_FILE_SYSTEM_APPLICATION_PATH . 'maintenance.log');
        //$this->refactor_opening_times_special_table();
        //$this->refactor_absence_table();
        //$this->refactor_duty_roster_table();
        //$this->refactor_receive_emails_on_changed_roster();
        //$this->refactor_user_email_notification_cache();
        //$this->refactor_pdr_self();
        //$this->refactor_principle_roster();
        $this->refactor_principle_roster2();
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
        if (database_wrapper::database_table_exists('Sonderöffnungszeiten') and!database_wrapper::database_table_exists('opening_times_special')) {
            database_wrapper::instance()->run("RENAME TABLE `Sonderöffnungszeiten` TO `opening_times_special`;");
            $sql_query = "ALTER TABLE `opening_times_special` CHANGE `Datum` `date` DATE NOT NULL, CHANGE `Beginn` `start` TIME NOT NULL, CHANGE `Ende` `end` TIME NOT NULL, CHANGE `Bezeichnung` `event_name` VARCHAR(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;";
            database_wrapper::instance()->run($sql_query);
        }
    }

    private function refactor_absence_table() {
        global $config;
        if (!database_wrapper::database_table_column_exists($config['database_name'], 'absence', 'reason_id')) {
            $Sql_query_array = array();
            $Sql_query_array[] = "CREATE TABLE IF NOT EXISTS `absence_reasons` (
              `id` tinyint(3) UNSIGNED NOT NULL,
              `reason_string` varchar(32) COLLATE latin1_german1_ci NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;";
            $Sql_query_array[] = "INSERT INTO `absence_reasons` (`id`, `reason_string`) VALUES
                (1, 'vacation'),
                (2, 'remaining vacation'),
                (3, 'sickness'),
                (4, 'sickness of child'),
                (5, 'taken overtime'),
                (6, 'paid leave of absence'),
                (7, 'maternity leave'),
                (8, 'parental leave');";
            $Sql_query_array[] = "ALTER TABLE `absence` ADD `reason_id` TINYINT UNSIGNED NOT NULL AFTER `employee_id`;";

            $Sql_query_array[] = "UPDATE `absence` SET `reason_id` = '1' WHERE `absence`.`reason` = 'vacation';";
            $Sql_query_array[] = "UPDATE `absence` SET `reason_id` = '2' WHERE `absence`.`reason` = 'remaining holiday';";
            $Sql_query_array[] = "UPDATE `absence` SET `reason_id` = '3' WHERE `absence`.`reason` = 'sickness';";
            $Sql_query_array[] = "UPDATE `absence` SET `reason_id` = '4' WHERE `absence`.`reason` = 'sickness of child';";
            $Sql_query_array[] = "UPDATE `absence` SET `reason_id` = '5' WHERE `absence`.`reason` = 'unpaid leave of absence';";
            $Sql_query_array[] = "UPDATE `absence` SET `reason_id` = '6' WHERE `absence`.`reason` = 'paid leave of absence';";
            $Sql_query_array[] = "UPDATE `absence` SET `reason_id` = '7' WHERE `absence`.`reason` = 'maternity leave';";
            $Sql_query_array[] = "UPDATE `absence` SET `reason_id` = '8' WHERE `absence`.`reason` = 'parental leave';";
            $Sql_query_array[] = "ALTER TABLE `absence` ADD FOREIGN KEY (`reason_id`) REFERENCES `absence_reasons`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;";
            $Sql_query_array[] = "ALTER TABLE `absence` DROP `reason`;";
            database_wrapper::instance()->beginTransaction();
            foreach ($Sql_query_array as $sql_query) {
                $result = database_wrapper::instance()->run($sql_query);
                if ('00000' !== $result->errorCode()) {
                    database_wrapper::instance()->rollBack();
                    return FALSE;
                }
            }
            database_wrapper::instance()->commit();
        }
        if (!database_wrapper::database_table_column_exists($config['database_name'], 'absence', 'comment')) {
            $sql_query = "ALTER TABLE `absence` ADD `comment` VARCHAR(64) NULL DEFAULT NULL AFTER `days`;";
            database_wrapper::instance()->run($sql_query);
        }
    }

    private function refactor_duty_roster_table() {
        if (database_wrapper::database_table_exists('Dienstplan') and!database_wrapper::database_table_exists('roster')) {
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
        if (database_wrapper::database_table_exists('users') and!database_wrapper::database_table_column_exists($database_name, 'users', 'receive_emails_on_changed_roster')) {
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
        if (database_wrapper::database_table_exists('user_email_notification_cache') and!database_wrapper::database_table_column_exists($database_name, 'user_email_notification_cache', 'date')) {
            $sql_query = "ALTER TABLE `user_email_notification_cache` ADD `date` DATE NOT NULL AFTER `employee_id`;";
            database_wrapper::instance()->run($sql_query);
        }
    }

    private function refactor_pdr_self() {
        $database_name = database_wrapper::get_database_name();
        if (database_wrapper::database_table_exists('pdr_self') and!database_wrapper::database_table_column_exists($database_name, 'pdr_self', 'principle_roster_start_date')) {
            $sql_query = "ALTER TABLE `pdr_self` ADD `principle_roster_start_date` date DEFAULT NULL AFTER `last_execution_of_maintenance`;";
            database_wrapper::instance()->run($sql_query);
        }
    }

    private function refactor_principle_roster() {
        if (!database_wrapper::database_table_exists('principle_roster')) {
            $sql_query = file_get_contents(PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/sql/principle_roster.sql');
            database_wrapper::instance()->run($sql_query);
        }
        if (database_wrapper::database_table_exists('Grundplan')) {
            database_wrapper::instance()->beginTransaction();
            $sql_query = "INSERT INTO `principle_roster` SELECT NULL, 0, `VK`, `Wochentag`, `Dienstbeginn`, `Dienstende`, `Mittagsbeginn`, `Mittagsende`, `Kommentar`, `Stunden`, `Mandant`, NULL, NULL FROM `Grundplan`;";
            $result = database_wrapper::instance()->run($sql_query);
            if ('00000' !== $result->errorCode()) {
                database_wrapper::instance()->rollBack();
                return FALSE;
            }
            $sql_query = "DROP TABLE Grundplan;";
            $result = database_wrapper::instance()->run($sql_query);
            if ('00000' !== $result->errorCode()) {
                database_wrapper::instance()->rollBack();
                return FALSE;
            }
            database_wrapper::instance()->commit();
            return TRUE;
        }
    }

    private function refactor_principle_roster2() {
        if (!database_wrapper::database_table_exists('principle_roster_archive')) {
            $sql_query = file_get_contents(PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/sql/principle_roster_archive.sql');
            database_wrapper::instance()->run($sql_query);
        }

        if (!database_wrapper::database_table_column_exists($config['database_name'], `principle_roster`, "valid_until")) {
            /**
             * <p lang=de>Die Tabelle ist bereits auf dem aktuellen Stand (1.0.0)</p>
             */
            return;
        }
        $sql_query_insert = "INSERT INTO `principle_roster_archive` (SELECT `primary_key`, `alternating_week_id`, `employee_id`, `weekday`, `duty_start`, `duty_end`, `break_start`, `break_end`, `comment`, `working_hours`, `branch_id`, NOW() FROM `principle_roster` WHERE `valid_until` IS NOT NULL)";
        $sql_query_delete = "DELETE FROM `principle_roster`  WHERE `valid_until` IS NOT NULL";
        $sql_query_from = "ALTER TABLE `principle_roster` DROP `valid_from`;";
        $sql_query_until = "ALTER TABLE `principle_roster` DROP `valid_until`;";

        database_wrapper::instance()->beginTransaction();
        $result = database_wrapper::instance()->run($sql_query_insert);
        if ('00000' !== $result->errorCode()) {
            database_wrapper::instance()->rollBack();
            return FALSE;
        }
        $result = database_wrapper::instance()->run($sql_query_delete);
        if ('00000' !== $result->errorCode()) {
            database_wrapper::instance()->rollBack();
            return FALSE;
        }
        $result = database_wrapper::instance()->run($sql_query_from);
        if ('00000' !== $result->errorCode()) {
            database_wrapper::instance()->rollBack();
            return FALSE;
        }
        $result = database_wrapper::instance()->run($sql_query_until);
        if ('00000' !== $result->errorCode()) {
            database_wrapper::instance()->rollBack();
            return FALSE;
        }
        database_wrapper::instance()->commit();
    }

}
