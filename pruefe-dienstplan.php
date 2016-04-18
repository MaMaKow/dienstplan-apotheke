<?php
    if (isset($ApprobiertenAnwesende) and isset($tagesEnde)) {
        //Wir überprüfen ob zu jeder Zeit Approbierte anwesend sind.
        foreach ($ApprobiertenAnwesende as $zeit => $anwesendeApprobierte) {
            if ($anwesendeApprobierte == 0 and $zeit != $tagesEnde) {
                $Fehlermeldung[] = 'Um '.date('H:i', $zeit).' Uhr ist kein Approbierter anwesend.';
                break 1; //We avoid to flood everything with errors for every 5 minutes in which noone is there.
            }
        }
    } else {
        echo 'Notwendige Variablen sind nicht gesetzt. Keine Zählung der anwesenden Approbierten.';
    }
    if (isset($Wareneingang_Anwesende) and isset($tagesEnde)) {
            //Wir überprüfen ob zu jeder Zeit jemand anwesend ist, der den Wareneingang machen kann.
            // TODO: Die tatsächlichen Termine für den Wareneingang wären sinnvoller, als die Öffnungszeiten. ($tagesEnde)
        foreach ($Wareneingang_Anwesende as $zeit => $anwesende_wareneingang) {
            if ($anwesende_wareneingang == 0 and $zeit != $tagesEnde) {
                $Fehlermeldung[] = 'Um '.date('H:i', $zeit).' Uhr niemand für den Wareneingang anwesend.';
                break 1; //We avoid to flood everything with errors for every 5 minutes in which noone is there.
            }
        }
    } else {
        echo 'Notwendige Variablen sind nicht gesetzt. Keine Zählung der anwesenden Ware-Menschen.';
    }
