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
     * @todo <p lang=de>Sobald alle existierenden und ehemaligen employees mit ihrem eigenen primary_key in der Tabelle stehen,
     *  gibt es wahrscheinlich keinen Grund mehr, hier zu unterscheiden.
     * Dann können wir alle Mitarbeiter in eine Instanz dieses Objektes laden.
     * Diese Liste von verschiedenen workforces braucht es dann nicht mehr.</p>
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
    private static $List_of_short_descriptors;

    public function __construct(string $date_start_sql = NULL, string $date_end_sql = NULL) {
        $this->date_start_sql = $date_start_sql;
        $this->date_end_sql = $date_end_sql;
        if (isset(self::$List_of_workforce_objects[$this->date_start_sql][$this->date_end_sql])) {
            /**
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
                    . 'ORDER BY `last_name`, `first_name` ASC;';
            $result = database_wrapper::instance()->run($sql_query);
        } else {
            if (NULL === $date_end_sql) {
                $date_end_sql = $date_start_sql;
            }
            $sql_query = 'SELECT * FROM `employees` '
                    . 'WHERE  (`end_of_employment` >= :date_start OR `end_of_employment` IS NULL) '
                    . 'AND  (`start_of_employment` <= :date_end OR `start_of_employment` IS NULL) '
                    . 'ORDER BY `last_name`, `first_name` ASC;';
            $result = database_wrapper::instance()->run($sql_query, array('date_end' => $date_end_sql, 'date_start' => $date_start_sql));
        }
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $this->List_of_employees[$row->primary_key] = new employee((int) $row->primary_key, $row->last_name, $row->first_name, (float) $row->working_week_hours, (float) $row->lunch_break_minutes, $row->profession, $row->compounding, $row->goods_receipt, (int) $row->branch, $row->start_of_employment, $row->end_of_employment, $row->holidays);
            $this->List_of_branch_employees[$row->branch][] = $row->primary_key;
            if (in_array($row->profession, array('Apotheker', 'PI'))) {
                $this->List_of_qualified_pharmacist_employees[] = $row->primary_key;
            }
            if (TRUE == $row->goods_receipt) {
                $this->List_of_goods_receipt_employees[] = $row->primary_key;
            }
            if (TRUE == $row->compounding) {
                $this->List_of_compounding_employees[] = $row->primary_key;
            }
        }
        $this->create_list_of_short_descriptors();
        self::$List_of_workforce_objects[$this->date_start_sql][$this->date_end_sql] = $this;
    }

    /**
     * @todo Get rid of this function!
      public function __set($name, $value) {
      if ('date_sql' === $name) {
      throw new Exception('$date_sql may only be given on __construct!');
      }
      $this->$name = $value;
      }
     */

    /**
     * Get the last name of an employee
     *
     * @param int $employee_key
     * @return string <p>last name of chosen employee or '???' if the employee is not known.
     * For example if an emergency service is not yet chosen ($employee_key = NULL)</p>
     */
    public function get_employee_last_name(int $employee_key) {
        if (FALSE !== $this->get_employee_value($employee_key, 'last_name')) {
            return $this->get_employee_value($employee_key, 'last_name');
        }
        return $employee_key . '???';
    }

    private function get_list_of_all_employees() {
        $sql_query = 'SELECT * FROM `employees` ORDER BY `last_name`, `first_name` ASC;';
        $result = database_wrapper::instance()->run($sql_query);
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $List_of_all_employees[$row->primary_key] = new employee((int) $row->primary_key, $row->last_name, $row->first_name, (float) $row->working_week_hours, (float) $row->lunch_break_minutes, $row->profession, $row->compounding, $row->goods_receipt, (int) $row->branch, $row->start_of_employment, $row->end_of_employment, $row->holidays);
        }
        return $List_of_all_employees;
    }

    /**
     * Get the profession of an employee
     *
     * @param int $employee_key
     * @return string profession of the chosen employee
     */
    public function get_employee_profession($employee_key) {
        if (!isset($this->List_of_employees[$employee_key])) {
            throw new Exception('This employee does not exist!');
        }
        if (FALSE !== $this->get_employee_value($employee_key, 'profession')) {
            return $this->get_employee_value($employee_key, 'profession');
        }
    }

    public function get_employee_object($employee_key) {
        if (!isset($this->List_of_employees[$employee_key])) {
            throw new Exception('This employee does not exist!');
        }
        if ($this->List_of_employees[$employee_key] instanceof employee) {
            return $this->List_of_employees[$employee_key];
        }
    }

    public function employee_exists($employee_key) {
        if (isset($this->List_of_employees[$employee_key]) and $this->List_of_employees[$employee_key] instanceof employee) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * @todo Delete this function. We do not need it, I hope.
     * @param int $employee_key
     * @param string $key
     * @return misc
     */
    private function get_employee_value(int $employee_key, string $key) {
        if (isset($this->List_of_employees[$employee_key])) {
            if (isset($this->List_of_employees[$employee_key]->$key)) {
                return $this->List_of_employees[$employee_key]->$key;
            }
        }
        return FALSE;
    }

    public function get_list_of_employee_names() {
        $List_of_employee_last_names = array();
        foreach ($this->List_of_employees as $employee_key => $employee_object) {
            $List_of_employee_last_names[$employee_key] = $employee_object->last_name;
        }
        return $List_of_employee_last_names;
    }

    public function get_list_of_employee_professions() {
        $List_of_employee_professions = array();
        foreach ($this->List_of_employees as $employee_key => $employee_object) {
            $List_of_employee_professions[$employee_key] = $employee_object->profession;
        }
        return $List_of_employee_professions;
    }

    /**
     * <p lang=de>Ich hätte gerne einen sehr kurzen Deskriptor für die Mitarbeiter. Er sollte aber eindeutig sein.
     * Wie kann ich das ereichen?
     * Ich muss auf jeden Fall eine vollständige Liste der aktuellen Mitarbeiter haben.
     * Dann kann ich versuchen, ob ein kurzer String aus dem ersten und zweiten Buchstaben des Vor- und Nachnamen ausreicht.
     * Wenn nicht, muss ich weitere Buchstaben ergänzen.
     * Das sollte möglichst nicht ständig erfolgen.
     * Das Ergebnis sollte also static gespeichert werden.
     * </p>
     */
    public function get_employee_short_descriptor($employee_key) {
        if (empty(self::$List_of_short_descriptors)) {
            $this->create_list_of_short_descriptors();
        }
        return self::$List_of_short_descriptors[$employee_key];
    }

    /**
     * @todo <p>maybe write a test with very specific employee names
     * "Albert Polk",
     * "Alex Parbs",
     * "Alexandra Probst",
     * "Alexandra Prokoviev",</p>
     */
    private function create_list_of_short_descriptors() {
        self::$List_of_short_descriptors = array();
        foreach ($this->get_list_of_all_employees() as $employee_key => $employee_object) {
            $number_of_characters_of_first_name = 2;
            $number_of_characters_of_last_name = 2;
            /**
             * Try to add into the array: 2+2
             */
            $short_descriptor = $this->create_short_descriptor($employee_object, $number_of_characters_of_first_name, $number_of_characters_of_last_name);
            $search_result = array_search($short_descriptor, self::$List_of_short_descriptors, FALSE);
            if (FALSE === $search_result) {
                self::$List_of_short_descriptors[$employee_key] = $short_descriptor;
                continue;
            }
            /**
             * Second try: 1+3
             */
            $number_of_characters_of_first_name = 1;
            $number_of_characters_of_last_name = 3;
            $this->change_short_descriptor_by_chars($search_result, $number_of_characters_of_first_name, $number_of_characters_of_last_name);
            $short_descriptor = $this->create_short_descriptor($employee_object, $number_of_characters_of_first_name, $number_of_characters_of_last_name);
            $search_result = array_search($short_descriptor, self::$List_of_short_descriptors, FALSE);
            if (FALSE === $search_result) {
                self::$List_of_short_descriptors[$employee_key] = $short_descriptor;
                continue;
            }
            /**
             * Third try: 0+4
             */
            $number_of_characters_of_first_name = 0;
            $number_of_characters_of_last_name = 4;
            $this->change_short_descriptor_by_chars($search_result, $number_of_characters_of_first_name, $number_of_characters_of_last_name);
            $short_descriptor = $this->create_short_descriptor($employee_object, $number_of_characters_of_first_name, $number_of_characters_of_last_name);
            $search_result = array_search($short_descriptor, self::$List_of_short_descriptors, FALSE);
            if (FALSE === $search_result) {
                self::$List_of_short_descriptors[$employee_key] = $short_descriptor;
                continue;
            }
            /**
             * Fourth try: 3+1
             */
            $number_of_characters_of_first_name = 3;
            $number_of_characters_of_last_name = 1;
            $this->change_short_descriptor_by_chars($search_result, $number_of_characters_of_first_name, $number_of_characters_of_last_name);
            $short_descriptor = $this->create_short_descriptor($employee_object, $number_of_characters_of_first_name, $number_of_characters_of_last_name);
            $search_result = array_search($short_descriptor, self::$List_of_short_descriptors, FALSE);
            if (FALSE === $search_result) {
                self::$List_of_short_descriptors[$employee_key] = $short_descriptor;
                continue;
            }
            /**
             * Last try: 1+1+primary_key
             */
            $number_of_characters_of_first_name = 1;
            $number_of_characters_of_last_name = 1;
            $this->change_short_descriptor_with_key($search_result, $number_of_characters_of_first_name, $number_of_characters_of_last_name);
            $this->change_short_descriptor_with_key($employee_key, $number_of_characters_of_first_name, $number_of_characters_of_last_name);
        }
    }

    /**
     * @param type $employee_key
     * @param type $number_of_characters_of_first_name
     * @param type $number_of_characters_of_last_name
     */
    private function change_short_descriptor_by_chars($employee_key, $number_of_characters_of_first_name, $number_of_characters_of_last_name) {
        $employee_object = $this->get_employee_object($employee_key);
        $short_descriptor = $this->create_short_descriptor($employee_object, $number_of_characters_of_first_name, $number_of_characters_of_last_name);
        /**
         * Only add this variant, if it does not create another duplicate:
         */
        $search_result = array_search($short_descriptor, self::$List_of_short_descriptors, FALSE);
        if (FALSE === $search_result) {
            self::$List_of_short_descriptors[$employee_key] = $short_descriptor;
        }
    }

    /**
     * @param type $employee_key
     * @param type $number_of_characters_of_first_name
     * @param type $number_of_characters_of_last_name
     */
    private function change_short_descriptor_with_key($employee_key, $number_of_characters_of_first_name, $number_of_characters_of_last_name) {
        $employee_object = $this->get_employee_object($employee_key);
        $short_descriptor = $this->create_short_descriptor($employee_object, $number_of_characters_of_first_name, $number_of_characters_of_last_name);
        $short_descriptor .= $employee_object->get_employee_key();
        self::$List_of_short_descriptors[$employee_key] = $short_descriptor;
    }

    private function create_short_descriptor($employee_object, $number_of_characters_of_first_name, $number_of_characters_of_last_name) {
        $short_descriptor = "";
        $short_descriptor .= mb_substr($employee_object->first_name, 0, $number_of_characters_of_first_name);
        $short_descriptor .= mb_substr($employee_object->last_name, 0, $number_of_characters_of_last_name);
        return $short_descriptor;
    }

    /**
     * We just return some random employee
     */
    public function get_default_employee_key() {
        if ($_SESSION['user_object'] instanceof user) {
            /**
             * Try to guess the employee_key from the logged in user:
             */
            $employee_key = $_SESSION['user_object']->get_employee_key();
            if ($this->employee_exists($employee_key)) {
                return $employee_key;
            }
        }
        if (!empty($this->List_of_employees and min($workforce->List_of_employees) instanceof employee)) {
            $employee = min($workforce->List_of_employees);
            $employee_key = $employee->get_employee_key();
            return $employee_key;
        }
        /**
         * If there is no employee at all in the workforce, we return NULL:
         */
        return NULL;
    }

    public function get_empty_employee() {
        $private_key = null;
        $last_name = null;
        $first_name = null;
        $working_week_hours = 40;
        $lunch_break_minutes = 30;
        $profession = null;
        $compounding = false;
        $goods_receipt = false;
        $networkOfBranchOffices = new PDR\Pharmacy\NetworkOfBranchOffices();
        $branch = $networkOfBranchOffices->get_main_branch_id();
        $start_of_employment = null;
        $end_of_employment = null;
        $holidays = null;
        $employee = new employee($private_key, $last_name, $first_name, $working_week_hours, $lunch_break_minutes, $profession, $compounding, $goods_receipt, $branch, $start_of_employment, $end_of_employment, $holidays);
        return $employee;
    }

}
