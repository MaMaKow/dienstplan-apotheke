<?php
	if( isset($ApprobiertenAnwesende) AND isset($tagesEnde) )
	{
		//Wir überprüfen ob zu jeder Zeit Approbierte anwesend sind.
		//Diese Funktion sollte in eine extra Datei geschoben werden, zusammen mit anderen Tests. debug DEBUG!
		foreach ($ApprobiertenAnwesende as $zeit => $anwesendeApprobierte)
		{
			if ($anwesendeApprobierte == 0 AND $zeit != $tagesEnde)
			{
				$Fehlermeldung[]="Um ".date('H:i', $zeit)." Uhr ist kein Approbierter anwesend.";
				break 1; //We avoid to flood everything with errors for every 5 minutes in which noone is there.
			}
		}
	}
	else
	{
		echo "Notwendige Variablen sind nicht gesetzt. Keine Zählung der anwesenden Approbierten.";
	}
?>
