<?php
//Wir werfen einen Blick in den Urlaubsplan und schauen, ob alle da sind.
for ($i=0; $i<count($Dienstplan); $i++)
{
	unset($Urlauber, $Kranke);
	$tag=$Dienstplan[$i]['Datum'][0];
	require_once 'db-lesen-abwesenheit.php';
	if(isset($Dienstplan[$i]['VK']))
	{
	$EingesetzteMitarbeiter=array_values($Dienstplan[$i]['VK']);
	}
	else
	{
		break;
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
	//Jetzt schauen wir, ob sonst alle da sind.
	if (count($EingesetzteMitarbeiter)>3)
	{
		$MitarbeiterDifferenz=array_diff(array_keys($MarienplatzMitarbeiter), $EingesetzteMitarbeiter);
		if(isset($Abwesende)){$MitarbeiterDifferenz=array_diff($MitarbeiterDifferenz, $Abwesende);}
		if (!empty($MitarbeiterDifferenz))
		{
			$fehler="Es sind folgende Mitarbeiter nicht eingesetzt: ";
			foreach($MitarbeiterDifferenz as $arbeiter)
			{
				$fehler.=$Mitarbeiter[$arbeiter].", ";
			}
			$fehler.=".";
			$Fehlermeldung[]=$fehler;
		}
	}
	else
	{
//		echo "Dienstplan erst halb voll?";
	}
}
?>

