<?php

/*
 * Copyright (C) 2019 Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
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
 * Rosters may be approved before they become visible to the employees.
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
abstract class roster_approval {

    /**
     *
     * @var array $List_of_approval_states[$date_sql][$branch_id] holds an array of approvals for differents dates and branches.
     */
    private static $List_of_approval_states = array();

    public static function get_approval(string $date_sql, int $branch_id) {
        if (!isset(self::$List_of_approval_states[$date_sql][$branch_id])) {
            return self::read_approval_from_database($date_sql, $branch_id);
        }
        return self::$List_of_approval_states[$date_sql][$branch_id];
    }

    private static function read_approval_from_database(string $date_sql, int $branch_id) {
        $sql_query = "SELECT state FROM `approval` WHERE date = :date AND branch = :branch_id";
        $result = database_wrapper::instance()->run($sql_query, array(
            'date' => $date_sql,
            'branch_id' => $branch_id,
        ));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            self::$List_of_approval_states[$date_sql][$branch_id] = $row->state;
            return $row->state;
        }
        /*
         * If there is no roster state found, then obviously the state is 'not_yet_approved'.
         */
        self::write_approval_to_database($date_sql, $branch_id, 'not_yet_approved');
        return 'not_yet_approved';
    }

    public static function set_roster_approval(int $branch_id, array $Roster, string $approval_state) {
        foreach (array_keys($Roster) as $date_unix) {
            $date_sql = date('Y-m-d', $date_unix);
            self::write_approval_to_database($date_sql, $branch_id, $approval_state);
        }
    }

    public static function write_approval_to_database(string $date_sql, int $branch_id, string $approval_state) {
        if (!in_array($approval_state, array('disapproved', 'approved', 'not_yet_approved',))) {
            /*
             * no valid state is given.
             */
            throw new Exception("An Error has occurred during approval!");
        }
        $sql_query = "INSERT INTO `approval` (date, branch, state, user) "
                . "values (:date, :branch_id, :state, :user) "
                . "ON DUPLICATE KEY "
                . "UPDATE date = :date2, branch = :branch_id2, state = :state2, user = :user2";
        $result = database_wrapper::instance()->run($sql_query, array(
            'date' => $date_sql, 'branch_id' => $branch_id, 'state' => $approval_state, 'user' => $_SESSION['user_object']->get_employee_key(),
            'date2' => $date_sql, 'branch_id2' => $branch_id, 'state2' => $approval_state, 'user2' => $_SESSION['user_object']->get_employee_key(),
        ));
        return $result;
    }

}
