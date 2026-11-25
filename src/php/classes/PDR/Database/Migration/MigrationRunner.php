<?php

namespace PDR\Database\Migration;

use PDO;
use RuntimeException;

class MigrationRunner {

    private const LOCK_NAME = 'pdr_schema_migrations_lock';

    /**
     * @var \database_wrapper
     */
    private $databaseWrapper;

    public function __construct(\database_wrapper $databaseWrapper) {
        $this->databaseWrapper = $databaseWrapper;
    }

    public function runMigrations(array $migrations): void {
        if ([] === $migrations) {
            return;
        }
        $this->createSchemaMigrationsTableIfNecessary();
        if (!$this->acquireLock()) {
            throw new RuntimeException('Could not acquire migration lock.');
        }
        try {
            foreach ($migrations as $migration) {
                if (!$migration instanceof MigrationInterface) {
                    throw new RuntimeException('Invalid migration type passed to MigrationRunner.');
                }
                if ($this->isApplied($migration->getVersion())) {
                    continue;
                }
                if (false === $migration->up()) {
                    throw new RuntimeException('Migration failed: ' . $migration->getVersion());
                }
                $this->markAsApplied($migration);
            }
        } finally {
            $this->releaseLock();
        }
    }

    private function createSchemaMigrationsTableIfNecessary(): void {
        $sql_query = "CREATE TABLE IF NOT EXISTS `schema_migrations` (
            `version` VARCHAR(191) NOT NULL,
            `description` VARCHAR(255) NOT NULL,
            `executed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`version`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->databaseWrapper->run($sql_query);
    }

    private function isApplied(string $version): bool {
        $sql_query = "SELECT `version` FROM `schema_migrations` WHERE `version` = :version LIMIT 1;";
        $result = $this->databaseWrapper->run($sql_query, array('version' => $version));
        return false !== $result->fetch(PDO::FETCH_ASSOC);
    }

    private function markAsApplied(MigrationInterface $migration): void {
        $sql_query = "INSERT INTO `schema_migrations` (`version`, `description`) VALUES (:version, :description);";
        $this->databaseWrapper->run($sql_query, array(
            'version' => $migration->getVersion(),
            'description' => $migration->getDescription(),
        ));
    }

    private function acquireLock(): bool {
        $sql_query = "SELECT GET_LOCK(:lock_name, 30) AS lock_acquired;";
        $result = $this->databaseWrapper->run($sql_query, array('lock_name' => self::LOCK_NAME));
        $row = $result->fetch(PDO::FETCH_OBJ);
        return isset($row->lock_acquired) && (int) $row->lock_acquired === 1;
    }

    private function releaseLock(): void {
        $sql_query = "SELECT RELEASE_LOCK(:lock_name);";
        $this->databaseWrapper->run($sql_query, array('lock_name' => self::LOCK_NAME));
    }
}
