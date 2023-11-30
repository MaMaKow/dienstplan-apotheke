<?php

/*
 * Copyright (C) 2017 Mandelkow
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

abstract class human_resource_management {

    private static function create_empty_employee(int $employee_key = null) {
        $networkOfBranchOffices = new \PDR\Pharmacy\NetworkOfBranchOffices();

        $Worker["employee_key"] = $employee_key;
        $Worker["first_name"] = null;
        $Worker["last_name"] = null;
        $Worker["profession"] = null;
        $Worker["working_week_hours"] = 40;
        $Worker["holidays"] = 28;
        $Worker["lunch_break_minutes"] = 30;
        $Worker["goods_receipt"] = null;
        $Worker["compounding"] = null;
        $Worker["branch"] = $networkOfBranchOffices->get_main_branch_id();
        $Worker["start_of_employment"] = null;
        $Worker["end_of_employment"] = null;
        return $Worker;
    }

    public static function write_employee_data_to_database() {
        if (filter_input(INPUT_POST, "submitStunden", FILTER_SANITIZE_FULL_SPECIAL_CHARS)) {
            $Worker["employee_key"] = filter_input(INPUT_POST, "employee_key", FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
            $Worker["first_name"] = filter_input(INPUT_POST, "first_name", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $Worker["last_name"] = filter_input(INPUT_POST, "last_name", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $Worker["profession"] = filter_input(INPUT_POST, "profession", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $Worker["working_week_hours"] = filter_input(INPUT_POST, "working_week_hours", FILTER_VALIDATE_FLOAT);
            $Worker["holidays"] = filter_input(INPUT_POST, "holidays", FILTER_VALIDATE_INT);
            $Worker["lunch_break_minutes"] = filter_input(INPUT_POST, "lunch_break_minutes", FILTER_VALIDATE_INT);
            $Worker["goods_receipt"] = filter_input(INPUT_POST, "goods_receipt", FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ? 1 : 0; //FILTER_NULL_ON_FAILURE because empty checkboxes are not sent by the browser.
            $Worker["compounding"] = filter_input(INPUT_POST, "compounding", FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ? 1 : 0; //FILTER_NULL_ON_FAILURE because empty checkboxes are not sent by the browser.
            $Worker["branch"] = filter_input(INPUT_POST, "branch", FILTER_VALIDATE_INT);
            $Worker["start_of_employment"] = database_wrapper::null_from_post_to_mysql(filter_input(INPUT_POST, "start_of_employment", FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $Worker["end_of_employment"] = database_wrapper::null_from_post_to_mysql(filter_input(INPUT_POST, "end_of_employment", FILTER_SANITIZE_FULL_SPECIAL_CHARS));

            $workforce = new workforce();
            if (null != $Worker["employee_key"] and $workforce->employee_exists($Worker["employee_key"])) {
                $result_archive = self::archiveExistingEmployee($Worker);
                $result_update = self::updateExistingEmployee($Worker);
                return $result_update;
            }
            $result_insertion = self::insertNewEmployee($Worker);
            return $result_insertion;
        } else {
            return FALSE;
        }
    }

    private static function insertNewEmployee($Worker): PDOStatement {
        $sql_query_insertion = "INSERT INTO `employees` (
            `first_name`, `last_name`, `profession`,
            `working_week_hours`, `holidays`, `lunch_break_minutes`,
            `goods_receipt`, `compounding`,
            `branch`,
            `start_of_employment`, `end_of_employment`
            )
            VALUES (
            :first_name, :last_name, :profession,
            :working_week_hours, :holidays, :lunch_break_minutes,
            :goods_receipt, :compounding, :branch,
            :start_of_employment, :end_of_employment)
            ";

        $result_insertion = database_wrapper::instance()->run($sql_query_insertion, array(
            'first_name' => $Worker['first_name'],
            'last_name' => $Worker['last_name'],
            'profession' => $Worker['profession'],
            'working_week_hours' => $Worker['working_week_hours'],
            'holidays' => $Worker['holidays'],
            'lunch_break_minutes' => $Worker['lunch_break_minutes'],
            'goods_receipt' => $Worker['goods_receipt'],
            'compounding' => $Worker['compounding'],
            'branch' => $Worker['branch'],
            'start_of_employment' => $Worker['start_of_employment'],
            'end_of_employment' => $Worker['end_of_employment'],
        ));
        return $result_insertion;
    }

    private static function archiveExistingEmployee($Worker): PDOStatement {
        $sql_query_archive = "INSERT INTO `employees_archive` (
                    `primary_key`,
                    `first_name`, `last_name`, `profession`,
                    `working_week_hours`, `holidays`, `lunch_break_minutes`,
                    `goods_receipt`, `compounding`,
                    `branch`,
                    `start_of_employment`, `end_of_employment`
                    )
                    VALUES (
                    :employee_key,
                    :first_name, :last_name, :profession,
                    :working_week_hours, :holidays, :lunch_break_minutes,
                    :goods_receipt, :compounding, :branch,
                    :start_of_employment, :end_of_employment)
                    ";

        $result_archive = database_wrapper::instance()->run($sql_query_archive, array(
            'employee_key' => $Worker['employee_key'],
            'first_name' => $Worker['first_name'],
            'last_name' => $Worker['last_name'],
            'profession' => $Worker['profession'],
            'working_week_hours' => $Worker['working_week_hours'],
            'holidays' => $Worker['holidays'],
            'lunch_break_minutes' => $Worker['lunch_break_minutes'],
            'goods_receipt' => $Worker['goods_receipt'],
            'compounding' => $Worker['compounding'],
            'branch' => $Worker['branch'],
            'start_of_employment' => $Worker['start_of_employment'],
            'end_of_employment' => $Worker['end_of_employment'],
        ));
        return $result_archive;
    }

    private static function updateExistingEmployee($Worker): PDOStatement {
        $sql_query_update = "UPDATE employees
                SET
                    first_name = :first_name,
                    last_name = :last_name,
                    profession = :profession,
                    working_week_hours = :working_week_hours,
                    holidays = :holidays,
                    lunch_break_minutes = :lunch_break_minutes,
                    goods_receipt = :goods_receipt,
                    compounding = :compounding,
                    branch = :branch,
                    start_of_employment = :start_of_employment,
                    end_of_employment = :end_of_employment
                WHERE
                    primary_key = :employee_key
                ";
        $result_update = database_wrapper::instance()->run($sql_query_update, array(
            'first_name' => $Worker['first_name'],
            'last_name' => $Worker['last_name'],
            'profession' => $Worker['profession'],
            'working_week_hours' => $Worker['working_week_hours'],
            'holidays' => $Worker['holidays'],
            'lunch_break_minutes' => $Worker['lunch_break_minutes'],
            'goods_receipt' => $Worker['goods_receipt'],
            'compounding' => $Worker['compounding'],
            'branch' => $Worker['branch'],
            'start_of_employment' => $Worker['start_of_employment'],
            'end_of_employment' => $Worker['end_of_employment'],
            'employee_key' => $Worker["employee_key"],
        ));
        return $result_update;
    }

    public static function make_radio_profession_list($checked) {
        $sql_query = "SHOW COLUMNS FROM `employees` LIKE 'profession'";
        $result = database_wrapper::instance()->run($sql_query);
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $set_column = $row["Type"];
            $clean_set_column = str_replace(["set(", ")", "'"], "", $set_column);
            $Professions = explode(",", $clean_set_column);
            $text = "";
            $text .= "<fieldset>\n";
            $text .= "<legend>" . gettext("Profession") . ":</legend>";
            //$text .= "<label for='profession'>Ausbildung: </label>\n";

            foreach ($Professions as $profession) {
                $text .= "<label>";
                $text .= "<input type='radio' name='profession' required ";
                $text .= "value='$profession'";
                if ($checked == $profession) {
                    $text .= " checked=checked";
                }
                $text .= ">&nbsp;$profession\n";
                $text .= "</span></label><br>";
            }
            //$text .= "&nbsp;<a title='Einen weiteren Beruf hinzufügen' id=button_new_profession>[Neu]</a>";
            $text .= "</fieldset>\n";
        }
        if (!empty($text)) {
            return $text;
        } else {
            error_log("Error while trying to build a list of professions.");
            return FALSE;
        }
    }

    public static function make_radio_branch_list(int $checked_branch_id) {
        $text = "";
        $text .= "<fieldset>\n";

        $text .= "<legend>" . gettext("Branch") . ": </legend>\n";
        $network_of_branch_offices = new \PDR\Pharmacy\NetworkOfBranchOffices;
        $List_of_branch_objects = $network_of_branch_offices->get_list_of_branch_objects();
        if (!isset($List_of_branch_objects[0])) {
            $branch_id = 0;
            $text .= "<label>";
            $text .= "<input type='radio' name='branch' ";
            $text .= "value='0'";
            if ($checked_branch_id == $branch_id) {
                $text .= " checked=checked";
            }
            $text .= ">&nbsp;<span>" . gettext("None") . "</span></label><br>\n";
        }
        foreach ($List_of_branch_objects as $branch_id => $branch_object) {
            $text .= "<label>";
            $text .= "<input type='radio' name='branch' ";
            $text .= "value='$branch_id'";
            if ($checked_branch_id == $branch_id) {
                $text .= " checked=checked";
            }
            $text .= ">&nbsp;<span>" . $branch_object->getName() . "</span></label><br>\n";
        }
        $text .= "</fieldset>\n";

        return $text;
    }

    public static function make_checkbox_ability($ability, $label, $checked) {
        $text = "<label for='$ability'>$label: </label>";
        $text .= "<input type='checkbox' name='$ability' id='$ability' ";
        if ($checked) {
            $text .= " checked='checked'";
        }
        $text .= ">";
        return $text;
    }
}
