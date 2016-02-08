<?php
//Die Variable $datum muss hierzu bereits mit dem korrekten Wert gefüllt sein.
//Der Zugang zu Datenbank muss bereits bestehen.
	unset ($notdienst);
	$sqlDatum=date('Y-m-d', strtotime($datum));
	$abfrage="SELECT * 
		FROM `Notdienst` 
		WHERE `Datum` = '$sqlDatum';"; 
//		WHERE `Datum` = '$sqlDatum' AND `Mandant` = '$mandant';"; //Derzeit werden alle Mandanten angezeigt. Schließlich sind wir ein Filialverbund.
	$ergebnis=mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
	while($row = mysqli_fetch_object($ergebnis))
	{
		$notdienst['vk']=$row->VK;
		$notdienst['mandant']=$row->Mandant;
	}
?>
