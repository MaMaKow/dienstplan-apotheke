<?php

function read_employee_data_from_database($employee_id) {
    $sql_query = "SELECT * FROM `employees` WHERE `id` = '$employee_id'";
    //echo "$sql_query<br>\n";
    $result = mysqli_query_verbose($sql_query);
    while ($row = mysqli_fetch_object($result)) {
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

function write_employee_data_to_database() {
    if (filter_input(INPUT_POST, "submitStunden", FILTER_SANITIZE_STRING)) {
        $Worker["employee_id"] = user_input::escape_sql_value(filter_input(INPUT_POST, "employee_id", FILTER_VALIDATE_INT));
        $Worker["first_name"] = user_input::escape_sql_value(filter_input(INPUT_POST, "first_name", FILTER_SANITIZE_STRING));
        $Worker["last_name"] = user_input::escape_sql_value(filter_input(INPUT_POST, "last_name", FILTER_SANITIZE_STRING));
        $Worker["profession"] = user_input::escape_sql_value(filter_input(INPUT_POST, "profession", FILTER_SANITIZE_STRING));
        $Worker["working_hours"] = user_input::escape_sql_value(filter_input(INPUT_POST, "working_hours", FILTER_VALIDATE_FLOAT));
        $Worker["working_week_hours"] = user_input::escape_sql_value(filter_input(INPUT_POST, "working_week_hours", FILTER_VALIDATE_FLOAT));
        $Worker["holidays"] = user_input::escape_sql_value(filter_input(INPUT_POST, "holidays", FILTER_VALIDATE_INT));
        $Worker["lunch_break_minutes"] = user_input::escape_sql_value(filter_input(INPUT_POST, "lunch_break_minutes", FILTER_VALIDATE_INT));
        $Worker["goods_receipt"] = user_input::escape_sql_value(filter_input(INPUT_POST, "goods_receipt", FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ? 1 : 0); //FILTER_NULL_ON_FAILURE because empty checkboxes are not sent by the browser.
        $Worker["compounding"] = user_input::escape_sql_value(filter_input(INPUT_POST, "compounding", FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ? 1 : 0); //FILTER_NULL_ON_FAILURE because empty checkboxes are not sent by the browser.
        $Worker["branch"] = user_input::escape_sql_value(filter_input(INPUT_POST, "branch", FILTER_VALIDATE_INT));
        $Worker["start_of_employment"] = user_input::escape_sql_value(null_from_post_to_mysql(filter_input(INPUT_POST, "start_of_employment", FILTER_SANITIZE_STRING)));
        $Worker["end_of_employment"] = user_input::escape_sql_value(null_from_post_to_mysql(filter_input(INPUT_POST, "end_of_employment", FILTER_SANITIZE_STRING)));

        $sql_query = "INSERT INTO `employees` (
        `id`, `first_name`, `last_name`,
        `profession`,
        `working_hours`, `working_week_hours`, `holidays`, `lunch_break_minutes`,
        `goods_receipt`, `compounding`,
        `branch`,
        `start_of_employment`, `end_of_employment`
        )
        VALUES ("
                . $Worker['employee_id'] . ", "
                . $Worker['first_name'] . ", "
                . $Worker['last_name'] . ", "
                . $Worker['profession'] . ", "
                . $Worker['working_hours'] . ", "
                . $Worker['working_week_hours'] . ", "
                . $Worker['holidays'] . ", "
                . $Worker['lunch_break_minutes'] . ", "
                . $Worker['goods_receipt'] . ", "
                . $Worker['compounding'] . ", "
                . $Worker['branch'] . ", "
                . $Worker['start_of_employment'] . ", "
                . $Worker['end_of_employment']
                . ")"
                . " ON DUPLICATE KEY UPDATE  `id` = "
                . $Worker['employee_id'] . ", "
                . "`first_name` = "
                . $Worker['first_name'] . ", "
                . " `last_name` = "
                . $Worker['last_name'] . ", "
                . "`profession` = "
                . $Worker['profession'] . ", "
                . "`working_hours` = "
                . $Worker['working_hours'] . ", "
                . " `working_week_hours` = "
                . $Worker['working_week_hours'] . ", "
                . " `holidays` = "
                . $Worker['holidays'] . ", "
                . " `lunch_break_minutes` = "
                . $Worker['lunch_break_minutes'] . ", "
                . " `goods_receipt` = "
                . $Worker['goods_receipt'] . ", "
                . " `compounding` = "
                . $Worker['compounding'] . ", "
                . " `branch` = "
                . $Worker['branch'] . ", "
                . " `start_of_employment` = "
                . $Worker['start_of_employment'] . ", "
                . " `end_of_employment` = "
                . $Worker['end_of_employment']
                . "";


        $result = mysqli_query_verbose($sql_query);
        return $result;
    } else {
        return FALSE;
    }
}

function make_radio_profession_list($checked) {
    $sql_query = "SHOW COLUMNS FROM `employees` LIKE 'profession'";
    $result = mysqli_query_verbose($sql_query);
    while ($row = mysqli_fetch_array($result)) {
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

function make_radio_branch_list($checked_branch_id) {
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

function make_checkbox_ability($ability, $label, $checked) {
    $text = "<label for='$ability'>$label: </label>";
    $text .= "<input type='checkbox' name='$ability' id='$ability' ";
    if ($checked) {
        $text .= " checked='checked'";
    }
    $text .= ">";
    return $text;
}
