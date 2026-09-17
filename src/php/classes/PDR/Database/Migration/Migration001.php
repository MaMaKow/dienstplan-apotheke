<?php

namespace PDR\Database\Migration;

final class Migration001 implements MigrationInterface {

    #[\Override]
    public function getVersion(): int {
        return 1;
    }

    #[\Override]
    public function migrate(): void {
        $this->refactorOpeningTimesSpecialTable();
    }

    #[\Override]
    public function getDescription(): string {
        return 'Refactor opening times special table';
    }

    private function refactorOpeningTimesSpecialTable(): void {
        if (
                \database_wrapper::database_table_exists('Sonderöffnungszeiten')
                and !\database_wrapper::database_table_exists('opening_times_special')
        ) {
            \database_wrapper::instance()->run("RENAME TABLE `Sonderöffnungszeiten` TO `opening_times_special`;");
            $sql_query = "ALTER TABLE `opening_times_special` "
                    . "CHANGE `Datum` `date` DATE NOT NULL, "
                    . "CHANGE `Beginn` `start` TIME NOT NULL, "
                    . "CHANGE `Ende` `end` TIME NOT NULL, "
                    . "CHANGE `Bezeichnung` `event_name` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;";
            \database_wrapper::instance()->run($sql_query);
        }
    }
}
