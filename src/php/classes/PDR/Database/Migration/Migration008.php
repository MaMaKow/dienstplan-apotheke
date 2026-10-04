<?php

namespace PDR\Database\Migration;

use database_wrapper;

final class Migration008 implements MigrationInterface {

    #[\Override]
    public function getVersion(): int {
        return 8;
    }

    #[\Override]
    public function migrate(): void {
        $this->refactorEmergencyService();
    }

    #[\Override]
    public function getDescription(): string {
        return "Refactor emergency service";
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
            throw new DatabaseMigrationException('Could not refactor emergency service table.');
        }
        $alterResult2 = database_wrapper::instance()->run($alterQuery2);
        if ('00000' !== $alterResult2->errorCode()) {
            database_wrapper::instance()->rollBack();
            throw new DatabaseMigrationException('Could not refactor emergency service table.');
        }
        $renameResult = database_wrapper::instance()->run($renameQuery);
        if ('00000' !== $renameResult->errorCode()) {
            database_wrapper::instance()->rollBack();
            throw new DatabaseMigrationException('Could not refactor emergency service table.');
        }
        if (true === database_wrapper::instance()->inTransaction()) {
            database_wrapper::instance()->commit();
        }
    }
}
