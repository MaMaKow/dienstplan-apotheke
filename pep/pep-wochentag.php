<?php

require '../default.php';
require '../db-verbindung.php';
require '../db-lesen-mandant.php';

//Die pep Tabelle führt die Mandanten unter einer anderen Nummer. Diese Nummer brauchen wir in den folgenden Aufrufen.
$abfrage = "TRUNCATE pep_zeit_im_wochentag";
$ergebnis = mysqli_query($verbindungi, $abfrage) or die("Error: $abfrage <br>".mysqli_error($verbindungi));

$abfrage = 'SELECT * FROM `Mandant`';
$ergebnis = mysqli_query($verbindungi, $abfrage) or die("Error: $abfrage <br>".mysqli_error($verbindungi));
while ($row = mysqli_fetch_object($ergebnis)) {
    $Mandant_pep[$row->Mandant] = $row->PEP;
}

//Für jeden Mandanten wird jetzt die pep_zeit_im_wochentag in der Datenbank ermittelt.
foreach ($Mandant_pep as $mandant => $name) {
  	echo "$mandant<br>\n";
    for ($wochentag = 1; $wochentag <= 7; ++$wochentag) {#Montag=2, Dienstag=3 Mi4, Do5, Fr6, Sa7
      echo "$mandant\t$wochentag<br>\n";

			$abfrage="SELECT * FROM Öffnungszeiten WHERE Wochentag = ".($wochentag-1)." AND Mandant = ".$mandant;
			$ergebnis = mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
			$row = mysqli_fetch_object($ergebnis);
			if (!empty($row->Beginn) and !empty($row->Ende)) {
				$tages_beginn=strtotime($row->Beginn);
				$tages_ende=strtotime($row->Ende);
			} else {
					continue 1;
					//die ("Es wurden keine Öffnungszeiten hinterlegt. Bitte konfigurieren Sie den Mandanten.\n");
					//$tagesbeginn = strtotime('08:00:00');
				  //$tagesende = strtotime('20:00:00');
			}
    //$abfrage = 'SELECT DISTINCT `Datum`  FROM `pep` WHERE Mandant = '.$Mandant_pep[$mandant].' AND DAYOFWEEK(Datum) = \''.$wochentag.'\' AND Datum >= DATE_SUB(NOW(),INTERVAL 1 YEAR);'; //Eine Liste aller Montage/Dienstage/Mittwoche in den letzten 12 Monaten.
    //$ergebnis = mysqli_query($verbindungi, $abfrage) or die("Error: $abfrage <br>".mysqli_error($verbindungi));
		//	echo "$abfrage<br>\n";
    //while ($row = mysqli_fetch_object($ergebnis)) {

        for ($uhrzeit = $tages_beginn; $uhrzeit < $tages_ende; $uhrzeit = strtotime('+5 minutes', $uhrzeit)) {
//          echo "$mandant\t$wochentag\t".date('G:i:s', $uhrzeit)."<br>\n";

            $anfangs_zeit = date('G:i:s', strtotime('-10 minutes', $uhrzeit));
            $end_zeit = date('G:i:s', strtotime('+10 minutes', $uhrzeit));
            //$abfrageb = 'SELECT sum(`Anzahl`) FROM `pep` WHERE Mandant = '.$Mandant_pep[$mandant]." AND `Datum` = '".$row->Datum."' AND `Zeit` BETWEEN '".$anfangszeit."' AND '".$endzeit."' AND `Anzahl` < 20"; //Summe der verkauften Packungen zu dieser Urzeit
						$abfrage="SELECT AVG(Count) FROM (SELECT SUM( Anzahl ) AS Count FROM  `pep`  WHERE DAYOFWEEK( Datum ) = ".$wochentag." AND Mandant = ".$Mandant_pep[$mandant]." AND `Zeit` BETWEEN '".$anfangs_zeit."' AND '".$end_zeit."' AND `Anzahl` < 20 GROUP BY Datum) AS meins";
            $ergebnis = mysqli_query($verbindungi, $abfrage) or die("Error: $abfrage <br>".mysqli_error($verbindungi));
            $row = mysqli_fetch_row($ergebnis);
            $packungen = $row[0];
						if (!empty($packungen)) {
                $abfrage = "REPLACE INTO pep_zeit_im_wochentag (Uhrzeit, Wochentag, Mittelwert, Mandant) VALUES ('".date('G:i:s', $uhrzeit)."', '$wochentag', '$packungen', '$mandant')";
                $ergebnis = mysqli_query($verbindungi, $abfrage) or die("Error: $abfrage ".mysqli_error($verbindungi));
            }

        }
    //}
#Wir haben jetzt eine Liste der Montage, Dienstage, .... Samstage in der PEP-Tabelle.
#Es werden bis zu 19 Artikel im Abverkauf regelmäßig gefunden. Größere Vorgänge sind gegenseitige Bestellungen oder andere Buchungen.
#Bestimmt anhand einer Gauß-Verteilungs-Kurve
#SELECT `Anzahl`, COUNT(*) FROM `pep` GROUP BY `Anzahl`
    }
}

#echo "<pre>"; var_dump($Packungen); echo"</pre>";
?>
