<?php
//Dieses Script fragt nach den Mitarbeitern, die an $datum Urlaub haben.
//Die Variable $datum muss hierzu bereits mit dem korrekten Wert gefüllt sein.
//Der Zugang zu Datenbank muss bereits bestehen.
	$sql_datum=date('Y-m-d', strtotime($datum));
	$abfrage="SELECT * 
		FROM `Urlaub` 
		WHERE `Beginn` <= '$sql_datum' AND `Ende` >= '$sql_datum';"; //Mitarbeiter, deren Urlaub schon begonnen hat, aber noch nicht beendet ist.
	$ergebnis=mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
	while($row = mysqli_fetch_object($ergebnis))
	{
		$Urlauber[]=$row->VK;
	}
?>
