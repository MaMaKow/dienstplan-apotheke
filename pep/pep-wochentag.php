<?php 
require '../default.php';
require '../db-verbindung.php';

for ($wochentag=1; $wochentag<=7; $wochentag++) #Montag=2, Dienstag=3 Mi4, Do5, Fr6, Sa7
{
#	echo "Wochentag $wochentag <br>\n";
	$abfrage='SELECT DISTINCT `Datum`  FROM `pep` WHERE DAYOFWEEK(Datum) = \''.$wochentag.'\' AND Datum >= DATE_SUB(NOW(),INTERVAL 1 YEAR);'; //Eine Liste aller Montage/Dienstage/Mittwoche in den letzten 12 Monaten.
	$ergebnis = mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
//	echo "$abfrage<br>\n";
	while($row = mysqli_fetch_object($ergebnis))
	{
#		$Tagesliste[$wochentag][]=$row->Datum;
//		echo $row->Datum; echo "<br>\n";
//		$schrittweite=15; //Abstand zwischen zwei Messungen in Minuten
		$tagesbeginn=strtotime("08:00:00");
		$tagesende = strtotime("20:00:00");
//		for ($uhrzeit=8; $uhrzeit<20; $uhrzeit++)
		for ($uhrzeit=$tagesbeginn; $uhrzeit<$tagesende; $uhrzeit=strtotime("+1 minutes", $uhrzeit))
		{
//			echo date('G:i:s', $uhrzeit)."<br>\n";
			$anfangszeit=date('G:i:s', strtotime('-10 minutes', $uhrzeit));
			$endzeit=date('G:i:s', strtotime('+10 minutes', $uhrzeit));
#			echo "$uhrzeit Uhr: ";
			$abfrageb = "SELECT sum(`Anzahl`) FROM `pep` WHERE `Datum` = '".$row->Datum."' AND `Zeit` BETWEEN '".$anfangszeit."' AND '".$endzeit."' AND `Anzahl` < 20"; //Summe der verkauften Packungen zu dieser Urzeit
			$ergebnisb = mysqli_query($verbindungi, $abfrageb) OR die ("Error: $abfrageb <br>".mysqli_error($verbindungi));
			while($rowb = mysqli_fetch_row($ergebnisb))
			{
#				echo "$rowb[0] <br>";
				$Packungen[$wochentag][$uhrzeit][]=$rowb[0];
			}	
		}
	}
#Wir haben jetzt eine Liste der Montage, Dienstage, .... Samstage in der PEP-Tabelle.
#Es werden bis zu 19 Artikel im Abverkauf regelmäßig gefunden. Größere Vorgänge sind gegenseitige Bestellungen oder andere Buchungen.
#Bestimmt anhand einer Gauß-Verteilungs-Kurve
#SELECT `Anzahl`, COUNT(*) FROM `pep` GROUP BY `Anzahl` 
}


$txt="";
for ($wochentag=1; $wochentag<=7; $wochentag++) 
{
//	for ($uhrzeit=8; $uhrzeit<20; $uhrzeit++)
	foreach($Packungen[$wochentag] as $uhrzeit => $ZeitPackungen)
	{
		$txt.=date('G:i:s', $uhrzeit).", ".calculate_percentile($Packungen[$wochentag][$uhrzeit],5) .", ". calculate_percentile($Packungen[$wochentag][$uhrzeit],50) .", ".  calculate_percentile($Packungen[$wochentag][$uhrzeit],95) . "\n";
	}
	$filename = "pep_wochentag".$wochentag.".csv";
	$myfile = fopen($filename, "w") or die("Unable to open file!");
	fwrite($myfile, $txt);
	fclose($myfile);
	$txt= "";
}



#echo "Montags ab 8 bis 9 werden durchschnittlich ".$packungsmedian." Packungen abgegeben. <br>";
#echo "Überwiegend sind es ".calculate_percentile($Packungen[2][8],25)." bis ".calculate_percentile($Packungen[2][8],75)." Packungen.<br>";

//echo "<pre>"; var_dump($Packungen); echo"</pre>";
//echo "Das Script wurde ausgeführt.";
?>
