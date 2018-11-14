<?php

/*
 * Copyright (C) 2018 Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
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
 * @author Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class maintenance {

    /**
     * @var int MAINTENANCE_PERIOD_IN_SECONDS the minimum time between two maintenance executions
     */
    const MAINTENANCE_PERIOD_IN_SECONDS = PDR_ONE_DAY_IN_SECONDS;

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
             * Check agains a maximum size.
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
            $this->cleanup_absence();
            $this->cleanup_overtime();
            /*
             * __destruct():
             */
            /*
             * TODO: Can we delete the ", array('time' => time())" and change the SQL to:
             *     "UPDATE `maintenance` SET `last_execution` = NOW()"
             *     ? We do not actually need prepared statements then.
             */
            $sql_query = "UPDATE `maintenance` SET `last_execution` = FROM_UNIXTIME(:time)";
            database_wrapper::instance()->run($sql_query, array('time' => time()));
            $message = date('Y-m-d') . ': ' . 'Done with general maintenance.' . PHP_EOL;
            error_log($message, 3, PDR_FILE_SYSTEM_APPLICATION_PATH . 'maintenance.log');
        } else {
            $message = date('Y-m-d') . ': ' . 'Nothing to do for general maintenance.' . PHP_EOL;
            $message .= 'Time: ' . strftime('%c', time()) . PHP_EOL;
            $message .= 'Last execution: ' . strftime('%c', $this->last_execution) . PHP_EOL;
            $message .= 'Earliest next execution: ' . strftime('%c', $this->last_execution + self::MAINTENANCE_PERIOD_IN_SECONDS) . PHP_EOL;
            error_log($message, 3, PDR_FILE_SYSTEM_APPLICATION_PATH . 'maintenance.log');
        }
    }

    private function cleanup_absence() {
        /*
         * Cleanup absence data of employees, who left the company:
         */
        $sql_query = "SELECT * FROM `absence` LEFT JOIN `employees` ON `employees`.`id`= `absence`.`employee_id` WHERE `employees`.`end_of_employment` < `absence`.`start`";
        database_wrapper::instance()->run($sql_query);
        /*
         * TODO: Cleanup absences of existing employees, that happened before they entered the company.
         * Those are from former employees with the same employee_id
         * Take care, not to delete data from employees with unkown employment start/end date
         * Make an archive table to store the old data.
         */
    }

    private function cleanup_overtime() {
        /*
         * Cleanup overtime data of employees, who left the company:
         */
        $sql_query = "SELECT * FROM `Stunden` LEFT JOIN `employees` ON `employees`.`id` = `Stunden`.`VK` WHERE `employees`.`start_of_employment` > `Stunden`.`Datum`";
        database_wrapper::instance()->run($sql_query);
        /*
         * TODO: Cleanup overtime of existing employees, that happened before they entered the company.
         * Those are from former employees with the same employee_id
         * Take care, not to delete data from employees with unkown employment start/end date
         * Make an archive table to store the old data.
         */
    }

}
