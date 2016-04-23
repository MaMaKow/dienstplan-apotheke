<?php
    if (isset($Approbierten_anwesende) and isset($tages_ende)) {
        //Wir überprüfen ob zu jeder Zeit Approbierte anwesend sind.
        foreach ($Approbierten_anwesende as $zeit => $anwesende_approbierte) {
            if ($anwesende_approbierte == 0 and $zeit != $tages_ende) {
                $Fehlermeldung[] = 'Um '.date('H:i', $zeit).' Uhr ist kein Approbierter anwesend.';
                break 1; //We avoid to flood everything with errors for every 5 minutes in which noone is there.
            }
        }
    } else {
        echo 'Notwendige Variablen sind nicht gesetzt. Keine Zählung der anwesenden Approbierten.';
    }
    if (isset($Wareneingang_Anwesende) and isset($tages_ende)) {
            //Wir überprüfen ob zu jeder Zeit jemand anwesend ist, der den Wareneingang machen kann.
            // TODO: Die tatsächlichen Termine für den Wareneingang wären sinnvoller, als die Öffnungszeiten. ($tages_ende)
        foreach ($Wareneingang_Anwesende as $zeit => $anwesende_wareneingang) {
            if ($anwesende_wareneingang == 0 and $zeit != $tages_ende) {
                $Fehlermeldung[] = 'Um '.date('H:i', $zeit).' Uhr niemand für den Wareneingang anwesend.';
                break 1; //We avoid to flood everything with errors for every 5 minutes in which noone is there.
            }
        }
    } else {
        echo 'Notwendige Variablen sind nicht gesetzt. Keine Zählung der anwesenden Ware-Menschen.';
    }
