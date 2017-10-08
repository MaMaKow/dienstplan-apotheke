<?php
//Hole eine Liste aller Mandanten (Filialen).
$sql_query='SELECT *
	FROM `Mandant`
	;';
$result = mysqli_query_verbose($sql_query);
while($row = mysqli_fetch_object($result))
{
	if ($row->Kurzname != "" )
	{
		$Mandant[$row->Mandant]=$row->Name;
		$Mandant_adresse[$row->Mandant]=$row->Adresse;
		$Kurz_mandant[$row->Mandant]=$row->Kurzname;
                $Pep_mandant[$row->Mandant]=$row->PEP;
	}
}
