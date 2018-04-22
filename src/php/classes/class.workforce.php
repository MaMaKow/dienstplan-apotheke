<?php

/*
 * Copyright (C) 2018 Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
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
 * @author Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class workforce {

//public $List_of_employee_ids;
    public $List_of_employees;
    public $List_of_qualified_pharmacist_employees;
    public $List_of_goods_receipt_employees;
    public $List_of_compounding_employees;

    public function __construct($date_sql = NULL) {
        global $pdo;
        if (NULL === $date_sql) {
            $sql_query = 'SELECT * FROM `employees` '
                    . 'ORDER BY `id` ASC, ISNULL(`end_of_employment`) ASC, `end_of_employment` ASC;';
        } else {
            $sql_query = 'SELECT * FROM `employees` '
                    . 'WHERE  (`end_of_employment` >= "' . $date_sql . '" OR `end_of_employment` IS NULL) '
                    . 'AND  (`start_of_employment` <= "' . $date_sql . '" OR `start_of_employment` IS NULL) '
                    . 'ORDER BY `id` ASC, ISNULL(`end_of_employment`) ASC, `end_of_employment` ASC;';
        }
        $statement = $pdo->prepare($sql_query);
        $statement->execute();
        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
            $this->List_of_employees[$row->id] = new employee((int) $row->id, $row->last_name, $row->first_name, (float) $row->working_week_hours, (float) $row->lunch_break_minutes, $row->profession, (int) $row->branch);
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

}
