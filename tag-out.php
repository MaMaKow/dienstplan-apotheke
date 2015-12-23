<?php
require 'default.php';
require 'db-verbindung.php';
#Diese Seite wird den kompletten Dienstplan eines einzelnen Tages anzeigen.
$mandant=1;	//Wir zeigen den Dienstplan für die "Apotheke am Marienplatz"
$tage=1;	//Dies ist eine Wochenansicht ohne Wochenende

//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';


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

$VKcount=count($Mitarbeiter); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
//end($Mitarbeiter); $VKmax=key($Mitarbeiter); reset($Mitarbeiter); //Wir suchen nach der höchsten VK-Nummer VKmax.
$VKmax=max(array_keys($Mitarbeiter)); // Die höchste verwendete VK-Nummer
//Wir schauen, on alle Anwesenden anwesend sind und alle Kranken und Siechenden im Urlaub.


//Hier beginnt die Fehlerausgabe. Es werden alle Fehler angezeigt, die wir in $Fehlermeldung gesammelt haben.
if (isset($Fehlermeldung))
{
	foreach($Fehlermeldung as $fehler)
	{
		echo "\t\t<div class=overlay><H1>".$fehler."<H1></div>\n";
	}
}



//Produziere die Ausgabe
?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body bgcolor=#D0E0F0>
<?php
echo "<a href=woche-out.php?datum=".$datum.">Kalenderwoche ".strftime('%V', strtotime($datum))."</a><br>\n";
if ( isset($datenübertragung) ) {echo $datenübertragung;}
echo "<form id=myform method=post>\n";
$RückwärtsButton="\t<input type=submit 	class=no-print value='1 Tag Rückwärts'	name='submitRückwärts'>\n";echo $RückwärtsButton;
$VorwärtsButton="\t<input type=submit 	class=no-print value='1 Tag Vorwärts'	name='submitVorwärts'>\n";echo $VorwärtsButton;
echo "<a href=tag-in.php?datum=".$datum." class=no-print>[Bearbeiten]</a>";
//$submitButton="\t<input type=submit value=Absenden name='submitDienstplan'>\n";echo $submitButton; Leseversion
echo "	<table border=0 >\n";
echo "			<tr>\n";
for ($i=0; $i<count($Dienstplan); $i++)
{//Datum
	$zeile="";
	echo "				<td>";
	$zeile.="<input type=hidden size=2 name=Dienstplan[".$i."][Datum][0] value=".$Dienstplan[$i]["Datum"][0].">";
	$zeile.=strftime('%d.%m.', strtotime( $Dienstplan[$i]["Datum"][0]));
	echo $zeile;
	require 'db-lesen-feiertag.php';
	if(isset($feiertag)){echo " ".$feiertag." ";}
	if(isset($notdienst)){echo " NOTDIENST ";}
	echo "</td>\n";
}	
if ( file_exists("images/dienstplan_m".$mandant."_".$datum.".png") )
{
echo "<td align=center valign=top rowspan=60 style=width:800px>";
//echo "<td align=center valign=top rowspan=60>";
echo "<img src=images/dienstplan_m".$mandant."_".$datum.".png?".filemtime('images/dienstplan_m'.$mandant.'_'.$datum.'.png')." style=width:90%;><br>"; 
//Um das Bild immer neu zu laden, wenn es verändert wurde müssen wir das Cachen verhindern.
echo "<img src=images/histogramm_m".$mandant."_".$datum.".png?".filemtime('images/dienstplan_m'.$mandant.'_'.$datum.'.png')." style=width:90%;></td>";
//echo "<td></td>";//Wir fügen hier eine Spalte ein, weil im IE9 die Tabelle über die Seite hinaus geht.
}
echo "			</tr><tr>\n";
for ($i=0; $i<count($Dienstplan); $i++)
{//Wochentag
	$zeile="";
	echo "				<td>";
	$zeile.=strftime('%A', strtotime( $Dienstplan[$i]["Datum"][0]));
	echo $zeile;
	echo "</td>\n";
}
for ($j=0; $j<$VKcount; $j++)
{
	if(!empty($feiertag) && !isset($notdienst)){break 1;}
	echo "			</tr><tr>\n";
	for ($i=0; $i<count($Dienstplan); $i++)
	{//Mitarbeiter
		$zeile="";
		if (isset($Dienstplan[$i]["VK"][$j]) && isset($Mitarbeiter[$Dienstplan[$i]["VK"][$j]]) )
		{ 
			$zeile.="\t\t\t<td><b><a href=mitarbeiter-out.php?datum=".$Dienstplan[$i]["Datum"][0]."&auswahlMitarbeiter=".$Dienstplan[$i]["VK"][$j].">";
			$zeile.=$Dienstplan[$i]["VK"][$j]." ".$Mitarbeiter[$Dienstplan[$i]["VK"][$j]];
			$zeile.="</a></b> ";
		}
		//Dienstbeginn
		if (isset($Dienstplan[$i]["VK"][$j])) 
		{
			$zeile.=strftime('%H:%M',strtotime($Dienstplan[$i]["Dienstbeginn"][$j]));
			$zeile.=" - ";
		}
		//Dienstende
		if (isset($Dienstplan[$i]["VK"][$j])) 
		{
			$zeile.=strftime('%H:%M',strtotime($Dienstplan[$i]["Dienstende"][$j]));
		}
		echo $zeile;
		echo "				</td>\n";
	}
	echo "			</tr><tr>\n";
	for ($i=0; $i<count($Dienstplan); $i++)
	{//Mittagspause
		$zeile="";
		if (isset($Dienstplan[$i]["VK"][$j]))
		{
			echo "				<td>&nbsp ";
		}
		if (isset($Dienstplan[$i]["VK"][$j]) and $Dienstplan[$i]["Mittagsbeginn"][$j] > 0 )
		{
			$zeile.=" Pause: ";
			$zeile.= strftime('%H:%M', strtotime($Dienstplan[$i]["Mittagsbeginn"][$j]));
		}
		if (isset($Dienstplan[$i]["VK"][$j]) and $Dienstplan[$i]["Mittagsbeginn"][$j] > 0 )
		{
			$zeile.=" - ";
			$zeile.= strftime('%H:%M', strtotime($Dienstplan[$i]["Mittagsende"][$j]));
		}
		echo $zeile;
		echo "</td>";
	}
}
echo "			</tr>\n";

echo "<tr><td></td></tr>";
echo "	</table>\n";
//echo $submitButton; Kein Schreibrecht in der Leseversion
echo "</form>\n";
	
//echo "<pre>";	var_export($Dienstplan);    	echo "</pre>"; // Hier kann der aus der Datenbank gelesene Datensatz zu Debugging-Zwecken angesehen werden.



		?>
	</body>
</html>
