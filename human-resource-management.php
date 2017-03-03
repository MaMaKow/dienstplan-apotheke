<?php
function read_employee_data_from_database($auswahl_mitarbeiter) {
    $abfrage = "SELECT * FROM `mitarbeiter` WHERE `VK` = '$auswahl_mitarbeiter'";
    //echo "$abfrage<br>\n";
    $ergebnis = mysqli_query_verbose($abfrage);
    while ($row = mysqli_fetch_object($ergebnis)) {
        $Worker["worker_id"] = $row->VK;
        $Worker["first_name"] = $row->Vorname;
        $Worker["last_name"] = $row->Nachname;
        $Worker["profession"] = $row->Ausbildung;
        $Worker["working_hours"] = $row->Stunden;
        $Worker["working_week_hours"] = $row->Arbeitswochenstunden;
        $Worker["holidays"] = $row->Urlaubstage;
        $Worker["lunch_break_minutes"] = $row->Mittag;
        $Worker["goods_receipt"] = $row->Wareneingang;
        $Worker["compounding"] = $row->Rezeptur;
        $Worker["branch"] = $row->Mandant;
        $Worker["start_of_employment"] = $row->Beschäftigungsbeginn;
        $Worker["end_of_employment"] = $row->Beschäftigungsende;
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

        $abfrage = "REPLACE INTO `mitarbeiter` (  
        `VK`, `Vorname`, `Nachname`,
        `Ausbildung`,
        `Stunden`, `Arbeitswochenstunden`, `Urlaubstage`, `Mittag`,
        `Wareneingang`, `Rezeptur`,
        `Mandant`,
        `Beschäftigungsbeginn`, `Beschäftigungsende`
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
        //echo "$abfrage<br>\n";
        $ergebnis = mysqli_query_verbose($abfrage);
        return $ergebnis;
    }  else {
        return FALSE;
    }
}

function make_radio_profession_list($checked) {
    $abfrage = "SHOW COLUMNS FROM `mitarbeiter` LIKE 'Ausbildung'";
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
    return $text;
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


?>