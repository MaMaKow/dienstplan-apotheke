<?php

function get_principle_roster($date_sql, $branch = 1, $day_index = 0, $number_of_days = 1) {

    global $Abwesende;
    for ($index = 0; $index < $number_of_days; $index++) {
        $current_date_sql = date('Y-m-d', strtotime($date_sql) + $index * PDR_ONE_DAY_IN_SECONDS);
        //Get principle roster data from the database
        $abfrage = "SELECT * FROM `Grundplan`"
                . "WHERE `Wochentag` = '" . date("N", strtotime($current_date_sql)) . "'"
                . "AND `Mandant` = '$branch'"
                . "ORDER BY `Dienstbeginn` + `Dienstende`, `Dienstbeginn`";
        $ergebnis = mysqli_query_verbose($abfrage);
        while ($row = mysqli_fetch_object($ergebnis)) {
            //Mitarbeiter, die im Urlaub/Krank sind, werden gar nicht erst beachtet.
            //TODO: This should be put somewhere else as a seperate function!
            if (isset($Abwesende[$row->VK])) {
                $Fehlermeldung[] = $Mitarbeiter[$row->VK] . " ist abwesend. 	Die Lücke eventuell auffüllen($row->Dienstbeginn - $row->Dienstende).<br>\n";
                continue 1;
            }
            if (isset($Mitarbeiter) AND array_search($row->VK, array_keys($Mitarbeiter)) === false) {
                //$Fehlermeldung[]=$Mitarbeiter[$row->VK]." ist nicht angestellt.<br>\n";
                continue 1;
            }
            $Dienstplan[$day_index]['Datum'][] = $current_date_sql;
            $Dienstplan[$day_index]['VK'][] = $row->VK;
            $Dienstplan[$day_index]['Dienstbeginn'][] = date("H:i", strtotime($row->Dienstbeginn));
            $Dienstplan[$day_index]['Dienstende'][] = date("H:i", strtotime($row->Dienstende));
            $Dienstplan[$day_index]['Mittagsbeginn'][] = $row->Mittagsbeginn;
            //echo $Mitarbeiter[$row->VK].": ".$row->Mittagsbeginn."<br>\n";
            //TODO: Make sure, that real NULL values are inserted into the database! By every php-file that inserts anything into the grundplan!
            $Dienstplan[$day_index]['Mittagsende'][] = $row->Mittagsende;
            $Dienstplan[$day_index]['Stunden'][] = $row->Stunden;
        }
        $day_index++;
    }

    return $Dienstplan;
}

function sort_roster_array(&$Roster) {
    foreach ($Roster as $day_index => $row)
        if (!empty($Roster[$day_index]['VK'])) {
            /* Um die Reihenfolge vernünftig zu sortieren, rechnen wir zunächst in Unix-Sekunden um. */
            $Sort_order = array_map('strtotime', $Roster[$day_index]['Dienstbeginn']);
            /* Dann sortieren wir ALLE Elemente des Arrays nach der soeben ermittelten Reihenfolge. */
            array_multisort($Sort_order, $Roster[$day_index]['Dienstbeginn'], $Roster[$day_index]['Dienstende'], $Roster[$day_index]['Mittagsbeginn'], $Roster[$day_index]['Mittagsende'], $Roster[$day_index]['VK']);
        }
}

function determine_lunch_breaks($Dienstplan, $tag) {
    global $Mittag_mitarbeiter;
    if (is_null($tag)) {
        $tag = 0;
    }
    //Hier entsteht die Mittagspausenvergabe.
    if (!empty($Dienstplan[$tag]['VK'])) { //Haben wir überhaupt einen Dienstplan?
        $Besetzte_mittags_beginne = array_map('strtotime', $Dienstplan[$tag]['Mittagsbeginn']); //Zeiten, zu denen schon jemand mit dem Essen beginnt.
        $Besetzte_mittags_enden = array_map('strtotime', $Dienstplan[$tag]['Mittagsende']); //Zeiten, zu denen jemand mit dem Essen fertig ist.
        $pausen_start = strtotime('11:30:00');
        foreach ($Dienstplan[$tag]['VK'] as $position => $vk) { //Die einzelnen Zeilen im Dienstplan
            //echo "Mittag für $Mitarbeiter[$vk]?<br>\n";
            if (!empty($Mittag_mitarbeiter[$vk]) AND ! ($Dienstplan[$tag]['Mittagsbeginn'][$position] > 0) AND ! ($Dienstplan[$tag]['Mittagsende'][$position] > 0)) {
                //echo "Mittag ist noch nicht definiert<br>\n";
                //Zunächst berechnen wir die Stunden, damit wir wissen, wer überhaupt eine Mittagspause bekommt.
                $dienstbeginn = $Dienstplan[$tag]["Dienstbeginn"][$position];
                $dienstende = $Dienstplan[$tag]["Dienstende"][$position];
                $sekunden = strtotime($dienstende) - strtotime($dienstbeginn) - $Mittag_mitarbeiter[$vk] * 60;
                if ($sekunden >= 6 * 3600) {
                    //echo "Mehr als 6 Stunden, also gibt es Mittag!";
                    //Wer länger als 6 Stunden Arbeitszeit hat, bekommt eine Mittagspause.
                    $pausen_ende = $pausen_start + $Mittag_mitarbeiter[$vk] * 60;
                    if (array_search($pausen_start, $Besetzte_mittags_beginne) !== false OR array_search($pausen_ende, $Besetzte_mittags_enden) !== false) {
                        //Zu diesem Zeitpunkt startet schon jemand sein Mittag. Wir warten 30 Minuten (1800 Sekunden)
                        $pausen_start+=1800;
                        $pausen_ende+=1800;
                    }
                    $Dienstplan[$tag]['Mittagsbeginn'][$position] = date('H:i', $pausen_start);
                    $Dienstplan[$tag]['Mittagsende'][$position] = date('H:i', $pausen_ende);
                    $pausen_start = $pausen_ende;
                }
            } elseif (!empty($vk) AND ! empty($Dienstplan[$tag]['Mittagsbeginn'][$position]) AND empty($Dienstplan[$tag]['Mittagsende'][$position])) {
                $Dienstplan[$tag]['Mittagsende'][$position] = date('H:i', strtotime('- ' . $Mittag_mitarbeiter[$vk] . ' minutes', $Dienstplan[$tag]['Mittagsbeginn'][$position]));
            } elseif (!empty($vk) AND empty($Dienstplan[$tag]['Mittagsbeginn'][$position]) AND ! empty($Dienstplan[$tag]['Mittagsende'][$position])) {
                $Dienstplan[$tag]['Mittagsbeginn'][$position] = date('H:i', strtotime('+ ' . $Mittag_mitarbeiter[$vk] . ' minutes', $Dienstplan[$tag]['Mittagsende'][$position]));
            }
        }
    }
    return $Dienstplan;
}
