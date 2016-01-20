<?php
require 'default.php';
require 'db-verbindung.php';

$mandant=1;	//Wir zeigen den Dienstplan für die "Apotheke am Marienplatz"
$filiale=2;	//Am unteren Rand werden auch unsere Mitarbeiter in dieser Filale angezeigt.
$tage=5;	//Dies ist eine Wochenansicht ohne Wochenende

//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';

$datenübertragung="";
$dienstplanCSV="";



//$Dienstbeginn=array( "8:00", "8:30", "9:00", "9:30", "10:00", "11:30", "12:00", "18:30" );
$heute=date('Y-m-d');
$datum=$heute; //Dieser Wert wird überschrieben, wenn "$wochenauswahl und $woche per POST oder $datum per GET übergeben werden."
require 'get-auswertung.php'; //Auswerten der per GET übergebenen Daten.
require 'post-auswertung.php'; //Auswerten der per POST übergebenen Daten.
$montagsDifferenz=date("w", strtotime($datum))-1; //Wir wollen den Anfang der Woche
$montagsDifferenzString="-".$montagsDifferenz." day";
$datum=strtotime($montagsDifferenzString, strtotime($datum));
$datum=date('Y-m-d', $datum);
require 'db-lesen-tage.php'; //Lesen der in der Datenbank gespeicherten Daten.
$Dienstplan=db_lesen_tage($tage, $mandant); //Die Funktion ruft die Daten nur für den angegebenen Mandanten und für den angegebenen Zeitraum ab.
$Filialplan=db_lesen_tage($tage, $filiale, '[^'.$filiale.']'); // Die Funktion schaut jetzt nach dem Arbeitsplan in der Helene.

$VKcount=count($Mitarbeiter); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
$VKmax=max(array_keys($Mitarbeiter)); //Wir suchen nach der höchsten VK-Nummer VKmax. Diese wird für den <option>-Bereich benötigt.




//Produziere die Ausgabe
?>
<html moznomarginboxes> <!-- Wir wollen beim Ausdrucken keinen Header mit auf dem Papier. -->
	<head>
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>
<?php
echo "\t\t<div class=no-print>Kalenderwoche ".strftime('%V', strtotime($datum))."</div>\n";
//if ( isset($datenübertragung) ) {echo $datenübertragung;}
echo "\t\t<form id=myform method=post>\n";
$RückwärtsButton="\t\t\t<input type=submit 	class=no-print	value='1 Woche Rückwärts'	name='submitWocheRückwärts'>\n";
echo $RückwärtsButton;
$VorwärtsButton="\t\t\t<input type=submit 	class=no-print	value='1 Woche Vorwärts'	name='submitWocheVorwärts'>\n";
echo $VorwärtsButton;
//$submitButton="\t<input type=submit value=Absenden name='submitDienstplan'>\n";echo $submitButton; Dies ist die Leseversion
echo "\t\t\t<table border=0 rules=groups>\n";
//echo "\t\t\t\t<div class=stretch-on-print>\n";
echo "\t\t\t\t<thead>\n";
echo "\t\t\t\t<tr>\n";
for ($i=0; $i<count($Dienstplan); $i++)
{//Datum
	echo "\t\t\t\t\t<td>";
	echo "<a href=tag-out.php?datum=".$Dienstplan[$i]["Datum"][0].">";
	echo strftime('%A', strtotime( $Dienstplan[$i]["Datum"][0]));
	echo " \n";
	echo "<input type=hidden size=2 name=Dienstplan[".$i."][Datum][0] value=".$Dienstplan[$i]["Datum"][0].">";
	echo strftime('%d.%m.', strtotime($Dienstplan[$i]["Datum"][0]));
	require 'db-lesen-feiertag.php'; //DEBUG debug ! Diese Funktion prüft nur für einen einzigen Tag. Nur $datum wird aufgerufen.
	if(isset($feiertag)){echo " ".$feiertag." ";}
	if(isset($notdienst)){echo " NOTDIENST ";}
	echo "</td></a>\n";
}
echo "\t\t\t\t</tr></thead><tbody>";


require 'schreiben-tabelle.php';
schreiben_tabelle($Dienstplan);
if (!empty(array_column($Filialplan, 'VK'))) //array_column durchsucht alle Tage nach einem 'VK'.
{
	echo "</tbody><tbody><tr><td colspan=$tage>Marienplatz in der Helenenstraße</td></tr>";
	schreiben_tabelle($Filialplan);
}
echo "\t\t\t\t</tbody>\n";
//echo "\t\t\t\t</div>\n";
echo "\t\t\t\t<tfoot><tr class=page-break></tr>\n";

//Wir werfen einen Blick in den Urlaubsplan und schauen, ob alle da sind.
for ($i=0; $i<count($Dienstplan); $i++)
{
	if (!isset($Dienstplan[$i]['VK'])) {break;} //Tage an denen kein Dienstplan existiert werden nicht geprüft.
	unset($Urlauber, $Kranke);
	$tag=($Dienstplan[$i]['Datum'][0]);
	require 'db-lesen-abwesenheit.php';
	$EingesetzteMitarbeiter=array_values($Dienstplan[$i]['VK']);
	if (isset($Urlauber))
	{
		foreach($Urlauber as $urlauber)
		{
			$pattern="/$urlauber/";
			$ArbeitendeUrlauber=preg_grep($pattern, $EingesetzteMitarbeiter);
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
			$pattern="/$kranker/";
			$ArbeitendeKranke=preg_grep($pattern, $EingesetzteMitarbeiter);
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
	if (count($Dienstplan)>3)
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

	//Jetzt notieren wir die Urlauber und die Kranken Mitarbeiter unten in der Tabelle.
	if (isset($Urlauber))
	{
		echo "\t\t\t\t<td align=left><b>Urlaub</b><br>"; foreach($Urlauber as $value){echo $Mitarbeiter[$value]."<br>";};
	}
	else
	{
		echo "\t\t\t\t\t<td>";
	}
	if (isset($Kranke))
	{
		echo "\t<br><b>Krank</b><br>"; foreach($Kranke as $value){echo $Mitarbeiter[$value]."<br>";}; echo "</td>\n";
	}
	else
	{
		echo "</td>\n";
	}
}
echo "\t\t\t\t</tr>\n";
echo "\t\t\t</table>\n";
echo "\t\t\t<table border=0 rules=groups>\n";

//Nun folgt die Liste der Wochenstunden.
echo "\t\t\t\t<tr>\n";
echo "\t\t\t\t\t<td colspan=5>\n";
for ($tag=0; $tag<count($Dienstplan); $tag++)
{
	if (!isset($Dienstplan[$tag]['Stunden'])) {continue;} //Tage an denen kein Dienstplan existiert werden nicht geprüft.
	foreach($Dienstplan[$tag]['Stunden'] as $key => $stunden)
	{
		$Stunden[$Dienstplan[$tag]['VK'][$key]][]=$stunden;
	}
}
for ($tag=0; $tag<count($Filialplan); $tag++)
{
	if (!isset($Filialplan[$tag]['Stunden'])) {continue;} //Tage an denen kein Dienstplan existiert werden nicht geprüft.
	foreach($Filialplan[$tag]['Stunden'] as $key => $stunden)
	{
		$Stunden[$Filialplan[$tag]['VK'][$key]][]=$stunden;
	}
}

//if(isset($Dienstplan[0]['VK'][2]) || isset($Dienstplan[1]['VK'][2])  || isset($Dienstplan[2]['VK'][2]) || isset($Dienstplan[3]['VK'][2]) || isset($Dienstplan[4]['VK'][2]) || isset($Dienstplan[5]['VK'][2])) //An leeren Wochen soll nicht gerechnet werden. 
if (!empty(array_column($Dienstplan, 'VK'))) //array_column durchsucht alle Tage nach einem 'VK'.
{
	echo "<b>Wochenstunden</b><tr>";
	ksort($Stunden);
	$i=0;$j=1; //Zahler für den Stunden-Array (wir wollen nach je 5 Mitarbeitern einen Umbruch)
	foreach($Stunden as $mitarbeiter => $stunden)
	{
		$k=$j*5; //Der Faktor gibt an, bei welcher VK-Nummer der Umbruch erfolgt.
		if($mitarbeiter>$k){$i++;}
		if($i>=1)
		{
			echo "</tr><tr>";
			$i=0;$j++;
		}
		echo "<td>".$Mitarbeiter[$mitarbeiter]." ".array_sum($stunden)."</td>";
	}
	echo "</tr>";
}
echo "\t\t\t\t\t</td>\n";
echo "\t\t\t\t</tr>\n";
echo "\t\t\t\t</tfoot>\n";
echo "\t\t\t</table>\n";
// echo $submitButton;
echo "\t\t</form>\n";
require 'contact-form.php';




//echo "<pre>";	var_export($Urlauber);    	echo "</pre>";

?>
	</body>
<html>

