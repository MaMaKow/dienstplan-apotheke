<?php
//Wir werfen einen Blick in den Urlaubsplan und schauen, ob alle da sind.
//for ($i=0; $i<count($Dienstplan); $i++)
//{
if (!isset($i)) {
	$i=0;
	// TODO: This is an Exception. It should probably be handled better!
}
	unset($Urlauber, $Kranke, $Abwesende);
	$datum=$Dienstplan[$i]['Datum'][0];
        list($Abwesende, $Urlauber, $Kranke)=db_lesen_abwesenheit($datum);
	if(isset($Dienstplan[$i]['VK']))
	{
		$Eingesetzte_mitarbeiter=array_values($Dienstplan[$i]['VK']);
	}
	else
	{
		$Eingesetzte_mitarbeiter=array();
		//continue;
	}
	if (isset($Urlauber))
	{
		foreach($Urlauber as $urlauber)
		{
			foreach($Eingesetzte_mitarbeiter as $anwesender)
			{
				if ($urlauber==$anwesender)
				{
					$Arbeitende_urlauber[]=$anwesender;
				}
			}
		}
		if (isset($Arbeitende_urlauber))
		{

			foreach($Arbeitende_urlauber as $arbeitender_urlauber)
			{
				//$Fehlermeldung[]=$Mitarbeiter[$arbeitender_urlauber]." ist im Urlaub und sollte nicht im Dienstplan sein.";
			}
		}
	}
	if (isset($Kranke))
	{
		foreach($Kranke as $kranker)
		{
			foreach($Eingesetzte_mitarbeiter as $anwesender)
			{
				if ($kranker==$anwesender)
				{
					$Arbeitende_kranke[]=$anwesender;
				}
			}
		}
		if (isset($Arbeitende_kranke))
		{
			foreach($Arbeitende_kranke as $arbeitender_kranker)
			{
				//$Fehlermeldung[]=$Mitarbeiter[$arbeitender_kranker]." ist krank und sollte nicht im Dienstplan sein.";
			}
		}
	}
	if (isset($Abwesende))
	{
		foreach($Abwesende as $abwesender)
		{
			foreach($Eingesetzte_mitarbeiter as $anwesender)
			{
				if ($abwesender==$anwesender)
				{
					$Arbeitende_abwesende[]=$anwesender;
				}
			}
		}
		if (isset($Arbeitende_abwesende))
		{
			foreach($Arbeitende_abwesende as $arbeitender_abwesender)
			{
				$Fehlermeldung[]=$Mitarbeiter[$arbeitender_abwesender]." ist abwesend (".$Abwesenheits_grund[$arbeitender_abwesender].") und sollte nicht im Dienstplan stehen.";
			}
		}
	}

	//Jetzt schauen wir, ob sonst alle da sind.
	if (count($Eingesetzte_mitarbeiter)>3)
	{
		$Mitarbeiter_differenz=array_diff(array_keys($Mandanten_mitarbeiter), $Eingesetzte_mitarbeiter);
		if(isset($Abwesende)){$Mitarbeiter_differenz=array_diff($Mitarbeiter_differenz, $Abwesende);}
		if (!empty($Mitarbeiter_differenz))
		{
			$fehler="Es sind folgende Mitarbeiter nicht eingesetzt: ";
			foreach($Mitarbeiter_differenz as $arbeiter)
			{
				$fehler.=$Mitarbeiter[$arbeiter].", ";
			}
			$fehler.=".\n";
			$Fehlermeldung[]=$fehler;
		}
	}
	else
	{
//		echo "Dienstplan erst halb voll?";
	}
//}
?>
