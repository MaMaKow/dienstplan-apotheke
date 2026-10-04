<?php

/*
 * Copyright (C) 2026 Martin Mandelkow
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

namespace PDR\Database\Migration;

use database_wrapper;

/**
 * Normalizes the frozen legacy schema to the names expected by later migrations.
 */
final class Migration000 implements MigrationInterface {

    #[\Override]
    public function getVersion(): int {
        return 0;
    }

    #[\Override]
    public function getDescription(): string {
        return 'Normalize legacy table names and convert mandant to branch';
    }

    #[\Override]
    public function migrate(): void {
        $this->renameLegacyTable('dienstplan', 'Dienstplan');
        $this->renameLegacyTable('feiertage', 'Feiertage');
        $this->renameLegacyTable('notdienst', 'Notdienst');
        $this->renameLegacyTable('schulferien', 'Schulferien');
        $this->renameLegacyTable('öffnungszeiten', 'opening_times');
        $this->renameLegacyTable('sonderöffnungszeiten', 'Sonderöffnungszeiten');
        $this->renameLegacyTable('grundplan', 'Grundplan');

        $mandantTable = $this->findExistingTable(['mandant', 'Mandant']);
        $hasBranch = $this->tableExistsExactly('branch');

        if (null !== $mandantTable && $hasBranch) {
            throw new DatabaseMigrationException(
                'Both a `mandant` and a `branch` table exist; refusing to discard either table.'
            );
        }

        if (null !== $mandantTable) {
            database_wrapper::instance()->run(
                'RENAME TABLE ' . database_wrapper::quote_identifier($mandantTable) . ' TO `branch`'
            );
            $hasBranch = true;
        }

        if (
            $hasBranch
            && !database_wrapper::database_table_column_exists(
                database_wrapper::get_database_name(),
                'branch',
                'branch_id'
            )
        ) {
            database_wrapper::instance()->run(
                'ALTER TABLE `branch` '
                . 'CHANGE `Mandant` `branch_id` TINYINT(3) UNSIGNED NOT NULL, '
                . 'CHANGE `Name` `name` VARCHAR(64) NOT NULL, '
                . 'CHANGE `Kurzname` `short_name` VARCHAR(16) NOT NULL, '
                . 'CHANGE `Adresse` `address` VARCHAR(64) NOT NULL, '
                . 'CHANGE `Leiter` `manager` VARCHAR(64) NOT NULL'
            );
        }

        $this->normalizeBranchRelatedTables();
        $this->ensureLegacyLoginColumns();
        $this->createMissingTablesFromLegacyBaseline();
    }

    private function normalizeBranchRelatedTables(): void {
        $databaseName = database_wrapper::get_database_name();

        if ($this->tableExistsExactly('opening_times')) {
            if (database_wrapper::database_table_column_exists($databaseName, 'opening_times', 'Wochentag')) {
                database_wrapper::instance()->run(
                    'ALTER TABLE `opening_times` '
                    . 'CHANGE `Wochentag` `weekday` TINYINT(4) NOT NULL, '
                    . 'CHANGE `Beginn` `start` TIME NOT NULL, '
                    . 'CHANGE `Ende` `end` TIME NOT NULL, '
                    . 'CHANGE `Mandant` `branch_id` INT(11) NOT NULL'
                );
            }
        }

        if ($this->tableExistsExactly('saturday_rotation_teams')) {
            $hasTeamId = database_wrapper::database_table_column_exists($databaseName, 'saturday_rotation_teams', 'team_id');
            if (!$hasTeamId && database_wrapper::database_table_column_exists($databaseName, 'saturday_rotation_teams', 'team_number')) {
                database_wrapper::instance()->run(
                    'ALTER TABLE `saturday_rotation_teams` '
                    . 'CHANGE `team_number` `team_id` TINYINT(3) UNSIGNED NOT NULL, '
                    . 'ADD `branch_id` TINYINT(4) NOT NULL DEFAULT 1, '
                    . 'DROP PRIMARY KEY, ADD PRIMARY KEY (`team_id`, `employee_id`, `branch_id`)'
                );
            } elseif (!$this->columnExists('saturday_rotation_teams', 'branch_id')) {
                database_wrapper::instance()->run(
                    'ALTER TABLE `saturday_rotation_teams` '
                    . 'ADD `branch_id` TINYINT(4) NOT NULL DEFAULT 1, '
                    . 'DROP PRIMARY KEY, ADD PRIMARY KEY (`team_id`, `employee_id`, `branch_id`)'
                );
            }
        }

        if ($this->tableExistsExactly('task_rotation') && !$this->columnExists('task_rotation', 'branch_id')) {
            database_wrapper::instance()->run(
                'ALTER TABLE `task_rotation` '
                . 'ADD `branch_id` TINYINT(4) NOT NULL DEFAULT 1 AFTER `VK`, '
                . 'DROP PRIMARY KEY, ADD PRIMARY KEY (`date`, `task`, `branch_id`)'
            );
        }
    }

    private function columnExists(string $tableName, string $columnName): bool {
        return database_wrapper::database_table_column_exists(
            database_wrapper::get_database_name(),
            $tableName,
            $columnName
        );
    }

    private function ensureLegacyLoginColumns(): void {
        if (!$this->tableExistsExactly('users')) {
            return;
        }

        $databaseName = database_wrapper::get_database_name();
        if (!database_wrapper::database_table_column_exists($databaseName, 'users', 'failed_login_attempts')) {
            database_wrapper::instance()->run(
                "ALTER TABLE `users` ADD `failed_login_attempts` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER `status`"
            );
        }
        if (!database_wrapper::database_table_column_exists($databaseName, 'users', 'failed_login_attempt_time')) {
            database_wrapper::instance()->run(
                'ALTER TABLE `users` ADD `failed_login_attempt_time` TIMESTAMP NULL DEFAULT NULL AFTER `failed_login_attempts`'
            );
        }
    }

    private function createMissingTablesFromLegacyBaseline(): void {
        $legacyTableDefinitions = [
            'maintenance' => "CREATE TABLE `maintenance` (
                `id` enum('1') COLLATE latin1_german1_ci NOT NULL DEFAULT '1',
                `last_execution` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci",
            'pdr_self' => "CREATE TABLE `pdr_self` (
                `id` enum('1') COLLATE latin1_german1_ci NOT NULL DEFAULT '1',
                `pdr_version_number` int(6) unsigned zerofill DEFAULT NULL,
                `pdr_version_string` varchar(64) COLLATE latin1_german1_ci DEFAULT NULL,
                `pdr_database_version_hash` char(40) COLLATE latin1_german1_ci DEFAULT NULL,
                `last_execution_of_maintenance` timestamp NULL DEFAULT NULL,
                `principle_roster_start_date` date DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci",
            'saturday_rotation' => "CREATE TABLE `saturday_rotation` (
                `date` date NOT NULL,
                `team_id` tinyint(4) NOT NULL,
                `branch_id` tinyint(4) NOT NULL,
                PRIMARY KEY (`date`,`branch_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci",
            'Stunden' => "CREATE TABLE `Stunden` (
                `VK` int(11) NOT NULL,
                `Datum` date NOT NULL,
                `Stunden` float DEFAULT NULL,
                `Saldo` float NOT NULL,
                `Grund` varchar(64) DEFAULT NULL,
                `Aktualisierung` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`VK`,`Datum`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1",
            'user_email_notification_cache' => "CREATE TABLE `user_email_notification_cache` (
                `notification_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `employee_id` tinyint(3) unsigned NOT NULL,
                `date` date NOT NULL,
                `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `notification_text` text COLLATE latin1_german1_ci NOT NULL,
                `notification_ics_file` blob NOT NULL,
                PRIMARY KEY (`notification_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci",
            'users_lost_password_token' => "CREATE TABLE `users_lost_password_token` (
                `employee_id` tinyint(3) unsigned NOT NULL,
                `token` binary(20) NOT NULL,
                `time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci",
        ];

        foreach ($legacyTableDefinitions as $tableName => $createStatement) {
            if (!$this->tableExistsExactly($tableName)) {
                database_wrapper::instance()->run($createStatement);
            }
        }

        $this->migrateGrundplanToPrincipleRoster();
    }

    private function migrateGrundplanToPrincipleRoster(): void {
        if (!$this->tableExistsExactly('principle_roster')) {
            database_wrapper::instance()->run(
                "CREATE TABLE `principle_roster` (
                    `primary_key` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `alternating_week_id` tinyint(4) NOT NULL,
                    `employee_id` tinyint(4) NOT NULL,
                    `weekday` tinyint(4) NOT NULL,
                    `duty_start` time DEFAULT NULL,
                    `duty_end` time DEFAULT NULL,
                    `break_start` time DEFAULT NULL,
                    `break_end` time DEFAULT NULL,
                    `comment` text COLLATE latin1_german1_ci,
                    `working_hours` float DEFAULT NULL,
                    `branch_id` int(11) NOT NULL DEFAULT '1',
                    `valid_from` date DEFAULT NULL,
                    `valid_until` date DEFAULT NULL,
                    PRIMARY KEY (`primary_key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci"
            );
        }

        if (!$this->tableExistsExactly('Grundplan')) {
            return;
        }

        database_wrapper::instance()->run(
            'INSERT INTO `principle_roster` '
            . '(`alternating_week_id`, `employee_id`, `weekday`, `duty_start`, `duty_end`, '
            . '`break_start`, `break_end`, `comment`, `working_hours`, `branch_id`, `valid_from`, `valid_until`) '
            . 'SELECT 0, `VK`, `Wochentag`, `Dienstbeginn`, `Dienstende`, `Mittagsbeginn`, '
            . '`Mittagsende`, `Kommentar`, `Stunden`, `Mandant`, NULL, NULL FROM `Grundplan` AS source '
            . 'WHERE NOT EXISTS ( '
            . 'SELECT 1 FROM `principle_roster` AS target '
            . 'WHERE target.`alternating_week_id` = 0 '
            . 'AND target.`employee_id` = source.`VK` '
            . 'AND target.`weekday` = source.`Wochentag` '
            . 'AND target.`duty_start` <=> source.`Dienstbeginn` '
            . 'AND target.`duty_end` <=> source.`Dienstende` '
            . 'AND target.`break_start` <=> source.`Mittagsbeginn` '
            . 'AND target.`break_end` <=> source.`Mittagsende` '
            . 'AND target.`comment` <=> source.`Kommentar` '
            . 'AND target.`working_hours` <=> source.`Stunden` '
            . 'AND target.`branch_id` = source.`Mandant` '
            . 'AND target.`valid_from` IS NULL AND target.`valid_until` IS NULL '
            . ')'
        );
        database_wrapper::instance()->run('DROP TABLE `Grundplan`');
    }

    private function renameLegacyTable(string $legacyName, string $canonicalName): void {
        $hasLegacyTable = $this->tableExistsExactly($legacyName);
        $hasCanonicalTable = $this->tableExistsExactly($canonicalName);

        if (
            $hasLegacyTable
            && !$hasCanonicalTable
            && 0 === strcasecmp($legacyName, $canonicalName)
            && 0 !== (int) database_wrapper::instance()->run(
                'SELECT @@lower_case_table_names'
            )->fetchColumn()
        ) {
            // On case-insensitive MySQL installations both spellings resolve
            // to the same table, so a case-only rename is unnecessary.
            return;
        }

        if ($hasLegacyTable && $hasCanonicalTable) {
            throw new DatabaseMigrationException(
                'Both `' . $legacyName . '` and `' . $canonicalName
                . '` tables exist; refusing to discard either table.'
            );
        }

        if ($hasLegacyTable) {
            database_wrapper::instance()->run(
                'RENAME TABLE ' . database_wrapper::quote_identifier($legacyName)
                . ' TO ' . database_wrapper::quote_identifier($canonicalName)
            );
        }
    }

    /** @param string[] $tableNames */
    private function findExistingTable(array $tableNames): ?string {
        $existingTables = array_values(array_filter(
            $tableNames,
            fn (string $tableName): bool => $this->tableExistsExactly($tableName)
        ));

        if (count($existingTables) > 1) {
            throw new DatabaseMigrationException(
                'Multiple legacy `mandant` table spellings exist; refusing to choose one.'
            );
        }

        return $existingTables[0] ?? null;
    }

    private function tableExistsExactly(string $tableName): bool {
        $result = database_wrapper::instance()->run(
            'SELECT `TABLE_NAME` FROM `information_schema`.`TABLES` '
            . 'WHERE `TABLE_SCHEMA` = :database_name AND BINARY `TABLE_NAME` = BINARY :table_name',
            [
                'database_name' => database_wrapper::get_database_name(),
                'table_name' => $tableName,
            ]
        );

        return false !== $result->fetchColumn();
    }
}
