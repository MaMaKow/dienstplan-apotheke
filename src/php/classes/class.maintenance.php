<?php

/*
 * Copyright (C) 2018 Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
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

/**
 * This class is meant to clean up the database once in a while.
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class maintenance {

    /**
     * @var int MAINTENANCE_PERIOD_IN_SECONDS the minimum time between two maintenance executions
     */
    const MAINTENANCE_PERIOD_IN_SECONDS = 24 * 60 * 60;
    const MAINTENANCE_PERIOD_DATE_INTERVAL_STRING = 'P1D';

    /**
     * @var int <p>unix time stamp of the last execution time of the maintenance functions</p>
     */
    private $last_execution = 0;

    public function __construct() {
        /*
         * Check, if it is necessary to do any maintenance:
         */
        $sql_query = "SELECT UNIX_TIMESTAMP(`last_execution`) as `last_execution_unix` from `maintenance`";
        $result = database_wrapper::instance()->run($sql_query);
        $row = $result->fetch(PDO::FETCH_OBJ);
        if (FALSE !== $row) {
            $this->last_execution = $row->last_execution_unix;
        } else {
            $sql_query = "INSERT INTO `maintenance` SET `last_execution` =  FROM_UNIXTIME(:time)";
            database_wrapper::instance()->run($sql_query, array('time' => time()));
        }
        if ($this->last_execution + self::MAINTENANCE_PERIOD_IN_SECONDS < time()) {
            $message = date('Y-m-d') . ': ' . 'Performing general maintenance.' . PHP_EOL;
            error_log($message, 3, PDR_FILE_SYSTEM_APPLICATION_PATH . 'maintenance.log');
            /*
             * TODO: build a rotation for the log file.
             * Check against a maximum size.
             * If the size is big, or the file is old, rotate it.
             * Keep some of the older versions.
             * Delete the others.
             */
            /*
             * user_dialog_email:
             */
            $workforce = new workforce();
            $user_dialog_email = new user_dialog_email();
            $user_dialog_email->clean_up_user_email_notification_cache();
            $user_dialog_email->aggregate_messages_about_changed_roster_to_employees($workforce);
            /*
             * Cleanup of database tables:
             */
            alternating_week::reorganize_ids();
            $this->cleanup_database_table_saturday_rotation();
            $this->cleanup_database_table_task_rotation();
            $this->cleanup_database_table_overtime();
            $this->cleanup_database_table_absence();
            $this->cleanup_database_table_emergency_service();
            $this->cleanup_database_table_approval();
            $this->cleanup_database_table_principle_roster();
            $this->cleanup_database_table_principle_roster_archive();
            $this->cleanup_database_table_saturday_rotation_teams();
            $this->cleanup_database_table_roster();
            $this->cleanup_database_table_users_lost_password_token();
            $this->cleanup_database_table_user_email_notification_cache();
            $this->cleanup_database_table_users();
            $this->cleanup_database_table_employees();
            /*
             * __destruct():
             */
            $sql_query = "UPDATE `maintenance` SET `last_execution` = NOW()";
            database_wrapper::instance()->run($sql_query);
            $message = date('Y-m-d') . ': ' . 'Done with general maintenance.' . PHP_EOL;
            error_log($message, 3, PDR_FILE_SYSTEM_APPLICATION_PATH . 'maintenance.log');
        } else {
            $message = date('Y-m-d') . ': ' . 'Nothing to do for general maintenance.' . PHP_EOL;
            $dateObjectNow = new DateTime("now");
            $dateObjectLastExecution = new DateTime('@' . $this->last_execution);
            $dateStringNow = $dateObjectNow->format("d.m.Y H:i:s");
            $dateStringLastExecution = $dateObjectLastExecution->format("d.m.Y H:i:s");
            $message .= 'Time: ' . $dateStringNow . PHP_EOL;
            $message .= 'Last execution: ' . $dateStringLastExecution . PHP_EOL;
            $dateObjectNextExecution = (clone $dateObjectLastExecution)->add(new DateInterval(self::MAINTENANCE_PERIOD_DATE_INTERVAL_STRING));
            $dateStringNextExecution = $dateObjectNextExecution->format("d.m.Y H:i:s");
            $message .= 'Earliest next execution: ' . $dateStringNextExecution . PHP_EOL;
            error_log($message, 3, PDR_FILE_SYSTEM_APPLICATION_PATH . 'maintenance.log');
        }
    }

    /**
     * This function cleans up old entries in the table saturday_rotation.
     *
     * @return void
     */
    private function cleanup_database_table_saturday_rotation() {
        $sql_query = "DELETE FROM `saturday_rotation` WHERE YEAR(`date`) < YEAR(now()-interval 24 month);";
        database_wrapper::instance()->run($sql_query);
    }

    /**
     * @see Arbeitszeitgesetz / § 16 Aushang und Arbeitszeitnachweise
     *  (2) Der Arbeitgeber ist verpflichtet, die über die werktägliche Arbeitszeit des § 3 Satz 1 hinausgehende Arbeitszeit der Arbeitnehmer aufzuzeichnen und ein Verzeichnis der Arbeitnehmer zu führen, die in eine Verlängerung der Arbeitszeit gemäß § 7 Abs. 7 eingewilligt haben.
     *  Die Nachweise sind mindestens zwei Jahre aufzubewahren.
     */
    private function cleanup_database_table_overtime() {
        $sql_query = "DELETE FROM `Stunden` WHERE YEAR(`Datum`) < YEAR(now()-interval 48 month)";
        database_wrapper::instance()->run($sql_query);
    }

    /**
     * @see Arbeitszeitgesetz / § 16 Aushang und Arbeitszeitnachweise
     *  (2) Der Arbeitgeber ist verpflichtet, die über die werktägliche Arbeitszeit des § 3 Satz 1 hinausgehende Arbeitszeit der Arbeitnehmer aufzuzeichnen und ein Verzeichnis der Arbeitnehmer zu führen, die in eine Verlängerung der Arbeitszeit gemäß § 7 Abs. 7 eingewilligt haben.
     *  Die Nachweise sind mindestens zwei Jahre aufzubewahren.
     */
    private function cleanup_database_table_absence() {
        $sql_query = "DELETE FROM `absence` WHERE YEAR(`end`) < YEAR(now()-interval 48 month)";
        database_wrapper::instance()->run($sql_query);
    }

    /**
     * @see Arbeitszeitgesetz / § 16 Aushang und Arbeitszeitnachweise
     *  (2) Der Arbeitgeber ist verpflichtet, die über die werktägliche Arbeitszeit des § 3 Satz 1 hinausgehende Arbeitszeit der Arbeitnehmer aufzuzeichnen und ein Verzeichnis der Arbeitnehmer zu führen, die in eine Verlängerung der Arbeitszeit gemäß § 7 Abs. 7 eingewilligt haben.
     *  Die Nachweise sind mindestens zwei Jahre aufzubewahren.
     */
    private function cleanup_database_table_emergency_service() {
        $sql_query = "DELETE FROM `Notdienst` WHERE YEAR(`Datum`) < YEAR(now()-interval 48 month)";
        database_wrapper::instance()->run($sql_query);
    }

    /**
     * Wie lange müssen Zeiterfassungsdaten aufbewahrt werden?
     * In Deutschland müssen Arbeitszeitaufzeichnungen 2 Jahre aufbewahrt werden.
     */
    private function cleanup_database_table_roster() {
        $sql_query = "DELETE FROM `Dienstplan` WHERE YEAR(`Datum`) < YEAR(now()-interval 48 month)";
        database_wrapper::instance()->run($sql_query);
    }

    private function cleanup_database_table_principle_roster() {
        $sql_query = "DELETE principle_roster FROM principle_roster LEFT JOIN employees
            ON principle_roster.employee_key = employees.primary_key
            WHERE employees.end_of_employment < NOW();";
        database_wrapper::instance()->run($sql_query);
    }

    private function cleanup_database_table_principle_roster_archive() {
        $sql_query = "DELETE principle_roster_archive FROM principle_roster_archive LEFT JOIN employees
            ON principle_roster_archive.employee_key = employees.primary_key
            WHERE YEAR(employees.end_of_employment) < YEAR(NOW() - INTERVAL 48 month);";
        database_wrapper::instance()->run($sql_query);
    }

    private function cleanup_database_table_users() {
        $sql_query = "DELETE users FROM users LEFT JOIN employees
            ON users.employee_key = employees.primary_key
            WHERE employees.end_of_employment < NOW() - INTERVAL 1 month;";
        database_wrapper::instance()->run($sql_query);
    }

    private function cleanup_database_table_saturday_rotation_teams() {
        $sql_query = "DELETE saturday_rotation_teams FROM saturday_rotation_teams LEFT JOIN employees
            ON saturday_rotation_teams.employee_key = employees.primary_key
            WHERE employees.end_of_employment < NOW() - INTERVAL 1 month;";
        database_wrapper::instance()->run($sql_query);
    }

    /**
     * Wie lange müssen Zeiterfassungsdaten aufbewahrt werden?
     * In Deutschland müssen Arbeitszeitaufzeichnungen 2 Jahre aufbewahrt werden.
     */
    private function cleanup_database_table_approval() {
        $sql_query = "DELETE FROM `approval` WHERE YEAR(`date`) < YEAR(now()-interval 48 month)";
        database_wrapper::instance()->run($sql_query);
    }

    private function cleanup_database_table_task_rotation() {
        $sql_query = "DELETE FROM `task_rotation` WHERE YEAR(`date`) < YEAR(now()-interval 12 month)";
        database_wrapper::instance()->run($sql_query);
    }

    /**
     * Employees are bound in multiple tables through CONSTRAINTs.
     * An employee can only be deleted, if all of its rosters, absences, overtimes etc. are deleted beforehand.
     * This data is typically deleted after four years (now()-interval 48 month)
     */
    private function cleanup_database_table_employees() {
        $sql_query = "DELETE IGNORE FROM employees WHERE end_of_employment < now();";
        database_wrapper::instance()->run($sql_query);
    }

    private function cleanup_database_table_users_lost_password_token() {
        $sql_query = "DELETE FROM `users_lost_password_token` WHERE `time_created` <= NOW() - INTERVAL 1 DAY;";
        database_wrapper::instance()->run($sql_query);
    }

    private function cleanup_database_table_user_email_notification_cache() {
        $sql_query = "DELETE FROM `user_email_notification_cache` WHERE `date` <= NOW() - INTERVAL 1 DAY;";
        database_wrapper::instance()->run($sql_query);
    }
}
