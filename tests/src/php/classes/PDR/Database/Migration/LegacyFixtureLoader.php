<?php

namespace PDR\Tests\Database\Migration;

/**
 * Setzt vor jedem Migrationstest ein sauberes Alt-Schema auf:
 * alle Tabellen der laufenden Testdatenbank droppen, dann die
 * eingefrorenen Legacy-Fixtures aus tests/fixtures/legacy-sql/ laden.
 *
 * Mehrpass-Ladelogik wegen Fremdschluessel-Abhaengigkeiten zwischen den
 * Fixture-Dateien (aehnlich InstallDatabase::createTables(), aber ohne
 * dessen Bugs und ohne Kopplung an den Web-Installer).
 */
final class LegacyFixtureLoader {

    private const MAX_PASSES = 5;

    public static function reset(): void {
        self::dropAllTables();
        self::loadFixtures();
    }

    private static function dropAllTables(): void {
        $db = \database_wrapper::instance();
        $db->exec('SET FOREIGN_KEY_CHECKS=0');
        $tables = $db->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $db->exec('DROP TABLE IF EXISTS ' . \database_wrapper::quote_identifier($table));
        }
        $db->exec('SET FOREIGN_KEY_CHECKS=1');
    }

    private static function loadFixtures(): void {
        $db = \database_wrapper::instance();
        $fixtureDirectory = PDR_FILE_SYSTEM_APPLICATION_PATH . 'tests/fixtures/legacy-sql/';
        $sqlFiles = glob($fixtureDirectory . '*.sql');

        if (false === $sqlFiles || [] === $sqlFiles) {
            throw new \RuntimeException('Keine Legacy-Fixtures gefunden unter: ' . $fixtureDirectory);
        }

        $pending = $sqlFiles;
        $lastError = null;

        for ($pass = 0; $pass < self::MAX_PASSES && [] !== $pending; $pass++) {
            $stillPending = [];
            foreach ($pending as $sqlFile) {
                try {
                    $db->exec(file_get_contents($sqlFile));
                } catch (\Throwable $exception) {
                    $stillPending[] = $sqlFile;
                    $lastError = $exception;
                }
            }
            if (count($stillPending) === count($pending)) {
                break; // kein Fortschritt mehr - weitere Versuche waeren sinnlos
            }
            $pending = $stillPending;
        }

        if ([] !== $pending) {
            throw new \RuntimeException(
                'Konnte folgende Legacy-Fixtures nicht laden: '
                . implode(', ', array_map('basename', $pending))
                . '. Letzter Fehler: ' . ($lastError?->getMessage() ?? 'unbekannt')
            );
        }
    }
}
