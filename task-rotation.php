<?php

/*
 * Copyright (C) 2017 Mandelkow
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

function task_rotation_main($Dates, $task) {
    global $Mitarbeiter;
    $weekly_rotation_div_html = "<div id='weekly_rotation'>\n";
    $weekly_rotation_div_html .= $task . ":<br>\n";
    foreach ($Dates as $date) {
        unset($rotation_vk);
        $rotation_vk = task_rotation_get_worker($date, $task);
        $weekly_rotation_div_html .= strftime("%a", $date) . ": ";
        $weekly_rotation_div_html .= $Mitarbeiter[$rotation_vk] . "<br>\n";
    }
    $weekly_rotation_div_html .= "</div>\n";
    return $weekly_rotation_div_html;
}

function task_rotation_get_worker($date_unix, $task) {
    $date_sql = date("Y-m-d", $date_unix);
    global $Mitarbeiter;
    //We want the PTAs to take turns in the lab at a weekly basis.
    //We sort them by VK number and check for the last one to take his turn.
    //TODO: Are there other tasks, that are rotated between people? Is there a weekly, daily or monthly basis?
    //Setup a table in the database:
    $abfrage = "CREATE TABLE IF NOT EXISTS "
            . "`apotheke`.`task_rotation_daily` ( "
            . "`date` DATE NOT NULL , "
            . "`task` VARCHAR(64) NOT NULL , "
            . "`VK` TINYINT NOT NULL , "
            . "PRIMARY KEY (`date`,`task`)) "
            . "ENGINE = InnoDB;";
    $ergebnis = mysqli_query_verbose($abfrage);
    $abfrage = "CREATE TABLE IF NOT EXISTS "
            . "`apotheke`.`task_rotation_weekly` ( "
            . "`week` TINYINT NOT NULL , "
            . "`year` SMALLINT NOT NULL , "
            . "`task` VARCHAR(64) NOT NULL , "
            . "`VK` TINYINT NOT NULL , "
            . "PRIMARY KEY (`week`,`year`,`task`)) "
            . "ENGINE = InnoDB;";
    $ergebnis = mysqli_query_verbose($abfrage);

    //Was this day already planned?
    $abfrage = "SELECT * FROM `task_rotation` WHERE `task` = '$task' and `date` = '$date_sql'";
    $ergebnis = mysqli_query_verbose($abfrage);
    $row = mysqli_fetch_object($ergebnis);
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
 * @return int $rotation_vk a worker for a given day and task.
 */

function task_rotation_set_worker($date_unix, $task) {
    global $Rezeptur_Mitarbeiter;
    reset($Rezeptur_Mitarbeiter);
    $date_sql = date("Y-m-d", $date_unix);
    $task_workers_count = count($Rezeptur_Mitarbeiter);
    //echo "Anzahl der Rezeptare: " . $task_workers_count . "<br>\n";
    //echo "\$date_sql: " . $date_sql . "<br>\n";

    $abfrage = "SELECT * FROM `task_rotation` WHERE `date` <= '$date_sql' and `task` = '$task' ORDER BY `date` DESC LIMIT 1";
    $ergebnis = mysqli_query_verbose($abfrage);
    $row = mysqli_fetch_object($ergebnis);
    $last_date = $row->date;
    //If nobody is stored to do a task. Then we have to decide, whos is up to do it.
    $last_date_unix = strtotime($last_date);
    //echo "last_date: $last_date<br>\n";
    for ($temp_date = strtotime(' +1 day', $last_date_unix); $temp_date <= $date_unix; $temp_date = strtotime(' +1 day', $temp_date)) {
        //echo "Let us think about " . date("d.m.Y", $temp_date) . "<br>\n";
        $from_date_sql = date("Y-m-d", strtotime("- $task_workers_count WEEKS SUNDAY", $temp_date));
        $to_date_sql = date("Y-m-d", strtotime("- 1 WEEKS SUNDAY", $temp_date));
        //echo "\$from_date_sql: $from_date_sql, \$to_date_sql: $to_date_sql<br>\n";
        $temp_date_sql = date("Y-m-d", $temp_date);
        //$abfrage = "SELECT VK FROM `task_rotation` WHERE `date` >= '$from_date_sql' AND `date` < '$date_sql'  GROUP BY VK ORDER BY COUNT(date) ASC, VK DESC LIMIT 1";
        //$abfrage = "SELECT `mitarbeiter`.`VK`, COUNT(date) FROM `task_rotation` RIGHT JOIN mitarbeiter ON `task_rotation`.`VK` = `mitarbeiter`.`VK` WHERE `mitarbeiter`.`VK` IN (7, 12, 16) GROUP BY `mitarbeiter`.VK ORDER BY `mitarbeiter`.`VK`, COUNT(`task_rotation`.date)";
        /* $abfrage = "SELECT `mitarbeiter`.`VK`, COUNT(`date`) "
          . "FROM `task_rotation` RIGHT JOIN `mitarbeiter` "
          . "ON `task_rotation`.`VK` = `mitarbeiter`.`VK` "
          . "WHERE `mitarbeiter`.`VK` IN (" . implode(",", array_keys($Rezeptur_Mitarbeiter)) . ") "
          . "AND ( `task_rotation`.`date` IS NULL "
          . "OR (`task_rotation`.`date` >= '$from_date_sql' "
          . "AND `task_rotation`.`date` < '$to_date_sql')) "
          . "GROUP BY `mitarbeiter`.`VK` "
          . "ORDER BY COUNT(`task_rotation`.`date`) ASC, `mitarbeiter`.`VK` ASC "
          . "LIMIT 1";
         * 
         */
        foreach ($Rezeptur_Mitarbeiter as $vk => $name) {
            $abfrage = "SELECT `VK`, COUNT(`date`) as `count`"
                    . "FROM `task_rotation` "
                    . "WHERE `VK` = '$vk' "
                    . "AND `date` > '$from_date_sql' "
                    . "AND `date` < '$to_date_sql' "
                    . "GROUP BY `VK` "
                    . "ORDER BY COUNT(`date`) ASC, `VK` ASC ";

            //echo "$abfrage<br>\n";
            $ergebnis = mysqli_query_verbose($abfrage);
            $row = mysqli_fetch_object($ergebnis);
            if (!empty($row->count)) {
                $Rezeptur_Count[$vk] = $row->count;
            } else {
                $Rezeptur_Count[$vk] = 0;
            }
        }
        reset($Rezeptur_Mitarbeiter);
        $next_VK = current(array_keys($Rezeptur_Count, min($Rezeptur_Count)));
//        echo "\$next_VK: $next_VK<br>\n";
        if (!empty($next_VK)) {
            $run_iterator = 0;
            while (key($Rezeptur_Mitarbeiter) != $next_VK and $run_iterator++ < count($Rezeptur_Mitarbeiter)) {
                //echo "key(\$Rezeptur_Mitarbeiter): " . key($Rezeptur_Mitarbeiter)."<br>\n";
                next($Rezeptur_Mitarbeiter);
            }
            $rotation_vk = key($Rezeptur_Mitarbeiter); //will be overwritten if not present on thet day because of illnes or holidays
            //          echo "\$rotation_vk: $rotation_vk<br>\n";

            list($Abwesende, $Urlauber, $Kranke) = db_lesen_abwesenheit($temp_date_sql);

            //echo "<pre>"; var_export($Abwesende); echo "</pre><br>";
            //In case the person is ill or on holidays, someone else has to take the turn:
            if (isset($Abwesende) and array_search($rotation_vk, $Abwesende) !== false) {
                $Standard_rotation_vk = $rotation_vk;
                if (empty(array_diff(array_keys($Rezeptur_Mitarbeiter), $Abwesende))) {
                    $rotation_vk = NULL;
                    continue;
                }
                while (isset($Abwesende) and array_search($rotation_vk, $Abwesende) !== false) {
                    if (FALSE === next($Rezeptur_Mitarbeiter)) {
                        reset($Rezeptur_Mitarbeiter);
                    }
                    $rotation_vk = key($Rezeptur_Mitarbeiter); //overwrites previously defined value
                }
                //Get beack to the previous position for the next turn:
                while ($Standard_rotation_vk != key($Rezeptur_Mitarbeiter)) {
                    if (FALSE === prev($Rezeptur_Mitarbeiter)) {
                        end($Rezeptur_Mitarbeiter);
                    }
                }
            }
            $abfrage = "INSERT INTO `task_rotation` (`task`, `date`, `VK`) VALUES ('$task', '$temp_date_sql', '$rotation_vk')";
            //echo "$abfrage<br>\n";
            $ergebnis = mysqli_query_verbose($abfrage);
        } else {
            //If there is noone anywhere in the past we just take the first person in the array.
            $rotation_vk = key($Rezeptur_Mitarbeiter);
        }
        //echo "end of for loop ";
    }
    return $rotation_vk;
}

function task_rotation_configure_task($task) {
    //There needs to be a page to configure existing tasks or to install new tasks.
    $abfrage = "REPLACE INTO ...";
    $ergebnis = mysqli_query_verbose($abfrage);
    return FALSE;
}
