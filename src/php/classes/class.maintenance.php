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
     *
     * @var int <p>unix time stamp of the last execution time of the maintenance functions</p>
     */
    private $last_execution = 0;

    public function __construct() {
        $sql_query = "SELECT UNIX_TIMESTAMP(`last_execution`) as `last_execution_unix` from `maintenance`";
        $result = database_wrapper::instance()->run($sql_query);
        $row = $result->fetch(PDO::FETCH_OBJ);
        if (FALSE !== $row) {
            $this->last_execution = $row->last_execution_unix;
        } else {
            $sql_query = "INSERT INTO `maintenance` SET `last_execution` =  FROM_UNIXTIME(:time)";
            database_wrapper::instance()->run($sql_query, array('time' => time()));
        }
        if ($this->last_execution < time() - PDR_ONE_DAY_IN_SECONDS) {
            $this->cleanup_absence();
            //$this->cleanup_overtime();
            $sql_query = "UPDATE `maintenance` SET `last_execution` = FROM_UNIXTIME(:time)";
            database_wrapper::instance()->run($sql_query, array('time' => time()));
        }
    }

    private function cleanup_absence() {
        /*
         * Cleanup absence data of employees, who left the company:
         */
        $sql_query = "DELETE `absence` FROM `absence` LEFT JOIN `employees` ON `employees`.`id`= `absence`.`employee_id` WHERE `employees`.`end_of_employment` < `absence`.`start`";
        database_wrapper::instance()->run($sql_query);
        /*
         * TODO: Cleanup absences of existing employees, that happened before they entered the company.
         * Those are from former employees with the same employee_id
         * Take care, not to delete data from employees with unkown employment start/end date
         */
    }

    private function cleanup_overtime() {
        /*
         * Cleanup overtime data of employees, who left the company:
         */
        $sql_query = "";
        database_wrapper::instance()->run($sql_query);
        /*
         * TODO: Cleanup absences of existing employees, that happened before they entered the company.
         * Those are from former employees with the same employee_id
         * Take care, not to delete data from employees with unkown employment start/end date
         */
    }

}
