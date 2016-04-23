<?php
//Die Variable $datum muss hierzu bereits mit dem korrekten Wert gefüllt sein.
//Der Zugang zu Datenbank muss bereits bestehen.
	unset ($feiertag);
	$sql_datum=date('Y-m-d', strtotime($datum));
	$abfrage="SELECT * 
		FROM `Feiertage` 
		WHERE `Datum` = '$sql_datum';"; 
	$ergebnis=mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
	while($row = mysqli_fetch_object($ergebnis))
	{
		$feiertag=$row->Name;
	}
?>
