<?php
//Dieses Script fragt nach den Mitarbeitern, die an $datum Urlaub haben.
//Die Variable $datum muss hierzu bereits mit dem korrekten Wert gefüllt sein.
//Der Zugang zu Datenbank muss bereits bestehen.

//function db_lesen_abwesenheit()
//{
	global $tag, $verbindung;
	$sqlDatum=date('Y-m-d', strtotime($tag));
	$abfrage="SELECT * 
		FROM `Abwesenheit` 
		WHERE `Beginn` <= '$sqlDatum' AND `Ende` >= '$sqlDatum';"; //Mitarbeiter, deren Urlaub schon begonnen hat, aber noch nicht beendet ist.
	$ergebnis=mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
	while($row = mysqli_fetch_object($ergebnis))
	{
		$Abwesende[]=$row->VK;
		if ($row->Grund=="Urlaub")
		{
			$Urlauber[]=$row->VK;
		}
		elseif ($row->Grund=="Krankheit")
		{
			$Kranke[]=$row->VK;
		}
	}
//	return array($Abwesende, $Urlauber, $Kranke);
//}
//Anschließend müssen wir die Arrays wieder auseinander nehmen
//list($Abwesende, $Urlauber, $Kranke)=db_lesen_abwesenheit()
?>
