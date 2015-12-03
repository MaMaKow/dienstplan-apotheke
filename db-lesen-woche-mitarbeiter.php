<?php

//Abruf der gespeicherten Daten aus der Datenbank
//$tag=$datum;
for ($i=0; $i<5; $i++)
{
	$tag=date('Y-m-d', strtotime("+$i days", strtotime($datum)));
	$abfrage='SELECT * 
		FROM `Dienstplan`
		WHERE `Datum` = "'.$tag.'"
			AND `VK`="'.$auswahlMitarbeiter.'"
		;';
	$ergebnis = mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
	while($row = mysqli_fetch_object($ergebnis))
	{
		$Dienstplan[$i]["Datum"][]=$row->Datum;
		$Dienstplan[$i]["VK"][]=$row->VK;
		$Dienstplan[$i]["Dienstbeginn"][]=$row->Dienstbeginn;
		$Dienstplan[$i]["Dienstende"][]=$row->Dienstende;
		$Dienstplan[$i]["Mittagsbeginn"][]=$row->Mittagsbeginn;
		$Dienstplan[$i]["Mittagsende"][]=$row->Mittagsende;
		$Dienstplan[$i]["Stunden"][]=$row->Stunden;
		$Dienstplan[$i]["Kommentar"][]=$row->Kommentar;
		$Dienstplan[$i]["Mandant"][]=$row->Mandant;
	}
	//Wir füllen komplett leere Tage mit Werten, damit trotzdem eine Anzeige entsteht.
	if ( !isset($Dienstplan[$i]) )
	{
		$Dienstplan[$i]["Datum"][]=$tag;
		$Dienstplan[$i]["VK"][]="$auswahlMitarbeiter";
		$Dienstplan[$i]["Dienstbeginn"][]="-";
		$Dienstplan[$i]["Dienstende"][]="-";
		$Dienstplan[$i]["Mittagsbeginn"][]="-";
		$Dienstplan[$i]["Mittagsende"][]="-";
		$Dienstplan[$i]["Stunden"][]="-";
		$Dienstplan[$i]["Kommentar"][]="-";
	}
}
?>
