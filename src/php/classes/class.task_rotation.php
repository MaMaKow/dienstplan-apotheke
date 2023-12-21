<?php

/*
 * Copyright (C) 2017 Mandelkow
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
 * task_rotation allows to appoint tasks to employees on a regular rotating basis.
 *
 * <p>
 * The absence of employees is regarded.
 * Also the abilities of the employees and their principle branch are considered.
 * Until now, only compounding can be used as a task.
 * </p>
 */
abstract class task_rotation {

    /**
     * @var MAX_FUTURE_WEEKS <p>The maximum number of weeks to be planned into the future.</p>
     */
    const MAX_FUTURE_WEEKS = 4;

    public static function task_rotation_main($Dates_unix, $task, $branch_id) {
        global $workforce;
        $number_of_filled_days = 0;
        $weekly_rotation_div_html = "<div id='weekly_rotation'>\n";
        $weekly_rotation_div_html .= "<h2>" . $task . "</h2>\n";
        $configuration = new \PDR\Application\configuration();
        $locale = $configuration->getLanguage();
        $weekdayFormatter = new IntlDateFormatter($locale, IntlDateFormatter::FULL, IntlDateFormatter::NONE);
        $weekdayFormatter->setPattern('EEE'); // 'EEEE' represents the full weekday name
        foreach ($Dates_unix as $date_unix) {
            $rotation_employee_key = self::task_rotation_get_worker($date_unix, $task, $branch_id);
            $dateString = $weekdayFormatter->format($date_unix);
            $weekly_rotation_div_html .= $dateString . ": ";
            if (NULL !== $rotation_employee_key) {
                $number_of_filled_days++;
                $weekly_rotation_div_html .= $workforce->List_of_employees[$rotation_employee_key]->last_name;
            }
            $weekly_rotation_div_html .= "<br>\n";
        }
        $weekly_rotation_div_html .= "</div>\n";
        if (0 === $number_of_filled_days) {
            return FALSE;
        }
        return $weekly_rotation_div_html;
    }

    private static function task_rotation_get_worker($date_unix, $task, $branch_id) {
        $date_sql = date("Y-m-d", $date_unix);
        /*
         * We want the PTAs to take turns in the lab at a weekly basis.
         * We sort them by employee id and check for the last one to take his turn.
         * TODO: Are there other tasks, that are rotated between people? Is there a weekly, daily or monthly basis?
         */

        //database_wrapper::instance()->run("DELETE FROM `task_rotation` WHERE `date` > DATE_ADD(NOW(), INTERVAL " . task_rotation::MAX_FUTURE_WEEKS . " WEEK)");
        /*
         * Was this day already planned?
         */
        $rotation_employee_key = self::read_task_employee_from_database($task, $date_sql, $branch_id);
        if (NULL !== $rotation_employee_key) {
            $absenceCollection = PDR\Database\AbsenceDatabaseHandler::readAbsenteesOnDate($dateSql);

            if (FALSE === $absenceCollection->containsEmployeeKey($rotation_employee_key)) {
                return $rotation_employee_key;
            }
            /*
             * If an employee is absent, then he/she can obviously not take the task:
             */
            database_wrapper::instance()->run("DELETE FROM `task_rotation` WHERE `date` = :date", array('date' => $date_sql));
        }
        $rotation_employee_key = self::task_rotation_set_worker($date_unix, $task, $branch_id);
        if (!empty($rotation_employee_key)) {
            return $rotation_employee_key;
        }
        return NULL;
    }

    /**
     * @param int $date_unix The date as a unix time stamp.
     * @param string $task The task that is to be rotated.
     * @return int $rotation_employee_key A worker for a given day and task.
     */
    private static function task_rotation_set_worker($date_unix, $task, $branch_id) {
        /*
         * If nobody is stored to do a task. Then we have to decide, whos is up to do it.
         */
        if ($date_unix < time()) {
            /*
             * We will not change the past anymore.
             */
            return FALSE;
        }
        global $workforce;
        $date_sql = date("Y-m-d", $date_unix);
        $rotation_employee_key = NULL;
        /*
         * Make a list of people who can do the task:
         * Currently only compounding is a task.
         */
        $List_of_compounding_rotation_employees = array();
        foreach ($workforce->List_of_compounding_employees as $employee_key) {
            if ($workforce->List_of_employees[$employee_key]->get_principle_branch_id() == $branch_id) {
                $List_of_compounding_rotation_employees[$employee_key] = $employee_key;
            }
        }
        if (array() === $List_of_compounding_rotation_employees) {
            return FALSE;
        }
        $task_workers_count = count($List_of_compounding_rotation_employees);

        /*
         * Get the last date when someone was assigned to this task:
         * From that point onwards, we will assign people to the task.
         * The assigning will take place on multiple days until the $date_sql.
         */
        $sql_query = "SELECT * FROM `task_rotation` WHERE `date` <= :date and `task` = :task and `branch_id` = :branch_id ORDER BY `date` DESC LIMIT 1";
        $result = database_wrapper::instance()->run($sql_query, array('date' => $date_sql, 'task' => $task, 'branch_id' => $branch_id));
        $row = $result->fetch(PDO::FETCH_OBJ);
        if (empty($row->date)) {
            if (NULL === $List_of_compounding_rotation_employees) {
                return FALSE;
            }
            /*
             * If there is noone anywhere in the past we just take the first person in the array.
             */
            $rotation_employee_key = min($List_of_compounding_rotation_employees);
            $sql_query = "INSERT INTO `task_rotation` (`task`, `date`, `employee_key`, `branch_id`) VALUES (:task, :date, :employee_key, :branch_id)";
            database_wrapper::instance()->run($sql_query, array(
                'task' => $task,
                'date' => $date_sql,
                'branch_id' => $branch_id,
                'employee_key' => $rotation_employee_key
            ));
            return $rotation_employee_key;
        }

        $temp_date_object = new DateTime($row->date);
        $stop_time_object = new DateTime();
        $stop_time_object->setTimestamp($date_unix);
        for ($temp_date_object->add(new DateInterval('P1D')); $temp_date_object <= $stop_time_object; $temp_date_object->add(new DateInterval('P1D'))) {
            $latest_allowed_date_object = new DateTime('today');
            $latest_allowed_date_object->add(new DateInterval('P' . task_rotation::MAX_FUTURE_WEEKS . 'W'));
            if ($temp_date_object > $latest_allowed_date_object) {
                /*
                 * This value is only calculated and stored in the database,
                 * if it is in the past or in the near future.
                 * This is to make sure, that fresh absences and new employees can be regarded.
                 * In the case of far future. An empty value is returned.
                 */
                return NULL;
            }

            $from_date_object = clone $temp_date_object;
            $from_date_object->setISODate($from_date_object->format("Y"), $from_date_object->format("W"), 0); //Sunday
            $from_date_object->sub(new DateInterval('P' . $task_workers_count . 'W')); //Sunday $task_workers_count weeks ago
            $to_date_object = clone $temp_date_object;
            $to_date_object->setISODate($to_date_object->format("Y"), $to_date_object->format("W"), 0); //Sunday
            /*
             * Remove absent employees for this day from the list of current available rotation employees:
             */
            $absenceCollection = PDR\Database\AbsenceDatabaseHandler::readAbsenteesOnDate($temp_date_object->format('Y-m-d'));
            $List_of_current_compounding_rotation_employees = array_diff($List_of_compounding_rotation_employees, $absenceCollection->getListOfEmployeeKeys());
            if (array() === $List_of_current_compounding_rotation_employees) {
                /*
                 * There is nobody here today to do the task.
                 */
                return FALSE;
            }
            $Done_rotation_count = self::read_done_rotation_count_from_database($List_of_current_compounding_rotation_employees, $from_date_object->format('Y-m-d'), $to_date_object->format('Y-m-d'));
            if (array() === $Done_rotation_count) {
                $next_rotation_employee_key = current($List_of_current_compounding_rotation_employees);
            } else {
                $next_rotation_employee_key = current(array_keys($Done_rotation_count, min($Done_rotation_count)));
            }
            /**
             * Take the employee, who did the task the least in the last weeks:
             * min($Done_rotation_count) is the minimum number someone did the task.
             * array_keys($Done_rotation_count, min($Done_rotation_count) is the employee_key(s) as an array of all the employees, who worked the least.
             * current() just takes one of those least task-working employees.
             */
            if (!empty($next_rotation_employee_key)) {
                $rotation_employee_key = $next_rotation_employee_key;
            }
            self::write_task_employee_to_database($task, $temp_date_object->format('Y-m-d'), $branch_id, $rotation_employee_key);
        }
        return $rotation_employee_key;
    }

    private static function read_done_rotation_count_from_database($List_of_compounding_rotation_employees, $from_date_sql, $to_date_sql) {
        $Done_rotation_count = array();
        foreach ($List_of_compounding_rotation_employees as $employee_key) {
            $Done_rotation_count[$employee_key] = 0;
            $sql_query = "SELECT `employee_key`, COUNT(`date`) as `count`"
                    . "FROM `task_rotation` "
                    . "WHERE "
                    . "`employee_key` = :employee_key "
                    . "AND `date` > :date_from "
                    . "AND `date` < :date_to ";
            $result = database_wrapper::instance()->run($sql_query, array(
                'employee_key' => $employee_key,
                'date_from' => $from_date_sql,
                'date_to' => $to_date_sql
            ));
            $row = $result->fetch(PDO::FETCH_OBJ);
            if (!empty($row->count)) {
                $Done_rotation_count[$row->employee_key] = $row->count;
            }
        }
        asort($Done_rotation_count);
        return $Done_rotation_count;
    }

    public static function build_html_task_rotation_select($task, $date_sql, $branch_id) {
        self::task_handle_user_input();
        $task_employee_key = self::read_task_employee_from_database($task, $date_sql, $branch_id);
        global $workforce;
        if (NULL === $workforce) {
            $workforce = new workforce($date_sql);
        }
        $task_rotation_select_html = "";
        $task_rotation_select_html .= "<div id='task_rotation_select_div'>";
        $task_rotation_select_html .= "<p>" . localization::gettext($task) . "</p>";
        $task_rotation_select_html .= "<form>";
        $task_rotation_select_html .= "<input  name='task_rotation_task' type='hidden' value='$task'>";
        $task_rotation_select_html .= "<input  name='task_rotation_date' type='hidden' value='$date_sql'>";
        $task_rotation_select_html .= "<input  name='task_rotation_branch' type='hidden' value='$branch_id'>";
        $task_rotation_select_html .= "<select name='task_rotation_employee' onchange='this.form.submit()'>";
        /*
         * The empty option is necessary to enable the deletion of employees:
         */
        $task_rotation_select_html .= "<option value=''>&nbsp;</option>";
        if (isset($workforce->List_of_employees[$task_employee_key]->last_name) or !isset($task_employee_key)) {
            foreach ($workforce->List_of_compounding_employees as $employee_key) {
                $employee_object = $workforce->List_of_employees[$employee_key];
                if ($task_employee_key == $employee_key and NULL !== $task_employee_key) {
                    $task_rotation_select_html .= "<option value=$employee_key selected>" . $employee_object->first_name . " " . $employee_object->last_name . "</option>";
                } else {
                    $task_rotation_select_html .= "<option value=$employee_key>" . $employee_object->first_name . " " . $employee_object->last_name . "</option>";
                }
            }
        } else {
            /*
             * Unknown employee, probably someone from the past.
             */
            $task_rotation_select_html .= "<option value=$task_employee_key selected>" . $task_employee_key . " " . gettext("Unknown employee") . "</option>";
        }

        $task_rotation_select_html .= "</select>\n";
        $task_rotation_select_html .= "</form>";
        $task_rotation_select_html .= "</div><!-- id='task_rotation_select_div'-->";
        return $task_rotation_select_html;
    }

    private static function read_task_employee_from_database($task, $date_sql, $branch_id) {
        $sql_query = "SELECT * FROM `task_rotation` WHERE `task` = :task and `date` = :date and `branch_id` = :branch_id";
        $result = database_wrapper::instance()->run($sql_query, array('task' => $task, 'date' => $date_sql, 'branch_id' => $branch_id));
        $row = $result->fetch(PDO::FETCH_OBJ);
        if (empty($row->employee_key)) {
            return NULL;
        }
        $employee_key = $row->employee_key;
        return $employee_key;
    }

    /**
     *
     * @todo This should probably be a part of the page, not of the class.
     * @return boolean FALSE in case of missing data.
     */
    public static function task_handle_user_input() {
        $task = user_input::get_variable_from_any_input('task_rotation_task', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $date_sql = user_input::get_variable_from_any_input('task_rotation_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $branch_id = user_input::get_variable_from_any_input('task_rotation_branch', FILTER_SANITIZE_NUMBER_INT);
        $employee_key = user_input::get_variable_from_any_input('task_rotation_employee', FILTER_SANITIZE_NUMBER_INT);
        if (is_null($task) or is_null($date_sql) or is_null($branch_id) or is_null($employee_key)) {
            return FALSE;
        }
        if ('' === $task or '' === $date_sql or '' === $branch_id or '' === $employee_key) {
            return FALSE;
        }
        self::write_task_employee_to_database($task, $date_sql, $branch_id, $employee_key);
    }

    private static function write_task_employee_to_database($task, $date_sql, $branch_id, $employee_key) {
        if (NULL === $employee_key) {
            return FALSE;
        }
        $sql_query = "INSERT INTO `task_rotation` SET "
                . "`task` = :task, `date` = :date, `branch_id` = :branch_id, `employee_key` = :employee_key "
                . "ON DUPLICATE KEY UPDATE "
                . "`task` = :task2, `date` = :date2, `branch_id` = :branch_id2, `employee_key` = :employee_key2";
        $result = database_wrapper::instance()->run($sql_query, array(
            'task' => $task,
            'date' => $date_sql,
            'branch_id' => $branch_id,
            'employee_key' => $employee_key,
            'task2' => $task,
            'date2' => $date_sql,
            'branch_id2' => $branch_id,
            'employee_key2' => $employee_key
        ));
        return $result;
    }
}
