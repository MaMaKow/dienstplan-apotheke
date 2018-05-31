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

    public static function read_employee_data_from_database($employee_id) {
        $sql_query = "SELECT * FROM `employees` WHERE `id` = :employee_id";
        $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $Worker["employee_id"] = $row->id;
            $Worker["first_name"] = $row->first_name;
            $Worker["last_name"] = $row->last_name;
            $Worker["profession"] = $row->profession;
            $Worker["working_hours"] = $row->working_hours;
            $Worker["working_week_hours"] = $row->working_week_hours;
            $Worker["holidays"] = $row->holidays;
            $Worker["lunch_break_minutes"] = $row->lunch_break_minutes;
            $Worker["goods_receipt"] = $row->goods_receipt;
            $Worker["compounding"] = $row->compounding;
            $Worker["branch"] = $row->branch;
            $Worker["start_of_employment"] = $row->start_of_employment;
            $Worker["end_of_employment"] = $row->end_of_employment;
        }
        return $Worker;
    }

    public static function write_employee_data_to_database() {
        if (filter_input(INPUT_POST, "submitStunden", FILTER_SANITIZE_STRING)) {
            $Worker["employee_id"] = filter_input(INPUT_POST, "employee_id", FILTER_VALIDATE_INT);
            $Worker["first_name"] = filter_input(INPUT_POST, "first_name", FILTER_SANITIZE_STRING);
            $Worker["last_name"] = filter_input(INPUT_POST, "last_name", FILTER_SANITIZE_STRING);
            $Worker["profession"] = filter_input(INPUT_POST, "profession", FILTER_SANITIZE_STRING);
            $Worker["working_hours"] = filter_input(INPUT_POST, "working_hours", FILTER_VALIDATE_FLOAT);
            $Worker["working_week_hours"] = filter_input(INPUT_POST, "working_week_hours", FILTER_VALIDATE_FLOAT);
            $Worker["holidays"] = filter_input(INPUT_POST, "holidays", FILTER_VALIDATE_INT);
            $Worker["lunch_break_minutes"] = filter_input(INPUT_POST, "lunch_break_minutes", FILTER_VALIDATE_INT);
            $Worker["goods_receipt"] = filter_input(INPUT_POST, "goods_receipt", FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ? 1 : 0; //FILTER_NULL_ON_FAILURE because empty checkboxes are not sent by the browser.
            $Worker["compounding"] = filter_input(INPUT_POST, "compounding", FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ? 1 : 0; //FILTER_NULL_ON_FAILURE because empty checkboxes are not sent by the browser.
            $Worker["branch"] = filter_input(INPUT_POST, "branch", FILTER_VALIDATE_INT);
            $Worker["start_of_employment"] = null_from_post_to_mysql(filter_input(INPUT_POST, "start_of_employment", FILTER_SANITIZE_STRING));
            $Worker["end_of_employment"] = null_from_post_to_mysql(filter_input(INPUT_POST, "end_of_employment", FILTER_SANITIZE_STRING));

            $sql_query = "INSERT INTO `employees` (
        `id`, `first_name`, `last_name`, `profession`,
        `working_hours`, `working_week_hours`, `holidays`, `lunch_break_minutes`,
        `goods_receipt`, `compounding`,
        `branch`,
        `start_of_employment`, `end_of_employment`
        )
        VALUES (
:employee_id, :first_name, :last_name, :profession,
:working_hours, :working_week_hours, :holidays, :lunch_break_minutes,
:goods_receipt, :compounding, :branch,
:start_of_employment, :end_of_employment)
ON DUPLICATE KEY UPDATE
`id` = :employee_id2,
`first_name` = :first_name2,
`last_name` = :last_name2,
`profession` = :profession2,
`working_hours` = :working_hours2,
 `working_week_hours` = :working_week_hours2,
`holidays` = :holidays2,
 `lunch_break_minutes` = :lunch_break_minutes2,
`goods_receipt` = :goods_receipt2,
 `compounding` = :compounding2,
`branch` = :branch2,
 `start_of_employment` = :start_of_employment2,
`end_of_employment` = :end_of_employment2
";


            $result = database_wrapper::instance()->run($sql_query, array(
                'employee_id' => $Worker['employee_id'],
                'first_name' => $Worker['first_name'],
                'last_name' => $Worker['last_name'],
                'profession' => $Worker['profession'],
                'working_hours' => $Worker['working_hours'],
                'working_week_hours' => $Worker['working_week_hours'],
                'holidays' => $Worker['holidays'],
                'lunch_break_minutes' => $Worker['lunch_break_minutes'],
                'goods_receipt' => $Worker['goods_receipt'],
                'compounding' => $Worker['compounding'],
                'branch' => $Worker['branch'],
                'start_of_employment' => $Worker['start_of_employment'],
                'end_of_employment' => $Worker['end_of_employment'],
                'employee_id2' => $Worker['employee_id'],
                'first_name2' => $Worker['first_name'],
                'last_name2' => $Worker['last_name'],
                'profession2' => $Worker['profession'],
                'working_hours2' => $Worker['working_hours'],
                'working_week_hours2' => $Worker['working_week_hours'],
                'holidays2' => $Worker['holidays'],
                'lunch_break_minutes2' => $Worker['lunch_break_minutes'],
                'goods_receipt2' => $Worker['goods_receipt'],
                'compounding2' => $Worker['compounding'],
                'branch2' => $Worker['branch'],
                'start_of_employment2' => $Worker['start_of_employment'],
                'end_of_employment2' => $Worker['end_of_employment']
            ));
            return $result;
        } else {
            return FALSE;
        }
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
                $text .= "<input type='radio' name='profession' ";
                $text .= "value='$profession'";
                if ($checked == $profession) {
                    $text .= " checked=checked";
                }
                $text .= ">&nbsp;$profession\n";
                $text .= "<br>";
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

    public static function make_radio_branch_list($checked_branch_id) {
        $text = "";
        $text .= "<fieldset>\n";

        $text .= "<legend>" . gettext("Branch") . ": </legend>\n";
        $List_of_branch_objects = branch::read_branches_from_database();
        if (!isset($List_of_branch_objects[0])) {
            $List_of_branch_objects[0] = new branch();
            $List_of_branch_objects[0]->name = gettext("None");
        }
        foreach ($List_of_branch_objects as $branch_id => $branch_object) {
            $text .= "<input type='radio' name='branch' ";
            $text .= "value='$branch_id'";
            if ($checked_branch_id == $branch_id) {
                $text .= " checked=checked";
            }
            $text .= ">&nbsp;$branch_object->name<br>\n";
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
