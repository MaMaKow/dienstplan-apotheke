<?php
//Hole eine Liste aller Mandanten (Filialen).
$abfrage='SELECT *
	FROM `Mandant`
	;';
$ergebnis = mysqli_query_verbose($abfrage);
while($row = mysqli_fetch_object($ergebnis))
{
	if ($row->Kurzname != "" )
	{
		$Mandant[$row->Mandant]=$row->Name;
		$Kurz_mandant[$row->Mandant]=$row->Kurzname;
                $Pep_mandant[$row->Mandant]=$row->PEP;
	}
}
