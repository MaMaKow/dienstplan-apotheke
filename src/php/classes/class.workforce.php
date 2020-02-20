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
 * Description of class
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class workforce {

    /**
     * @var array List_of_workforce_objects <p>is an array of known workforce objects</p>
     */
    static private $List_of_workforce_objects = array();

    /**
     *
     * @var string $date_start_sql is the date string with which the object was instantiated. It is only stored for debugging purposes.
     */
    public $date_start_sql;

    /**
     *
     * @var string $date_end_sql is an optional date string with which the object was instantiated. It is only stored for debugging purposes.
     */
    public $date_end_sql;
    public $List_of_employees;
    public $List_of_qualified_pharmacist_employees;
    public $List_of_goods_receipt_employees;
    public $List_of_compounding_employees;

    public function __construct($date_start_sql = NULL, $date_end_sql = NULL) {
        $this->date_start_sql = $date_start_sql;
        $this->date_end_sql = $date_end_sql;
        if (isset(self::$List_of_workforce_objects[$this->date_start_sql][$this->date_end_sql])) {
            /*
             * If this exact workforce is known already, we do not have to repeat that queries.
             */
            $this->List_of_employees = self::$List_of_workforce_objects[$this->date_start_sql][$this->date_end_sql]->List_of_employees;
            $this->List_of_qualified_pharmacist_employees = self::$List_of_workforce_objects[$this->date_start_sql][$this->date_end_sql]->List_of_qualified_pharmacist_employees;
            $this->List_of_goods_receipt_employees = self::$List_of_workforce_objects[$this->date_start_sql][$this->date_end_sql]->List_of_goods_receipt_employees;
            $this->List_of_compounding_employees = self::$List_of_workforce_objects[$this->date_start_sql][$this->date_end_sql]->List_of_compounding_employees;

            return TRUE;
        }
        if (NULL === $date_start_sql) {
            $sql_query = 'SELECT * FROM `employees` '
                    . 'ORDER BY `id` ASC, ISNULL(`end_of_employment`) ASC, `end_of_employment` ASC;';
            $result = database_wrapper::instance()->run($sql_query);
        } else {
            if (NULL === $date_end_sql) {
                $date_end_sql = $date_start_sql;
            }
            $sql_query = 'SELECT * FROM `employees` '
                    . 'WHERE  (`end_of_employment` >= :date_start OR `end_of_employment` IS NULL) '
                    . 'AND  (`start_of_employment` <= :date_end OR `start_of_employment` IS NULL) '
                    . 'ORDER BY `id` ASC, ISNULL(`end_of_employment`) ASC, `end_of_employment` ASC;';
            $result = database_wrapper::instance()->run($sql_query, array('date_end' => $date_end_sql, 'date_start' => $date_start_sql));
        }
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $this->List_of_employees[$row->id] = new employee((int) $row->id, $row->last_name, $row->first_name, (float) $row->working_week_hours, (float) $row->lunch_break_minutes, $row->profession, (int) $row->branch, $row->start_of_employment, $row->end_of_employment, $row->holidays);
            $this->List_of_branch_employees[$row->branch][] = $row->id;
            if (in_array($row->profession, array('Apotheker', 'PI'))) {
                $this->List_of_qualified_pharmacist_employees[] = $row->id;
            }
            if (TRUE == $row->goods_receipt) {
                $this->List_of_goods_receipt_employees[] = $row->id;
            }
            if (TRUE == $row->compounding) {
                $this->List_of_compounding_employees[] = $row->id;
            }
        }
        self::$List_of_workforce_objects[$this->date_start_sql][$this->date_end_sql] = $this;
    }

    public function __set($name, $value) {
        if ('date_sql' === $name) {
            throw new Exception('$date_sql may only be given on __construct!');
        }
        $this->$name = $value;
    }

    /**
     * Get the last name of an employee
     *
     * @param int $employee_id
     * @return string <p>last name of chosen employee or '???' if the employee is not known.
     * For example if an emergency service is not yet chosen ($employee_id = NULL)</p>
     */
    public function get_employee_last_name($employee_id) {
        if (FALSE !== $this->get_employee_value($employee_id, 'last_name')) {
            return $this->get_employee_value($employee_id, 'last_name');
        }
        return $employee_id . '???';
    }

    /**
     * Get the profession of an employee
     *
     * @param int $employee_id
     * @return string profession of the chosen employee
     */
    public function get_employee_profession($employee_id) {
        if (FALSE !== $this->get_employee_value($employee_id, 'profession')) {
            return $this->get_employee_value($employee_id, 'profession');
        } else {
            throw new Exception('This employee does not exist!');
        }
    }

    public function get_employee_object($employee_id) {
        if ($this->List_of_employees[$employee_id] instanceof employee) {
            return $this->List_of_employees[$employee_id];
        }
        throw new Exception('This employee does not exist!');
    }

    private function get_employee_value($employee_id, $key) {
        if (isset($this->List_of_employees[$employee_id])) {
            if (isset($this->List_of_employees[$employee_id]->$key)) {
                return $this->List_of_employees[$employee_id]->$key;
            }
        }
        return FALSE;
    }

    public function get_list_of_employee_names() {
        $List_of_employee_last_names = array();
        foreach ($this->List_of_employees as $employee_id => $employee_object) {
            $List_of_employee_last_names[$employee_id] = $employee_object->last_name;
        }
        return $List_of_employee_last_names;
    }

    public function get_list_of_employee_professions() {
        $List_of_employee_professions = array();
        foreach ($this->List_of_employees as $employee_id => $employee_object) {
            $List_of_employee_professions[$employee_id] = $employee_object->profession;
        }
        return $List_of_employee_professions;
    }

    public static function get_first_start_of_employment($employee_id) {
        $sql_query = "SELECT min(`start_of_employment`) as `first_start_of_employment` "
                . " FROM `employees` "
                . " WHERE `id` = :employee_id";
        $result = database_wrapper::instance()->run($sql_query, array(
            'employee_id' => $employee_id,
        ));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            if (NULL === $row->first_start_of_employment) {
                $row->first_start_of_employment = "1970-01-01";
            }
            return new DateTime($row->first_start_of_employment);
        }
        return FALSE;
    }

}
