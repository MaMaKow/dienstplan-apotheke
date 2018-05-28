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

abstract class task_rotation {

    public static function task_rotation_main($Dates_unix, $task, $branch_id) {
        global $workforce;
        $weekly_rotation_div_html = "<div id='weekly_rotation'>\n";
        $weekly_rotation_div_html .= $task . ":<br>\n";
        foreach ($Dates_unix as $date_unix) {
            unset($rotation_employee_id);
            $rotation_employee_id = self::task_rotation_get_worker($date_unix, $task, $branch_id);
            $weekly_rotation_div_html .= strftime("%a", $date_unix) . ": ";
            if (NULL !== $rotation_employee_id) {
                $weekly_rotation_div_html .= $workforce->List_of_employees[$rotation_employee_id]->last_name;
            }
            $weekly_rotation_div_html .= "<br>\n";
        }
        $weekly_rotation_div_html .= "</div>\n";
        return $weekly_rotation_div_html;
    }

    private static function task_rotation_get_worker($date_unix, $task, $branch_id) {
        $date_sql = date("Y-m-d", $date_unix);
        global $workforce;
        //We want the PTAs to take turns in the lab at a weekly basis.
        //We sort them by VK number and check for the last one to take his turn.
        //TODO: Are there other tasks, that are rotated between people? Is there a weekly, daily or monthly basis?

        database_wrapper::instance()->run("DELETE FROM `task_rotation` WHERE `date` > NOW()");
        //Was this day already planned?
        $sql_query = "SELECT * FROM `task_rotation` WHERE `task` = :task and `date` = :date";
        $result = database_wrapper::instance()->run($sql_query, array('task' => $task, 'date' => $date_sql));
        $row = $result->fetch(PDO::FETCH_OBJ);
        if (!empty($row->task)) {
            $rotation_employee_id = $row->VK;
            return $rotation_employee_id;
        } else {
            $rotation_employee_id = self::task_rotation_set_worker($date_unix, $task, $branch_id);
            if (!empty($rotation_employee_id)) {
                return $rotation_employee_id;
            }
        }
        return NULL;
    }

    /*
     * @param int $date_unix The date as a unix time stamp.
     * @param string $task The task that is to be rotated.
     * @return int $rotation_employee_id A worker for a given day and task.
     */

    private static function task_rotation_set_worker($date_unix, $task, $branch_id) {
        if ($date_unix < time()) {
            /*
             * We will not change the past anymore.
             */
            return FALSE;
        }
        global $workforce;
        foreach ($workforce->List_of_compounding_employees as $employee_id) {
            if ($workforce->List_of_employees[$employee_id]->principle_branch_id == $branch_id) {
                $Rezeptur_Mitarbeiter[$employee_id] = $employee_id;
            }
        }
        reset($Rezeptur_Mitarbeiter);
        $date_sql = date("Y-m-d", $date_unix);
        $task_workers_count = count($Rezeptur_Mitarbeiter);

        $sql_query = "SELECT * FROM `task_rotation` WHERE `date` <= :date and `task` = :task ORDER BY `date` DESC LIMIT 1";
        $result = database_wrapper::instance()->run($sql_query, array('date' => $date_sql, 'task' => $task));
        $row = $result->fetch(PDO::FETCH_OBJ);
        if (!empty($row->date)) {
            $last_date = $row->date;
            //If nobody is stored to do a task. Then we have to decide, whos is up to do it.
            $last_date_unix = strtotime($last_date);
            for ($temp_date = strtotime(' +1 day', $last_date_unix); $temp_date <= $date_unix; $temp_date = strtotime(' +1 day', $temp_date)) {
                $from_date_sql = date("Y-m-d", strtotime("- $task_workers_count WEEKS SUNDAY", $temp_date));
                $to_date_sql = date("Y-m-d", strtotime("- 1 WEEKS SUNDAY", $temp_date));
                $temp_date_sql = date("Y-m-d", $temp_date);
                foreach ($Rezeptur_Mitarbeiter as $vk) {
                    $sql_query = "SELECT `VK`, COUNT(`date`) as `count`"
                            . "FROM `task_rotation` "
                            . "WHERE `VK` = :employee_id "
                            . "AND `date` > :date_from "
                            . "AND `date` < :date_to "
                            . "GROUP BY `VK` "
                            . "ORDER BY COUNT(`date`) ASC, `VK` ASC ";
                    $result = database_wrapper::instance()->run($sql_query, array(
                        'employee_id' => $vk,
                        'date_from' => $from_date_sql,
                        'date_to' => $to_date_sql
                    ));
                    $row = $result->fetch(PDO::FETCH_OBJ);
                    if (!empty($row->count)) {
                        $Rezeptur_Count[$vk] = $row->count;
                    } else {
                        $Rezeptur_Count[$vk] = 0;
                    }
                }
                reset($Rezeptur_Mitarbeiter);
                $next_VK = current(array_keys($Rezeptur_Count, min($Rezeptur_Count)));
                if (!empty($next_VK)) {
                    $run_iterator = 0;
                    while (current($Rezeptur_Mitarbeiter) != $next_VK and $run_iterator++ < count($Rezeptur_Mitarbeiter)) {
                        next($Rezeptur_Mitarbeiter);
                    }
                    $rotation_employee_id = current($Rezeptur_Mitarbeiter); //will be overwritten if not present on thet day because of illnes or holidays

                    $Abwesende = absence::read_absentees_from_database($temp_date_sql);

                    //In case the person is ill or on holidays, someone else has to take the turn:
                    if (isset($Abwesende[$rotation_employee_id])) {
                        //$Standard_rotation_vk = $rotation_employee_id;
                        if (empty(array_diff(array_keys($Rezeptur_Mitarbeiter), array_keys($Abwesende)))) {
                            //There is nobody working:
                            $rotation_employee_id = NULL;
                            continue;
                        }
                        while (isset($Abwesende[$rotation_employee_id])) {
                            if (FALSE === next($Rezeptur_Mitarbeiter)) {
                                reset($Rezeptur_Mitarbeiter);
                            }
                            $rotation_employee_id = current($Rezeptur_Mitarbeiter); //overwrites previously defined value
                        }
                    }
                    if (time() > $temp_date) {
                        /*
                         * This value is only stored in the database, if it is in the past.
                         * This is to make sure, that fresh absences can be regarded.
                         */
                        $sql_query = "INSERT INTO `task_rotation` (`task`, `date`, `VK`) VALUES (:task, :date, :employee_id)";
                        database_wrapper::instance()->run($sql_query, array(
                            'task' => $task,
                            'date' => $temp_date_sql,
                            'employee_id' => $rotation_employee_id
                        ));
                    }
                }
            }
            return $rotation_employee_id;
        } else {
            //If there is noone anywhere in the past we just take the first person in the array.
            $rotation_employee_id = min($Rezeptur_Mitarbeiter);
            $sql_query = "INSERT INTO `task_rotation` (`task`, `date`, `VK`) VALUES (:task, :date, :employee_id)";
            database_wrapper::instance()->run($sql_query, array(
                'task' => $task,
                'date' => $date_sql,
                'employee_id' => $rotation_employee_id
            ));
        }
        return $rotation_employee_id;
    }

}
