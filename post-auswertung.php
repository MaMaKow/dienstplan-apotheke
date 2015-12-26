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
		$Dienstplan[$plan]=$inhalt;
	}
//	echo "<pre>";	var_export($Dienstplan);    	echo "</pre>"; // Hier kann der übergebene Datensatz zu Debugging-Zwecken angesehen werden.
	foreach(array_keys($Dienstplan) as $tag ) //Hier sollte eigentlich nur ein einziger Tag ankommen.
	{
		$datum=$Dienstplan[$tag]['Datum'][0];
		$abfrage="DELETE FROM `Dienstplan`
			WHERE `Datum` = '$datum'
			AND `Mandant` = '$mandant'
			;"; //Der Mandant wird entweder als default gesetzt oder per POST übergeben und dann im vorherigen if-clause übeschrieben.
		$ergebnis = mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
//		echo "$abfrage<br>\n";
		foreach($Dienstplan[$tag]['VK'] as $key => $VK) //Die einzelnen Zeilen im Dienstplan
		{
			if ( !empty($VK) ) //Wir ignorieren die nicht ausgefüllten Felder
			{
				list($VK)=explode(' ', $VK); //Wir brauchen nur die VK Nummer. Die steht vor dem Leerzeichen.
				$dienstbeginn=$Dienstplan[$tag]["Dienstbeginn"][$key];
				$dienstende=$Dienstplan[$tag]["Dienstende"][$key];
				$mittagsbeginn=$Dienstplan[$tag]["Mittagsbeginn"][$key]; if(empty($Mittagsbeginn)){$Mittagsbeginn="0:00";}
				$mittagsende=$Dienstplan[$tag]["Mittagsende"][$key]; if(empty($Mittagsende)){$Mittagsende="0:00";}
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
//				echo "$abfrage<br>\n";
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

		//Wir zeichnen eine Kurve der Anzahl der Mitarbeiter.
		require "zeichne-histogramm.php";
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
