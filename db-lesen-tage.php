<?php

/*
 * This function reads the roster of one or more days from the database into an array.
 *
 * @global string $datum
 * @param int $tage number of days (typically just 1 or 5, 6, 7)
 * @param int $mandant
 * @param string $branch_of_target_employees as regular expression.
 *      This is used for the selection of which employees to show in the branch roster table.
 *      The default is to show the employees of all branches (regexp [0-9]*).
 * @return array $Dienstplan for the branch $mandant including $tage days beginning with $datum
 */

function db_lesen_tage($tage, $mandant, $branch_of_target_employees = '[0-9]*') {
    //TODO: Make $datum a parameter of the function!
    global $datum;
    //Abruf der gespeicherten Daten aus der Datenbank
    //$tage ist die Anzahl der Tage. 5 Tage = Woche; 1 Tag = 1 Tag.
    //Branch #0 can be used for the boss, the cleaning lady, and other special people, who do not regularly appear in the roster.

    for ($i = 0; $i < $tage; $i++) {
        $date_sql = date('Y-m-d', strtotime("+$i days", strtotime($datum)));
        $sql_query = 'SELECT DISTINCT Dienstplan.* '
                . 'FROM `Dienstplan` LEFT JOIN employees ON Dienstplan.VK=employees.id '
                . 'WHERE Dienstplan.Mandant = "' . $mandant . '" AND `Datum` = "' . $date_sql . '" AND employees.branch REGEXP "^' . $branch_of_target_employees . '$" '
                . 'ORDER BY `Dienstbeginn` ASC, `Dienstende` ASC, `Mittagsbeginn` ASC;';
        $result = mysqli_query_verbose($sql_query);
        $dienstplanCSV = "";

        while ($row = mysqli_fetch_object($result)) {
            $Dienstplan[$i]["Datum"][] = $row->Datum;
            $Dienstplan[$i]["VK"][] = $row->VK;
            $Dienstplan[$i]["Dienstbeginn"][] = $row->Dienstbeginn;
            $Dienstplan[$i]["Dienstende"][] = $row->Dienstende;
            $Dienstplan[$i]["Mittagsbeginn"][] = $row->Mittagsbeginn;
            $Dienstplan[$i]["Mittagsende"][] = $row->Mittagsende;
            $Dienstplan[$i]["Stunden"][] = $row->Stunden;
            $Dienstplan[$i]["Kommentar"][] = $row->Kommentar;
        }
        //Wir füllen komplett leere Tage mit Werten, damit trotzdem eine Anzeige entsteht.
        if (!isset($Dienstplan[$i])) {
            $Dienstplan[$i]["Datum"][] = $date_sql;
            /*
              $Dienstplan[$i]["VK"][]="";
              $Dienstplan[$i]["Dienstbeginn"][]="";
              $Dienstplan[$i]["Dienstende"][]="";
              $Dienstplan[$i]["Mittagsbeginn"][]="";
              $Dienstplan[$i]["Mittagsende"][]="";
              $Dienstplan[$i]["Stunden"][]="";
              $Dienstplan[$i]["Kommentar"][]="";
             */
        }
    }
    if (isset($Dienstplan)) {
        return $Dienstplan;
    } else {
        return FALSE;
    }
}
