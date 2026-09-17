<?php

namespace PDR\Database\Migration;

final class Migration005 implements MigrationInterface {

    #[\Override]
    public function getVersion(): int {
        return 5;
    }

    #[\Override]
    public function migrate(): void {
        $this->refactor_pdr_self();
    }

    #[\Override]
    public function getDescription(): string {
        return "Refactor pdr self";
    }

    private function refactor_pdr_self() {
        $database_name = database_wrapper::get_database_name();
        if (\database_wrapper::database_table_exists('pdr_self') and !\database_wrapper::database_table_column_exists($database_name, 'pdr_self', 'principle_roster_start_date')) {
            $sql_query = "ALTER TABLE `pdr_self` ADD `principle_roster_start_date` date DEFAULT NULL AFTER `last_execution_of_maintenance`;";
            \database_wrapper::instance()->run($sql_query);
        }
    }
}
