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
        list($in_placeholder, $IN_employees_list) = database_wrapper::create_placeholder_for_mysql_IN_function(array_keys($workforce->List_of_employees), TRUE);

        $sql_query = "SELECT * FROM `absence` "
                . "WHERE `start` <= :start "
                . "AND `end` >= :end "
                . "AND `employee_id` IN ($in_placeholder)"; //Employees, whose absence has started but not ended yet.
        /*
         * TODO: The above query does not discriminate between approved an non-approved vacations.
         */
        $result = database_wrapper::instance()->run($sql_query, array_merge($IN_employees_list, array('start' => $date_sql, 'end' => $date_sql)));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $Absentees[$row->employee_id] = $row->reason;
        }
        return $Absentees;
    }

    public static function get_absence_data_specific($date_sql, $employee_id) {
        $query = "SELECT *
		FROM `absence`
		WHERE `start` <= :start AND `end` >= :end AND `employee_id` = :employee_id";
        $result = database_wrapper::instance()->run($query, array('start' => $date_sql, 'end' => $date_sql, 'employee_id' => $employee_id));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
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
      WHERE `start` <= :start AND `end` >= :end";
      $result = database_wrapper::instance()->run($query, array('start'=>$start_date_sql,'end'=>$end_date_sql));
      while ($row = $result->fetch(PDO::FETCH_OBJ)) {
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
