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
    /*
     * TODO: This class is now used more often to create objects.
     *     It should be optimized.
     *     For example it could store an array of known workforce objects/arrays.
     */

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
    }

    public function __set($name, $value) {
        if ('date_sql' === $name) {
            throw new Exception('$date_sql may only be given on __construct!');
        }
        $this->$name = $value;
    }

    public function get_list_of_employee_names() {
        $List_of_employee_last_names = array();
        foreach ($this->List_of_employees as $employee_id => $employee_object) {
            $List_of_employee_last_names[$employee_id] = $employee_object->last_name;
        }
        return $List_of_employee_last_names;
    }

    public static function get_first_start_of_employment($employee_id) {
        $sql_query = "SELECT min(`start_of_employment`) as `first_start_of_employment` "
                . " FROM `employees` "
                . " WHERE `id` = :employee_id";
        $result = database_wrapper::instance()->run($sql_query, array(
            'employee_id' => $employee_id,
        ));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            return $row->first_start_of_employment;
        }
        return FALSE;
    }

}
