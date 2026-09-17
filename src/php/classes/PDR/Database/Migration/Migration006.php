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
            $sql_query = file_get_contents(PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/sql/principle_roster_archive.sql');
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

        database_wrapper::instance()->beginTransaction();
        $result = database_wrapper::instance()->run($sql_query_insert);
        if ('00000' !== $result->errorCode()) {
            database_wrapper::instance()->rollBack();
            throw new DatabaseMigrationException('Could not refactor principle roster.');
        }
        $result = database_wrapper::instance()->run($sql_query_delete);
        if ('00000' !== $result->errorCode()) {
            database_wrapper::instance()->rollBack();
            throw new DatabaseMigrationException('Could not refactor principle roster.');
        }
        $result = database_wrapper::instance()->run($sql_query_from);
        if ('00000' !== $result->errorCode()) {
            database_wrapper::instance()->rollBack();
            throw new DatabaseMigrationException('Could not refactor principle roster.');
        }
        $result = database_wrapper::instance()->run($sql_query_until);
        if ('00000' !== $result->errorCode()) {
            database_wrapper::instance()->rollBack();
            throw new DatabaseMigrationException('Could not refactor principle roster.');
        }
        database_wrapper::instance()->commit();
        return TRUE;
    }
}
