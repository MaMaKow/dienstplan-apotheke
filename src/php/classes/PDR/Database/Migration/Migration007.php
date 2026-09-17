<?php

namespace PDR\Database\Migration;

use database_wrapper;

final class Migration007 implements MigrationInterface {

    #[\Override]
    public function getVersion(): int {
        return 7;
    }

    #[\Override]
    public function migrate(): void {
        $this->employee_refactor_primary_key();
    }

    #[\Override]
    public function getDescription(): string {
        return "Employee refactor primary key";
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
                throw new DatabaseMigrationException('Could not refactor employee primary key.');
            }
        }
        if (true === database_wrapper::instance()->inTransaction()) {
            database_wrapper::instance()->commit();
        }
    }
}
