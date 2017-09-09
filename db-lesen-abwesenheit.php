<?php

//TODO: We might rename this file /src/php/absence-management.php
//
//Dieses Script fragt nach den Mitarbeitern, die an $datum Urlaub haben.
//Die Variable $datum muss hierzu bereits mit dem korrekten Wert gefüllt sein.
//Der Zugang zu Datenbank muss bereits bestehen.
function db_lesen_abwesenheit($date) {
    global $Mitarbeiter, $verbindungi;
    unset($Urlauber, $Kranke, $Abwesende);
    //Im folgenden prüfen wir, ob $datum bereis als UNIX timestamp vorliegt. Wenn es ein Timestamp ist, können wir direkt in 'Y-m-d' umrechnen. Wenn nicht, dann wandeln wir vorher um.
    if (is_numeric($date) && (int) $date == $date) {
        $sql_date = date('Y-m-d', $date);
    } else {
        $sql_date = date('Y-m-d', strtotime($date));
    }

    //We define a list of still existing coworkers. There might be workers in the database, that do not work anymore, but still have vacations registered in the database.
    //TODO: Build an option to delete future vacations of people when leaving.
    if (!isset($Mitarbeiter)) {
        require "db-lesen-mitarbeiter.php";
    }
    $mitarbeiterliste = implode(", ", array_keys($Mitarbeiter));
    
    $abfrage = "SELECT * FROM `absence` "
            . "WHERE `start` <= '$sql_date' "
            . "AND `end` >= '$sql_date' "
            . "AND `employee_id` IN (" . $mitarbeiterliste . ")"; //Mitarbeiter, deren Urlaub schon begonnen hat, aber noch nicht beendet ist.
    //TODO: The above query does not discriminate between approved an non-approved vacations.
    $ergebnis = mysqli_query_verbose($abfrage);
    while ($row = mysqli_fetch_object($ergebnis)) {
        $Abwesende[$row->employee_id] = $row->reason;
        if ($row->reason == "Urlaub") {
            $Urlauber[] = $row->employee_id;
        } elseif (preg_match('/Krank/i', $row->reason)) { //Auch Krank mit Kind sollte hier enthalten sein. //Außerdem suchen wir Case insensitive krank=Krank=kRaNk
            $Kranke[] = $row->employee_id;
        }
    }
    return array($Abwesende, $Urlauber, $Kranke);
//Anschließend müssen wir die Arrays wieder auseinander nehmen
//list($Abwesende, $Urlauber, $Kranke)=db_lesen_abwesenheit($datum);
}

function get_absence_data_specific($date_sql, $employee_id) {
    $query = "SELECT *
		FROM `absence`
		WHERE `start` <= '$date_sql' AND `end` >= '$date_sql' AND `employee_id` = '$employee_id'";
    $result = mysqli_query_verbose($query);
    while ($row = mysqli_fetch_object($result)) {
        $Absence['employee_id'] = $row->employee_id;
        $Absence['reason'] = $row->reason;
        $Absence['start'] = $row->start;
        $Absence['end'] = $row->end;
    }
    return $Absence;
}

/*
function get_all_absence_data_in_period($start_date_sql, $end_date_sql) {
    $query = "SELECT *
		FROM `absence`
		WHERE `start` <= '$start_date_sql' AND `end` >= '$end_date_sql'";
    $result = mysqli_query_verbose($query);
    while ($row = mysqli_fetch_object($result)) {
        $Absences[]['employee_id'] = $row->employee_id;
        $Absences[]['reason'] = $row->reason;
        $Absences[]['start'] = $row->start;
        $Absences[]['end'] = $row->end;
    }
    return $Absences;
}
*/

function calculate_absence_days($start_date_string, $end_date_string) {
    if (!function_exists('is_holiday')) {
        require 'src/php/calculate-holidays.php';
    }
    $days = 0;
    for ($date_unix = strtotime($start_date_string); $date_unix <= strtotime($end_date_string); $date_unix = strtotime('+1 day', $date_unix)) {
        if (6 !== intval(date('w', $date_unix)) and 0 !== intval(date('w', $date_unix)) and FALSE === is_holiday($date_unix)) {
            $days++;
        }
    }
    return $days;
}
