<?php
//Hole eine Liste aller Mitarbeiter
$abfrage='SELECT *
	FROM `Mitarbeiter`
	ORDER BY `VK` ASC
	;';
$ergebnis = mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
while($row = mysqli_fetch_object($ergebnis))
{
	if ($row->Nachname != "" )
	{
		$Mitarbeiter[$row->VK]=$row->Nachname;
		$StundenMitarbeiter[$row->VK]=$row->Arbeitswochenstunden;
		$MittagMitarbeiter[$row->VK]=$row->Mittag;
		$AusbildungMitarbeiter[$row->VK]=$row->Ausbildung;
		if ($row->Mandant==1 && $row->Stunden>10) //Welche Mitarbeiter sind immer da?
		{
			$MarienplatzMitarbeiter[$row->VK]=$row->Nachname;
		}
		if (isset($mandant) && $row->Mandant==$mandant && $row->Stunden>10) //Welche Mitarbeiter sind immer da?
		{
			$MandantenMitarbeiter[$row->VK]=$row->Nachname;
		}
		if ($row->Ausbildung=='Apotheker' || $row->Ausbildung=='PI') //Wer ist ausreichend approbiert??
		{
			$ApprobierteMitarbeiter[$row->VK]=$row->Nachname;
		}
	}
}
?>
