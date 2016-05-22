<?php
function examine_duty_roster ()
{
    global $verbindungi;
    global $Dienstplan, $mandant, $datum;
    global $Approbierte_mitarbeiter, $Wareneingang_Mitarbeiter;
    //Diese Datei zählt Anwesende, Approbierte, Ware-Menschen,...
    require_once 'headcount-duty-roster.php';

    if (isset($Approbierten_anwesende) and isset($tages_ende)) {
        //Wir überprüfen ob zu jeder Zeit Approbierte anwesend sind.
        foreach ($Approbierten_anwesende as $zeit => $anwesende_approbierte) {
            if ($anwesende_approbierte == 0 and $zeit != $tages_ende) {
              if (!isset($attendant_error)) {
                $Fehlermeldung[] = 'Um '.date('H:i', $zeit).' Uhr ist kein Approbierter anwesend.';
                //We avoid to flood everything with errors for every 5 minutes in which noone is there.
                $attendant_error=true;
                //break 1;
              }
            }
            else {
                unset ($attendant_error);
            }
        }
    } else {
        echo "Notwendige Variablen sind nicht gesetzt. Keine Zählung der anwesenden Approbierten.<br>\n";
    }
    if (isset($Wareneingang_Anwesende) and isset($tages_ende)) {
            //Wir überprüfen ob zu jeder Zeit jemand anwesend ist, der den Wareneingang machen kann.
            // TODO: Die tatsächlichen Termine für den Wareneingang wären sinnvoller, als die Öffnungszeiten. ($tages_ende)
        foreach ($Wareneingang_Anwesende as $zeit => $anwesende_wareneingang) {
            if ($anwesende_wareneingang == 0 and $zeit != $tages_ende) {
              if (!isset($attendant_error)) {
                $Warnmeldung[] = 'Um '.date('H:i', $zeit).' Uhr ist niemand für den Wareneingang anwesend.';
                //break 1; //We avoid to flood everything with errors for every 5 minutes in which noone is there.
                $attendant_error=true;
              }
            }
            else {
                unset ($attendant_error);
            }
        }
    } else {
        echo "Notwendige Variablen sind nicht gesetzt. Keine Zählung der anwesenden Ware-Menschen.<br>\n";
    }
    if (isset($Anwesende) and isset($tages_ende)) {
            //Wir überprüfen ob zu jeder Zeit jemand anwesend ist, der den Wareneingang machen kann.
            // TODO: Die tatsächlichen Termine für den Wareneingang wären sinnvoller, als die Öffnungszeiten. ($tages_ende)
        foreach ($Anwesende as $zeit => $anwesende) {
            if ($anwesende < 2 and $zeit != $tages_ende) {
                if (!isset($attendant_error)) {
                  $Fehlermeldung[] = 'Um '.date('H:i', $zeit).' Uhr sind weniger als zwei Mitarbeiter anwesend.';
                  $attendant_error=true;
                }
                //break 1; //We avoid to flood everything with errors for every 5 minutes in which noone is there.
            }
            else {
                unset ($attendant_error);
            }
        }
    } else {
        echo "Notwendige Variablen sind nicht gesetzt. Keine Zählung der Anwesenden.<br>\n";
    }
  }

examine_duty_roster();
