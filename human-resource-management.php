<?php
function read_employee_data_from_database($auswahl_mitarbeiter) {
    $abfrage = "SELECT * FROM `employees` WHERE `id` = '$auswahl_mitarbeiter'";
    //echo "$abfrage<br>\n";
    $ergebnis = mysqli_query_verbose($abfrage);
    while ($row = mysqli_fetch_object($ergebnis)) {
        $Worker["worker_id"] = $row->id;
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
        //print_debug_variable($Worker);
    }
    return $Worker;
}

function write_employee_data_to_database() {
    if (filter_input(INPUT_POST, "submitStunden", FILTER_SANITIZE_STRING)) {
        $Worker["worker_id"] = escape_sql_value(filter_input(INPUT_POST, "worker_id", FILTER_VALIDATE_INT));
        $Worker["first_name"] = escape_sql_value(filter_input(INPUT_POST, "first_name", FILTER_SANITIZE_STRING));
        $Worker["last_name"] = escape_sql_value(filter_input(INPUT_POST, "last_name", FILTER_SANITIZE_STRING));
        $Worker["profession"] = escape_sql_value(filter_input(INPUT_POST, "profession", FILTER_SANITIZE_STRING));
        $Worker["working_hours"] = escape_sql_value(filter_input(INPUT_POST, "working_hours", FILTER_VALIDATE_FLOAT));
        $Worker["working_week_hours"] = escape_sql_value(filter_input(INPUT_POST, "working_week_hours", FILTER_VALIDATE_FLOAT));
        $Worker["holidays"] = escape_sql_value(filter_input(INPUT_POST, "holidays", FILTER_VALIDATE_INT));
        $Worker["lunch_break_minutes"] = escape_sql_value(filter_input(INPUT_POST, "lunch_break_minutes", FILTER_VALIDATE_INT));
        $Worker["goods_receipt"] = escape_sql_value(filter_input(INPUT_POST, "goods_receipt", FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ? 1 : 0); //FILTER_NULL_ON_FAILURE because empty checkboxes are not sent by the browser.
        $Worker["compounding"] = escape_sql_value(filter_input(INPUT_POST, "compounding", FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ? 1 : 0); //FILTER_NULL_ON_FAILURE because empty checkboxes are not sent by the browser.
        $Worker["branch"] = escape_sql_value(filter_input(INPUT_POST, "branch", FILTER_VALIDATE_INT));
        $Worker["start_of_employment"] = escape_sql_value(null_from_post_to_mysql(filter_input(INPUT_POST, "start_of_employment", FILTER_SANITIZE_STRING)));
        $Worker["end_of_employment"] = escape_sql_value(null_from_post_to_mysql(filter_input(INPUT_POST, "end_of_employment", FILTER_SANITIZE_STRING)));

        $abfrage = "REPLACE INTO `employees` (  
        `id`, `first_name`, `last_name`,
        `profession`,
        `working_hours`, `working_week_hours`, `holidays`, `lunch_break_minutes`,
        `goods_receipt`, `compounding`,
        `branch`,
        `start_of_employment`, `end_of_employment`
        )
        VALUES ("
                . $Worker['worker_id'] . ", "
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
                . ")";
//        echo "$abfrage<br>\n";
        $ergebnis = mysqli_query_verbose($abfrage);
        return $ergebnis;
    }  else {
        return FALSE;
    }
}

function make_radio_profession_list($checked) {
    $abfrage = "SHOW COLUMNS FROM `employees` LIKE 'profession'";
    $ergebnis = mysqli_query_verbose($abfrage);
    while ($row = mysqli_fetch_array($ergebnis)) {
        $set_column = $row["Type"];
        $clean_set_column = str_replace(["set(", ")", "'"], "", $set_column);
        $Professions = explode(",", $clean_set_column);
        //$text = "<fieldset>\n";
        $text = "<label for='profession'>Ausbildung: </label>\n";

        foreach ($Professions as $profession) {
            $text .= "<input type='radio' name='profession' ";
            $text .= "value='$profession'";
            if ($checked == $profession) {
                $text .= " checked=checked";
            }
            $text .= ">&nbsp;$profession\n";
        }
        $text .= "&nbsp;<a title='Einen weiteren Beruf hinzufügen' id=button_new_profession>[Neu]</a>";
        //$text .= "</fieldset>\n";
    }
    if (!empty($text)){
        return $text;
    } else {
        error_log("Error while trying to build a list of professions.");
        return FALSE;
    }
}

function make_radio_branch_list($checked) {
    //$text = "<fieldset>\n";
    $text = "<label for='branch'>Mandant: </label>\n";

    foreach ($GLOBALS['Kurz_mandant'] as $branch => $branch_name) {
        $text .= "<input type='radio' name='branch' ";
        $text .= "value='$branch'";
        if ($checked == $branch) {
            $text .= " checked=checked";
        }
        $text .= ">&nbsp;$branch_name\n";
    }
    //$text .= "</fieldset>\n";

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

//CREATE TRIGGER backup_employee_data AFTER INSERT ON employees FOR EACH ROW INSERT INTO employees_backup SELECT * FROM employees WHERE employees.id = NEW.id;
//ALTER TABLE `employees` CHANGE `pseudo_id` `pseudo_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT, CHANGE `id` `id` SMALLINT UNSIGNED NOT NULL, CHANGE `Nachname` `last_name` VARCHAR(35) CHARACTER SET latin1 COLLATE latin1_german1_ci NOT NULL, CHANGE `Vorname` `first_name` VARCHAR(35) CHARACTER SET latin1 COLLATE latin1_german1_ci NOT NULL, CHANGE `Ausbildung` `profession` SET('Apotheker','PI','PTA','PKA','Praktikant','Ernährungsberater','Kosmetiker','Zugehfrau') CHARACTER SET latin1 COLLATE latin1_german1_ci NOT NULL, CHANGE `Stunden` `working_hours` FLOAT NOT NULL DEFAULT '40', CHANGE `Arbeitswochenstunden` `working_week_hours` FLOAT NOT NULL DEFAULT '38.5', CHANGE `Urlaubstage` `holidays` TINYINT(11) NOT NULL DEFAULT '28', CHANGE `Mittag` `lunch_break_minutes` TINYINT(11) NOT NULL DEFAULT '30', CHANGE `Wareneingang` `goods_receipt` TINYINT(1) NULL DEFAULT NULL, CHANGE `Rezeptur` `compounding` TINYINT(1) NULL DEFAULT NULL, CHANGE `Mandant` `branch` INT(11) NOT NULL DEFAULT '1', CHANGE `Beschäftigungsbeginn` `start_of_employment` DATE NOT NULL, CHANGE `Beschäftigungsende` `end_of_employment` DATE NULL DEFAULT NULL, CHANGE `timestamp` `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
?>