<?php
//Hole eine Liste aller Mandanten (Filialen).
$abfrage='SELECT *
	FROM `Mandant`
	;';
$ergebnis = mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
while($row = mysqli_fetch_object($ergebnis))
{
	if ($row->Kurzname != "" )
	{
		$Mandant[$row->Mandant]=$row->Name;
		$KurzMandant[$row->Mandant]=$row->Kurzname;
	}
}
?>
