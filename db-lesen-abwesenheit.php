<?php

//TODO: We might rename this file /src/php/absence-management.php
/*
 * This script gets a ist of absent employees
 *
 * @param date string date in the format 'Y-m-d' a unix date is accepted. This might be removed in the future
 *
 * @return array $Absentees array(employee_id => reason)
 */
function db_lesen_abwesenheit($date) {
    global $List_of_employees;
    //Im folgenden prüfen wir, ob $datum bereis als UNIX timestamp vorliegt. Wenn es ein Timestamp ist, können wir direkt in 'Y-m-d' umrechnen. Wenn nicht, dann wandeln wir vorher um.
    if (is_numeric($date) && (int) $date == $date) {
        $sql_date = date('Y-m-d', $date);
    } else {
        $sql_date = date('Y-m-d', strtotime($date));
    }

    //We define a list of still existing coworkers. There might be workers in the database, that do not work anymore, but still have vacations registered in the database.
    //TODO: Build an option to delete future vacations of people when leaving.
    if (!isset($List_of_employees)) {
        require "db-lesen-mitarbeiter.php";
    }
    $mitarbeiterliste = implode(", ", array_keys($List_of_employees));

    $sql_query = "SELECT * FROM `absence` "
            . "WHERE `start` <= '$sql_date' "
            . "AND `end` >= '$sql_date' "
            . "AND `employee_id` IN (" . $mitarbeiterliste . ")"; //Employees, whose absence has started but not ended yet.
    //TODO: The above query does not discriminate between approved an non-approved vacations.
    $result = mysqli_query_verbose($sql_query);
    while ($row = mysqli_fetch_object($result)) {
        $Absentees[$row->employee_id] = $row->reason;
    }
    return $Absentees;
}

function get_absence_data_specific($date_sql, $employee_id) {
    $query = "SELECT *
		FROM `absence`
		WHERE `start` <= '$date_sql' AND `end` >= '$date_sql' AND `employee_id` = '$employee_id'";
    $result = mysqli_query_verbose($query);
    while ($row = mysqli_fetch_object($result)) {
        $Absence['employee_id'] = $row->employee_id;
        $Absence['reason'] = $row->reason;
        $Absence['start'] = $row->start;
        $Absence['end'] = $row->end;
    }
    return $Absence;
}

/*
  function get_all_absence_data_in_period($start_date_sql, $end_date_sql) {
  $query = "SELECT *
  FROM `absence`
  WHERE `start` <= '$start_date_sql' AND `end` >= '$end_date_sql'";
  $result = mysqli_query_verbose($query);
  while ($row = mysqli_fetch_object($result)) {
  $Absences[]['employee_id'] = $row->employee_id;
  $Absences[]['reason'] = $row->reason;
  $Absences[]['start'] = $row->start;
  $Absences[]['end'] = $row->end;
  }
  return $Absences;
  }
 */

function calculate_absence_days($start_date_string, $end_date_string) {
    if (!function_exists('is_holiday')) {
        require 'src/php/calculate-holidays.php';
    }
    $days = 0;
    for ($date_unix = strtotime($start_date_string); $date_unix <= strtotime($end_date_string); $date_unix = strtotime('+1 day', $date_unix)) {
        if (6 !== intval(date('w', $date_unix)) and 0 !== intval(date('w', $date_unix)) and FALSE === is_holiday($date_unix)) {
            $days++;
        }
    }
    return $days;
}
