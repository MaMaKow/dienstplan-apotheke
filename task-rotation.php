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

function task_rotation_main($Dates_unix, $task) {
    global $List_of_employees;
    $weekly_rotation_div_html = "<div id='weekly_rotation'>\n";
    $weekly_rotation_div_html .= $task . ":<br>\n";
    foreach ($Dates_unix as $date_unix) {
        unset($rotation_vk);
        $rotation_vk = task_rotation_get_worker($date_unix, $task);
        $weekly_rotation_div_html .= strftime("%a", $date_unix) . ": ";
        $weekly_rotation_div_html .= $List_of_employees[$rotation_vk] . "<br>\n";
    }
    $weekly_rotation_div_html .= "</div>\n";
    return $weekly_rotation_div_html;
}

function task_rotation_get_worker($date_unix, $task) {
    $date_sql = date("Y-m-d", $date_unix);
    global $List_of_employees;
    //We want the PTAs to take turns in the lab at a weekly basis.
    //We sort them by VK number and check for the last one to take his turn.
    //TODO: Are there other tasks, that are rotated between people? Is there a weekly, daily or monthly basis?
    //Setup a table in the database:
    $sql_query = "CREATE TABLE IF NOT EXISTS "
            . "`task_rotation` ( "
            . "`date` DATE NOT NULL , "
            . "`task` VARCHAR(64) NOT NULL , "
            . "`VK` TINYINT NOT NULL , "
            . "PRIMARY KEY (`date`,`task`)) "
            . "ENGINE = InnoDB;";
    $result = mysqli_query_verbose($sql_query);

    //Was this day already planned?
    $sql_query = "SELECT * FROM `task_rotation` WHERE `task` = '$task' and `date` = '$date_sql'";
    $result = mysqli_query_verbose($sql_query);
    $row = mysqli_fetch_object($result);
    if (!empty($row->task)) {
        $rotation_vk = $row->VK;
        return $rotation_vk;
    } else {
        $rotation_vk = task_rotation_set_worker($date_unix, $task);
        if (!empty($rotation_vk)) {
            return $rotation_vk;
        }
    }
    return NULL;
}

/*
 * @param int $date_unix The date as a unix time stamp.
 * @param string $task The task that is to be rotated.
 * @return int $rotation_vk A worker for a given day and task.
 */

function task_rotation_set_worker($date_unix, $task) {
    global $Rezeptur_Mitarbeiter;
    reset($Rezeptur_Mitarbeiter);
    $date_sql = date("Y-m-d", $date_unix);
    $task_workers_count = count($Rezeptur_Mitarbeiter);

    $sql_query = "SELECT * FROM `task_rotation` WHERE `date` <= '$date_sql' and `task` = '$task' ORDER BY `date` DESC LIMIT 1";
    $result = mysqli_query_verbose($sql_query);
    $row = mysqli_fetch_object($result);
    if (!empty($row->date)) {
        $last_date = $row->date;
        //If nobody is stored to do a task. Then we have to decide, whos is up to do it.
        $last_date_unix = strtotime($last_date);
        for ($temp_date = strtotime(' +1 day', $last_date_unix); $temp_date <= $date_unix; $temp_date = strtotime(' +1 day', $temp_date)) {
            $from_date_sql = date("Y-m-d", strtotime("- $task_workers_count WEEKS SUNDAY", $temp_date));
            $to_date_sql = date("Y-m-d", strtotime("- 1 WEEKS SUNDAY", $temp_date));
            $temp_date_sql = date("Y-m-d", $temp_date);
            foreach ($Rezeptur_Mitarbeiter as $vk => $name) {
                $sql_query = "SELECT `VK`, COUNT(`date`) as `count`"
                        . "FROM `task_rotation` "
                        . "WHERE `VK` = '$vk' "
                        . "AND `date` > '$from_date_sql' "
                        . "AND `date` < '$to_date_sql' "
                        . "GROUP BY `VK` "
                        . "ORDER BY COUNT(`date`) ASC, `VK` ASC ";
                $result = mysqli_query_verbose($sql_query);
                $row = mysqli_fetch_object($result);
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
                while (key($Rezeptur_Mitarbeiter) != $next_VK and $run_iterator++ < count($Rezeptur_Mitarbeiter)) {
                    next($Rezeptur_Mitarbeiter);
                }
                $rotation_vk = key($Rezeptur_Mitarbeiter); //will be overwritten if not present on thet day because of illnes or holidays

                $Abwesende = db_lesen_abwesenheit($temp_date_sql);

                //In case the person is ill or on holidays, someone else has to take the turn:
                if (isset($Abwesende[$rotation_vk])) {
                    $Standard_rotation_vk = $rotation_vk;
                    if (empty(array_diff(array_keys($Rezeptur_Mitarbeiter), array_keys($Abwesende)))) {
                        //There is nobody working:
                        $rotation_vk = NULL;
                        continue;
                    }
                    while (isset($Abwesende[$rotation_vk])) {
                        if (FALSE === next($Rezeptur_Mitarbeiter)) {
                            reset($Rezeptur_Mitarbeiter);
                        }
                        $rotation_vk = key($Rezeptur_Mitarbeiter); //overwrites previously defined value
                    }
                }
                $sql_query = "INSERT INTO `task_rotation` (`task`, `date`, `VK`) VALUES ('$task', '$temp_date_sql', '$rotation_vk')";
                $result = mysqli_query_verbose($sql_query);
            }
        }
        return $rotation_vk;
    } else {
        //If there is noone anywhere in the past we just take the first person in the array.
        $rotation_vk = key($Rezeptur_Mitarbeiter);
        $sql_query = "INSERT INTO `task_rotation` (`task`, `date`, `VK`) VALUES ('$task', '$date_sql', '$rotation_vk')";
        $result = mysqli_query_verbose($sql_query);
    }
    return $rotation_vk;
}
