<?php

namespace PDR\Database\Migration;

use database_wrapper;

final class Migration004 implements MigrationInterface {
    #[\Override]
    public function getVersion(): int {
        return 4;
    }

    #[\Override]
    public function migrate(): void {
        $this->refactor_user_email_notification_cache();
    }

    #[\Override]
    public function getDescription(): string {
        return "Refactor user email notification cache";
    }

    private function refactor_user_email_notification_cache() {
        if (!database_wrapper::database_table_exists('user_email_notification_cache')) {
            $sql_query = file_get_contents(PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/sql/user_email_notification_cache.sql');
            database_wrapper::instance()->run($sql_query);
        }
        $database_name = database_wrapper::get_database_name();
        if (database_wrapper::database_table_exists('user_email_notification_cache') and !database_wrapper::database_table_column_exists($database_name, 'user_email_notification_cache', 'date')) {
            $sql_query = "ALTER TABLE `user_email_notification_cache` ADD `date` DATE NOT NULL AFTER `employee_id`;";
            database_wrapper::instance()->run($sql_query);
        }
    }
}
