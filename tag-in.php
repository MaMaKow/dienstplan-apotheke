<?php
require 'default.php';
require 'db-verbindung.php';
$mandant=1;	//Wir zeigen den Dienstplan standardmäßig für die "Apotheke am Marienplatz"
$tage=1;	//Dies ist eine Wochenansicht ohne Wochenende

#Diese Seite wird den kompletten Dienstplan eines einzelnen Tages anzeigen.


$datenübertragung="";
$dienstplanCSV="";



//$Dienstbeginn=array( "8:00", "8:30", "9:00", "9:30", "10:00", "11:30", "12:00", "18:30" );
$heute=date('Y-m-d');
$datum=$heute; //Dieser Wert wird überschrieben, wenn "$wochenauswahl und $woche per POST übergeben werden."



require 'get-auswertung.php'; //Auswerten der per GET übergebenen Daten.
require 'post-auswertung.php'; //Auswerten der per POST übergebenen Daten.
//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';
//Hole eine Liste aller Mandanten (Filialen)
require 'db-lesen-mandant.php';
require 'db-lesen-tage.php'; //Lesen der in der Datenbank gespeicherten Daten.
$Dienstplan=db_lesen_tage($tage, $mandant);
require 'db-lesen-feiertag.php';
require_once 'db-lesen-abwesenheit.php';

if( empty($Dienstplan[0]['VK'][0]) AND date('N', strtotime($datum))<6 ) //Samstag und Sonntag planen wir nicht.
{
	//Wir wollen eine automatische Dienstplanfindung beginnen.
	//Mal sehen, wie viel die Maschine selbst gestalten kann.
	$Fehlermeldung[]="Kein Plan in der Datenbank dies is ein Vorschlag!";
//	unset ($Dienstplan);
	require_once 'plane-tag.php';
}
if( !empty($Dienstplan[0]['VK'][0]) )
{
	require 'pruefe-dienstplan.php';
}
else
{
	echo "Dienstplan konnte nicht überprüft werden.";
}

require 'db-lesen-notdienst.php';
if(isset($notdienst['mandant']))
{
	$Warnmeldung[]="An den Notdienst denken!";
}




$VKcount=count($Mitarbeiter); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
//end($Mitarbeiter); $VKmax=key($Mitarbeiter); reset($Mitarbeiter); //Wir suchen nach der höchsten VK-Nummer VKmax.
$VKmax=max(array_keys($Mitarbeiter));

//Wir schauen, on alle Anwesenden anwesend sind und alle Kranken und Siechenden im Urlaub.
require 'pruefe-abwesenheit.php';




//Produziere die Ausgabe
?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="style.css" media="all">
		<link rel="stylesheet" type="text/css" href="print.css" media="print">
	</head>
	<body bgcolor=#D0E0F0>
<?php
require 'navigation.php';
//Hier beginnt die Fehlerausgabe. Es werden alle Fehler angezeigt, die wir in $Fehlermeldung gesammelt haben.
if (isset($Fehlermeldung))
{
	echo "		<div class=errormsg><H1>";
	foreach($Fehlermeldung as $fehler)
	{
		echo "		<H1>".$fehler."</H1>";
	}
	echo "</div>";
}
if (isset($Warnmeldung))
{
	echo "		<div class=warningmsg><H1>";
	foreach($Warnmeldung as $warnung)
	{
		echo "		<H1>".$warnung."</H1>";
	}
	echo "</div>";
}

//Hier beginnt die Normale Ausgabe.
echo "<div class=no-image>\n";
echo "\t\tKalenderwoche ".strftime('%V', strtotime($datum))."<br><div class=only-print><b>". $Mandant[$mandant] ."</b></div><br>\n";
echo "\t\t<form id=mandantenformular method=post>\n";
echo "\t\t\t<input type=hidden name=datum value=".$Dienstplan[0]["Datum"][0].">\n";
echo "\t\t\t<select class=no-print style=font-size:150% name=mandant onchange=this.form.submit()>\n";
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
$vorwärtsButton="\t\t\t\t<input type=submit 	value='1 Tag Vorwärts'	name='submitVorwärts'><br>\n";echo $vorwärtsButton;
$copyButton="\t\t\t\t<input type=submit 	value='In die nächste Woche kopieren'	name='submitCopyPaste'>\n";echo $copyButton;
$submitButton="\t\t\t\t<input type=submit value=Absenden name='submitDienstplan'>\n";echo "$submitButton";
echo "\t\t\t\t<a href=tag-out.php?datum=".$datum.">[Lesen]</a>\n";
echo "\t\t\t</div>\n";
echo "\t\t\t<div id=wochenAuswahl>\n";
echo "\t\t\t\t<input name=tag type=date value=".date('Y-m-d', strtotime($datum)).">\n";
echo "\t\t\t\t<input type=submit name=tagesAuswahl value=Anzeigen>\n";
echo "\t\t\t</div>\n";
echo "\t\t\t<table border=2>\n";
echo "\t\t\t\t<tr>\n";
for ($i=0; $i<count($Dienstplan); $i++)
{//Datum
	$zeile="";
	echo "\t\t\t\t\t<td>";
	$zeile.="<input type=hidden name=Dienstplan[".$i."][Datum][0] value=".$Dienstplan[$i]["Datum"][0].">";
	$zeile.="<input type=hidden name=mandant value=".$mandant.">";
	$zeile.=strftime('%d.%m. ', strtotime( $Dienstplan[$i]["Datum"][0]));
	echo $zeile;
//Wochentag
	$zeile="";
	$zeile.=strftime('%A ', strtotime( $Dienstplan[$i]["Datum"][0]));
	echo $zeile;
	require 'db-lesen-feiertag.php';
	if(isset($feiertag)){echo " ".$feiertag." ";}
	require 'db-lesen-notdienst.php';
	if(isset($notdienst['mandant']))
	{
		echo "<br>NOTDIENST<br>".$Mitarbeiter[$notdienst['vk']]." / ". $Mandant[$notdienst['mandant']];
	}
	echo "</td>\n";
}
for ($j=0; $j<$VKcount; $j++)
{
	if(isset($feiertag) && !isset($notdienst)){break 1;}
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
		$zeile.="</select>\n";
		//Dienstbeginn
		$zeile.="\t\t\t\t\t\t<input type=hidden name=Dienstplan[".$i."][Datum][".$j."] value=".$Dienstplan[0]["Datum"][0].">\n";
		$zeile.="\t\t\t\t\t\t<input type=time size=1 name=Dienstplan[".$i."][Dienstbeginn][".$j."] tabindex=".($i*$VKcount*5 + $j*5 + 2 )." value=";
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
	echo "\t\t<tr><td><b>Urlaub</b><br>"; foreach($Urlauber as $value){echo $Mitarbeiter[$value]."<br>";}; echo "</td></tr>\n";
}
if (isset($Kranke))
{
	echo "\t\t<tr><td><b>Krank</b><br>"; foreach($Kranke as $value){echo $Mitarbeiter[$value]."<br>";}; echo "</td></tr>\n";
}
echo "\t\t\t</table>\n";
echo "$submitButton";
echo "\t\t</form>\n";
echo "</div>";
if ( file_exists("images/dienstplan_m".$mandant."_".$datum.".png") )
{
echo "<div class=above-image>";
echo "<div class=image>";
//echo "<td align=center valign=top rowspan=60>";
echo "<img src=images/dienstplan_m".$mandant."_".$datum.".png?".filemtime('images/dienstplan_m'.$mandant.'_'.$datum.'.png')." style=width:100%;><br>"; 
//Um das Bild immer neu zu laden, wenn es verändert wurde müssen wir das Cachen verhindern.
//echo "</div>";
//echo "<div class=image>";
echo "<img src=images/histogramm_m".$mandant."_".$datum.".png?".filemtime('images/dienstplan_m'.$mandant.'_'.$datum.'.png')." style=width:100%;>";
echo "</div>";
//echo "<td></td>";//Wir fügen hier eine Spalte ein, weil im IE9 die Tabelle über die Seite hinaus geht.
}
//	echo "<pre>";	var_export($MandantenMitarbeiter);    	echo "</pre>"; // Hier kann der aus der Datenbank gelesene Datensatz zu Debugging-Zwecken angesehen werden.
//	echo "<pre>";	var_export($Dienstplan);    	echo "</pre>"; // Hier kann der aus der Datenbank gelesene Datensatz zu Debugging-Zwecken angesehen werden.

echo "\t</body>\n";
echo "</html>";
?>
