<?php

namespace PDR\Tests\Database\Migration;

use PDR\Database\Migration\DatabaseMigrationException;
use PDR\Database\Migration\DatabaseMigrator;
use PHPUnit\Framework\TestCase;

final class DatabaseMigratorTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        LegacyFixtureLoader::reset();
    }

    public function testMigratesLegacySchemaToCurrentVersion(): void {
        (new DatabaseMigrator())->migrate();

        $db = \database_wrapper::instance();
        $appliedVersions = array_map(
            'intval',
            $db->query('SELECT `version` FROM `database_migrations` ORDER BY `version`')->fetchAll(\PDO::FETCH_COLUMN)
        );
        self::assertSame(range(0, 9), $appliedVersions);
        self::assertTrue(\database_wrapper::database_table_exists('branch'));
        self::assertFalse(\database_wrapper::database_table_exists('mandant'));

        // Migration007 deliberately removes this obsolete table.
        self::assertFalse(\database_wrapper::database_table_exists('opening_times_special'));
        self::assertFalse(\database_wrapper::database_table_exists('Sonderöffnungszeiten'));

        // The legacy duty-roster conversion is intentionally not part of the
        // current migration chain, so the table retains its historical name.
        self::assertTrue(\database_wrapper::database_table_exists('Dienstplan'));
        self::assertFalse(\database_wrapper::database_table_exists('roster'));

        self::assertTrue(\database_wrapper::database_table_exists('emergency_services'));
        self::assertFalse(\database_wrapper::database_table_exists('Notdienst'));

        self::assertFalse(\database_wrapper::database_table_column_exists(
            \database_wrapper::get_database_name(),
            'employees',
            'id'
        ));
    }

    public function testMigrationIsIdempotent(): void {
        $migrator = new DatabaseMigrator();
        $migrator->migrate();
        $migrator->migrate(); // zweiter Lauf darf nichts mehr anfassen

        $db = \database_wrapper::instance();
        $count = (int) $db->query('SELECT COUNT(*) FROM `database_migrations`')->fetchColumn();
        self::assertSame(10, $count);
    }

    public function testFailedMigrationDoesNotMarkVersionAsApplied(): void {
        $db = \database_wrapper::instance();

        // Migration008 (Notdienst -> emergency_services) aendert u.a.
        // Migration008 adds this column. Pre-creating it forces a failure in
        // migration 8 without disrupting the preceding employee migration.
        $db->exec('ALTER TABLE `notdienst` ADD COLUMN `primary_key` INT UNSIGNED NOT NULL DEFAULT 0');

        $migrator = new DatabaseMigrator();

        try {
            $migrator->migrate();
            self::fail('Erwartete DatabaseMigrationException wurde nicht geworfen.');
        } catch (DatabaseMigrationException $exception) {
            // erwartet
        }

        $appliedVersions = array_map(
            'intval',
            $db->query('SELECT `version` FROM `database_migrations` ORDER BY `version`')->fetchAll(\PDO::FETCH_COLUMN)
        );

        self::assertContains(7, $appliedVersions, 'Migrationen vor dem Fehler sollten trotzdem angewendet worden sein.');
        self::assertNotContains(8, $appliedVersions, 'Die fehlgeschlagene Migration 8 darf nicht als angewendet markiert sein.');
        self::assertFalse(\database_wrapper::database_table_exists('emergency_services'));
    }
}
