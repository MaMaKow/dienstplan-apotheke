<?php

/*
 * Copyright (C) 2026 Martin Mandelkow
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

namespace PDR\Database\Migration;

use database_wrapper;

/**
 * @author martin
 */
class Migration002 implements MigrationInterface {
    #[\Override]
    public function getDescription(): string {
        return "Refactor absence table";
    }

    #[\Override]
    public function getVersion(): int {
        return 2;
    }

    #[\Override]
    public function migrate(): void {
        $this->refactorAbsenceTable();
    }

    private function refactorAbsenceTable(): void {
        if (!database_wrapper::database_table_column_exists(database_wrapper::get_database_name(), 'absence', 'comment')) {
            $sql_query = "ALTER TABLE `absence` ADD `comment` VARCHAR(64) NULL DEFAULT NULL AFTER `days`;";
            database_wrapper::instance()->run($sql_query);
        }

        if (!database_wrapper::database_table_column_exists(database_wrapper::get_database_name(), 'absence', 'reason_id')) {
            /**
             * Change the database and all the tables and columns to utf8mb4:
             */
            $this->change_charset_to_utf8mb4();
            /**
             * Change the actual absence tables:
             */
            database_wrapper::instance()->run("CREATE TABLE IF NOT EXISTS `absence_reasons` (
              `id` tinyint(3) UNSIGNED NOT NULL,
              `reason_string` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
            database_wrapper::instance()->run("INSERT INTO `absence_reasons` (`id`, `reason_string`) VALUES
                (1, 'vacation'),
                (2, 'remaining vacation'),
                (3, 'sickness'),
                (4, 'sickness of child'),
                (5, 'taken overtime'),
                (6, 'paid leave of absence'),
                (7, 'maternity leave'),
                (8, 'parental leave')
                ON DUPLICATE KEY UPDATE `reason_string` = VALUES(`reason_string`)");
            if (!database_wrapper::database_table_column_exists(database_wrapper::get_database_name(), 'absence', 'reason_id')) {
                database_wrapper::instance()->run("ALTER TABLE `absence` ADD `reason_id` TINYINT UNSIGNED NOT NULL AFTER `employee_id`");
            }

            $reasonUpdates = [
                1 => 'vacation',
                2 => 'remaining holiday',
                3 => 'sickness',
                4 => 'sickness of child',
                5 => 'unpaid leave of absence',
                6 => 'paid leave of absence',
                7 => 'maternity leave',
                8 => 'parental leave',
            ];
            foreach ($reasonUpdates as $reasonId => $reason) {
                $result = database_wrapper::instance()->run(
                    'UPDATE `absence` SET `reason_id` = :reason_id WHERE `reason` = :reason',
                    ['reason_id' => $reasonId, 'reason' => $reason]
                );
                if ('00000' !== $result->errorCode()) {
                    throw new DatabaseMigrationException('Could not refactor absence table.');
                }
            }

            if (!$this->absenceReasonForeignKeyExists()) {
                database_wrapper::instance()->run(
                    'ALTER TABLE `absence` ADD FOREIGN KEY (`reason_id`) '
                    . 'REFERENCES `absence_reasons`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE'
                );
            }
            if (database_wrapper::database_table_column_exists(database_wrapper::get_database_name(), 'absence', 'reason')) {
                database_wrapper::instance()->run('ALTER TABLE `absence` DROP `reason`');
            }
        }
    }

    private function absenceReasonForeignKeyExists(): bool {
        $result = database_wrapper::instance()->run(
            'SELECT 1 FROM `information_schema`.`KEY_COLUMN_USAGE` '
            . 'WHERE `TABLE_SCHEMA` = :database_name AND `TABLE_NAME` = \'absence\' '
            . 'AND `COLUMN_NAME` = \'reason_id\' AND `REFERENCED_TABLE_NAME` = \'absence_reasons\' LIMIT 1',
            ['database_name' => database_wrapper::get_database_name()]
        );

        return false !== $result->fetchColumn();
    }

    private function change_charset_to_utf8mb4(): void {
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
        foreach ($Sql_query_array as $sql_query) {
            $result = database_wrapper::instance()->run($sql_query);
            if ('00000' !== $result->errorCode()) {
                throw new DatabaseMigrationException('Could not change charset to uft8mb4.');
            }
        }
    }
}
