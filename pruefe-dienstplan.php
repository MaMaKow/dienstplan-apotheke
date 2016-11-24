<?php
function examine_duty_roster ()
{
    global $verbindungi;
    global $Dienstplan, $mandant, $datum;
    global $Approbierte_mitarbeiter, $Wareneingang_Mitarbeiter;
    //Variabkes that will be set here have to be global too, to make them visible outside.
    global $Fehlermeldung, $Warnmeldung;
    //Diese Datei zählt Anwesende, Approbierte, Ware-Menschen,...
    require_once 'headcount-duty-roster.php';

    if (isset($Approbierten_anwesende) and isset($tages_ende)) {
        //Wir überprüfen ob zu jeder Zeit Approbierte anwesend sind.
        foreach ($Approbierten_anwesende as $zeit => $anwesende_approbierte) {
            if ($anwesende_approbierte === 0 and $zeit != $tages_ende) {
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
            if ($anwesende_wareneingang === 0 and $zeit != $tages_ende) {
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

$abfrage  = "SELECT `first`.`VK`,"
        . " `first`.`Dienstbeginn` as first_start, `first`.`Dienstende` as first_end, "
        . " `first`.`Mandant` as first_branch,"
        . " `second`.`Dienstbeginn` as second_start, `second`.`Dienstende` as second_end,"
        . " `second`.`Mandant` as second_branch"
        . " FROM `Dienstplan` AS first"
        . " 	INNER JOIN `Dienstplan` as second"
        . " 		ON first.VK = second.VK"  //compare multiple different rows together
        . " 		AND first.datum = second.datum" //compare multiple different rows together
        . " WHERE `first`.`Datum` = '$datum'" //some real date here
        . " 	AND ((`first`.`Dienstbeginn` != `second`.`Dienstbeginn` )" //eliminate pure self-duplicates;
        . " 	OR (`first`.`mandant` != `second`.`mandant` ))" //eliminate pure self-duplicates primary key is VK+start+mandant
        . " 	AND (`first`.`Dienstbeginn` > `second`.`Dienstbeginn` AND `first`.`Dienstbeginn` < `second`.`Dienstende`)"; //find overlaping time values!
//echo "$abfrage<br>\n";
$ergebnis = mysqli_query($verbindungi, $abfrage) or error_log("Error: $abfrage <br>".mysqli_error($verbindungi)) and die("Error: $abfrage <br>".mysqli_error($verbindungi));	
while($row = mysqli_fetch_array($ergebnis))
{
    $Fehlermeldung[] = "Konflikt bei Mitarbeiter " 
    . $Mitarbeiter[$row['VK']] 
    ."<br>"
            .$row['first_start']
            ." bis ".$row['first_end']
            ." (".$Kurz_mandant[$row['first_branch']]
            .") mit <br>".$row['second_start']
            ." bis "
            .$row['second_end']
            ." (" 
            .$Kurz_mandant[$row['second_branch']]
            .")!";
   // echo "<pre>"; var_dump($row); echo "</pre><br>\n<br>\n";
    }
examine_duty_roster();
