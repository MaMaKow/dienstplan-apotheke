<?php
//Dieses Script fragt nach den Mitarbeitern, die an $datum Urlaub haben.
//Die Variable $datum muss hierzu bereits mit dem korrekten Wert gefüllt sein.
//Der Zugang zu Datenbank muss bereits bestehen.

//function db_lesen_abwesenheit()
//{
	global $datum, $verbindung;
	unset($Urlauber, $Kranke, $Abwesende);
//Im folgenden prüfen wir, ob $datum bereis als UNIX timestamp vorliegt. Wenn es ein Timestamp ist, können wir direkt in 'Y-m-d' umrechnen. Wenn nicht, dann wandeln wir vorher um.
	if (is_numeric($datum) && (int)$datum == $datum) {
		$sqlDatum=date('Y-m-d', $datum);
	} else {
		$sqlDatum=date('Y-m-d', strtotime($datum));
	}

	$abfrage="SELECT *
		FROM `Abwesenheit`
		WHERE `Beginn` <= '$sqlDatum' AND `Ende` >= '$sqlDatum';"; //Mitarbeiter, deren Urlaub schon begonnen hat, aber noch nicht beendet ist.
	$ergebnis=mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
	while($row = mysqli_fetch_object($ergebnis))
	{
		$Abwesende[]=$row->VK;
		$AbwesenheitsGrund[$row->VK]=$row->Grund;
		if ($row->Grund=="Urlaub")
		{
			$Urlauber[]=$row->VK;
		}
		elseif ( preg_match('/Krank/i', $row->Grund) ) //Auch Krank mit Kind sollte hier enthalten sein. //Außerdem suchen wir Case insensitive krank=Krank=kRaNk
		{
			$Kranke[]=$row->VK;
		}
	}
//	return array($Abwesende, $Urlauber, $Kranke);
//}
//Anschließend müssen wir die Arrays wieder auseinander nehmen
//list($Abwesende, $Urlauber, $Kranke)=db_lesen_abwesenheit()
?>
