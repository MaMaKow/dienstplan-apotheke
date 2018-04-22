<?php

class absence {
    /*
     * This function gets a ist of absent employees
     *
     * @param date_sql string date in the format 'Y-m-d' a unix date is accepted. This might be removed in the future
     *
     * @return array $Absentees array(employee_id => reason)
     */

    public static function read_absentees_from_database($date_sql) {

        $Absentees = array();
        global $workforce;
        if (is_numeric($date_sql) && (int) $date_sql == $date_sql) {
            throw new Exception("\$date_sql has to be a string! $date_sql given.");
        }

        /*
         * We define a list of still existing coworkers. There might be workers in the database, that do not work anymore, but still have vacations registered in the database.
         * TODO: Build an option to delete future vacations of people when leaving.
         */
        if (!isset($workforce)) {
            throw new UnexpectedValueException("\$workforce must be set but was '$workforce'. ");
        }
        $mitarbeiterliste = implode(", ", array_keys($workforce->List_of_employees));

        $sql_query = "SELECT * FROM `absence` "
                . "WHERE `start` <= '$date_sql' "
                . "AND `end` >= '$date_sql' "
                . "AND `employee_id` IN (" . $mitarbeiterliste . ")"; //Employees, whose absence has started but not ended yet.
        /*
         * TODO: The above query does not discriminate between approved an non-approved vacations.
         */
        $result = mysqli_query_verbose($sql_query);
        while ($row = mysqli_fetch_object($result)) {
            $Absentees[$row->employee_id] = $row->reason;
        }
        return $Absentees;
    }

    public static function get_absence_data_specific($date_sql, $employee_id) {
        $query = "SELECT *
		FROM `absence`
		WHERE `start` <= '$date_sql' AND `end` >= '$date_sql' AND `employee_id` = '$employee_id'";
        $result = mysqli_query_verbose($query);
        while ($row = mysqli_fetch_object($result)) {
            $Absence['employee_id'] = $row->employee_id;
            $Absence['reason'] = $row->reason;
            $Absence['start'] = $row->start;
            $Absence['end'] = $row->end;
            $Absence['approval'] = $row->approval;
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

    public static function calculate_absence_days($start_date_string, $end_date_string) {
        $days = 0;
        for ($date_unix = strtotime($start_date_string); $date_unix <= strtotime($end_date_string); $date_unix = strtotime('+1 day', $date_unix)) {
            if (6 !== intval(date('w', $date_unix)) and 0 !== intval(date('w', $date_unix)) and FALSE === holidays::is_holiday($date_unix)) {
                $days++;
            }
        }
        return $days;
    }

}
