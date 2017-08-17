<?php

//Hier schauen wir, welche Daten an uns übersendet wurden und aus welchem Formular sie stammen.
function get_Roster_from_POST_secure() {
    global $Columns; //Will be needed to slice out empty rows later.
    //The following statement requires PHP >= 7.0.0
    //define("TIME_COLUMNS", array("Dienstbeginn", "Dienstende"));
    $time_columns = array("Dienstbeginn", "Dienstende", "Mittagsbeginn", "Mittagsende");

    $Roster_from_post = filter_input(INPUT_POST, 'Dienstplan', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    foreach ($Roster_from_post as $day_number => $inhalt_tag) {
        $day_number = filter_var($day_number, FILTER_SANITIZE_NUMBER_INT);
        foreach ($inhalt_tag as $column_name => $Lines) {
            $column_name = filter_var($column_name, FILTER_SANITIZE_STRING);
            $Columns[$column_name] = $column_name; //Will be needed to slice out empty rows later.
            foreach ($Lines as $line_number => $line) {
                $line = filter_var($line, FILTER_SANITIZE_STRING);
                if (!empty($line) and in_array($column_name, $time_columns)) {
                    $line = strftime('%H:%M:%S', strtotime($line));
                }
                $line_number = filter_var($line_number, FILTER_SANITIZE_NUMBER_INT);
                if ('' === $line) {
                    //Empty fields should be inserted as null values inside the database.
                    //TODO: Should we make an exeption for Comments?
                    //$line = 'null';
                    $line = NULL;
                }
                $Roster[$day_number][$column_name][$line_number] = $line;
            }
        }
    }
    return $Roster;
}

function get_old_Roster_from_database($date_sql, $branch_id) {
    $query = "SELECT * FROM `Dienstplan`
			WHERE `Datum` = '$date_sql'
                            AND `Mandant` = '$branch_id'
			;"; //Der Mandant wird entweder als default gesetzt oder per POST übergeben und dann im vorherigen if-clause übeschrieben.
    $result = mysqli_query_verbose($query);
    while ($row = mysqli_fetch_object($result)) {
        $Roster_old_day["Datum"][] = $row->Datum;
        $Roster_old_day["VK"][] = $row->VK;
        $Roster_old_day["Dienstbeginn"][] = $row->Dienstbeginn;
        $Roster_old_day["Dienstende"][] = $row->Dienstende;
        $Roster_old_day["Mittagsbeginn"][] = $row->Mittagsbeginn;
        $Roster_old_day["Mittagsende"][] = $row->Mittagsende;
        $Roster_old_day["Mandant"][] = $row->Mandant;
        $Roster_old_day["Kommentar"][] = $row->Kommentar;
    }
    return $Roster_old_day;
}

function remove_changed_entries_from_database($date_sql, $branch_id, $Employee_id_list) {
    if (!empty($Employee_id_list)) {
        $sql_query = "DELETE FROM `Dienstplan`"
                . " WHERE `Datum` = '$date_sql'"
                . " AND `VK` IN (" . implode(', ', $Employee_id_list) . ")"
                . " AND `Mandant` = '$branch_id';"; //Der Mandant wird entweder als default gesetzt oder per POST übergeben und dann im vorherigen if-clause übeschrieben.
        mysqli_query_verbose($sql_query, TRUE);
    }
}

function insert_changed_entries_into_database($date_sql, $day_number, $branch_id, $Dienstplan, $Changed_roster_employee_id_list) {
    foreach ($Dienstplan[$day_number]['VK'] as $key => $employee_id) { //Die einzelnen Zeilen im Dienstplan
        if (!in_array($employee_id, $Changed_roster_employee_id_list)) {
            continue;
        }
        if (isset($Dienstplan[$day_number]["Mittagsbeginn"][$key]) && isset($Dienstplan[$day_number]["Mittagsende"][$key])) {
            $lunch_break = strtotime($Dienstplan[$day_number]["Mittagsende"][$key]) - strtotime($Dienstplan[$day_number]["Mittagsbeginn"][$key]);
        } else {
            $lunch_break = 0;
        }
        $working_seconds = strtotime($Dienstplan[$day_number]["Dienstende"][$key]) - strtotime($Dienstplan[$day_number]["Dienstbeginn"][$key]) - $lunch_break;
        $working_hours = $working_seconds / 3600;
        $sql_query = "REPLACE INTO `Dienstplan` (VK, Datum, Dienstbeginn, Dienstende, Mittagsbeginn, Mittagsende, Stunden, Mandant, Kommentar, user)
            VALUES ($employee_id"
                . ", " . escape_sql_value($date_sql)
                . ", " . escape_sql_value($Dienstplan[$day_number]["Dienstbeginn"][$key])
                . ", " . escape_sql_value($Dienstplan[$day_number]["Dienstende"][$key])
                . ", " . escape_sql_value($Dienstplan[$day_number]["Mittagsbeginn"][$key])
                . ", " . escape_sql_value($Dienstplan[$day_number]["Mittagsende"][$key])
                . ", " . $working_hours
                . ", " . $branch_id
                . ", " . escape_sql_value($Dienstplan[$day_number]["Kommentar"][$key])
                . ", " . escape_sql_value($_SESSION['user_name'])
                . ")";
        mysqli_query_verbose($sql_query, TRUE);
    }
}

if (filter_has_var(INPUT_POST, 'Dienstplan')) {
    $Dienstplan = get_Roster_from_POST_secure();
}

if (filter_has_var(INPUT_POST, 'mandant')) {
    $mandant = filter_input(INPUT_POST, 'mandant', FILTER_SANITIZE_NUMBER_INT);
}

if (filter_has_var(INPUT_POST, 'datum')) {
    $datum = filter_input(INPUT_POST, 'datum', FILTER_SANITIZE_STRING);
}

function remove_empty_rows($Roster, $tag, $Columns) {
    //Slice out empty rows in all columns:
    foreach ($Roster[$tag]["VK"] as $line_number => $employee_id) {
        if (NULL === $employee_id) {
            foreach ($Columns as $column_name) {
                unset($Roster[$tag][$column_name][$line_number]);
            }
        }
    }
    return $Roster;
}

function insert_new_approval_into_database($date_sql, $branch_id) {
    //TODO: We should manage situations, where an entry already exists better.
    $sql_query = "INSERT IGNORE INTO `approval` (date, state, branch, user)
			VALUES ('$date_sql', 'not_yet_approved', '$branch_id', " . escape_sql_value($_SESSION['user_name']) . ")";
    $ergebnis = mysqli_query_verbose($sql_query);
}

if (filter_has_var(INPUT_POST, 'submitDienstplan') && $session->user_has_privilege('create_roster') && count($Dienstplan) > 0) {
    foreach (array_keys($Dienstplan) as $tag) { //Hier sollte eigentlich nur ein einziger Tag ankommen.
        $Dienstplan = remove_empty_rows($Dienstplan, $tag, $Columns);
        $roster_first_key = min(array_keys($Dienstplan[$tag]['Datum']));
        if (!empty($Dienstplan[$tag]['Datum'][$roster_first_key])) {
            $date_sql = $Dienstplan[$tag]['Datum'][$roster_first_key];
        } else {
            $date_sql = filter_input(INPUT_POST, 'date_sql', FILTER_SANITIZE_STRING);
        }
        //The following line will add an entry for every day in the table approval.
        insert_new_approval_into_database($date_sql, $mandant);
        $Roster_old[$tag] = get_old_Roster_from_database($date_sql, $mandant);

        /*
         * Remove deleted data rows:
         * TODO: Find the changed or the deleted rows:
         */
        foreach ($Dienstplan[$tag]["VK"] as $key => $employee_id) {
            $Comparison_keys = array_keys($Roster_old[$tag]["VK"], $employee_id);
            foreach ($Comparison_keys as $comparison_key) {
                foreach ($Dienstplan[$tag] as $column_name => $Column) {
                    if ($Roster_old[$tag][$column_name][$comparison_key] !== $Dienstplan[$tag][$column_name][$key]) {
                        $Changed_roster_employee_id_list[] = $employee_id;
                    }
                }
            }
        }
        $Changed_roster_employee_id_list = array_unique($Changed_roster_employee_id_list);
        if (empty($Dienstplan[$tag]["VK"])) {
            $Deleted_roster_employee_id_list = $Roster_old[$tag]["VK"];
        } else {
            $Deleted_roster_employee_id_list = array_diff($Roster_old[$tag]["VK"], $Dienstplan[$tag]["VK"]);
        }
        if (empty($Roster_old[$tag]["VK"])) {
            $Inserted_employee_id_list = $Dienstplan[$tag]["VK"];
        } else {
            $Inserted_employee_id_list = array_diff($Dienstplan[$tag]["VK"], $Roster_old[$tag]["VK"]);
        }

        //TODO: There should be a transaction here:
        mysqli_query_verbose("START TRANSACTION");
        remove_changed_entries_from_database($date_sql, $mandant, $Deleted_roster_employee_id_list);
        remove_changed_entries_from_database($date_sql, $mandant, $Changed_roster_employee_id_list);
        insert_changed_entries_into_database($date_sql, $tag, $mandant, $Dienstplan, $Changed_roster_employee_id_list);
        insert_changed_entries_into_database($date_sql, $tag, $mandant, $Dienstplan, $Inserted_employee_id_list);
        mysqli_query_verbose("COMMIT");
    }
    if (!empty($date_sql)) {
        $datum = $date_sql;
    }
} elseif (filter_has_var(INPUT_POST, 'submitWocheVorwärts') && isset($Dienstplan[0]['Datum'][0])) {
    //TODO: These lines should be changed to the ones below for every file
    $date_sql = filter_var($Dienstplan[0]['Datum'][0], FILTER_SANITIZE_STRING);
    $datum = strtotime('+1 week', strtotime($datum));
    $datum = date('Y-m-d', $datum);
} elseif (filter_has_var(INPUT_POST, 'submitWocheVorwärts') && filter_has_var(INPUT_POST, 'date') && filter_has_var(INPUT_POST, 'selected_employee')) {
    $auswahl_mitarbeiter = filter_input(INPUT_POST, 'selected_employee', FILTER_SANITIZE_NUMBER_INT);
    $datum = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    $datum = strtotime('+1 week', strtotime($datum));
    $datum = date('Y-m-d', $datum);
} elseif (filter_has_var(INPUT_POST, 'submitWocheRückwärts') && filter_has_var(INPUT_POST, 'date') && filter_has_var(INPUT_POST, 'selected_employee')) {
    $auswahl_mitarbeiter = filter_input(INPUT_POST, 'selected_employee', FILTER_SANITIZE_NUMBER_INT);
    $datum = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    $datum = strtotime('-1 week', strtotime($datum));
    $datum = date('Y-m-d', $datum);
} elseif (filter_has_var(INPUT_POST, 'submitWocheRückwärts') && isset($Dienstplan[0]['Datum'][0])) {
    $datum = $Dienstplan[0]['Datum'][0];
    $datum = strtotime('-1 week', strtotime($datum));
    $datum = date('Y-m-d', $datum);
} elseif (filter_has_var(INPUT_POST, 'submitVorwärts') && filter_has_var(INPUT_POST, 'tag')) {
    $datum = filter_input(INPUT_POST, 'date_sql', FILTER_SANITIZE_STRING);
    $datum = strtotime('+1 day', strtotime($datum));
    $datum = date('Y-m-d', $datum);
} elseif (filter_has_var(INPUT_POST, 'submitRückwärts') && filter_has_var(INPUT_POST, 'tag')) {
    $datum = filter_input(INPUT_POST, 'date_sql', FILTER_SANITIZE_STRING);
    $datum = strtotime('-1 day', strtotime($datum));
    $datum = date('Y-m-d', $datum);
} elseif (filter_has_var(INPUT_POST, 'wochenAuswahl') && filter_has_var(INPUT_POST, 'woche')) {
    $datum = filter_input(INPUT_POST, 'woche', FILTER_SANITIZE_STRING);
    $montags_differenz = date("w", strtotime($datum)) - 1; //Wir wollen den Anfang der Woche
    $montags_differenzString = "-" . $montags_differenz . " day";
    $datum = strtotime($montags_differenzString, strtotime($datum));
    $datum = date('Y-m-d', $datum);
} elseif (filter_has_var(INPUT_POST, 'tagesAuswahl') && filter_has_var(INPUT_POST, 'tag')) {
    $datum = filter_input(INPUT_POST, 'tag', FILTER_SANITIZE_STRING);
} elseif (filter_has_var(INPUT_POST, 'tagesAuswahl') && filter_has_var(INPUT_POST, 'woche')) {
    $datum = filter_input(INPUT_POST, 'woche', FILTER_SANITIZE_STRING);
} elseif ((filter_has_var(INPUT_POST, 'submit_approval') or filter_has_var(INPUT_POST, 'submit_disapproval')) && count($Dienstplan) > 0 && $session->user_has_privilege('approve_roster')) {
    require 'db-write-approval.php';
    $datum = $Dienstplan[0]['Datum'][0];
// TODO: Is this save? Is the key 0 allways set?
} else {
    //There is nothing inside the POST variable that we can work with.
}
