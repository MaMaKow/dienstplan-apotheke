<?php

/*
 * Copyright (C) 2026 Martin Mandelkow
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace PDR\Database\Migration;

/**
 * @author martin
 */
class Migration003 implements MigrationInterface {

    #[\Override]
    public function getDescription(): string {
        return "Refactor absence table";
    }

    #[\Override]
    public function getVersion(): int {
        return 3;
    }

    #[\Override]
    public function migrate(): void {
        $this->refactor_receive_emails_on_changed_roster();
    }

    private function refactor_receive_emails_on_changed_roster() {
        $database_name = database_wrapper::get_database_name();
        if (database_wrapper::database_table_exists('users') and !database_wrapper::database_table_column_exists($database_name, 'users', 'receive_emails_on_changed_roster')) {
            $sql_query = "ALTER TABLE `users`  ADD `receive_emails_on_changed_roster` BOOLEAN NOT NULL DEFAULT FALSE  AFTER `failed_login_attempt_time`;";
            database_wrapper::instance()->run($sql_query);
        }
    }
}
