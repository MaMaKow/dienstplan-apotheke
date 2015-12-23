<?php
require 'default.php';
require 'db-verbindung.php';
$mandant=1;	//Wir zeigen den Dienstplan standardmäßig für die "Apotheke am Marienplatz"
$tage=1;	//Dies ist eine Wochenansicht ohne Wochenende


#Diese Seite wird den kompletten Dienstplan eines einzelnen Tages anzeigen.

//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';
//Hole eine Liste aller Mandanten (Filialen)
require 'db-lesen-mandant.php';


$datenübertragung="";
$dienstplanCSV="";



//$Dienstbeginn=array( "8:00", "8:30", "9:00", "9:30", "10:00", "11:30", "12:00", "18:30" );
$heute=date('Y-m-d');
$datum=$heute; //Dieser Wert wird überschrieben, wenn "$wochenauswahl und $woche per POST übergeben werden."



require 'get-auswertung.php'; //Auswerten der per GET übergebenen Daten.
require 'post-auswertung.php'; //Auswerten der per POST übergebenen Daten.
//require 'db-lesen-tag.php'; //Lesen der in der Datenbank gespeicherten Daten.
require 'db-lesen-tage.php'; //Lesen der in der Datenbank gespeicherten Daten.
$Dienstplan=db_lesen_tage($tage, $mandant);
require 'db-lesen-feiertag.php';

$VKcount=count($Mitarbeiter); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
//end($Mitarbeiter); $VKmax=key($Mitarbeiter); reset($Mitarbeiter); //Wir suchen nach der höchsten VK-Nummer VKmax.
$VKmax=max(array_keys($Mitarbeiter));

//Wir schauen, on alle Anwesenden anwesend sind und alle Kranken und Siechenden im Urlaub.
require 'pruefe-abwesenheit.php';




//Produziere die Ausgabe
?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body bgcolor=#D0E0F0>
<?php
//Hier beginnt die Fehlerausgabe. Es werden alle Fehler angezeigt, die wir in $Fehlermeldung gesammelt haben.
if (isset($Fehlermeldung))
{
	echo "		<div class=overlay><H1>";
	foreach($Fehlermeldung as $fehler)
	{
		echo "		<H1>".$fehler."</H1>";
	}
	echo "</div>";
}

//Hier beginnt die Normale Ausgabe.
echo "\t\tKalenderwoche ".strftime('%V', strtotime($datum))."<br><b>". $Mandant[$mandant] ."</b><br>\n";
echo "\t\t<form id=mandantenformular method=post>\n";
echo "\t\t\t<input type=hidden name=datum value=".$Dienstplan[0]["Datum"][0].">\n";
echo "\t\t\t<select style=font-size:150% name=mandant onchange=this.form.submit()>\n";
echo "\t\t\t\t<option value=".$mandant.">".$Mandant[$mandant]."</option>\n";
foreach ($Mandant as $key => $value) //wir verwenden nicht die Variablen $filiale oder Mandant, weil wir diese jetzt nicht verändern wollen!
{
	if ($key!=$mandant)
	{
		echo "\t\t\t\t<option value=".$key.">".$value."</option>\n";
	}
}
echo "\t\t\t</select>\n\t\t</form>\n";
if ( isset($datenübertragung) ) {echo $datenübertragung;}
echo "\t\t<form id=myform method=post>\n";
//echo "\t\t<form id=myform method=post action=test-post.php>\n";
echo "\t\t\t<div id=navigationsElemente>";
$rückwärtsButton="\t\t\t\t<input type=submit 	value='1 Tag Rückwärts'	name='submitRückwärts'>\n";echo $rückwärtsButton;
$vorwärtsButton="\t\t\t\t<input type=submit 	value='1 Tag Vorwärts'	name='submitVorwärts'>\n";echo $vorwärtsButton;
$copyButton="\t\t\t\t<input type=submit 	value='In die nächste Woche kopieren'	name='submitCopyPaste'>\n";echo $copyButton;
$submitButton="\t\t\t\t<input type=submit value=Absenden name='submitDienstplan'>\n";echo "$submitButton";
echo "\t\t\t\t<a href=tag-out.php?datum=".$datum.">[Lesen]</a>\n";
echo "\t\t\t</div>\n";
echo "\t\t\t<div id=wochenAuswahl>\n";
echo "\t\t\t\t<input name=tag type=date value=".date('Y-m-d', strtotime($datum)).">\n";
echo "\t\t\t\t<input type=submit name=tagesAuswahl value=Anzeigen>\n";
echo "\t\t\t</div>\n";
echo "\t\t\t<table border=2 style=width:99%>\n";
echo "\t\t\t\t<tr>\n";
for ($i=0; $i<count($Dienstplan); $i++)
{//Datum
	$zeile="";
	echo "\t\t\t\t\t<td>";
	$zeile.="<input type=hidden name=Dienstplan[".$i."][Datum][0] value=".$Dienstplan[$i]["Datum"][0].">";
	$zeile.="<input type=hidden name=mandant value=".$mandant.">";
	$zeile.=strftime('%d.%m.', strtotime( $Dienstplan[$i]["Datum"][0]));
	echo $zeile;
	if(!empty($feiertag)){echo " ".$feiertag." ";}
	if(isset($notdienst)){echo " NOTDIENST ";}
	echo "</td>\n";
}	
if ( file_exists("images/dienstplan_m".$mandant."_".$datum.".png") )
{
echo "\t\t\t\t\t<td align=center valign=top rowspan=30 style=width:800px>";
echo "<img src=images/dienstplan_m".$mandant."_".$datum.".png?".filemtime('images/dienstplan_m'.$mandant.'_'.$datum.'.png')." style=width:90%;><br>"; 
//Um das Bild immer neu zu laden, wenn es verändert wurde müssen wir das Cachen verhindern. Daher hängen wir das Änderungsdatum an.
echo "<img src=images/histogramm_m".$mandant."_".$datum.".png?".filemtime('images/dienstplan_m'.$mandant.'_'.$datum.'.png')." style=width:90%;></td>\n";
}
echo "\t\t\t\t</tr><tr>\n";
for ($i=0; $i<count($Dienstplan); $i++)
{//Wochentag
	$zeile="";
	echo "\t\t\t\t\t<td style=width:30%>";
	$zeile.=strftime('%A', strtotime( $Dienstplan[$i]["Datum"][0]));
	echo $zeile;
	echo "</td>\n";
}
for ($j=0; $j<$VKcount; $j++)
{
	if(!empty($feiertag) && !isset($notdienst)){break 1;}
	echo "\t\t\t\t</tr><tr>\n";
	for ($i=0; $i<count($Dienstplan); $i++)
	{//Mitarbeiter
		$zeile="";
		echo "\t\t\t\t\t<td align=right>";
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
		
		echo "</td>\n";
	}
	echo "\t\t\t\t</tr><tr>\n";
	for ($i=0; $i<count($Dienstplan); $i++)
	{//Mittagspause
		$zeile="";
		echo "\t\t\t\t\t<td align=right>";
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
		echo "</td>\n";
	}
}
echo "\t\t\t\t</tr>";

//Wir werfen einen Blick in den Urlaubsplan und schauen, ob alle da sind.
if (isset($Urlauber))
{
	echo "\t\t<tr><td align=right>Urlaub</td><td>"; foreach($Urlauber as $value){echo $Mitarbeiter[$value]."<br>";}; echo "</td></tr>\n";
}
if (isset($Kranke))
{
	echo "\t\t<tr><td align=right>Krank</td><td>"; foreach($Kranke as $value){echo $Mitarbeiter[$value]."<br>";}; echo "</td></tr>\n";
}
echo "\t\t\t</table>\n";
echo "$submitButton";
echo "\t\t</form>\n";
//	echo "<pre>";	var_export($MarienplatzMitarbeiter);    	echo "</pre>"; // Hier kann der aus der Datenbank gelesene Datensatz zu Debugging-Zwecken angesehen werden.

echo "\t</body>\n";
echo "</html>";
?>
