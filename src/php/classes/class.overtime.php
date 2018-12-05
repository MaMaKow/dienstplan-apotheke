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
class overtime {

    public static function handle_user_input_insert() {
        $user_dialog = new user_dialog();
        $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
        $date = filter_input(INPUT_POST, 'datum', FILTER_SANITIZE_STRING);
        $overtime_hours_new = filter_input(INPUT_POST, 'stunden', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        list($balance_old, $date_old) = overtime::get_current_balance($employee_id);
        //list($balance_first, $date_first) = overtime::get_first_balance($employee_id);
        $first_balance_row = overtime::get_first_balance($employee_id);
        /*
         * In case the user inserts a date, that is before the last inserted date, a warning is shown.
         * If the user still wishes to enter the data, the flag user_has_been_warned_about_date_sequence is set to 1.
         * We cancel the execution if that warning has not been approved.
         */
        $user_has_been_warned_about_date_sequence = filter_input(INPUT_POST, 'user_has_been_warned_about_date_sequence', FILTER_SANITIZE_STRING);
        if (strtotime($date) < strtotime($date_old) and 'true' !== $user_has_been_warned_about_date_sequence) {
            $message = gettext('An error has occurred while inserting the overtime data.');
            $user_dialog->add_message($message, E_USER_ERROR);
            $message = gettext('The input date lies before the last existent date.');
            $user_dialog->add_message($message, E_USER_WARNING);
            $message = gettext('Please enable JavaScript in order to allow PDR to handle this case.');
            $user_dialog->add_message($message, E_USER_WARNING);
            return FALSE;
        }
        $balance_new = $balance_old + $overtime_hours_new;

        if ($first_balance_row->Datum > $date) {
            /*
             * The new entry lies before the very first entry.
             * This is a special case.
             * In this case we calculate the balance given on a date that lies in the future, in regard to the new data.
             */
            $balance_new = $first_balance_row->Saldo - $first_balance_row->Stunden;
        }

        $sql_query = "INSERT INTO `Stunden` (VK, Datum, Stunden, Saldo, Grund)
        VALUES (:employee_id, :date, :overtime_hours, :balance, :reason)";
        try {
            $result = database_wrapper::instance()->run($sql_query, array(
                'employee_id' => $employee_id,
                'date' => $date,
                'overtime_hours' => $overtime_hours_new,
                'balance' => $balance_new,
                'reason' => filter_input(INPUT_POST, 'grund', FILTER_SANITIZE_STRING)
            ));
        } catch (Exception $exception) {
            if (database_wrapper::ERROR_MESSAGE_DUPLICATE_ENTRY_FOR_KEY === $exception->getMessage()) {
                $user_dialog->add_message(gettext('There is already an entry on this date.'), E_USER_ERROR);
                $user_dialog->add_message(gettext('The data was therefore not inserted in the database.'), E_USER_WARNING);
            } else {
                print_debug_variable($exception);
                $message = gettext('There was an error while querying the database.')
                        . " " . gettext('Please see the error log for more details!');
                die("<p>$message</p>");
            }
        }

        overtime::recalculate_balances($employee_id);
    }

    public static function handle_user_input_delete() {
        $Remove = filter_input(INPUT_POST, 'loeschen', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
        foreach ($Remove as $employee_id => $Data) {
            $employee_id = intval($employee_id);
            foreach (array_keys($Data) as $date_sql) {
                $sql_query = "DELETE FROM `Stunden` WHERE `VK` = :employee_id AND `Datum` = :date";
                database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id, 'date' => $date_sql));
            }
        }
    }

    public static function handle_user_input($session, $employee_id) {
        if (!$session->user_has_privilege('create_overtime')) {
            return FALSE;
        }
        /*
         * Deleting rows of data:
         */
        if (filter_has_var(INPUT_POST, 'loeschen')) {
            self::handle_user_input_delete();
        }

        /*
         * Insert new data:
         */
        if (filter_has_var(INPUT_POST, 'submitStunden') and filter_has_var(INPUT_POST, 'employee_id') and filter_has_var(INPUT_POST, 'datum') and filter_has_var(INPUT_POST, 'stunden') and filter_has_var(INPUT_POST, 'grund')) {
            self::handle_user_input_insert();
        }
        /*
         * Sorting and recalculating the entries:
         */
        overtime::recalculate_balances($employee_id);
    }

    public static function recalculate_balances($employee_id) {
        $Overtime_list = array();
        $sql_query = "SELECT * FROM `Stunden` WHERE `VK` = :employee_id ORDER BY `Datum` ASC";
        $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id));
        $first_loop = TRUE;
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            if ($first_loop === TRUE) {
                $balance = $row->Saldo - $row->Stunden;
                $first_loop = FALSE;
            }
            $date_unix = strtotime($row->Datum);
            $Overtime_list[$date_unix] = $row;
        }
        ksort($Overtime_list);
        foreach ($Overtime_list as $overtime_entry) {
            $balance += $overtime_entry->Stunden;
            $sql_query = "UPDATE `Stunden` SET `Saldo` = :balance WHERE `VK` = :employee_id and `Datum` = :date";
            database_wrapper::instance()->run($sql_query, array('employee_id' => $overtime_entry->VK, 'date' => $overtime_entry->Datum, 'balance' => $balance));
        }
        return TRUE;
    }

    /**
     * <p>The last balance stored in the database for a given employee. Current means, that the date (`Datum`) of the entry is the highest.</p>
     *
     * @param int $employee_id
     * @return array [$balance, $date]
     */
    public static function get_current_balance($employee_id) {
        $sql_query = "SELECT * FROM `Stunden` WHERE `VK` = :employee_id ORDER BY `Datum` DESC LIMIT 1";
        $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            /*
             * We cast the result to float,
             * so in case there is no balance yet, we just set it to 0.
             */
            $balance = (float) $row->Saldo;
            $date = $row->Datum;
            return [$balance, $date];
        }
        return [0, (new DateTime())->format('Y-m-d')];
    }

    /**
     * <p>
     * The first balance stored in the database for a given employee.
     * First means, that the date (`Datum`) of the entry is the lowest.
     * </p>
     *
     * @param int $employee_id
     * @return object <p>A standard PHP object representing a single row of data.</p>
     */
    public static function get_first_balance($employee_id) {
        $sql_query = "SELECT * FROM `Stunden` WHERE `VK` = :employee_id ORDER BY `Datum` ASC LIMIT 1";
        $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            return $row;
        }
        return FALSE;
    }

    public static function build_overview_table() {
        $table_head = overtime::build_overview_table_head();
        $table_body = overtime::build_overview_table_body();
        $table = "<table id='overtime_overview_table'>" . $table_head . $table_body . "</table>\n";
        return $table;
    }

    private static function build_overview_table_head() {
        $table_head = "<thead>";
        $table_head .= "<th>" . gettext('Employee') . "</th>";
        $table_head .= "<th>" . gettext('Balance') . "</th>";
        $table_head .= "<th>" . gettext('Date') . "</th>";
        $table_head .= "</thead>\n";
        return $table_head;
    }

    private static function build_overview_table_body() {
        $workforce = new workforce();
        $table_rows = "<tbody>";
        foreach (array_keys($workforce->List_of_employees) as $employee_id) {
            $sql_query = "SELECT * FROM `Stunden` WHERE `VK` = :employee_id ORDER BY `Datum` DESC LIMIT 1";
            $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id));
            while ($row = $result->fetch(PDO::FETCH_OBJ)) {
                switch (TRUE) {
                    case 40 < $row->Saldo:
                        $class = "positive_very_high";
                        break;
                    case 20 < $row->Saldo:
                        $class = "positive_high";
                        break;
                    case 0 == $row->Saldo:
                        $class = "zero";
                        break;
                    case 0 > $row->Saldo:
                        $class = "negative";
                        break;
                    default:
                        $class = "positive";
                        break;
                }
                $table_rows .= "<tr class='$class'>";
                $table_rows .= "<td>" . $row->VK . " " . $workforce->List_of_employees[$row->VK]->last_name . "</td>";
                $table_rows .= "<td>" . $row->Saldo . "</td>";
                $table_rows .= "<td>" . strftime('%x', strtotime($row->Datum)) . "</td>";
                $table_rows .= "</tr>\n";
            }
        }
        $table_rows .= "</tbody>\n";
        return $table_rows;
    }

}
