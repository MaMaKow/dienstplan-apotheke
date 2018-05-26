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

/**
 * This function reads the roster of one or more days from the database into an array.
 *
 * @param int $number_of_days number of days (typically just 1 or 5, 6, 7)
 * @param int $branch_id
 * @param string $branch_of_target_employees as regular expression.
 *      This is used for the selection of which employees to show in the branch roster table.
 *      The default is to show the employees of all branches (regexp [0-9]*).
 * @return array $Roster for the branch $branch_id including $number_of_days days beginning with $date_sql
 */
function read_roster_array_from_db($date_sql, $number_of_days, $branch_id, $branch_of_target_employees = '[0-9]*') {
    $branch_of_target_employees = '^' . $branch_of_target_employees . '$';
    //Abruf der gespeicherten Daten aus der Datenbank
    //$number_of_days ist die Anzahl der Tage. 5 Tage = Woche; 1 Tag = 1 Tag.
    //Branch #0 can be used for the boss, the cleaning lady, and other special people, who do not regularly appear in the roster.

    for ($i = 0; $i < $number_of_days; $i++) {
        $sql_query = 'SELECT DISTINCT Dienstplan.* '
                . 'FROM `Dienstplan` LEFT JOIN employees ON Dienstplan.VK=employees.id '
                . 'WHERE Dienstplan.Mandant = :branch_id AND `Datum` = :date AND employees.branch REGEXP :branch_of_target_employees '
                . 'ORDER BY `Dienstbeginn` ASC, `Dienstende` ASC, `Mittagsbeginn` ASC;';
        $result = database_wrapper::instance()->run($sql_query, array('branch_id' => $branch_id, 'date' => $date_sql, 'branch_of_target_employees' => $branch_of_target_employees));

        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $Roster[$i]["Datum"][] = $row->Datum;
            $Roster[$i]["VK"][] = $row->VK;
            $Roster[$i]["Dienstbeginn"][] = $row->Dienstbeginn;
            $Roster[$i]["Dienstende"][] = $row->Dienstende;
            $Roster[$i]["Mittagsbeginn"][] = $row->Mittagsbeginn;
            $Roster[$i]["Mittagsende"][] = $row->Mittagsende;
            $Roster[$i]["Stunden"][] = $row->Stunden;
            $Roster[$i]["Kommentar"][] = $row->Kommentar;
        }
        //Wir füllen komplett leere Tage mit Werten, damit trotzdem eine Anzeige entsteht.
        if (!isset($Roster[$i])) {
            $Roster[$i]["Datum"][] = $date_sql;
            /*
              $Roster[$i]["VK"][]="";
              $Roster[$i]["Dienstbeginn"][]="";
              $Roster[$i]["Dienstende"][]="";
              $Roster[$i]["Mittagsbeginn"][]="";
              $Roster[$i]["Mittagsende"][]="";
              $Roster[$i]["Stunden"][]="";
              $Roster[$i]["Kommentar"][]="";
             */
        }
        $date_sql = date('Y-m-d', strtotime("+1 day", strtotime($date_sql)));
    }
    return $Roster;
}
