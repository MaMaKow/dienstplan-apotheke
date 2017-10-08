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
$sql_query="SELECT *
		FROM `Notdienst`
		WHERE `Datum` = '$sql_datum';";
//		WHERE `Datum` = '$sql_datum' AND `Mandant` = '$mandant';"; //Derzeit werden alle Mandanten angezeigt. Schließlich sind wir ein Filialverbund.
	$result = mysqli_query_verbose($sql_query);
	while($row = mysqli_fetch_object($result))
	{
		$notdienst['vk']=$row->VK;
		$notdienst['mandant']=$row->Mandant;
	}
