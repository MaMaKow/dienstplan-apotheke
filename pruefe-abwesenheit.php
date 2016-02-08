<?php
//Wir werfen einen Blick in den Urlaubsplan und schauen, ob alle da sind.
for ($i=0; $i<count($Dienstplan); $i++)
{
	unset($Urlauber, $Kranke, $Abwesende);
	$datum=$Dienstplan[$i]['Datum'][0];
	require 'db-lesen-abwesenheit.php';
	if(isset($Dienstplan[$i]['VK']))
	{
		$EingesetzteMitarbeiter=array_values($Dienstplan[$i]['VK']);
	}
	else
	{
		continue;
	}
	if (isset($Urlauber))
	{
		foreach($Urlauber as $urlauber)
		{
			foreach($EingesetzteMitarbeiter as $anwesender)
			{
				if ($urlauber==$anwesender)
				{
					$ArbeitendeUrlauber[]=$anwesender;
				}
			}
		}
		if (isset($ArbeitendeUrlauber))
		{
		
			foreach($ArbeitendeUrlauber as $arbeitenderUrlauber)
			{
				$Fehlermeldung[]=$Mitarbeiter[$arbeitenderUrlauber]." ist im Urlaub und sollte nicht arbeiten.";
			}
		}
	}
	if (isset($Kranke))
	{
		foreach($Kranke as $kranker)
		{
			foreach($EingesetzteMitarbeiter as $anwesender)
			{
				if ($kranker==$anwesender)
				{
					$ArbeitendeKranke[]=$anwesender;
				}
			}
		}
		if (isset($ArbeitendeKranke))
		{
			foreach($ArbeitendeKranke as $arbeitenderKranker)
			{
				$Fehlermeldung[]=$Mitarbeiter[$arbeitenderKranker]." ist krank und sollte der Arbeit fern bleiben.";
			}
		}
	}
	if (isset($Abwesende))
	{
		foreach($Abwesende as $abwesender)
		{
			foreach($EingesetzteMitarbeiter as $anwesender)
			{
				if ($abwesender==$anwesender)
				{
					$ArbeitendeAbwesende[]=$anwesender;
				}
			}
		}
		if (isset($ArbeitendeAbwesende))
		{
			foreach($ArbeitendeAbwesende as $arbeitenderAbwesender)
			{
				$Fehlermeldung[]=$Mitarbeiter[$arbeitenderAbwesender]." ist abwesend (".$AbwesenheitsGrund[$arbeitenderAbwesender].") und sollte der Arbeit fern bleiben.";
			}
		}
	}

	//Jetzt schauen wir, ob sonst alle da sind.
	if (count($EingesetzteMitarbeiter)>3)
	{
		$MitarbeiterDifferenz=array_diff(array_keys($MandantenMitarbeiter), $EingesetzteMitarbeiter);
		if(isset($Abwesende)){$MitarbeiterDifferenz=array_diff($MitarbeiterDifferenz, $Abwesende);}
		if (!empty($MitarbeiterDifferenz))
		{
			$fehler="Es sind folgende Mitarbeiter nicht eingesetzt: ";
			foreach($MitarbeiterDifferenz as $arbeiter)
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
}
?>

