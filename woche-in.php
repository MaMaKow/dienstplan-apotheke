<?php
#Diese Seite wird den kompletten Dienstplan einer Woche  anzeigen.
require 'default.php';
require 'db-verbindung.php';

$mandant=1;	//Wir zeigen den Dienstplan für die "Apotheke am Marienplatz"
$tage=5;	//Dies ist eine Wochenansicht ohne Wochenende


//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';

$datenübertragung="";
$dienstplanCSV="";



//$Dienstbeginn=array( "8:00", "8:30", "9:00", "9:30", "10:00", "11:30", "12:00", "18:30" );
$heute=date('Y-m-d');
$datum=$heute; //Dieser Wert wird überschrieben, wenn "$wochenauswahl und $woche per POST übergeben werden."
$montagsDifferenz=date("w", strtotime($datum))-1; //Wir wollen den Anfang der Woche
$montagsDifferenzString="-".$montagsDifferenz." day";
$datum=strtotime($montagsDifferenzString, strtotime($datum));
$datum=date('Y-m-d', $datum);



require 'post-auswertung.php'; //Auswerten der per POST übergebenen Daten.
require 'db-lesen-tage.php'; //Lesen der in der Datenbank gespeicherten Daten.
$Dienstplan=db_lesen_tage($tage, $mandant);
require 'db-lesen-feiertag.php';

$VKcount=count($Mitarbeiter); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
//end($Mitarbeiter); $VKmax=key($Mitarbeiter); reset($Mitarbeiter); //Wir suchen nach der höchsten VK-Nummer VKmax.
$VKmax=max(array_keys($Mitarbeiter));





//Produziere die Ausgabe
?>
<html>
	<head>
		<style type=text/css>
 			td {white-space: nowrap;}
			.overlay 
			{
				position: absolute;
				top:50%;
				left: 50%;
				transform: translateX(-50%) translateY(-50%);
				text-align: center;
				z-index: 10;
				background-color: rgba(255,60,60,0.8); /*dim the background*/
			}
		</style>
	</head>
	<body bgcolor=#D0E0F0>
<?php
echo "Kalenderwoche ".strftime('%V', strtotime($datum))."<br>\n";
if ( isset($datenübertragung) ) {echo $datenübertragung;}
echo "<form id=myform method=post>\n";
$RückwärtsButton="\t<input type=submit 	value='1 Woche Rückwärts'	name='submitWocheRückwärts'>\n";echo $RückwärtsButton;
$VorwärtsButton="\t<input type=submit 	value='1 Woche Vorwärts'	name='submitWocheVorwärts'>\n";echo $VorwärtsButton;
$submitButton="\t<input type=submit value=Absenden name='submitDienstplan'>\n";echo $submitButton;
echo "<div id=wochenAuswahl><input name=woche type=date value=".date('Y-m-d', strtotime($datum)).">";
echo "<input type=submit name=wochenAuswahl value=Anzeigen></div>";
echo "	<table border=2 style=width:99%>\n";
echo "			<tr>\n";
for ($i=0; $i<count($Dienstplan); $i++)
{//Datum
	$zeile="";
	echo "				<td>";
	$zeile.="<input type=hidden size=2 name=Dienstplan[".$i."][Datum][0] value=".$Dienstplan[$i]["Datum"][0].">";
	$zeile.=strftime('%d.%m.', strtotime( $Dienstplan[$i]["Datum"][0]));
	echo $zeile;
	if(isset($feiertag)){echo " ".$feiertag." ";}
	if(isset($notdienst)){echo " NOTDIENST ";}
	echo "</td>\n";
}	
if ( file_exists("dienstplan_".$datum.".png") )
{
//echo "<td align=center valign=top rowspan=30 style=width:800px>";
//echo "<img src=dienstplan_".$datum.".png?".filemtime('dienstplan_'.$datum.'.png')." style=width:90%;><br>"; //Um das Bild immer neu zu laden, wenn es verändert wurde müssen wir das Cachen verhindern.
//echo "<img src=histogramm_".$datum.".png?".filemtime('dienstplan_'.$datum.'.png')." style=width:90%;></td>";
//echo "<td></td>";//Wir fügen hier eine Spalte ein, weil im IE9 die Tabelle über die Seite hinaus geht.
}
echo "			</tr><tr>\n";
for ($i=0; $i<count($Dienstplan); $i++)
{//Wochentag
	$zeile="";
	echo "				<td style=width:20%>";
	$zeile.=strftime('%A', strtotime( $Dienstplan[$i]["Datum"][0]));
	echo $zeile;
	echo "</td>\n";
}
for ($j=0; $j<$VKcount; $j++)
{
	if(isset($feiertag) && !isset($notdienst)){break 1;}
	echo "			</tr><tr>\n";
	for ($i=0; $i<count($Dienstplan); $i++)
	{//Mitarbeiter
		$zeile="";
		echo "				<td align=right>";
		$zeile.="<select name=Dienstplan[".$i."][VK][".$j."] tabindex=".(($i*$VKcount*5) + ($j*5) + 1)."><option>";
		if (isset($Dienstplan[$i]["VK"][$j]) && isset($Mitarbeiter[$Dienstplan[$i]["VK"][$j]]) )
		{ 
			$zeile.=$Dienstplan[$i]["VK"][$j]." ".$Mitarbeiter[$Dienstplan[$i]["VK"][$j]];
		}
		$zeile.="</option>";
		for ($k=1; $k<$VKmax+1; $k++)
		{
			if (isset($Dienstplan[$i]["VK"][$j]))
			{
				if ( isset($Mitarbeiter[$k]) and $Dienstplan[$i]["VK"][$j]!=$k ) //Dieser Ausdruck dient nur dazu, dass der vorgesehene  Mitarbeiter nicht zwei mal in der Liste auftaucht.
				{
					$zeile.="<option>".$k." ".$Mitarbeiter[$k]."</option>,";
				}
				else
				{
					$zeile.="<option></option>,"; // Es ist sinnvoll, auch eine leere Zeile zu besitzen, damit Mitarbeiter auch wieder gelöscht werden können.
				}
			}
			elseif ( isset($Mitarbeiter[$k]) )
			{
					$zeile.="<option>".$k." ".$Mitarbeiter[$k]."</option>,";
			}
		}
		$zeile.="</select>";
		//Dienstbeginn
		$zeile.=" <input type=time size=1 name=Dienstplan[".$i."][Dienstbeginn][".$j."] tabindex=".($i*$VKcount*5 + $j*5 + 2 )." value=";
		if (isset($Dienstplan[$i]["VK"][$j])) 
		{
			$zeile.=strftime('%H:%M',strtotime($Dienstplan[$i]["Dienstbeginn"][$j]));
		}
		$zeile.="> bis <input type=time size=1 name=Dienstplan[".$i."][Dienstende][".$j."] tabindex=".($i*$VKcount*5 + $j*5 + 3 )." value=";
		//Dienstende
		if (isset($Dienstplan[$i]["VK"][$j])) 
		{
			$zeile.=strftime('%H:%M',strtotime($Dienstplan[$i]["Dienstende"][$j]));
		}
		$zeile.=">";
		echo $zeile;
		
		echo "				</td>\n";
	}
	echo "			</tr><tr>\n";
	for ($i=0; $i<count($Dienstplan); $i++)
	{//Mittagspause
		$zeile="";
		echo "				<td align=right>";
		$zeile.=" Pause: <input type=time size=1 name=Dienstplan[".$i."][Mittagsbeginn][".$j."] tabindex=".($i*$VKcount*5 + $j*5 + 4 )." value=";
		if (isset($Dienstplan[$i]["VK"][$j]) and $Dienstplan[$i]["Mittagsbeginn"][$j] > 0 )
		{
			$zeile.= strftime('%H:%M', strtotime($Dienstplan[$i]["Mittagsbeginn"][$j]));
		}
		$zeile.="> bis <input type=time size=1 name=Dienstplan[".$i."][Mittagsende][".$j."] tabindex=".($i*$VKcount*5 + $j*5 + 5 )." value=";
		if (isset($Dienstplan[$i]["VK"][$j]) and $Dienstplan[$i]["Mittagsbeginn"][$j] > 0 )
		{
			$zeile.= strftime('%H:%M', strtotime($Dienstplan[$i]["Mittagsende"][$j]));
		}
		$zeile.=">";
		
		echo $zeile;
		echo "</td>";
	}
}
echo "			</tr>\n";

//Wir werfen einen Blick in den Urlaubsplan und schauen, ob alle da sind.
for ($i=0; $i<count($Dienstplan); $i++)
{
	$tag=$Dienstplan[$i]['Datum'][0];
	require 'db-lesen-abwesenheit.php';
//	list($Abwesende, $Urlauber, $Kranke)=db_lesen_abwesenheit();
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
}
if (isset($Urlauber))
{
	echo "	<tr><td align=right>Urlaub</td><td>"; foreach($Urlauber as $value){echo $Mitarbeiter[$value]."<br>";}; echo "</td></tr>";
}
if (isset($Kranke))
{
	echo "	<tr><td align=right>Krank</td><td>"; foreach($Kranke as $value){echo $Mitarbeiter[$value]."<br>";}; echo "</td></tr>";
}
echo "	</table>\n";
echo $submitButton;
echo "</form>\n";
//	echo "<pre>";	var_export($MarienplatzMitarbeiter);    	echo "</pre>"; // Hier kann der aus der Datenbank gelesene Datensatz zu Debugging-Zwecken angesehen werden.

//Hier beginnt die Fehlerausgabe. Es werden alle Fehler angezeigt, die wir in $Fehlermeldung gesammelt haben.
/*
if (isset($Fehlermeldung))
{
	echo "		<div class=overlay><H1>".$fehler."<H1></div>";
	foreach($Fehlermeldung as $fehler)
	{
		echo "		<<H1>".$fehler."<H1>";
	}
	echo "</div>";
}
*/
echo "</body>";
?>

