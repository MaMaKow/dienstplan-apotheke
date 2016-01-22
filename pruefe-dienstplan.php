<?php
	//Wir überprüfen ob zu jeder Zeit Approbierte anwesend sind.
	//Diese Funktion sollte in eine extra Datei geschoben werden, zusammen mit anderen Tests. debug DEBUG!
	foreach ($ApprobiertenAnwesende as $zeit => $anwesende)
	{
		if ($anwesende == 0 AND $zeit != strtotime("20:00"))
		{
			$Fehlermeldung[]="Um ".date('H:i', $zeit)." Uhr ist kein Approbierter anwesend.";
		}
	}
?>
