<?php
//Dieses Script fragt nach den Mitarbeitern, die an $datum Urlaub haben.
//Die Variable $datum muss hierzu bereits mit dem korrekten Wert gefüllt sein.
//Der Zugang zu Datenbank muss bereits bestehen.
function db_lesen_abwesenheit($date)
{
        global $Mitarbeiter, $verbindungi;
	unset($Urlauber, $Kranke, $Abwesende);
	//Im folgenden prüfen wir, ob $datum bereis als UNIX timestamp vorliegt. Wenn es ein Timestamp ist, können wir direkt in 'Y-m-d' umrechnen. Wenn nicht, dann wandeln wir vorher um.
	if (is_numeric($date) && (int)$date == $date) {
		$sql_date=date('Y-m-d', $date);
	} else {
		$sql_date=date('Y-m-d', strtotime($date));
	}

	//We define a list of still existing coworkers. There might be workers in the database, that do not work anymore, but still have vacations registered in the database.
        //TODO: Build an option to delete future vacations of people when leaving.
        $mitarbeiterliste="";
	foreach ($Mitarbeiter as $VK => $nachname) {
		$mitarbeiterliste.=$VK.", ";
	}
	$mitarbeiterliste=substr($mitarbeiterliste, 0, -2); //The last comma has to be cut off.

	$abfrage="SELECT *
		FROM `Abwesenheit`
		WHERE `Beginn` <= '$sql_date' AND `Ende` >= '$sql_date' AND VK IN (".$mitarbeiterliste.")"; //Mitarbeiter, deren Urlaub schon begonnen hat, aber noch nicht beendet ist.
	$ergebnis=  mysqli_query_verbose($abfrage);
	while($row = mysqli_fetch_object($ergebnis))
	{
		$Abwesende[$row->VK]=$row->Grund;
		if ($row->Grund=="Urlaub")
		{
			$Urlauber[]=$row->VK;
		}
		elseif ( preg_match('/Krank/i', $row->Grund) ) //Auch Krank mit Kind sollte hier enthalten sein. //Außerdem suchen wir Case insensitive krank=Krank=kRaNk
		{
			$Kranke[]=$row->VK;
		}
	}
	return array($Abwesende, $Urlauber, $Kranke);
}
//Anschließend müssen wir die Arrays wieder auseinander nehmen
//list($Abwesende, $Urlauber, $Kranke)=db_lesen_abwesenheit($datum);
?>
