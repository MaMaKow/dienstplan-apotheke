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

        $this->refactor_opening_times_special_table();
        $this->refactor_absence_table(); // CAVE! This contains the change to utf8mb4.
//$this->refactor_duty_roster_table();
        $this->refactor_receive_emails_on_changed_roster();
        $this->refactor_user_email_notification_cache();
        $this->refactor_pdr_self();
//$this->refactor_principle_roster();
        if (FALSE === $this->refactor_principle_roster2()) {
            return FALSE;
        }
        $this->employee_refactor_primary_key();
        $this->refactorEmergencyService();
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

    private function rename_database_table($table_name_old, $table_name_new) {
        /**
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
        if (database_wrapper::database_table_exists('Sonderöffnungszeiten') and !database_wrapper::database_table_exists('opening_times_special')) {
            database_wrapper::instance()->run("RENAME TABLE `Sonderöffnungszeiten` TO `opening_times_special`;");
            $sql_query = "ALTER TABLE `opening_times_special` "
                    . "CHANGE `Datum` `date` DATE NOT NULL, "
                    . "CHANGE `Beginn` `start` TIME NOT NULL, "
                    . "CHANGE `Ende` `end` TIME NOT NULL, "
                    . "CHANGE `Bezeichnung` `event_name` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;";
            database_wrapper::instance()->run($sql_query);
        }
    }

    private function refactor_absence_table() {
        if (!database_wrapper::database_table_column_exists(database_wrapper::get_database_name(), 'absence', 'reason_id')) {
            $Sql_query_array = array();
            /**
             * Change the database and all the tables and columns to utf8mb4:
             */
            if (FALSE === $this->change_charset_to_utf8mb4()) {
                return FALSE;
            }
            /**
             * Change the actual absence tables:
             */
            $Sql_query_array[] = "CREATE TABLE IF NOT EXISTS `absence_reasons` (
              `id` tinyint(3) UNSIGNED NOT NULL,
              `reason_string` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
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
        if (!database_wrapper::database_table_column_exists(database_wrapper::get_database_name(), 'absence', 'comment')) {
            $sql_query = "ALTER TABLE `absence` ADD `comment` VARCHAR(64) NULL DEFAULT NULL AFTER `days`;";
            database_wrapper::instance()->run($sql_query);
        }
    }

    private function refactor_duty_roster_table() {
        if (database_wrapper::database_table_exists('Dienstplan') and !database_wrapper::database_table_exists('roster')) {
            $sql_query_list = array();
            $sql_query_list[] = "ALTER TABLE `Dienstplan` CHANGE `employee_key` `employee_id` TINYINT UNSIGNED NOT NULL ";
            $sql_query_list[] = "ALTER TABLE `Dienstplan` CHANGE `Datum` `date` DATE NOT NULL";
            $sql_query_list[] = "ALTER TABLE `Dienstplan` CHANGE `Dienstbeginn` `start_of_shift` TIME NOT NULL DEFAULT '00:00:00'";
            $sql_query_list[] = "ALTER TABLE `Dienstplan` CHANGE `Dienstende` `end_of_shift` TIME NULL DEFAULT NULL";
            $sql_query_list[] = "ALTER TABLE `Dienstplan` CHANGE `Mittagsbeginn` `start_of_lunch_break` TIME NULL DEFAULT NULL";
            $sql_query_list[] = "ALTER TABLE `Dienstplan` CHANGE `Mittagsende` `end_of_lunch_break` TIME NULL DEFAULT NULL";
            $sql_query_list[] = "ALTER TABLE `Dienstplan` CHANGE `Kommentar` `comment` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL";
            $sql_query_list[] = "ALTER TABLE `Dienstplan` CHANGE `Stunden` `working_hours` FLOAT NULL DEFAULT NULL";
            $sql_query_list[] = "ALTER TABLE `Dienstplan` CHANGE `Mandant` `branch` TINYINT UNSIGNED NOT NULL DEFAULT '1'";
            $sql_query_list[] = "ALTER TABLE `Dienstplan` CHANGE `timestamp` `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;";
            database_wrapper::instance()->beginTransaction();
            foreach ($sql_query_list as $sql_query) {
                error_log($sql_query);
                $result = database_wrapper::instance()->run($sql_query);
                if ('00000' !== $result->errorCode()) {
                    database_wrapper::instance()->rollBack();
                    return FALSE;
                }
            }
            database_wrapper::instance()->run("RENAME TABLE `Dienstplan` TO `roster`;");
            database_wrapper::instance()->commit();
        }
    }

    private function refactor_receive_emails_on_changed_roster() {
        $database_name = database_wrapper::get_database_name();
        if (database_wrapper::database_table_exists('users') and !database_wrapper::database_table_column_exists($database_name, 'users', 'receive_emails_on_changed_roster')) {
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
        if (database_wrapper::database_table_exists('user_email_notification_cache') and !database_wrapper::database_table_column_exists($database_name, 'user_email_notification_cache', 'date')) {
            $sql_query = "ALTER TABLE `user_email_notification_cache` ADD `date` DATE NOT NULL AFTER `employee_id`;";
            database_wrapper::instance()->run($sql_query);
        }
    }

    private function refactor_pdr_self() {
        $database_name = database_wrapper::get_database_name();
        if (database_wrapper::database_table_exists('pdr_self') and !database_wrapper::database_table_column_exists($database_name, 'pdr_self', 'principle_roster_start_date')) {
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
            $sql_query = "INSERT INTO `principle_roster` SELECT NULL, 0, `employee_key`, `Wochentag`, `Dienstbeginn`, `Dienstende`, `Mittagsbeginn`, `Mittagsende`, `Kommentar`, `Stunden`, `Mandant`, NULL, NULL FROM `Grundplan`;";
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

        if (!database_wrapper::database_table_column_exists(database_wrapper::get_database_name(), "principle_roster", "valid_until")) {
            /**
             * <p lang=de>Die Tabelle ist bereits auf dem aktuellen Stand (1.0.0)</p>
             */
            return null;
        }
        $sql_query_insert = "INSERT INTO `principle_roster_archive` (SELECT `primary_key`, `alternating_week_id`, `employee_id`, `weekday`, `duty_start`, `duty_end`, `break_start`, `break_end`, `comment`, `working_hours`, `branch_id`, `valid_until` FROM `principle_roster` WHERE `valid_until` IS NOT NULL)";
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
        return TRUE;
    }

    private function change_charset_to_utf8mb4() {
        $Sql_query_array = array();
        $quoted_database_name = database_wrapper::quote_identifier(database_wrapper::get_database_name());
        $Sql_query_array[] = "ALTER DATABASE $quoted_database_name CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`absence` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`approval` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`branch` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`Dienstplan` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        /**
         * <p lang=de>Um den Charset für ein mysql SET zu ändern musste zunächst
         *  einmal das ganze SET gekürzt werden. Vieleicht gibt es einen eleganteren Weg?</p>
         */
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`employees` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`employees` CHANGE `profession` `profession` SET('Apotheker','PI','PTA','PKA','Praktikant','Ernährungsberater','Kosmetiker','Zugehfrau','Apo','Pra','Ern','Kos','Zug') CHARACTER SET latin1 COLLATE latin1_german1_ci NOT NULL;";
        $Sql_query_array[] = "UPDATE $quoted_database_name.`employees` SET `profession` = 'Apo' WHERE `employees`.`profession` = 'Apotheker';";
        $Sql_query_array[] = "UPDATE $quoted_database_name.`employees` SET `profession` = 'Pra' WHERE `employees`.`profession` = 'Praktikant';";
        $Sql_query_array[] = "UPDATE $quoted_database_name.`employees` SET `profession` = 'Ern' WHERE `employees`.`profession` = 'Ernährungsberater';";
        $Sql_query_array[] = "UPDATE $quoted_database_name.`employees` SET `profession` = 'Kos' WHERE `employees`.`profession` = 'Kosmetiker';";
        $Sql_query_array[] = "UPDATE $quoted_database_name.`employees` SET `profession` = 'Zug' WHERE `employees`.`profession` = 'Zugehfrau';";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`employees` CHANGE `profession` `profession` SET('Apotheker','PI','PTA','PKA','Praktikant','Ernährungsberater','Kosmetiker','Zugehfrau','Apo','Pra','Ern','Kos','Zug') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "UPDATE $quoted_database_name.`employees` SET `profession` = 'Apotheker' WHERE `employees`.`profession` = 'Apo';";
        $Sql_query_array[] = "UPDATE $quoted_database_name.`employees` SET `profession` = 'Praktikant' WHERE `employees`.`profession` = 'Pra';";
        $Sql_query_array[] = "UPDATE $quoted_database_name.`employees` SET `profession` = 'Ernährungsberater' WHERE `employees`.`profession` = 'Ern';";
        $Sql_query_array[] = "UPDATE $quoted_database_name.`employees` SET `profession` = 'Kosmetiker' WHERE `employees`.`profession` = 'Kos';";
        $Sql_query_array[] = "UPDATE $quoted_database_name.`employees` SET `profession` = 'Zugehfrau' WHERE `employees`.`profession` = 'Zug';";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`employees` CHANGE `profession` `profession` SET('Apotheker','PI','PTA','PKA','Praktikant','Kosmetiker','Zugehfrau') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`employees_backup` CHANGE `profession` `profession` SET('Apotheker','PI','PTA','PKA','Praktikant','Ernährungsberater','Kosmetiker','Zugehfrau','Apo','Pra','Ern','Kos','Zug') CHARACTER SET latin1 COLLATE latin1_german1_ci NOT NULL;";
        $Sql_query_array[] = "UPDATE $quoted_database_name.`employees_backup` SET `profession` = 'Apo' WHERE `employees_backup`.`profession` = 'Apotheker';";
        $Sql_query_array[] = "UPDATE $quoted_database_name.`employees_backup` SET `profession` = 'Pra' WHERE `employees_backup`.`profession` = 'Praktikant';";
        $Sql_query_array[] = "UPDATE $quoted_database_name.`employees_backup` SET `profession` = 'Ern' WHERE `employees_backup`.`profession` = 'Ernährungsberater';";
        $Sql_query_array[] = "UPDATE $quoted_database_name.`employees_backup` SET `profession` = 'Kos' WHERE `employees_backup`.`profession` = 'Kosmetiker';";
        $Sql_query_array[] = "UPDATE $quoted_database_name.`employees_backup` SET `profession` = 'Zug' WHERE `employees_backup`.`profession` = 'Zugehfrau';";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`employees_backup` CHANGE `profession` `profession` SET('Apotheker','PI','PTA','PKA','Praktikant','Ernährungsberater','Kosmetiker','Zugehfrau','Apo','Pra','Ern','Kos','Zug') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "UPDATE $quoted_database_name.`employees_backup` SET `profession` = 'Apotheker' WHERE `employees_backup`.`profession` = 'Apo';";
        $Sql_query_array[] = "UPDATE $quoted_database_name.`employees_backup` SET `profession` = 'Praktikant' WHERE `employees_backup`.`profession` = 'Pra';";
        $Sql_query_array[] = "UPDATE $quoted_database_name.`employees_backup` SET `profession` = 'Ernährungsberater' WHERE `employees_backup`.`profession` = 'Ern';";
        $Sql_query_array[] = "UPDATE $quoted_database_name.`employees_backup` SET `profession` = 'Kosmetiker' WHERE `employees_backup`.`profession` = 'Kos';";
        $Sql_query_array[] = "UPDATE $quoted_database_name.`employees_backup` SET `profession` = 'Zugehfrau' WHERE `employees_backup`.`profession` = 'Zug';";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`employees_backup` CHANGE `profession` `profession` SET('Apotheker','PI','PTA','PKA','Praktikant','Ernährungsberater','Kosmetiker','Zugehfrau') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        /**
         * Fertig mit den beiden SETS für Berufe der Mitarbeiter
         */
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`employees` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`employees_backup` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`Feiertage` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`maintenance` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`Notdienst` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`opening_times` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`opening_times_special` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`pdr_self` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`pep` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`pep_month_day` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`pep_weekday_time` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`pep_year_month` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`principle_roster` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`saturday_rotation` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`saturday_rotation_teams` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`Schulferien` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`Stunden` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`task_rotation` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`users` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`users_lost_password_token` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`users_privileges` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`user_email_notification_cache` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`users` CHANGE `user_name` `user_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`users` CHANGE `email` `email` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`users` CHANGE `password` `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`Dienstplan` CHANGE `user` `user` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`Feiertage` CHANGE `Name` `Name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`Schulferien` CHANGE `Name` `Name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`Stunden` CHANGE `Grund` `Grund` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`absence` CHANGE `comment` `comment` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`absence` CHANGE `user` `user` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`approval` CHANGE `user` `user` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`branch` CHANGE `name` `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`branch` CHANGE `short_name` `short_name` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`branch` CHANGE `address` `address` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`branch` CHANGE `manager` `manager` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`employees` CHANGE `last_name` `last_name` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`employees` CHANGE `first_name` `first_name` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`employees_backup` CHANGE `last_name` `last_name` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`employees_backup` CHANGE `first_name` `first_name` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`pdr_self` CHANGE `pdr_version_string` `pdr_version_string` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`pdr_self` CHANGE `pdr_database_version_hash` `pdr_database_version_hash` char(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`task_rotation` CHANGE `task` `task` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`users_privileges` CHANGE `privilege` `privilege` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`opening_times_special` CHANGE `event_name` `event_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`Dienstplan` CHANGE `Kommentar` `Kommentar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`user_email_notification_cache` CHANGE `notification_text` `notification_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE $quoted_database_name.`principle_roster` CHANGE `comment` `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;";
        if (!database_wrapper::instance()->inTransaction()) {
            database_wrapper::instance()->beginTransaction();
        }
        foreach ($Sql_query_array as $sql_query) {
            $result = database_wrapper::instance()->run($sql_query);
            if ('00000' !== $result->errorCode()) {
                database_wrapper::instance()->rollBack();
                return FALSE;
            }
        }
        database_wrapper::instance()->commit();
    }

    private function employee_refactor_primary_key() {
        if (!database_wrapper::database_table_column_exists(database_wrapper::get_database_name(), "users_privileges", "employee_id")) {
            /**
             * <p lang=de>Die Tabelle ist bereits auf dem aktuellen Stand (0.17.1)</p>
             */
            return;
        }

        if (!database_wrapper::database_table_column_exists(database_wrapper::get_database_name(), "employees", "primary_key")) {
            $Sql_query_array[] = "ALTER TABLE `employees` CHANGE `pseudo_id` `primary_key` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT; ";
        }

        if (database_wrapper::database_table_constraint_exists('Dienstplan', 'Dienstplan_ibfk_1')) {
            $Sql_query_array[] = "ALTER TABLE `Dienstplan` DROP FOREIGN KEY Dienstplan_ibfk_1;";
        }
        if (database_wrapper::database_table_constraint_exists('Dienstplan', 'Dienstplan_ibfk_2')) {
            $Sql_query_array[] = "ALTER TABLE `Dienstplan` DROP FOREIGN KEY Dienstplan_ibfk_2;";
        }
        if (database_wrapper::database_table_index_exists(database_wrapper::get_database_name(), "employees", "pseudo")) {
            $Sql_query_array[] = "ALTER TABLE `employees` DROP PRIMARY KEY, ADD PRIMARY KEY(`primary_key`);";
            $Sql_query_array[] = "ALTER TABLE `employees` DROP INDEX `pseudo`;";
        }
//DROP `working_hours` after moving the data to working_week_hours:
        $Sql_query_array[] = "UPDATE `employees` SET `employees`.`working_week_hours` = `employees`.`working_hours`;";
        $Sql_query_array[] = "ALTER TABLE `employees` DROP `working_hours`;";
        $Sql_query_array[] = "UPDATE `employees_backup` SET `employees_backup`.`working_week_hours` = `employees_backup`.`working_hours`;";
        $Sql_query_array[] = "ALTER TABLE `employees_backup` DROP `working_hours`;";
        /**
         * <p lang=de>Alte Mitarbeiter zurück in die employees table holen:</p>
         */
        $Sql_query_array[] = "DROP TRIGGER IF EXISTS `backup_employee_data`;";
        $Sql_query_array[] = "DELETE `employees_backup` FROM `employees`  LEFT JOIN `employees_backup` ON `employees`.`primary_key` = `employees_backup`.`backup_id` WHERE `employees`.`last_name` = `employees_backup`.`last_name` AND `employees`.`first_name` = `employees_backup`.`first_name`;";
        $Sql_query_array[] = "INSERT INTO employees (`id`, `last_name`, `first_name`, `profession`,
          `working_week_hours`, `holidays`, `lunch_break_minutes`, `goods_receipt`, `compounding`,
          `branch`, `start_of_employment`, `end_of_employment`, `timestamp`) (SELECT `id`, `last_name`, `first_name`, `profession`,
          `working_week_hours`, `holidays`, `lunch_break_minutes`, `goods_receipt`, `compounding`,
          `branch`, `start_of_employment`, `end_of_employment`, `timestamp`
          FROM `employees_backup`) ORDER BY `employees_backup`.`backup_id` DESC;";

        /**
         * Delete employees with same id and last name;
         * Delete employees with same id and first name;
         * Keep the row with the bigger timestamp:
         */
        $Sql_query_array[] = "UPDATE `employees` SET start_of_employment = NULL WHERE start_of_employment = '0000-00-00';";
        $Sql_query_array[] = "UPDATE `employees` SET end_of_employment = NULL WHERE end_of_employment = '0000-00-00';";

        $Sql_query_array[] = "DELETE t1 FROM `employees` t1 INNER JOIN `employees` t2 WHERE t1.primary_key < t2.primary_key AND t1.id = t2.id AND t1.last_name = t2.last_name AND t1.start_of_employment = t2.start_of_employment;";
        $Sql_query_array[] = "DELETE t1 FROM `employees` t1 INNER JOIN `employees` t2 WHERE t1.primary_key < t2.primary_key AND t1.id = t2.id AND t1.first_name = t2.first_name AND t1.start_of_employment = t2.start_of_employment;";
        $Sql_query_array[] = "DELETE t1 FROM `employees` t1 INNER JOIN `employees` t2 WHERE t1.primary_key < t2.primary_key AND t1.id = t2.id AND t1.last_name = t2.last_name AND t1.end_of_employment = t2.end_of_employment;";
        $Sql_query_array[] = "DELETE t1 FROM `employees` t1 INNER JOIN `employees` t2 WHERE t1.primary_key < t2.primary_key AND t1.id = t2.id AND t1.first_name = t2.first_name AND t1.end_of_employment = t2.end_of_employment;";

        if (!database_wrapper::database_table_column_exists(database_wrapper::get_database_name(), "users", "backup_id")) {
            /**
             * <p lang=de>Die Tabelle ist nicht auf dem aktuellen Stand (0.17.1)</p>
             */
            $Sql_query_array[] = "ALTER TABLE `users` ADD `employee_key` INT UNSIGNED NULL AFTER employee_id;";
            $Sql_query_array[] = "ALTER TABLE `users` ADD `primary_key` INT UNSIGNED NOT NULL FIRST;";
            $Sql_query_array[] = "UPDATE `users` SET `users`.`primary_key` = `users`.`employee_id`;";
            if (database_wrapper::database_table_constraint_exists("users", "users_ibfk_1")) {
                /**
                 * Alternativ vielleicht eine Funktion, die geziehlt nach referenzierten Columns sucht
                 *  oder einfach alle constraints von einer Tabelle löscht?
                 */
                $Sql_query_array[] = "ALTER TABLE `users` DROP FOREIGN KEY `users_ibfk_1`;";
            }
            if (database_wrapper::database_table_index_exists(database_wrapper::get_database_name(), 'users', 'PRIMARY')) {
                $Sql_query_array[] = "ALTER TABLE `users` DROP PRIMARY KEY, ADD PRIMARY KEY (`primary_key`);";
            } else {
                $Sql_query_array[] = "ALTER TABLE `users` ADD PRIMARY KEY (`primary_key`);";
            }
        }
        $Sql_query_array[] = "UPDATE `users` LEFT JOIN `employees` ON users.employee_id=employees.id"
                . " SET users.employee_key = employees.primary_key"
                . " WHERE users.employee_key IS NULL"
                . " AND employees.end_of_employment IS NULL;";
        $Sql_query_array[] = "UPDATE `users` LEFT JOIN `employees` ON users.employee_id=employees.id"
                . " SET users.employee_key = employees.primary_key"
                . " WHERE users.employee_key IS NULL"
                . " AND employees.end_of_employment > NOW();";

        /**
         * <p lang=de>Die neuen Keys in die Daten-Tabellen einfügen:</p>
         */
        /**
         * Dienstplan
         */
        if (!database_wrapper::database_table_column_exists(database_wrapper::get_database_name(), "Dienstplan", "employee_key")) {
            $Sql_query_array[] = "ALTER TABLE `Dienstplan` ADD `employee_key` INT UNSIGNED NULL AFTER VK;";
        }
        $Sql_query_array[] = "ALTER TABLE `Dienstplan` ADD CONSTRAINT `employee_key` FOREIGN KEY (`employee_key`) REFERENCES `employees`(`primary_key`) ON DELETE RESTRICT ON UPDATE RESTRICT;";
// zuerst die Mitarbeiter, die noch da sind (IS NULL employees.end_of_employment):
        $Sql_query_array[] = "UPDATE `Dienstplan` LEFT JOIN `employees` ON Dienstplan.VK=employees.id"
                . " SET Dienstplan.employee_key = employees.primary_key"
                . " WHERE Dienstplan.employee_key IS NULL AND Dienstplan.Datum >= employees.start_of_employment AND employees.end_of_employment IS NULL;";
// dann die Mitarbeiter, mit definierter bekannter Zeit von bis:
        $Sql_query_array[] = "UPDATE `Dienstplan` LEFT JOIN `employees` ON Dienstplan.VK=employees.id"
                . " SET Dienstplan.employee_key = employees.primary_key"
                . " WHERE Dienstplan.employee_key IS NULL AND Dienstplan.Datum >= employees.start_of_employment AND Dienstplan.Datum <= employees.end_of_employment;";
// dann die Mitarbeiter, die nur ei Ende, aber keinen Anfang kennen:
        $Sql_query_array[] = "UPDATE `Dienstplan` LEFT JOIN `employees` ON Dienstplan.VK=employees.id"
                . " SET Dienstplan.employee_key = employees.primary_key"
                . " WHERE Dienstplan.employee_key IS NULL AND employees.start_of_employment IS NULL AND Dienstplan.Datum <= employees.end_of_employment;";
// jetzt noch Mitarbeiter, bei denen Beginn und Ende NULL ist:
        $Sql_query_array[] = "UPDATE `Dienstplan` LEFT JOIN `employees` ON Dienstplan.VK=employees.id"
                . " SET Dienstplan.employee_key = employees.primary_key"
                . " WHERE Dienstplan.employee_key IS NULL AND employees.start_of_employment IS NULL AND employees.end_of_employment IS NULL;";
// Wenn nun noch Einträge übrig sind, werden sie gelöscht. Sie sind nicht zuzuordnen.
        $Sql_query_array[] = "DELETE FROM `Dienstplan` WHERE `employee_key` IS NULL;";
        $Sql_query_array[] = "ALTER TABLE `Dienstplan` DROP PRIMARY KEY, ADD PRIMARY KEY(`employee_key`,`Datum`,`Dienstbeginn`);";

        /**
         * Notdienst
         */
        $Sql_query_array[] = "ALTER TABLE `Notdienst` ADD `employee_key` INT UNSIGNED NULL AFTER VK;";
// zuerst die Mitarbeiter, die noch da sind (IS NULL employees.end_of_employment):
        $Sql_query_array[] = "UPDATE `Notdienst` LEFT JOIN `employees` ON Notdienst.VK=employees.id"
                . " SET Notdienst.employee_key = employees.primary_key"
                . " WHERE Notdienst.employee_key IS NULL AND Notdienst.Datum >= employees.start_of_employment AND employees.end_of_employment IS NULL;";
// dann die Mitarbeiter, mit definierter bekannter Zeit von bis:
        $Sql_query_array[] = "UPDATE `Notdienst` LEFT JOIN `employees` ON Notdienst.VK=employees.id"
                . " SET Notdienst.employee_key = employees.primary_key"
                . " WHERE Notdienst.employee_key IS NULL AND Notdienst.Datum >= employees.start_of_employment AND Notdienst.Datum <= employees.end_of_employment;";
// dann die Mitarbeiter, die nur ei Ende, aber keinen Anfang kennen:
        $Sql_query_array[] = "UPDATE `Notdienst` LEFT JOIN `employees` ON Notdienst.VK=employees.id"
                . " SET Notdienst.employee_key = employees.primary_key"
                . " WHERE Notdienst.employee_key IS NULL AND employees.start_of_employment IS NULL AND Notdienst.Datum <= employees.end_of_employment;";
// jetzt noch Mitarbeiter, bei denen Beginn und Ende NULL ist:
        $Sql_query_array[] = "UPDATE `Notdienst` LEFT JOIN `employees` ON Notdienst.VK=employees.id"
                . " SET Notdienst.employee_key = employees.primary_key"
                . " WHERE Notdienst.employee_key IS NULL AND employees.start_of_employment IS NULL AND employees.end_of_employment IS NULL;";
// Wenn nun noch Einträge übrig sind, werden sie gelöscht. Sie sind nicht zuzuordnen.
        $Sql_query_array[] = "DELETE FROM `Notdienst` WHERE `employee_key` IS NULL;";

        /**
         * Stunden
         */
        $Sql_query_array[] = "ALTER TABLE `Stunden` ADD `employee_key` INT UNSIGNED NULL AFTER VK;";
// zuerst die Mitarbeiter, die noch da sind (IS NULL employees.end_of_employment):
        $Sql_query_array[] = "UPDATE `Stunden` LEFT JOIN `employees` ON Stunden.VK=employees.id"
                . " SET Stunden.employee_key = employees.primary_key"
                . " WHERE Stunden.employee_key IS NULL AND Stunden.Datum >= employees.start_of_employment AND employees.end_of_employment IS NULL;";
// dann die Mitarbeiter, mit definierter bekannter Zeit von bis:
        $Sql_query_array[] = "UPDATE `Stunden` LEFT JOIN `employees` ON Stunden.VK=employees.id"
                . " SET Stunden.employee_key = employees.primary_key"
                . " WHERE Stunden.employee_key IS NULL AND Stunden.Datum >= employees.start_of_employment AND Stunden.Datum <= employees.end_of_employment;";
// dann die Mitarbeiter, die nur ei Ende, aber keinen Anfang kennen:
        $Sql_query_array[] = "UPDATE `Stunden` LEFT JOIN `employees` ON Stunden.VK=employees.id"
                . " SET Stunden.employee_key = employees.primary_key"
                . " WHERE Stunden.employee_key IS NULL AND employees.start_of_employment IS NULL AND Stunden.Datum <= employees.end_of_employment;";
// jetzt noch Mitarbeiter, bei denen Beginn und Ende NULL ist:
        $Sql_query_array[] = "UPDATE `Stunden` LEFT JOIN `employees` ON Stunden.VK=employees.id"
                . " SET Stunden.employee_key = employees.primary_key"
                . " WHERE Stunden.employee_key IS NULL AND employees.start_of_employment IS NULL AND employees.end_of_employment IS NULL;";
// Wenn nun noch Einträge übrig sind, werden sie gelöscht. Sie sind nicht zuzuordnen.
        $Sql_query_array[] = "DELETE FROM `Stunden` WHERE `employee_key` IS NULL;";
        $Sql_query_array[] = "ALTER TABLE `Stunden` DROP PRIMARY KEY, ADD PRIMARY KEY (`employee_key`,`Datum`);";

        /**
         * absence
         */
        $Sql_query_array[] = "ALTER TABLE `absence` ADD `employee_key` INT UNSIGNED NULL AFTER employee_id;";
// zuerst die Mitarbeiter, die noch da sind (IS NULL employees.end_of_employment):
        $Sql_query_array[] = "UPDATE `absence` LEFT JOIN `employees` ON absence.employee_id=employees.id"
                . " SET absence.employee_key = employees.primary_key"
                . " WHERE absence.employee_key IS NULL AND absence.start >= employees.start_of_employment AND employees.end_of_employment IS NULL;";
// dann die Mitarbeiter, mit definierter bekannter Zeit von bis:
        $Sql_query_array[] = "UPDATE `absence` LEFT JOIN `employees` ON absence.employee_id=employees.id"
                . " SET absence.employee_key = employees.primary_key"
                . " WHERE absence.employee_key IS NULL AND absence.start >= employees.start_of_employment AND absence.end <= employees.end_of_employment;";
// dann die Mitarbeiter, die nur ei Ende, aber keinen Anfang kennen:
        $Sql_query_array[] = "UPDATE `absence` LEFT JOIN `employees` ON absence.employee_id=employees.id"
                . " SET absence.employee_key = employees.primary_key"
                . " WHERE absence.employee_key IS NULL AND employees.start_of_employment IS NULL AND absence.end <= employees.end_of_employment;";
// jetzt noch Mitarbeiter, bei denen Beginn und Ende NULL ist:
        $Sql_query_array[] = "UPDATE `absence` LEFT JOIN `employees` ON absence.employee_id=employees.id"
                . " SET absence.employee_key = employees.primary_key"
                . " WHERE absence.employee_key IS NULL AND employees.start_of_employment IS NULL AND employees.end_of_employment IS NULL;";
// Wenn nun noch Einträge übrig sind, werden sie gelöscht. Sie sind nicht zuzuordnen.
        $Sql_query_array[] = "DELETE FROM `absence` WHERE `employee_key` IS NULL;";
        $Sql_query_array[] = "ALTER TABLE `absence` DROP PRIMARY KEY, ADD PRIMARY KEY (`employee_key`,`start`);";

        /**
         * principle_roster
         */
        $Sql_query_array[] = "ALTER TABLE `principle_roster` ADD `employee_key` INT UNSIGNED NULL AFTER `primary_key`;";
// zuerst die Mitarbeiter, die noch da sind (IS NULL employees.end_of_employment):
        $Sql_query_array[] = "UPDATE `principle_roster` LEFT JOIN `employees` ON principle_roster.employee_id=employees.id"
                . " SET principle_roster.employee_key = employees.primary_key"
                . " WHERE principle_roster.employee_key IS NULL AND employees.end_of_employment IS NULL;";
// dann die Mitarbeiter, mit definierter bekannter Zeit von bis:
        $Sql_query_array[] = "UPDATE `principle_roster` LEFT JOIN `employees` ON principle_roster.employee_id=employees.id"
                . " SET principle_roster.employee_key = employees.primary_key"
                . " WHERE principle_roster.employee_key IS NULL AND NOW() <= employees.end_of_employment;";
        $Sql_query_array[] = "DELETE FROM `principle_roster` WHERE `employee_key` IS NULL;";

        /**
         * principle_roster_archive
         */
        $Sql_query_array[] = "ALTER TABLE `principle_roster_archive` ADD `employee_key` INT UNSIGNED NULL AFTER `primary_key`;";
// zuerst die Mitarbeiter, die noch da sind (IS NULL employees.end_of_employment):
        $Sql_query_array[] = "UPDATE `principle_roster_archive` LEFT JOIN `employees` ON principle_roster_archive.employee_id=employees.id"
                . " SET principle_roster_archive.employee_key = employees.primary_key"
                . " WHERE principle_roster_archive.employee_key IS NULL AND principle_roster_archive.was_valid_until >= employees.start_of_employment AND employees.end_of_employment IS NULL;";
// dann die Mitarbeiter, mit definierter bekannter Zeit von bis:
        $Sql_query_array[] = "UPDATE `principle_roster_archive` LEFT JOIN `employees` ON principle_roster_archive.employee_id=employees.id"
                . " SET principle_roster_archive.employee_key = employees.primary_key"
                . " WHERE principle_roster_archive.employee_key IS NULL AND principle_roster_archive.was_valid_until >= employees.start_of_employment AND principle_roster_archive.was_valid_until <= employees.end_of_employment;";
// dann die Mitarbeiter, die nur ei Ende, aber keinen Anfang kennen:
        $Sql_query_array[] = "UPDATE `principle_roster_archive` LEFT JOIN `employees` ON principle_roster_archive.employee_id=employees.id"
                . " SET principle_roster_archive.employee_key = employees.primary_key"
                . " WHERE principle_roster_archive.employee_key IS NULL AND employees.start_of_employment IS NULL AND principle_roster_archive.was_valid_until <= employees.end_of_employment;";
// jetzt noch Mitarbeiter, bei denen Beginn und Ende NULL ist:
        $Sql_query_array[] = "UPDATE `principle_roster_archive` LEFT JOIN `employees` ON principle_roster_archive.employee_id=employees.id"
                . " SET principle_roster_archive.employee_key = employees.primary_key"
                . " WHERE principle_roster_archive.employee_key IS NULL AND employees.start_of_employment IS NULL AND employees.end_of_employment IS NULL;";
// Wenn nun noch Einträge übrig sind, werden sie gelöscht. Sie sind nicht zuzuordnen.
        $Sql_query_array[] = "DELETE FROM `principle_roster_archive` WHERE `employee_key` IS NULL;";

//saturday_rotation_teams
        $Sql_query_array[] = "ALTER TABLE `saturday_rotation_teams` ADD `employee_key` INT UNSIGNED NULL AFTER employee_id;";
// zuerst die Mitarbeiter, die noch da sind (IS NULL employees.end_of_employment):
        $Sql_query_array[] = "UPDATE `saturday_rotation_teams` LEFT JOIN `employees` ON saturday_rotation_teams.employee_id=employees.id"
                . " SET saturday_rotation_teams.employee_key = employees.primary_key"
                . " WHERE saturday_rotation_teams.employee_key IS NULL AND employees.end_of_employment IS NULL;";
// dann die Mitarbeiter, mit definierter bekannter Zeit von bis:
        $Sql_query_array[] = "UPDATE `saturday_rotation_teams` LEFT JOIN `employees` ON saturday_rotation_teams.employee_id=employees.id"
                . " SET saturday_rotation_teams.employee_key = employees.primary_key"
                . " WHERE saturday_rotation_teams.employee_key IS NULL AND NOW() <= employees.end_of_employment;";
// Wenn nun noch Einträge übrig sind, werden sie gelöscht. Sie sind nicht zuzuordnen.
        $Sql_query_array[] = "DELETE FROM `saturday_rotation_teams` WHERE `employee_key` IS NULL;";
        $Sql_query_array[] = "ALTER TABLE `saturday_rotation_teams` DROP PRIMARY KEY, ADD PRIMARY KEY (`team_id`,`employee_key`,`branch_id`);";

//task_rotation
        $Sql_query_array[] = "ALTER TABLE `task_rotation` ADD `employee_key` INT UNSIGNED NULL AFTER VK;";
// zuerst die Mitarbeiter, die noch da sind (IS NULL employees.end_of_employment):
        $Sql_query_array[] = "UPDATE `task_rotation` LEFT JOIN `employees` ON task_rotation.VK=employees.id"
                . " SET task_rotation.employee_key = employees.primary_key"
                . " WHERE task_rotation.employee_key IS NULL AND employees.end_of_employment IS NULL;";
// dann die Mitarbeiter, mit definierter bekannter Zeit von bis:
        $Sql_query_array[] = "UPDATE `task_rotation` LEFT JOIN `employees` ON task_rotation.VK=employees.id"
                . " SET task_rotation.employee_key = employees.primary_key"
                . " WHERE task_rotation.employee_key IS NULL AND NOW() <= employees.end_of_employment;";
// Wenn nun noch Einträge übrig sind, werden sie gelöscht. Sie sind nicht zuzuordnen.
        $Sql_query_array[] = "DELETE FROM `task_rotation` WHERE `employee_key` IS NULL;";

//user_email_notification_cache
        $Sql_query_array[] = "ALTER TABLE `user_email_notification_cache` ADD `user_key` INT UNSIGNED NULL AFTER employee_id;";
        $Sql_query_array[] = "UPDATE `user_email_notification_cache` LEFT JOIN `users` ON user_email_notification_cache.employee_id=users.employee_id"
                . " SET user_email_notification_cache.user_key = users.employee_key"
                . " WHERE user_email_notification_cache.user_key IS NULL;";
        $Sql_query_array[] = "DELETE FROM `user_email_notification_cache` WHERE `user_key` IS NULL;";

//users_lost_password_token
        $Sql_query_array[] = "ALTER TABLE `users_lost_password_token` ADD `user_key` INT UNSIGNED NULL AFTER employee_id;";
        $Sql_query_array[] = "UPDATE `users_lost_password_token` LEFT JOIN `users` ON users_lost_password_token.employee_id=users.employee_id"
                . " SET users_lost_password_token.user_key = users.primary_key"
                . " WHERE users_lost_password_token.user_key IS NULL;";
        $Sql_query_array[] = "DELETE FROM `users_lost_password_token` WHERE `user_key` IS NULL;";

//users_privileges
        $Sql_query_array[] = "ALTER TABLE `users_privileges` ADD `user_key` INT UNSIGNED NULL AFTER employee_id;";
        $Sql_query_array[] = "UPDATE `users_privileges` LEFT JOIN `users` ON users_privileges.employee_id=users.employee_id"
                . " SET users_privileges.user_key = users.primary_key"
                . " WHERE users_privileges.user_key IS NULL;";
// Wenn nun noch Einträge übrig sind, werden sie gelöscht. Sie sind nicht zuzuordnen.
        $Sql_query_array[] = "DELETE FROM `users_privileges` WHERE `user_key` IS NULL;";
        $Sql_query_array[] = "ALTER TABLE `users_privileges` DROP PRIMARY KEY, ADD PRIMARY KEY (`user_key`,`privilege`);";

        $Sql_query_array[] = "ALTER TABLE `branch` MODIFY COLUMN `short_name` varchar(32) NOT NULL;";
        /**
         * DROP some columns:
         */
        $Sql_query_array[] = "ALTER TABLE `absence` DROP `employee_id`;"; // CAVE! Darf erst gedropt werden, wenn alle anderen Daten aller Tabellen auf employee_key übertragen wurden.
        $Sql_query_array[] = "ALTER TABLE `Dienstplan` DROP `VK`;";
        $Sql_query_array[] = "ALTER TABLE `Notdienst` DROP `VK`;";
        $Sql_query_array[] = "ALTER TABLE `Stunden` DROP `VK`;";
        $Sql_query_array[] = "ALTER TABLE `employees` DROP `id`;";
        $Sql_query_array[] = "ALTER TABLE `principle_roster` DROP `employee_id`;";
        $Sql_query_array[] = "ALTER TABLE `principle_roster_archive` DROP `employee_id`;";
        $Sql_query_array[] = "ALTER TABLE `saturday_rotation_teams` DROP `employee_id`;";
        $Sql_query_array[] = "ALTER TABLE `task_rotation` DROP `VK`;";
        $Sql_query_array[] = "ALTER TABLE `users` DROP `employee_id`;"; // CAVE! Darf erst gedropt werden, wenn auch die privileges übertragen wurden.
        $Sql_query_array[] = "ALTER TABLE `user_email_notification_cache` DROP `employee_id`;";
        $Sql_query_array[] = "ALTER TABLE `users_lost_password_token` DROP `employee_id`;";
        $Sql_query_array[] = "ALTER TABLE `users_privileges` DROP `employee_id`;";
        /**
         * Add all the new and old CONSTRAINTs:
         */
        $Sql_query_array[] = "ALTER TABLE `absence` ADD FOREIGN KEY (`employee_key`) REFERENCES `employees`(`primary_key`) ON DELETE RESTRICT ON UPDATE RESTRICT;";

        $Sql_query_array[] = "ALTER TABLE `approval` CHANGE `branch` `branch` TINYINT UNSIGNED NOT NULL; "; // Change branch from int to tinyint unsigned to match it with branch_id in branch table
        $Sql_query_array[] = "DELETE FROM `approval` WHERE `approval`.`branch` = 0;";
        $Sql_query_array[] = "ALTER TABLE `approval` ADD FOREIGN KEY (`branch`) REFERENCES `branch`(`branch_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";

        $Sql_query_array[] = "ALTER TABLE `employees` CHANGE `branch` `branch` TINYINT UNSIGNED NULL DEFAULT '1';";
        $Sql_query_array[] = "UPDATE `employees` SET `branch` = NULL WHERE `employees`.`branch` = 0;";
        $Sql_query_array[] = "ALTER TABLE `employees` ADD FOREIGN KEY (`branch`) REFERENCES `branch`(`branch_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";

        $Sql_query_array[] = "ALTER TABLE `Notdienst` CHANGE `Mandant` `Mandant` TINYINT UNSIGNED NOT NULL DEFAULT '1';";
        $Sql_query_array[] = "ALTER TABLE `Notdienst` ADD FOREIGN KEY (`employee_key`) REFERENCES `employees`(`primary_key`) ON DELETE RESTRICT ON UPDATE RESTRICT;";
        $Sql_query_array[] = "ALTER TABLE `Notdienst` ADD FOREIGN KEY (`Mandant`) REFERENCES `branch`(`branch_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";

        $Sql_query_array[] = "ALTER TABLE `opening_times` CHANGE `branch_id` `branch_id` TINYINT UNSIGNED NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE `opening_times` ADD FOREIGN KEY (`branch_id`) REFERENCES `branch`(`branch_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";

        $Sql_query_array[] = "ALTER TABLE `principle_roster` CHANGE `branch_id` `branch_id` TINYINT UNSIGNED NOT NULL DEFAULT '1';";
        $Sql_query_array[] = "ALTER TABLE `principle_roster` ADD FOREIGN KEY (`employee_key`) REFERENCES `employees`(`primary_key`) ON DELETE RESTRICT ON UPDATE RESTRICT;";
        $Sql_query_array[] = "ALTER TABLE `principle_roster` ADD FOREIGN KEY (`branch_id`) REFERENCES `branch`(`branch_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";

        $Sql_query_array[] = "ALTER TABLE `saturday_rotation` CHANGE `branch_id` `branch_id` TINYINT UNSIGNED NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE `saturday_rotation` ADD FOREIGN KEY (`branch_id`) REFERENCES `branch`(`branch_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";

        $Sql_query_array[] = "ALTER TABLE `saturday_rotation_teams` CHANGE `branch_id` `branch_id` TINYINT UNSIGNED NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE `saturday_rotation_teams` ADD FOREIGN KEY (`branch_id`) REFERENCES `branch`(`branch_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";
        $Sql_query_array[] = "ALTER TABLE `saturday_rotation_teams` ADD FOREIGN KEY (`employee_key`) REFERENCES `employees`(`primary_key`) ON DELETE RESTRICT ON UPDATE RESTRICT;";

        $Sql_query_array[] = "ALTER TABLE `Stunden` ADD FOREIGN KEY (`employee_key`) REFERENCES `employees`(`primary_key`) ON DELETE RESTRICT ON UPDATE RESTRICT;";

        $Sql_query_array[] = "DELETE FROM `task_rotation` WHERE `task_rotation`.`branch_id` = 0";
        $Sql_query_array[] = "ALTER TABLE `task_rotation` CHANGE `branch_id` `branch_id` TINYINT UNSIGNED NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE `task_rotation` ADD FOREIGN KEY (`branch_id`) REFERENCES `branch`(`branch_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";
        $Sql_query_array[] = "ALTER TABLE `task_rotation` ADD FOREIGN KEY (`employee_key`) REFERENCES `employees`(`primary_key`) ON DELETE RESTRICT ON UPDATE RESTRICT;";

        $Sql_query_array[] = "ALTER TABLE `users` ADD FOREIGN KEY (`employee_key`) REFERENCES `employees`(`primary_key`) ON DELETE RESTRICT ON UPDATE RESTRICT;";

        $Sql_query_array[] = "ALTER TABLE `users_lost_password_token` ADD FOREIGN KEY (`user_key`) REFERENCES `users`(`primary_key`) ON DELETE RESTRICT ON UPDATE RESTRICT;";

        $Sql_query_array[] = "ALTER TABLE `users_privileges` ADD FOREIGN KEY (`user_key`) REFERENCES `users`(`primary_key`) ON DELETE RESTRICT ON UPDATE RESTRICT;";

        $Sql_query_array[] = "DROP TABLE IF EXISTS `Feiertage`;";
        $Sql_query_array[] = "DROP TABLE IF EXISTS `Schulferien`;";
        $Sql_query_array[] = "DROP TABLE IF EXISTS `opening_times_special`;";
        $Sql_query_array[] = "DROP TABLE IF EXISTS `employees_backup`";

        foreach ($Sql_query_array as $sql_query) {
            error_log($sql_query . PHP_EOL, 3, PDR_FILE_SYSTEM_APPLICATION_PATH . 'maintenance.log');
            $result = database_wrapper::instance()->run($sql_query);
            error_log("result->errorInfo(): " . implode(":", $result->errorInfo()) . PHP_EOL, 3, PDR_FILE_SYSTEM_APPLICATION_PATH . 'maintenance.log');
            if ('00000' !== $result->errorCode()) {
                return FALSE;
            }
        }
        if (true === database_wrapper::instance()->inTransaction()) {
            database_wrapper::instance()->commit();
        }
    }

    private function refactorEmergencyService(): void {
        if (database_wrapper::database_table_exists("emergency_services")) {
            return;
        }
        $alterQuery = "ALTER TABLE `Notdienst` DROP PRIMARY KEY, ADD `primary_key` INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`primary_key`);";
        $alterQuery2 = "ALTER TABLE `Notdienst` CHANGE `Datum` `date` DATE NOT NULL, CHANGE `Mandant` `branch_id` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1';";
        $renameQuery = "RENAME TABLE `Notdienst` TO `emergency_services`;";

        database_wrapper::instance()->beginTransaction();
        $alterResult = database_wrapper::instance()->run($alterQuery);
        if ('00000' !== $alterResult->errorCode()) {
            database_wrapper::instance()->rollBack();
            return;
        }
        $alterResult2 = database_wrapper::instance()->run($alterQuery2);
        if ('00000' !== $alterResult2->errorCode()) {
            database_wrapper::instance()->rollBack();
            return;
        }
        $renameResult = database_wrapper::instance()->run($renameQuery);
        if ('00000' !== $renameResult->errorCode()) {
            database_wrapper::instance()->rollBack();
            return;
        }
        if (true === database_wrapper::instance()->inTransaction()) {
            database_wrapper::instance()->commit();
        }
    }

    /**
     * @todo Implement this Constraint in the installation data.
     * @todo Refactor the absence table to use a primary_key (update the case of overlap, where the start date matches)
     * @return void
     */
    private function refactorAbsence(): void {
        "ALTER TABLE `absence` ADD CONSTRAINT `start_before_end` CHECK (`start` < `end`);";
        "ALTER TABLE `absence` MODIFY COLUMN `approval` ENUM('approved','not_yet_approved','disapproved','changed_after_approval') NOT NULL DEFAULT 'not_yet_approved';";
    }
}
