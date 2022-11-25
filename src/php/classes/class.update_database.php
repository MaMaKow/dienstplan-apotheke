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
            $sql_query = "ALTER TABLE `opening_times_special` "
                    . "CHANGE `Datum` `date` DATE NOT NULL, "
                    . "CHANGE `Beginn` `start` TIME NOT NULL, "
                    . "CHANGE `Ende` `end` TIME NOT NULL, "
                    . "CHANGE `Bezeichnung` `event_name` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;";
            database_wrapper::instance()->run($sql_query);
        }
    }

    private function refactor_absence_table() {
        global $config;
        if (!database_wrapper::database_table_column_exists($config['database_name'], 'absence', 'reason_id')) {
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
        if (!database_wrapper::database_table_column_exists($config['database_name'], 'absence', 'comment')) {
            $sql_query = "ALTER TABLE `absence` ADD `comment` VARCHAR(64) NULL DEFAULT NULL AFTER `days`;";
            database_wrapper::instance()->run($sql_query);
        }
    }

    private function refactor_duty_roster_table() {
        if (database_wrapper::database_table_exists('Dienstplan') and!database_wrapper::database_table_exists('roster')) {
            $sql_query_list = array();
            $sql_query_list[] = "ALTER TABLE `Dienstplan` CHANGE `VK` `employee_id` TINYINT UNSIGNED NOT NULL ";
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
        global $config;
        if (!database_wrapper::database_table_exists('principle_roster_archive')) {
            $sql_query = file_get_contents(PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/sql/principle_roster_archive.sql');
            database_wrapper::instance()->run($sql_query);
        }

        if (!database_wrapper::database_table_column_exists($config['database_name'], "principle_roster", "valid_until")) {
            /**
             * <p lang=de>Die Tabelle ist bereits auf dem aktuellen Stand (1.0.0)</p>
             */
            return;
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
        $Sql_query_array[] = "ALTER DATABASE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . " CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`absence` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`approval` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`branch` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`Dienstplan` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        /**
         * <p lang=de>Um den Charset für ein mysql SET zu ändern musste zunächst
         *  einmal das ganze SET gekürzt werden. Vieleicht gibt es einen eleganteren Weg?</p>
         */
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE `Apotheke_testing`.`employees` CHANGE `profession` `profession` SET('Apotheker','PI','PTA','PKA','Praktikant','Ernährungsberater','Kosmetiker','Zugehfrau','Apo','Pra','Ern','Kos','Zug') CHARACTER SET latin1 COLLATE latin1_german1_ci NOT NULL;";
        $Sql_query_array[] = "UPDATE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees` SET `profession` = 'Apo' WHERE `employees`.`profession` = 'Apotheker';";
        $Sql_query_array[] = "UPDATE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees` SET `profession` = 'Pra' WHERE `employees`.`profession` = 'Praktikant';";
        $Sql_query_array[] = "UPDATE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees` SET `profession` = 'Ern' WHERE `employees`.`profession` = 'Ernährungsberater';";
        $Sql_query_array[] = "UPDATE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees` SET `profession` = 'Kos' WHERE `employees`.`profession` = 'Kosmetiker';";
        $Sql_query_array[] = "UPDATE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees` SET `profession` = 'Zug' WHERE `employees`.`profession` = 'Zugehfrau';";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees` CHANGE `profession` `profession` SET('Apotheker','PI','PTA','PKA','Praktikant','Ernährungsberater','Kosmetiker','Zugehfrau','Apo','Pra','Ern','Kos','Zug') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "UPDATE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees` SET `profession` = 'Apotheker' WHERE `employees`.`profession` = 'Apo';";
        $Sql_query_array[] = "UPDATE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees` SET `profession` = 'Praktikant' WHERE `employees`.`profession` = 'Pra';";
        $Sql_query_array[] = "UPDATE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees` SET `profession` = 'Ernährungsberater' WHERE `employees`.`profession` = 'Ern';";
        $Sql_query_array[] = "UPDATE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees` SET `profession` = 'Kosmetiker' WHERE `employees`.`profession` = 'Kos';";
        $Sql_query_array[] = "UPDATE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees` SET `profession` = 'Zugehfrau' WHERE `employees`.`profession` = 'Zug';";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees` CHANGE `profession` `profession` SET('Apotheker','PI','PTA','PKA','Praktikant','Kosmetiker','Zugehfrau') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees_backup` CHANGE `profession` `profession` SET('Apotheker','PI','PTA','PKA','Praktikant','Ernährungsberater','Kosmetiker','Zugehfrau','Apo','Pra','Ern','Kos','Zug') CHARACTER SET latin1 COLLATE latin1_german1_ci NOT NULL;";
        $Sql_query_array[] = "UPDATE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees_backup` SET `profession` = 'Apo' WHERE `employees_backup`.`profession` = 'Apotheker';";
        $Sql_query_array[] = "UPDATE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees_backup` SET `profession` = 'Pra' WHERE `employees_backup`.`profession` = 'Praktikant';";
        $Sql_query_array[] = "UPDATE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees_backup` SET `profession` = 'Ern' WHERE `employees_backup`.`profession` = 'Ernährungsberater';";
        $Sql_query_array[] = "UPDATE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees_backup` SET `profession` = 'Kos' WHERE `employees_backup`.`profession` = 'Kosmetiker';";
        $Sql_query_array[] = "UPDATE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees_backup` SET `profession` = 'Zug' WHERE `employees_backup`.`profession` = 'Zugehfrau';";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees_backup` CHANGE `profession` `profession` SET('Apotheker','PI','PTA','PKA','Praktikant','Ernährungsberater','Kosmetiker','Zugehfrau','Apo','Pra','Ern','Kos','Zug') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "UPDATE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees_backup` SET `profession` = 'Apotheker' WHERE `employees_backup`.`profession` = 'Apo';";
        $Sql_query_array[] = "UPDATE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees_backup` SET `profession` = 'Praktikant' WHERE `employees_backup`.`profession` = 'Pra';";
        $Sql_query_array[] = "UPDATE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees_backup` SET `profession` = 'Ernährungsberater' WHERE `employees_backup`.`profession` = 'Ern';";
        $Sql_query_array[] = "UPDATE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees_backup` SET `profession` = 'Kosmetiker' WHERE `employees_backup`.`profession` = 'Kos';";
        $Sql_query_array[] = "UPDATE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees_backup` SET `profession` = 'Zugehfrau' WHERE `employees_backup`.`profession` = 'Zug';";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees_backup` CHANGE `profession` `profession` SET('Apotheker','PI','PTA','PKA','Praktikant','Ernährungsberater','Kosmetiker','Zugehfrau') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        /**
         * Fertig mit den beiden SETS für Berufe der Mitarbeiter
         */
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees_backup` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`Feiertage` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`maintenance` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`Notdienst` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`opening_times` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`opening_times_special` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`pdr_self` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`pep` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`pep_month_day` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`pep_weekday_time` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`pep_year_month` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`principle_roster` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`saturday_rotation` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`saturday_rotation_teams` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`Schulferien` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`Stunden` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`task_rotation` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`users` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`users_lost_password_token` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`users_privileges` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`user_email_notification_cache` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`users` CHANGE `user_name` `user_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`users` CHANGE `email` `email` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`users` CHANGE `password` `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`Dienstplan` CHANGE `user` `user` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`Feiertage` CHANGE `Name` `Name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`Schulferien` CHANGE `Name` `Name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`Stunden` CHANGE `Grund` `Grund` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`absence` CHANGE `comment` `comment` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`absence` CHANGE `user` `user` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`approval` CHANGE `user` `user` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`branch` CHANGE `name` `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`branch` CHANGE `short_name` `short_name` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`branch` CHANGE `address` `address` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`branch` CHANGE `manager` `manager` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees` CHANGE `last_name` `last_name` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees` CHANGE `first_name` `first_name` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees_backup` CHANGE `last_name` `last_name` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`employees_backup` CHANGE `first_name` `first_name` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`pdr_self` CHANGE `pdr_version_string` `pdr_version_string` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`pdr_self` CHANGE `pdr_database_version_hash` `pdr_database_version_hash` char(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`task_rotation` CHANGE `task` `task` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`users_privileges` CHANGE `privilege` `privilege` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`opening_times_special` CHANGE `event_name` `event_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`Dienstplan` CHANGE `Kommentar` `Kommentar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`user_email_notification_cache` CHANGE `notification_text` `notification_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
        $Sql_query_array[] = "ALTER TABLE " . database_wrapper::quote_identifier(database_wrapper::get_database_name()) . ".`principle_roster` CHANGE `comment` `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;";
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

}
