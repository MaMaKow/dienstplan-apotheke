<?php

namespace PDR\Database\Migration;

use database_wrapper;

final class Migration009 implements MigrationInterface {

    #[\Override]
    public function getVersion(): int {
        return 9;
    }

    #[\Override]
    public function migrate(): void {
        $this->refactorEmployeesArchive();
    }

    #[\Override]
    public function getDescription(): string {
        return "Refactor employees archive";
    }

    /**
     * Refactors the `employees_archive` table by adding a `row_id` column with auto-increment and primary key,
     * and renaming the existing `primary_key` column to `employee_key`.
     */
    private function refactorEmployeesArchive() {
        if (
            database_wrapper::database_table_exists('employees_archive')
            && !database_wrapper::database_table_column_exists(database_wrapper::get_database_name(), 'employees_archive', 'row_id')
        ) {
            $alterQueryId = "ALTER TABLE `employees_archive` "
                    . " ADD `row_id` INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST, "
                    . " ADD PRIMARY KEY (`row_id`), "
                    . " CHANGE `primary_key` `employee_key` INT(10) UNSIGNED NOT NULL;";
            database_wrapper::instance()->run($alterQueryId);
        }
    }
}
