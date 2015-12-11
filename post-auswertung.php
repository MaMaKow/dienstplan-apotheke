<?php
require 'funktionen.php';
//Hier schauen wir, welche Daten an uns übersendet wurden und aus welchem Formular sie stammen.
if ( isset($_POST['mandant']))
{
	$mandant=htmlspecialchars($_POST['mandant']);
}
if ( isset($_POST['datum']))
{
	$datum=htmlspecialchars($_POST['datum']);
}
if ( isset($_POST['submitDienstplan']) && count($_POST['Dienstplan']) > 0 )
{
	$datenempfang="Die Daten wurden empfangen.<br>\n";
//	$datum=$_POST['Dienstplan'][0]['Datum'][0];
	foreach ( $_POST['Dienstplan'] as $plan => $inhalt ) 
	{
		$Dienstplanung[$plan]=$inhalt;
	}
//	echo "<pre>";	var_export($Dienstplanung);    	echo "</pre>"; // Hier kann der übergebene Datensatz zu Debugging-Zwecken angesehen werden.
	foreach(array_keys($Dienstplanung) as $tag ) //Hier sollte eigentlich nur ein einziger Tag ankommen.
	{
		$datum=$Dienstplanung[$tag]['Datum'][0];
		$abfrage="DELETE FROM `Dienstplan`
			WHERE `Datum` = '$datum'
			AND `Mandant` = '$mandant'
			;"; //Der Mandant wird entweder als default gesetzt oder per POST übergeben und dann im vorherigen if-clause übeschrieben.
		$ergebnis = mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
		foreach($Dienstplanung[$tag]['VK'] as $key => $VK) //Die einzelnen Zeilen im Dienstplan
		{
			if ( !empty($VK) ) //Wir ignorieren die nicht ausgefüllten Felder
			{
				list($VK)=explode(' ', $VK); //Wir brauchen nur die VK Nummer. Die steht vor dem Leerzeichen.
				$dienstbeginn=$Dienstplanung[$tag]["Dienstbeginn"][$key];
				$dienstende=$Dienstplanung[$tag]["Dienstende"][$key];
				$mittagsbeginn=$Dienstplanung[$tag]["Mittagsbeginn"][$key]; if(empty($Mittagsbeginn)){$Mittagsbeginn="0:00";}
				$mittagsende=$Dienstplanung[$tag]["Mittagsende"][$key]; if(empty($Mittagsende)){$Mittagsende="0:00";}
	//			$kommentar='Noch nicht eingebaut'
				if (isset($mittagsbeginn) && isset($mittagsende))
				{
					$sekunden=strtotime($dienstende)-strtotime($dienstbeginn);
					$mittagspause=strtotime($mittagsende)-strtotime($mittagsbeginn);
					$sekunden=$sekunden-$mittagspause;
					$stunden=$sekunden/3600;
				}
				else
				{
					$sekunden=strtotime($dienstende)-strtotime($dienstbeginn);
					$stunden=$sekunden/3600;
				}
				$abfrage="REPLACE INTO `Dienstplan` (VK, Datum, Dienstbeginn, Dienstende, Mittagsbeginn, Mittagsende, Stunden, Mandant) 
					VALUES ('$VK', '$datum', '$dienstbeginn', '$dienstende', '$mittagsbeginn', '$mittagsende', '$stunden', '$mandant')";  
				$ergebnis = mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
				$Datenübertragung="Die Daten wurden in die Datenbank eingetragen.<br>\n";
				//Und jetzt schreiben wir die Daten noch in eine Datei, damit wir sie mit gnuplot darstellen können.
				if(empty($mittagsbeginn)){$mittagsbeginn="0:00";}
				if(empty($mittagsende)){$mittagsende="0:00";}
				$dienstplanCSV.=$Mitarbeiter[$VK].", $VK, $datum";
				$dienstplanCSV.=", ".$dienstbeginn;
				$dienstplanCSV.=", ".$dienstende;
				$dienstplanCSV.=", ".$mittagsbeginn;
				$dienstplanCSV.=", ".$mittagsende;  
				$dienstplanCSV.=", ".$stunden;  
				$dienstplanCSV.=", ".$mandant."\n";  
		}
		}
		$filename = "tmp/Dienstplan.csv";
		$myfile = fopen($filename, "w") or die("Unable to open file!");
		fwrite($myfile, $dienstplanCSV);
		fclose($myfile);
		$dienstplanCSV="";
		$command=('./Dienstplan_image.sh '.escapeshellcmd("m".$mandant."_".$datum));
		exec($command, $kommandoErgebnis);
//		echo "<pre>";	var_export($kommandoErgebnis);    	echo "</pre>"; // Hier kann der aus der Datenbank gelesene Datensatz zu Debugging-Zwecken angesehen werden.
		
		//Wir zeichnen eine Kurve der Anzahl der Mitarbeiter.
		//Zunächst müssen wir festlegen, wie groß die Zeitsprünge im Diagramm werden.
		$zeitAbstand=30*60; //Eine halbe Stunde
		$tagesBeginn=strtotime(min(array_filter(array_values($Dienstplanung[$tag]["Dienstbeginn"]))));
		$tagesEnde=strtotime(max(array_values($Dienstplanung[$tag]["Dienstende"])));
//		echo "Wir beginnen bei $tagesBeginn und enden nach Schritten von $zeitAbstand bei $tagesEnde.";
		for ($dienstzeit=$tagesBeginn; $dienstzeit<=$tagesEnde; $dienstzeit=$dienstzeit+$zeitAbstand){$Dienstzeiten[]=$dienstzeit;}
		$DienstEnden=array_map('strtotime', $Dienstplanung[$tag]["Dienstende"]);
		$DienstBeginne=array_map('strtotime', $Dienstplanung[$tag]["Dienstbeginn"]);
		$MittagsEnden=array_map('strtotime', $Dienstplanung[$tag]["Mittagsende"]);
		$MittagsBeginne=array_map('strtotime', $Dienstplanung[$tag]["Mittagsbeginn"]);
		$histogrammCSV="";
		foreach($Dienstzeiten as $zeit)
		{
			$gekommene=count(array_filter(array_filter($DienstBeginne, function($value) {global $zeit; return $value <= $zeit;}))); //Die Zahl der Mitarbeiter, die irgendwann heute angefangen haben.
			$nichtgegangene=count(array_filter(array_filter($DienstEnden, function($value) {global $zeit; return $value > $zeit;}))); //Die Anzahl der Mitarbeiter, die noch nicht gegangen sind.
			$mittagende=count(array_filter(array_filter($MittagsBeginne, function($value) {global $zeit; return $value <= $zeit;})));
			$gemittagte=count(array_filter(array_filter($MittagsEnden, function($value) {global $zeit; return $value <= $zeit;})));
			$mittagende=$mittagende-$gemittagte;
			$anwesende=min($gekommene, $nichtgegangene);
			$anwesende=$anwesende-$mittagende;
			$histogrammCSV.=date('H:i', $zeit).", ".$anwesende."\n";
		}
		$filename = "tmp/Histogramm.csv";
		$myfile = fopen($filename, "w") or die("Unable to open file!");
		fwrite($myfile, $histogrammCSV);
		fclose($myfile);
		$histogrammCSV="";
		//Und jetzt erraten wir noch die geschätzen Packungen, die wir an diesem Tag pro Zeit abverkaufen.
		$lines=file('./pep/pep_monatimjahr.csv');
		foreach($lines as $key => $value)
		{
			list($Monatimjahr['monat'][], $Monatimjahr['min'][], $Monatimjahr['median'][], $Monatimjahr['max'][])=explode(", ", $value);
		}
		$durchschnittsmonat=calculate_percentile($Monatimjahr['median'],50);
		$faktorMonatimjahr=$Monatimjahr['median'][date('n', strtotime($datum))-1]/$durchschnittsmonat;
		$lines=file('./pep/pep_monatstag.csv');
		foreach($lines as $key => $value)
		{
			list($Tagimmonat['tag'][], $Tagimmonat['min'][], $Tagimmonat['median'][], $Tagimmonat['max'][])=explode(", ", $value);
		}
		$durchschnittstag=calculate_percentile($Tagimmonat['median'],50);

		$faktorTagimmonat=$Tagimmonat['median'][date('n', strtotime($datum))-1]/$durchschnittstag;
		$datei="./pep/pep_wochentag";
		$datei.=date("w", strtotime($datum))+1;
		$datei.=".csv";
		$lines=file($datei);
		foreach($lines as $key => $value)
		{
			list($Wochentag['uhrzeit'][], $Wochentag['min'][], $Wochentag['median'][], $Wochentag['max'][])=explode(", ", $value);
		}
		$erwartungCSV="";
		foreach($Wochentag['median'] as $key => $tageszeitmedian)
		{
			$Erwartung['uhrzeit'][]=$Wochentag['uhrzeit'][$key];
			$Erwartung['packungen'][]=$tageszeitmedian*$faktorTagimmonat*$faktorMonatimjahr;
			$erwartungCSV.=$Wochentag['uhrzeit'][$key].", ".$tageszeitmedian*$faktorTagimmonat*$faktorMonatimjahr."\n";
		}
		
		$filename = "tmp/Erwartung.csv";
		$myfile = fopen($filename, "w") or die("Unable to open file!");
		fwrite($myfile, $erwartungCSV);
		fclose($myfile);
		$erwartungCSV="";
//		echo "<pre>";	var_export($Erwartung);    	echo "</pre>"; // Hier kann der aus der Datenbank gelesene Datensatz zu Debugging-Zwecken angesehen werden.
//			echo "<pre>";	var_export($anwesende);    	echo "</pre>"; // Hier kann der aus der Datenbank gelesene Datensatz zu Debugging-Zwecken angesehen werden.
		$command=('./Histogramm_image.sh '.escapeshellcmd("m".$mandant."_".$datum));
		exec($command, $kommandoErgebnis);
		//debug DEBUG to do: Die Dateien im tmp/ könnten wir anschließend alle wieder löschen.
		//debug DEBUG to do: EinEindeutige Unique Namen! Wenn gleichtzeitig mehrere Mitarbeiter zugreifen, werden mehrere Dateien mit dem gleichen Namen erzeugt. Das kann zu Fehlern führen.
	}
}
elseif ( isset($_POST['submitWocheVorwärts']) && isset($_POST['Dienstplan'][0]['Datum'][0])  )
{
	$datum=$_POST['Dienstplan'][0]['Datum'][0];
	$datum=strtotime('+1 week', strtotime($datum));
	$datum=date('Y-m-d', $datum);
	$datenempfang="Tag wurde geblättert.<br>\n";
}
elseif ( isset($_POST['submitWocheRückwärts']) && isset($_POST['Dienstplan'][0]['Datum'][0])  )
{
	$datum=$_POST['Dienstplan'][0]['Datum'][0];
	$datum=strtotime('-1 week', strtotime($datum));
	$datum=date('Y-m-d', $datum);
	$datenempfang="Tag wurde geblättert.<br>\n";
}
elseif ( isset($_POST['submitVorwärts']) && isset($_POST['Dienstplan'][0]['Datum'][0])  )
{
	$datum=$_POST['Dienstplan'][0]['Datum'][0];
	$datum=strtotime('+1 day', strtotime($datum));
	$datum=date('Y-m-d', $datum);
	$datenempfang="Tag wurde geblättert.<br>\n";
}
elseif ( isset($_POST['submitRückwärts']) && isset($_POST['Dienstplan'][0]['Datum'][0])  )
{
	$datum=$_POST['Dienstplan'][0]['Datum'][0];
	$datum=strtotime('-1 day', strtotime($datum));
	$datum=date('Y-m-d', $datum);
	$datenempfang="Tag wurde geblättert.<br>\n";
}
elseif ( isset($_POST['wochenAuswahl']) && isset($_POST['woche'])  )
{
	$datum=$_POST['woche'];
	$montagsDifferenz=date("w", strtotime($datum))-1; //Wir wollen den Anfang der Woche
	$montagsDifferenzString="-".$montagsDifferenz." day";
	$datum=strtotime($montagsDifferenzString, strtotime($datum));
	$datum=date('Y-m-d', $datum);
	$datenempfang="Das Formular wartet auf Eingabe.<br>\n";
}
elseif ( isset($_POST['tagesAuswahl']) && isset($_POST['tag'])  )
{
	$datum=$_POST['tag'];
	$datenempfang="Das Formular wartet auf Eingabe.<br>\n";
}
elseif ( isset($_POST['tagesAuswahl']) && isset($_POST['woche'])  )
{
	$datum=$_POST['woche'];
	$datenempfang="Das Formular wartet auf Eingabe.<br>\n";
}
elseif ( isset($_POST['submitCopyPaste']) && count($_POST['Dienstplan']) > 0 )
{
require 'copy-paste.php';
}
else
{
	$datenempfang="Das Formular wartet auf Eingabe.<br>\n";
}
?>
