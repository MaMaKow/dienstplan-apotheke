<?php
//Hole eine Liste aller Mitarbeiter
$abfrage='SELECT *
	FROM `Mitarbeiter`
	;';
$ergebnis = mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
while($row = mysqli_fetch_object($ergebnis))
{
	if ($row->Nachname != "" )
	{
		$Mitarbeiter[$row->VK]=$row->Nachname;
		if ($row->Mandant==1 && $row->Stunden>10) //Welche Mitarbeiter sind immer da?
		{
			$MarienplatzMitarbeiter[$row->VK]=$row->Nachname;
		}
	}
}
?>
