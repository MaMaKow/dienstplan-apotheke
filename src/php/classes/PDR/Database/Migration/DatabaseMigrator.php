<?php

namespace PDR\Database\Migration;

use database_wrapper;

class DatabaseMigrator {

    private function getMigrations(): array {
        return [
            new Migration000(),
            new Migration001(),
            new Migration002(),
            new Migration003(),
            new Migration004(),
            new Migration005(),
            new Migration006(),
            new Migration007(),
            new Migration008(),
            new Migration009(),
        ];
    }

    private function acquireLock(): void {
        $result = database_wrapper::instance()->run(
                "SELECT GET_LOCK('dienstplan_apotheke_database_migration', 60) AS `lock_acquired`"
        );

        $row = $result->fetch();

        if (1 !== (int) $row->lock_acquired) {
            throw new DatabaseMigrationException(
                            'Could not acquire database migration lock.'
                    );
        }
    }

    private function releaseLock(): void {
        database_wrapper::instance()->run("SELECT RELEASE_LOCK('dienstplan_apotheke_database_migration');");
    }

    public function migrate(): void {
        $this->acquireLock();

        try {
            $this->ensureMigrationTableExists();

            $currentVersion = $this->getCurrentVersion();

            foreach ($this->getMigrations() as $migration) {
                if (null !== $currentVersion && $migration->getVersion() <= $currentVersion) {
                    continue;
                }

                $this->executeMigration($migration);
            }
        } finally {
            $this->releaseLock();
        }
    }

    private function executeMigration(MigrationInterface $migration): void {
        $version = $migration->getVersion();

        try {
            $migration->migrate();
            database_wrapper::instance()->run(
                    'INSERT INTO `database_migrations` (`version`, `applied_at`) '
                    . 'VALUES (:version, NOW())',
                    ['version' => $version]
            );
        } catch (\Throwable $exception) {
            throw new DatabaseMigrationException(
                            'Database migration ' . $version
                            . ' (' . $migration->getDescription() . ') failed.',
                            0,
                            $exception
                    );
        }
    }

    private function getCurrentVersion(): ?int {
        $result = database_wrapper::instance()->run(
                'SELECT MAX(`version`) AS `version` FROM `database_migrations`'
        );

        $row = $result->fetch();

        if (NULL === $row->version) {
            return null;
        }

        return (int) $row->version;
    }

    private function ensureMigrationTableExists(): void {
        if (database_wrapper::database_table_exists("database_migrations")) {
            return;
        }
        $create_statement = file_get_contents(PDR_FILE_SYSTEM_APPLICATION_PATH . "src/sql/database_migrations.sql");
        database_wrapper::instance()->run($create_statement);
    }
}
