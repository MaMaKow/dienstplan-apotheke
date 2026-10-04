<?php

namespace PDR\Database\Migration;

use database_wrapper;

final class Migration006 implements MigrationInterface {

    #[\Override]
    public function getVersion(): int {
        return 6;
    }

    #[\Override]
    public function migrate(): void {
        $this->refactor_principle_roster2();
    }

    #[\Override]
    public function getDescription(): string {
        return "Refactor principle roster again";
    }

    private function refactor_principle_roster2() {
        if (!database_wrapper::database_table_exists('principle_roster_archive')) {
            // Migration007 converts employee_id to employee_key. Create the
            // archive at the schema version immediately before that change.
            $sql_query = "CREATE TABLE `principle_roster_archive` (
                `primary_key` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `alternating_week_id` tinyint(4) NOT NULL,
                `employee_id` tinyint(4) NOT NULL,
                `weekday` tinyint(4) NOT NULL,
                `duty_start` time NOT NULL,
                `duty_end` time NOT NULL,
                `break_start` time DEFAULT NULL,
                `break_end` time DEFAULT NULL,
                `comment` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `working_hours` float DEFAULT NULL,
                `branch_id` int(11) NOT NULL DEFAULT 1,
                `was_valid_until` date NOT NULL,
                PRIMARY KEY (`primary_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            database_wrapper::instance()->run($sql_query);
        }

        if (!database_wrapper::database_table_column_exists(database_wrapper::get_database_name(), "principle_roster", "valid_until")) {
            /**
             * <p lang=de>Die Tabelle ist bereits auf dem aktuellen Stand (1.0.0)</p>
             */
            return;
        }
        $sql_query_insert = "INSERT INTO `principle_roster_archive` (SELECT `primary_key`, `alternating_week_id`, `employee_id`, `weekday`, `duty_start`, `duty_end`, `break_start`, `break_end`, `comment`, `working_hours`, `branch_id`, `valid_until` FROM `principle_roster` WHERE `valid_until` IS NOT NULL)";
        $sql_query_delete = "DELETE FROM `principle_roster`  WHERE `valid_until` IS NOT NULL";
        $sql_query_from = "ALTER TABLE `principle_roster` DROP `valid_from`;";
        $sql_query_until = "ALTER TABLE `principle_roster` DROP `valid_until`;";

        $result = database_wrapper::instance()->run($sql_query_insert);
        if ('00000' !== $result->errorCode()) {
            throw new DatabaseMigrationException('Could not refactor principle roster.');
        }
        $result = database_wrapper::instance()->run($sql_query_delete);
        if ('00000' !== $result->errorCode()) {
            throw new DatabaseMigrationException('Could not refactor principle roster.');
        }
        $result = database_wrapper::instance()->run($sql_query_from);
        if ('00000' !== $result->errorCode()) {
            throw new DatabaseMigrationException('Could not refactor principle roster.');
        }
        $result = database_wrapper::instance()->run($sql_query_until);
        if ('00000' !== $result->errorCode()) {
            throw new DatabaseMigrationException('Could not refactor principle roster.');
        }
        return TRUE;
    }
}
