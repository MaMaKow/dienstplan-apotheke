<?php
require 'default.php';
require 'db-verbindung.php';
#Diese Seite wird den kompletten Dienstplan eines einzelnen Tages anzeigen.
$mandant=1;	//Wir zeigen den Dienstplan für die "Apotheke am Marienplatz"
$tage=1;	//Dies ist eine Wochenansicht ohne Wochenende

//Hole eine Liste aller Mandanten (Filialen)
require 'db-lesen-mandant.php';


$datenübertragung="";
$dienstplanCSV="";



//$Dienstbeginn=array( "8:00", "8:30", "9:00", "9:30", "10:00", "11:30", "12:00", "18:30" );
$heute=date('Y-m-d');
$datum=$heute; //Dieser Wert wird überschrieben, wenn "$wochenauswahl und $woche per POST übergeben werden."
require 'cookie-auswertung.php'; //Auswerten der per GET übergebenen Daten.
require 'get-auswertung.php'; //Auswerten der per GET übergebenen Daten.
require 'post-auswertung.php'; //Auswerten der per POST übergebenen Daten.
if (isset($mandant))
{
	create_cookie("mandant", $mandant, 30);
}
if (isset($datum))
{
	create_cookie("datum", $datum, 0.5);
}

//The following lines check for the state of approval.
//Duty rosters have to be approved by the leader, before the staff can view them.
unset($approval);
$abfrage="SELECT state FROM `approval` WHERE date='$datum' AND branch='$mandant'";
$ergebnis = mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
while($row = mysqli_fetch_object($ergebnis)){
	$approval=$row->state;
}
if (isset($approval)) {
	if ($approval=="approved") {
		//$Warnmeldung[]="Alles ist gut.";
	} elseif ($approval=="not_yet_approved") {
		$Warnmeldung[]="Der Dienstplan wurde noch nicht von der Leitung bestätigt!";
	} elseif ($approval=="disapproved") {
		$Warnmeldung[]="Der Dienstplan wird noch überarbeitet!";
	}
} else {
	$approval="not_yet_approved";
	$Warnmeldung[]="Fehlende Daten in der Tabelle `approval`";
	// TODO: This is an Exception. It will occur when There is no approval, disapproval or other connected information in the approval table of the database.
	//That might espacially occur during the development stage of this feature.
}


//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';
//Lesen der in der Datenbank gespeicherten Daten.
require 'db-lesen-tage.php';
$Dienstplan=db_lesen_tage($tage, $mandant);
//require "zeichne-histogramm.php";
$VKcount=count($Mitarbeiter); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
//end($Mitarbeiter); $VKmax=key($Mitarbeiter); reset($Mitarbeiter); //Wir suchen nach der höchsten VK-Nummer VKmax.
$VKmax=max(array_keys($Mitarbeiter)); // Die höchste verwendete VK-Nummer

//Wir schauen, on alle Anwesenden anwesend sind und alle Kranken und Siechenden im Urlaub.



//Produziere die Ausgabe
?>
<html>
<?php require 'head.php';?>
	<body>
<?php
require 'navigation.php';

if (isset($Fehlermeldung))
{
	echo "\t\t<div class=errormsg>\n";
	foreach($Fehlermeldung as $fehler)
	{
		echo "\t\t\t<H1>".$fehler."</H1>\n";
	}
	echo "\t\t</div>";
}
if (isset($Warnmeldung))
{
	echo "\t\t<div class=warningmsg>\n";
	foreach($Warnmeldung as $warnung)
	{
		echo "\t\t\t<H2>".$warnung."</H2>\n";
	}
	echo "\t\t</div>\n";
}

echo "\t\t<div class=main-area>\n";
echo "\t\t\t<a href=woche-out.php?datum=".$datum.">Kalenderwoche ".strftime('%V', strtotime($datum))."</a><br>\n";


//Support for various branch clients.
echo "\t\t\t<form id=mandantenformular method=post>\n";
echo "\t\t\t\t<input type=hidden name=datum value=".$Dienstplan[0]["Datum"][0].">\n";
echo "\t\t\t\t<select class='no-print large' name=mandant onchange=this.form.submit()>\n";
//echo "\t\t\t\t<option value=".$mandant.">".$Mandant[$mandant]."</option>\n";
foreach ($Mandant as $filiale => $name)
{
	if ($filiale!=$mandant)
	{
		echo "\t\t\t\t\t<option value=".$filiale.">".$name."</option>\n";
	}else {
		echo "\t\t\t\t\t<option value=".$filiale." selected>".$name."</option>\n";
	}
}
echo "\t\t\t\t</select>\n\t\t\t</form>\n";
echo "\t\t\t<form id=myform method=post>\n";
echo "<div class=no-print>";
echo "$rückwärts_button_img";
echo "$vorwärts_button_img";
echo "<br><br>\n";
echo "\t\t\t\t<a href=tag-in.php?datum=".$datum.">[Bearbeiten]</a>\n";
echo "<br><br>\n";
//echo "</div>\n";

//$submit_button="\t<input type=submit value=Absenden name='submitDienstplan'>\n";echo $submit_button; Leseversion
//echo "\t\t\t\t<div id=wochenAuswahl class=no-print>\n";
echo "\t\t\t\t\t<input name=tag type=date value=".date('Y-m-d', strtotime($datum)).">\n";
echo "\t\t\t\t\t<input type=submit name=tagesAuswahl value=Anzeigen>\n";
echo "\t\t\t\t</div>\n";
echo "\t\t\t\t<table border=0 >\n";
echo "\t\t\t\t\t<tr>\n";
for ($i=0; $i<count($Dienstplan); $i++)
{//Datum
	$zeile="";
	echo "\t\t\t\t\t\t<td>";
	$zeile.="<input type=hidden size=2 name=Dienstplan[".$i."][Datum][0] value=".$Dienstplan[$i]["Datum"][0].">";
	$zeile.="<input type=hidden name=mandant value=".$mandant.">";
	$zeile.=strftime('%d.%m. ', strtotime( $Dienstplan[$i]["Datum"][0]));
	echo $zeile;
//	echo "</td>\n";
//}
//echo "\t\t\t\t\t</tr><tr>\n";
//for ($i=0; $i<count($Dienstplan); $i++)
//{//Wochentag
	$zeile="";
//	echo "\t\t\t\t\t\t<td>";
	$zeile.=strftime('%A', strtotime( $Dienstplan[$i]["Datum"][0]));
	echo $zeile;
	require 'db-lesen-feiertag.php';
	if(isset($feiertag)){echo " ".$feiertag." ";}
	require_once 'db-lesen-abwesenheit.php';
	require 'db-lesen-notdienst.php';
	if(isset($notdienst['mandant'])){
		echo "<br>NOTDIENST<br>";
		if (isset($Mitarbeiter[$notdienst['vk']])) {
			echo $Mitarbeiter[$notdienst['vk']];
		}else {
			echo "???";
		}
		echo " / ". $Mandant[$notdienst['mandant']];
	}
	echo "</td>\n";
}
if ($approval=="approved" OR $config['hide_disapproved']==false) {
for ($j=0; $j<$VKcount; $j++)
{
	//TODO The following line will prevent planning on hollidays. The problem ist, that we work might emergency service on hollidays. And if the service starts on the day before, then the programm does not know here. But we have to be here until 8:00 AM.
	//if(isset($feiertag) && !isset($notdienst)){break 1;}
	echo "\t\t\t\t\t</tr><tr>\n";
	for ($i=0; $i<count($Dienstplan); $i++)
	{//Mitarbeiter
		$zeile="";
		if (isset($Dienstplan[$i]["VK"][$j]) && isset($Mitarbeiter[$Dienstplan[$i]["VK"][$j]]) )
		{
			$zeile.="\t\t\t\t\t\t<td><b><a href=mitarbeiter-out.php?datum=".$Dienstplan[$i]["Datum"][0]."&auswahl_mitarbeiter=".$Dienstplan[$i]["VK"][$j].">";
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
		if (isset($Dienstplan[$i]["VK"][$j]) && isset($Mitarbeiter[$Dienstplan[$i]["VK"][$j]]) )
		{
		echo "</td>\n";
		}
	}
	echo "\t\t\t\t\t</tr><tr>\n";
	for ($i=0; $i<count($Dienstplan); $i++)
	{//Mittagspause
		$zeile="";
		if (isset($Dienstplan[$i]["VK"][$j]))
		{
			echo "\t\t\t\t\t\t<td>&nbsp ";
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
		if (isset($Dienstplan[$i]["VK"][$j]))
		{
			echo "</td>\n";
		}
	}
}
echo "\t\t\t\t\t</tr>\n";

echo "\t\t\t\t\t<tr><td></td></tr>\n";
require 'schreiben-tabelle.php';
foreach ($Mandant as $filiale => $Name) {
	if ($mandant == $filiale) {
		continue 1;
	}
	$Filialplan[$filiale]=db_lesen_tage($tage, $filiale, '['.$mandant.']'); // Die Funktion schaut jetzt nach dem Arbeitsplan in der Helene.
	if (!empty(array_column($Filialplan[$filiale], 'VK'))) //array_column durchsucht alle Tage nach einem 'VK'.
	{
		echo "<tr><td><br></td></tr>";
		echo "</tbody><tbody><tr><td colspan=$tage>".$Kurz_mandant[$mandant]." in ".$Kurz_mandant[$filiale]."</td></tr>";
		$table_html =	schreiben_tabelle($Filialplan[$filiale]);
		echo $table_html;
	}
}
echo "<tr><td><br></td></tr>";
if (isset($Urlauber))
{
	echo "\t\t<tr><td><b>Urlaub</b><br>"; foreach($Urlauber as $value){echo $Mitarbeiter[$value]."<br>";}; echo "</td></tr>\n";
}
if (isset($Kranke))
{
	echo "\t\t<tr><td><b>Krank</b><br>"; foreach($Kranke as $value){echo $Mitarbeiter[$value]."<br>";}; echo "</td></tr>\n";
}

}
echo "\t\t\t\t</table>\n";
//echo $submit_button; Kein Schreibrecht in der Leseversion
echo "\t\t\t</form>\n";
echo "\t\t</div>\n";

if ( ($approval=="approved" OR $config['hide_disapproved']==false) AND !empty($Dienstplan[0]["Dienstbeginn"]))
{
	echo "\t\t<div class=above-image>\n";
	echo "\t\t\t<div class=image>\n";
	require_once 'image_dienstplan.php';
        $svg_image_dienstplan = draw_image_dienstplan($Dienstplan);
        echo $svg_image_dienstplan;
        echo "<br>\n";
        require_once 'image_histogramm.php';
        $svg_image_histogramm = draw_image_histogramm($Dienstplan);
        echo "<br>\n";
        echo $svg_image_histogramm;
	echo "\t\t\t</div>\n";
	echo "\t\t</div>\n";
}
require 'contact-form.php';

//echo "<pre>";	var_export($Dienstplan);    	echo "</pre>";

		?>
	</body>
</html>
