<?php
//Die Variable $datum muss hierzu bereits mit dem korrekten Wert gefüllt sein.
//Der Zugang zu Datenbank muss bereits bestehen.
	unset ($notdienst);
	//Im folgenden prüfen wir, ob $datum bereis als UNIX timestamp vorliegt. Wenn es ein Timestamp ist, können wir direkt in 'Y-m-d' umrechnen. Wenn nicht, dann wandeln wir vorher um.
	if (is_numeric($datum) && (int)$datum == $datum) {
		$sql_datum=date('Y-m-d', $datum);
	} else {
		$sql_datum=date('Y-m-d', strtotime($datum));
	}
$abfrage="SELECT *
		FROM `Notdienst`
		WHERE `Datum` = '$sql_datum';";
//		WHERE `Datum` = '$sql_datum' AND `Mandant` = '$mandant';"; //Derzeit werden alle Mandanten angezeigt. Schließlich sind wir ein Filialverbund.
	$ergebnis=mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
	while($row = mysqli_fetch_object($ergebnis))
	{
		$notdienst['vk']=$row->VK;
		$notdienst['mandant']=$row->Mandant;
	}
?>
