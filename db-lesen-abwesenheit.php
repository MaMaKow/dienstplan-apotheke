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
    $mitarbeiterliste = "";
    foreach ($Mitarbeiter as $VK => $nachname) {
        $mitarbeiterliste.=$VK . ", ";
    }
    $mitarbeiterliste = substr($mitarbeiterliste, 0, -2); //The last comma has to be cut off.

    $abfrage = "SELECT *
		FROM `absence`
		WHERE `start` <= '$sql_date' AND `end` >= '$sql_date' AND `employee_id` IN (" . $mitarbeiterliste . ")"; //Mitarbeiter, deren Urlaub schon begonnen hat, aber noch nicht beendet ist.
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
    global $verbindungi;


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

function calculate_absence_days($start_date_string, $end_date_string) {
    if (!function_exists('is_holiday')) {
        require 'src/php/calculate-holidays.php';
    }
    $days = 0;
    for ($date_unix = strtotime($start_date_string); $date_unix <= strtotime($end_date_string); $date_unix = strtotime('+1 day', $date_unix)) {
        if (intval(date('w', $date_unix)) !== 6 and intval(date('w', $date_unix)) !== 0 and ! is_holiday($date_unix)) {
            $days++;
        }
    }
    return $days;
}

?>
