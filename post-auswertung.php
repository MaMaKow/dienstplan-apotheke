<?php

//Hier schauen wir, welche Daten an uns übersendet wurden und aus welchem Formular sie stammen.
if (isset($_POST['mandant'])) {
    if (is_int((int) $_POST['mandant'])) {
        $mandant = htmlspecialchars($_POST['mandant']);
    } else {
        throw new InvalidArgumentException("Ungültiger Wert für Mandant per POST übergeben");
    }
}

if (isset($_POST['datum'])) {
    $datum = filter_input(INPUT_POST, 'datum', FILTER_SANITIZE_STRING);
}

if (isset($_POST['submitDienstplan']) && count($_POST['Dienstplan']) > 0) {
//                echo "<pre>\$_POST-Dienstplan:"; var_export($_POST['Dienstplan']); echo "</pre>\n";
    foreach ($_POST['Dienstplan'] as $day_number => $inhalt_tag) {
        $day_number = filter_var($day_number, FILTER_SANITIZE_NUMBER_INT);
        foreach ($inhalt_tag as $column => $lines) {
            $column = filter_var($column, FILTER_SANITIZE_STRING);
            $Columns[$column] = $column; //Will be needed to sice out empty rows later.
            foreach ($lines as $line_number => $line) {
                $line = filter_var($line, FILTER_SANITIZE_STRING);
                $line_number = filter_var($line_number, FILTER_SANITIZE_NUMBER_INT);
                if ('' === $line) {
                    //Empty fields should be inserted as null values inside the database.
                    //TODO: Should we make an exeption for Comments?
                    $line = 'null';
                }
                $Dienstplan[$day_number][$column][$line_number] = $line;
            }
        }
    }

    //Slice out empty rows in all columns:
    foreach ($Dienstplan[$tag]["VK"] as $line_number => $employee_id){
        if ( 'null' === $employee_id){
            foreach ($Columns as $column){
                unset($Dienstplan[$tag][$column][$line_number]);
            }
        }
    }

    foreach (array_keys($Dienstplan) as $tag) { //Hier sollte eigentlich nur ein einziger Tag ankommen.
        $date_sql = $Dienstplan[$tag]['Datum'][0];
        //The following lines will add an entry for every day in the table approval.
        //TODO: We should manage situations, where an entry already exists better.
        $abfrage = "INSERT IGNORE INTO `approval` (date, state, branch, user)
			VALUES ('$date_sql', 'not_yet_approved', '$mandant', '$user')";
        $ergebnis = mysqli_query_verbose($abfrage);

        $query = "SELECT * FROM `Dienstplan`
			WHERE `Datum` = '$date_sql'
                            AND `Mandant` = '$mandant'
			;"; //Der Mandant wird entweder als default gesetzt oder per POST übergeben und dann im vorherigen if-clause übeschrieben.
        $result = mysqli_query_verbose($query);
        while ($row = mysqli_fetch_object($result)) {
            $Dienstplan_old[$tag]["VK"][] = $row->VK;
            $Dienstplan_old[$tag]["Dienstbeginn"][] = $row->Dienstbeginn;
            $Dienstplan_old[$tag]["Dienstende"][] = $row->Dienstende;
            $Dienstplan_old[$tag]["Mittagsbeginn"][] = $row->Mittagsbeginn;
            $Dienstplan_old[$tag]["Mittagsende"][] = $row->Mittagsende;
            $Dienstplan_old[$tag]["Mandant"][] = $row->Mandant;
            $Dienstplan_old[$tag]["Kommentar"][] = $row->Kommentar;
        }

        $Deleted_employee_id_list = array_diff($Dienstplan_old[$tag]["VK"], $Dienstplan[$tag]["VK"]);
        //$Inserted_employee_id_list = array_diff($Dienstplan[$tag]["VK"], $Dienstplan_old[$tag]["VK"]);
        if (array() !== $Deleted_employee_id_list) {
            $abfrage = "DELETE FROM `Dienstplan`"
                    . " WHERE `Datum` = '$date_sql'"
                    . " AND `VK` IN (" . implode(', ', $Deleted_employee_id_list) .")"
                    . " AND `Mandant` = '$mandant';"; //Der Mandant wird entweder als default gesetzt oder per POST übergeben und dann im vorherigen if-clause übeschrieben.
            $ergebnis = mysqli_query_verbose($abfrage);
        }

        foreach ($Dienstplan[$tag]['VK'] as $key => $employee_id) { //Die einzelnen Zeilen im Dienstplan
            
                $dienstbeginn = $Dienstplan[$tag]["Dienstbeginn"][$key];
                $dienstende = $Dienstplan[$tag]["Dienstende"][$key];
                $mittagsbeginn = $Dienstplan[$tag]["Mittagsbeginn"][$key];
                if (empty($Mittagsbeginn)) {
                    $Mittagsbeginn = "0:00";
                }
                $mittagsende = $Dienstplan[$tag]["Mittagsende"][$key];
                if (empty($Mittagsende)) {
                    $Mittagsende = "0:00";
                }
                $kommentar = $Dienstplan[$tag]["Kommentar"][$key];
                if (isset($mittagsbeginn) && isset($mittagsende)) {
                    $sekunden = strtotime($dienstende) - strtotime($dienstbeginn);
                    $mittagspause = strtotime($mittagsende) - strtotime($mittagsbeginn);
                    $sekunden = $sekunden - $mittagspause;
                    $stunden = $sekunden / 3600;
                } else {
                    $sekunden = strtotime($dienstende) - strtotime($dienstbeginn);
                    $stunden = $sekunden / 3600;
                }
                $abfrage = "REPLACE INTO `Dienstplan` (VK, Datum, Dienstbeginn, Dienstende, Mittagsbeginn, Mittagsende, Stunden, Mandant, Kommentar, user)
					VALUES ($employee_id, ".escape_sql_value($date_sql)
                                        .", ".escape_sql_value($dienstbeginn)
                                        .", ".escape_sql_value($dienstende)
                                        .", ".escape_sql_value($mittagsbeginn)
                                        .", ".escape_sql_value($mittagsende)
                                        .", ".$stunden
                                        .", ".$mandant
                                        .", ".escape_sql_value($kommentar)
                                        .", ".escape_sql_value($user)
                                        .")";
//        echo "<pre>\$abfrage:\n"; echo $abfrage; echo "</pre>"; //die;
                $ergebnis = mysqli_query_verbose($abfrage);
            
        }
    }
    $datum = $Dienstplan[0]['Datum'][0];
} elseif (isset($_POST['submitWocheVorwärts']) && isset($_POST['Dienstplan'][0]['Datum'][0])) { 
    //TODO: These lines should be changed to the ones below for every file
    $date_sql = filter_var($_POST['Dienstplan'][0]['Datum'][0], FILTER_SANITIZE_STRING);
    $datum = strtotime('+1 week', strtotime($datum));
    $datum = date('Y-m-d', $datum);
}  elseif (isset($_POST['submitWocheVorwärts']) && isset($_POST['date']) && isset($_POST['selected_employee'])) {
    $auswahl_mitarbeiter = filter_input(INPUT_POST, 'selected_employee', FILTER_SANITIZE_NUMBER_INT);
    $datum = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    $datum = strtotime('+1 week', strtotime($datum));
    $datum = date('Y-m-d', $datum);
}  elseif (isset($_POST['submitWocheRückwärts']) && isset($_POST['date']) && isset($_POST['selected_employee'])) {
    $auswahl_mitarbeiter = filter_input(INPUT_POST, 'selected_employee', FILTER_SANITIZE_NUMBER_INT);
    $datum = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    $datum = strtotime('-1 week', strtotime($datum));
    $datum = date('Y-m-d', $datum);
} elseif (isset($_POST['submitWocheRückwärts']) && isset($_POST['Dienstplan'][0]['Datum'][0])) {
    $datum = $_POST['Dienstplan'][0]['Datum'][0];
    $datum = strtotime('-1 week', strtotime($datum));
    $datum = date('Y-m-d', $datum);
} elseif (isset($_POST['submitVorwärts']) && filter_has_var(INPUT_POST, 'tag')) {
    print_debug_variable("Wir sind da!");
    $datum = filter_input(INPUT_POST, 'tag', FILTER_SANITIZE_STRING);
    $datum = strtotime('+1 day', strtotime($datum));
    $datum = date('Y-m-d', $datum);
} elseif (isset($_POST['submitRückwärts']) && filter_has_var(INPUT_POST, 'tag')) {
    $datum = filter_input(INPUT_POST, 'tag', FILTER_SANITIZE_STRING);
    $datum = strtotime('-1 day', strtotime($datum));
    $datum = date('Y-m-d', $datum);
} elseif (isset($_POST['wochenAuswahl']) && isset($_POST['woche'])) {
    $datum = $_POST['woche'];
    $montags_differenz = date("w", strtotime($datum)) - 1; //Wir wollen den Anfang der Woche
    $montags_differenzString = "-" . $montags_differenz . " day";
    $datum = strtotime($montags_differenzString, strtotime($datum));
    $datum = date('Y-m-d', $datum);
} elseif (isset($_POST['tagesAuswahl']) && isset($_POST['tag'])) {
    $datum = $_POST['tag'];
} elseif (isset($_POST['tagesAuswahl']) && isset($_POST['woche'])) {
    $datum = $_POST['woche'];
} elseif (isset($_POST['submitCopyPaste']) && count($_POST['Dienstplan']) > 0) {
    require 'copy-paste.php';
} elseif ((isset($_POST['submit_approval']) or isset($_POST['submit_disapproval'])) && count($_POST['Dienstplan']) > 0) {
    require 'db-write-approval.php';
    $datum = $_POST['Dienstplan'][0]['Datum'][0];
// TODO: Is this save? Is the key 0 allways set?
} else {
    //Es gibt nichts im $_POST mit dem wir etwas anfangen können.
}
