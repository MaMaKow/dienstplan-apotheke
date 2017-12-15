<?php

if (!isset($tag)) {
    $tag = 0;
} //Beim Aufruf aus tag-out wird kein Tag übergeben. Beim Aufruf aus der Auswertung, wird ein $tag übergeben.
$Approbierten_anwesende = array();
$Wareneingang_Anwesende = array();
if (!empty($Dienstplan[$tag]["Dienstbeginn"])) {
    //Im folgenden Suchen wir die Approbierten, die heute anwesend sind. Sie werden im $Approbierten_plan gespeichert.
    $Spalten = array_keys($Dienstplan[$tag]);
    foreach ($Dienstplan[$tag]["VK"] as $key => $value) {
        if (array_search($value, array_keys($Approbierte_mitarbeiter)) !== false) {
            foreach ($Spalten as $spalte) {
                $Approbierten_dienstplan[$tag][$spalte][$key] = $Dienstplan[$tag][$spalte][$key];
            }
        }
    }
    foreach ($Dienstplan[$tag]["VK"] as $key => $value) {
        if (array_search($value, array_keys($Wareneingang_Mitarbeiter)) !== false) {
            foreach ($Spalten as $spalte) {
                $Wareneingang_dienstplan[$tag][$spalte][$key] = $Dienstplan[$tag][$spalte][$key];
            }
        }
    }

    //Wir lesen die Öffnungszeiten aus der Datenbank.
    $sql_query = "SELECT * FROM Öffnungszeiten WHERE Wochentag = " . date('N', strtotime($datum)) . " AND Mandant = " . $mandant;
    $result = mysqli_query_verbose($sql_query);
    $row = mysqli_fetch_object($result);
    if (!empty($row->Beginn) and ! empty($row->Ende)) {
        $tages_beginn = strtotime($row->Beginn);
        $tages_ende = strtotime($row->Ende);
    } else {
        $Warnmeldung[] = "Es wurden keine Öffnungszeiten hinterlegt. Bitte konfigurieren Sie den Mandanten.";
        $tages_beginn = strtotime('1:00');
        $tages_ende = strtotime('23:00');
        return 1;
    }
    //print_debug_variable('$Dienstplan, $Dienstplan[$tag], $Dienstplan[$tag]["Dienstbeginn"]', $Dienstplan, $Dienstplan[$tag], $Dienstplan[$tag]["Dienstbeginn"]);
    //Für den Fall, dass auch außerhalb der üblichen Zeiten jemand anwesend ist (Notdienst, Late-Night,...) könnte man mit den folgenden zwei Zeilen die Prüfung erweitern.
    //$tages_beginn=min($tages_beginn, strtotime(min(array_filter(array_values($Dienstplan[$tag]["Dienstbeginn"])))));
    //$tages_ende=max($tages_ende, strtotime(max(array_values($Dienstplan[$tag]["Dienstende"]))));
    //Wenn die Funktion bereits aufgerufen wurde, ist dieser Wert bereits gesetzt.
    if (empty($Changing_times[0])) {
        global $Changing_times;
        //print_debug_variable("calculate_changing_times");
        $Changing_times = calculate_changing_times($Dienstplan);
    }
    //print_debug_variable('$Changing_times', $Changing_times);
    if (isset($Approbierten_dienstplan)) {
        $Approbierten_dienst_enden = array_map('strtotime', $Approbierten_dienstplan[$tag]["Dienstende"]);
        $Approbierten_dienst_beginne = array_map('strtotime', $Approbierten_dienstplan[$tag]["Dienstbeginn"]);
        $Approbierten_mittags_enden = array_map('strtotime', $Approbierten_dienstplan[$tag]["Mittagsende"]);
        $Approbierten_mittags_beginne = array_map('strtotime', $Approbierten_dienstplan[$tag]["Mittagsbeginn"]);
    }
    if (isset($Wareneingang_dienstplan)) {
        $Wareneingang_Dienst_Enden = array_map('strtotime', $Wareneingang_dienstplan[$tag]["Dienstende"]);
        $Wareneingang_Dienst_Beginne = array_map('strtotime', $Wareneingang_dienstplan[$tag]["Dienstbeginn"]);
        $Wareneingang_Mittags_Enden = array_map('strtotime', $Wareneingang_dienstplan[$tag]["Mittagsende"]);
        $Wareneingang_Mittags_Beginne = array_map('strtotime', $Wareneingang_dienstplan[$tag]["Mittagsbeginn"]);
    }
    $Dienst_enden = array_map('strtotime', $Dienstplan[$tag]["Dienstende"]);
    $Dienst_beginne = array_map('strtotime', $Dienstplan[$tag]["Dienstbeginn"]);
    $Mittags_enden = array_map('strtotime', $Dienstplan[$tag]["Mittagsende"]);
    $Mittags_beginne = array_map('strtotime', $Dienstplan[$tag]["Mittagsbeginn"]);
    $histogrammCSV = "";
    foreach ($Changing_times as $zeit) {
        //Die folgende Umschreibung von $zeit auf eine globale $dienstzeit ist notwendig, um innerhalb der array-filter Funtion darauf per global zugreifen zu können.
        global $unix_time;
        $unix_time = strtotime($zeit);
        if (isset($Wareneingang_dienstplan)) {
            //Wir zählen die Approbierten Mitarbeiter
            //Die Zahl der Mitarbeiter, die irgendwann heute angefangen haben.
            $wareneingang_Gekommene = count(array_filter(array_filter($Wareneingang_Dienst_Beginne, function($value) {
                                global $unix_time;
                                return $value <= $unix_time;
                            })));
            //Die Anzahl der Mitarbeiter, die noch nicht gegangen sind.
            $wareneingang_Gegangene = count(array_filter(array_filter($Wareneingang_Dienst_Enden, function($value) {
                                global $unix_time;
                                return $value <= $unix_time;
                            })));
            $wareneingang_Mittagende = count(array_filter(array_filter($Wareneingang_Mittags_Beginne, function($value) {
                                global $unix_time;
                                return $value <= $unix_time;
                            })));
            $wareneingang_Gemittagte = count(array_filter(array_filter($Wareneingang_Mittags_Enden, function($value) {
                                global $unix_time;
                                return $value <= $unix_time;
                            })));
            $wareneingang_Mittagende = $wareneingang_Mittagende - $wareneingang_Gemittagte;
            $wareneingang_Anwesende = $wareneingang_Gekommene - $wareneingang_Gegangene;
            $wareneingang_Anwesende = $wareneingang_Anwesende - $wareneingang_Mittagende;
            $Wareneingang_Anwesende[$unix_time] = $wareneingang_Anwesende;
        }
        if (isset($Approbierten_dienstplan)) {
            //Wir zählen die Approbierten Mitarbeiter
            //Die Zahl der Mitarbeiter, die irgendwann heute angefangen haben.
            $approbierten_gekommene = count(array_filter(array_filter($Approbierten_dienst_beginne, function($value) {
                                global $unix_time;
                                return $value <= $unix_time;
                            })));
            //Die Anzahl der Mitarbeiter, die noch nicht gegangen sind.
            $approbierten_gegangene = count(array_filter(array_filter($Approbierten_dienst_enden, function($value) {
                                global $unix_time;
                                return $value <= $unix_time;
                            })));
            $approbierten_mittagende = count(array_filter(array_filter($Approbierten_mittags_beginne, function($value) {
                                global $unix_time;
                                return $value <= $unix_time;
                            })));
            $approbierten_gemittagte = count(array_filter(array_filter($Approbierten_mittags_enden, function($value) {
                                global $unix_time;
                                return $value <= $unix_time;
                            })));
            $approbierten_mittagende = $approbierten_mittagende - $approbierten_gemittagte;
            $approbierten_anwesende = $approbierten_gekommene - $approbierten_gegangene;
            $approbierten_anwesende = $approbierten_anwesende - $approbierten_mittagende;
            $Approbierten_anwesende[$unix_time] = $approbierten_anwesende;
        }
        //Und jetzt noch mal für alle Mitarbeiter
        $gekommene = count(array_filter(array_filter($Dienst_beginne, function($value) {
                            global $unix_time;
                            return $value <= $unix_time;
                        }))); //Die Zahl der Mitarbeiter, die irgendwann heute angefangen haben.
        $gegangene = count(array_filter(array_filter($Dienst_enden, function($value) {
                            global $unix_time;
                            return $value <= $unix_time;
                        }))); //Die Anzahl der Mitarbeiter, die noch nicht gegangen sind.
        $mittagende = count(array_filter(array_filter($Mittags_beginne, function($value) {
                            global $unix_time;
                            return $value <= $unix_time;
                        })));
        $gemittagte = count(array_filter(array_filter($Mittags_enden, function($value) {
                            global $unix_time;
                            return $value <= $unix_time;
                        })));
        $mittagende = $mittagende - $gemittagte;
        $anwesende = $gekommene - $gegangene;
        $anwesende = $anwesende - $mittagende;
        $Anwesende[$unix_time] = $anwesende;
    }
    //print_debug_variable('$Anwesende', $Anwesende);
} else {
    global $Warnmeldung;
    $Warnmeldung[] = "Kein Dienstplan gefunden beim Zeichnen des Histogramms.";
}
