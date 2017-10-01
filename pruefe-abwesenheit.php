<?php

//TODO: Put some functions here!
//Wir werfen einen Blick in den Urlaubsplan und schauen, ob alle da sind.
//for ($i=0; $i<count($Dienstplan); $i++)
//{
if (!isset($i)) {
    $i = 0;
    // TODO: This is an Exception. It should probably be handled better!
}
unset($Abwesende);
$datum = $Dienstplan[$i]['Datum'][0];
$Abwesende = db_lesen_abwesenheit($datum);
if (isset($Dienstplan[$i]['VK'])) {
    $Eingesetzte_mitarbeiter = array_values($Dienstplan[$i]['VK']);
} else {
    $Eingesetzte_mitarbeiter = array();
    //continue;
}
if (isset($Abwesende)) {
    foreach ($Abwesende as $abwesender => $grund) {
        foreach ($Eingesetzte_mitarbeiter as $anwesender) {
            if ($abwesender == $anwesender) {
                $Arbeitende_abwesende[] = $anwesender;
            }
        }
    }
    if (isset($Arbeitende_abwesende)) {
        foreach ($Arbeitende_abwesende as $arbeitender_abwesender) {
            $Fehlermeldung[] = $Mitarbeiter[$arbeitender_abwesender] . " ist abwesend (" . $Abwesende[$arbeitender_abwesender] . ") und sollte nicht im Dienstplan stehen.";
        }
    }
}

//Let us check if everyone is there:
if (NULL !== $Principle_roster and FALSE === $holiday) {
    $Principle_roster_workers = $Principle_roster[$tag]["VK"];
    $Available_roster_workers = array_unique(array_merge(array_keys($Mandanten_mitarbeiter), $Principle_roster_workers)); //We combine the employees in the branch and the employees in the principle roster.
    $Mitarbeiter_differenz = array_diff($Available_roster_workers, $Eingesetzte_mitarbeiter);
    if (isset($Abwesende)) {
        $Mitarbeiter_differenz = array_diff($Mitarbeiter_differenz, array_keys($Abwesende));
    }
    if (!empty($Mitarbeiter_differenz)) {
        $separator = "";
        $fehler = "Es sind folgende Mitarbeiter nicht eingesetzt: <br>\n";
        foreach ($Mitarbeiter_differenz as $arbeiter) {
            $position_in_principle_roster = array_search($arbeiter, $Principle_roster[$tag]["VK"]);
            $fehler .= $separator . $Mitarbeiter[$arbeiter];
            $fehler .= " ("
                    . $Principle_roster[$tag]["Dienstbeginn"][$position_in_principle_roster]
                    . " - "
                    . $Principle_roster[$tag]["Dienstende"][$position_in_principle_roster]
                    . ")";
            $separator = ", <br>";
        }
        $fehler.="\n";
        //TODO: A Warning would probably be enough.
        $Fehlermeldung[] = $fehler;
    }
}
