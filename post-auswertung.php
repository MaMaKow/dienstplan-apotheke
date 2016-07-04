<?php
//Hier schauen wir, welche Daten an uns übersendet wurden und aus welchem Formular sie stammen.
if ( isset($_POST['mandant']))
{
	if (is_int((int)$_POST['mandant'])){
		$mandant=htmlspecialchars($_POST['mandant']);
	} else {
		throw new InvalidArgumentException("Ungültiger Wert für Mandant per POST übergeben");
	}
}

if ( isset($_POST['datum']))
{
	echo "$datum";
	$datum=htmlspecialchars($_POST['datum']);
}

if ( isset($_POST['submitDienstplan']) && count($_POST['Dienstplan']) > 0 )
{
	foreach ( $_POST['Dienstplan'] as $plan => $inhalt )
	{
		$Dienstplan[$plan]=$inhalt;
	}
	foreach(array_keys($Dienstplan) as $tag ) //Hier sollte eigentlich nur ein einziger Tag ankommen.
	{
		$datum=$Dienstplan[$tag]['Datum'][0];
		$abfrage="DELETE FROM `Dienstplan`
			WHERE `Datum` = '$datum'
			AND `Mandant` = '$mandant'
			;"; //Der Mandant wird entweder als default gesetzt oder per POST übergeben und dann im vorherigen if-clause übeschrieben.
		$ergebnis = mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
		foreach($Dienstplan[$tag]['VK'] as $key => $VK) //Die einzelnen Zeilen im Dienstplan
		{
			if ( !empty($VK) ) //Wir ignorieren die nicht ausgefüllten Felder
			{
				// TODO: Do we still need to explode? Or is only the number sent in POST?
				list($VK)=explode(' ', $VK); //Wir brauchen nur die VK Nummer. Die steht vor dem Leerzeichen.
				$dienstbeginn=$Dienstplan[$tag]["Dienstbeginn"][$key];
				$dienstende=$Dienstplan[$tag]["Dienstende"][$key];
				$mittagsbeginn=$Dienstplan[$tag]["Mittagsbeginn"][$key]; if(empty($Mittagsbeginn)){$Mittagsbeginn="0:00";}
				$mittagsende=$Dienstplan[$tag]["Mittagsende"][$key]; if(empty($Mittagsende)){$Mittagsende="0:00";}
				$kommentar=$Dienstplan[$tag]["Kommentar"][$key];
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
				$abfrage="REPLACE INTO `Dienstplan` (VK, Datum, Dienstbeginn, Dienstende, Mittagsbeginn, Mittagsende, Stunden, Mandant, Kommentar, user)
					VALUES ('$VK', '$datum', '$dienstbeginn', '$dienstende', '$mittagsbeginn', '$mittagsende', '$stunden', '$mandant', '$kommentar', '$user')";
				$ergebnis = mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
//				echo "$abfrage<br>\n";

				//Und jetzt schreiben wir die Daten noch in eine Datei, damit wir sie mit gnuplot darstellen können.
				if(empty($mittagsbeginn)){$mittagsbeginn="0:00";}
				if(empty($mittagsende)){$mittagsende="0:00";}
				if (!isset($Mitarbeiter)) {
					require 'db-lesen-mitarbeiter.php';
				}
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
		exec($command, $kommando_ergebnis);

		//Wir zeichnen eine Kurve der Anzahl der Mitarbeiter.
		require "zeichne-histogramm.php";
	}
	$datum=$Dienstplan[0]['Datum'][0];
}
elseif ( isset($_POST['submitWocheVorwärts']) && isset($_POST['Dienstplan'][0]['Datum'][0])  )
{
	$datum=$_POST['Dienstplan'][0]['Datum'][0];
	$datum=strtotime('+1 week', strtotime($datum));
	$datum=date('Y-m-d', $datum);
}
elseif ( isset($_POST['submitWocheRückwärts']) && isset($_POST['Dienstplan'][0]['Datum'][0])  )
{
	$datum=$_POST['Dienstplan'][0]['Datum'][0];
	$datum=strtotime('-1 week', strtotime($datum));
	$datum=date('Y-m-d', $datum);
}
elseif ( isset($_POST['submitVorwärts']) && isset($_POST['Dienstplan'][0]['Datum'][0])  )
{
	$datum=$_POST['Dienstplan'][0]['Datum'][0];
	$datum=strtotime('+1 day', strtotime($datum));
	$datum=date('Y-m-d', $datum);
}
elseif ( isset($_POST['submitRückwärts']) && isset($_POST['Dienstplan'][0]['Datum'][0])  )
{
	$datum=$_POST['Dienstplan'][0]['Datum'][0];
	$datum=strtotime('-1 day', strtotime($datum));
	$datum=date('Y-m-d', $datum);
}
elseif ( isset($_POST['wochenAuswahl']) && isset($_POST['woche'])  )
{
	$datum=$_POST['woche'];
	$montags_differenz=date("w", strtotime($datum))-1; //Wir wollen den Anfang der Woche
	$montags_differenzString="-".$montags_differenz." day";
	$datum=strtotime($montags_differenzString, strtotime($datum));
	$datum=date('Y-m-d', $datum);
}
elseif ( isset($_POST['tagesAuswahl']) && isset($_POST['tag'])  )
{
	$datum=$_POST['tag'];
}
elseif ( isset($_POST['tagesAuswahl']) && isset($_POST['woche'])  )
{
	$datum=$_POST['woche'];
}
elseif ( isset($_POST['submitCopyPaste']) && count($_POST['Dienstplan']) > 0 )
{
require 'copy-paste.php';
}
elseif ( (isset($_POST['submit_approval']) or isset($_POST['submit_disapproval'])) && count($_POST['Dienstplan']) > 0 ) {
require 'db-write-approval.php';
$datum=$_POST['Dienstplan'][0]['Datum'][0];
// TODO: Is this save? Is the key 0 allways set?
}
else
{
	//Es gibt nichts im $_POST mit dem wir etwas anfangen können.
}
?>
