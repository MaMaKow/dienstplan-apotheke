<?php
#Diese Seite wird den kompletten Dienstplan einer Woche  anzeigen.
require 'default.php';
$mandant=1;	//First branch is allways the default.
$tage=7;	//Dies ist eine Wochenansicht mit Wochenende



$datenübertragung="";
$dienstplanCSV="";



//$Dienstbeginn=array( "8:00", "8:30", "9:00", "9:30", "10:00", "11:30", "12:00", "18:30" );
$datum=date('Y-m-d'); //Dieser Wert wird überschrieben, wenn "$wochenauswahl und $woche per POST übergeben werden."
require 'cookie-auswertung.php'; //Auswerten der als COOKIE übergebenen Daten.
require 'get-auswertung.php'; //Auswerten der per GET übergebenen Daten.
require 'post-auswertung.php'; //Auswerten der per POST übergebenen Daten.
$montags_differenz=date("w", strtotime($datum))-1; //Wir wollen den Anfang der Woche
$montags_differenzString="-".$montags_differenz." day";
$datum=strtotime($montags_differenzString, strtotime($datum));
$datum=date('Y-m-d', $datum);
if (isset($datum))
{
	create_cookie("datum", $datum, 0.5); //Diese Funktion muss vor dem ersten echo durchgeführt werden.
}
if (isset($mandant))
{
	create_cookie("mandant", $mandant, 30); //Diese Funktion wird von cookie-auswertung.php bereit gestellt. Sie muss vor dem ersten echo durchgeführt werden.
}

//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';
//Hole eine Liste aller Mandanten (Filialen)
require 'db-lesen-mandant.php';
require 'db-lesen-tage.php'; //Lesen der in der Datenbank gespeicherten Daten.
$Dienstplan=db_lesen_tage($tage, $mandant);
require 'db-lesen-feiertag.php';

//end($Mitarbeiter); $VKmax=key($Mitarbeiter); reset($Mitarbeiter); //Wir suchen nach der höchsten VK-Nummer VKmax.
$VKmax=max(array_keys($Mitarbeiter));
$VKcount = calculate_VKcount ($Dienstplan);




//Produziere die Ausgabe
require 'head.php';
require 'navigation.php';

echo "Kalenderwoche ".strftime('%V', strtotime($datum))."<br>\n";
//Support for various branch clients.
echo "\t\t<form id=mandantenformular method=post>\n";
echo "\t\t\t<input type=hidden name=datum value=".$Dienstplan[0]["Datum"][0].">\n";
echo "\t\t\t<select class='no-print large' name=mandant onchange=this.form.submit()>\n";
foreach ($Mandant as $key => $value) //wir verwenden nicht die Variablen $filiale oder Mandant, weil wir diese jetzt nicht verändern wollen!
{
	if ($key!=$mandant)
	{
		echo "\t\t\t\t<option value=".$key.">".$value."</option>\n";
	} else {
		echo "\t\t\t\t<option value=".$key." selected>".$value."</option>\n";
	}
}
echo "\t\t\t</select>\n\t\t</form>\n";

echo "<form id=myform method=post>\n";
echo "<div class=no-print>";
echo $rückwärts_button_week_img;
echo $vorwärts_button_week_img;
echo "$submit_button_img";
echo "<br><br>\n";
echo "<div id=wochenAuswahl><input name=woche type=date id=date_chooser_input class='datepicker' value=".date('Y-m-d', strtotime($datum)).">";
echo "<input type=submit name=wochenAuswahl value=Anzeigen></div>";
echo "<br><br>";
//$submit_button="\t<input type=submit value=Absenden name='submitDienstplan'>\n";echo $submit_button;
echo "$submit_approval_button_img";
echo "$submit_disapproval_button_img";
echo "<br><br>\n";
echo "\t\t\t\t<a href='woche-out.php?datum=".$datum."' class=no-print>[Lesen]</a>\n";
// TODO: The button should be inactive when the approval already was done.
//$submit_approval_button="\t\t\t\t<input type=submit value=Genehmigen name='submit_approval'>\n";
//$submit_disapproval_button="\t\t\t\t<input type=submit value=Ablehnen name='submit_disapproval'>\n";
echo "<br><br>\n";
echo "</div>";

echo "\t<table>\n";
echo "\t\t\t\t\t<thead>\n";
echo "\t\t\t<tr>\n";
for ($i=0; $i<count($Dienstplan); $i++)
{//Datum
	$zeile="";
	echo "\t\t\t\t<td><a href='tag-in.php?datum=".$Dienstplan[$i]["Datum"][0]."'>";
	$zeile.="<input type=hidden size=2 name=Dienstplan[".$i."][Datum][0] value=".$Dienstplan[$i]["Datum"][0].">";
	$zeile.=strftime('%d.%m.', strtotime( $Dienstplan[$i]["Datum"][0]));
	echo $zeile;
	$datum=($Dienstplan[$i]['Datum'][0]);
	require 'db-lesen-feiertag.php';
	if(isset($feiertag)){echo " ".$feiertag." ";}
	require 'db-lesen-notdienst.php';
	if(isset($notdienst)){echo "<br> NOTDIENST ";}

	echo "<br>\n";//Wochentag
	$zeile="";
	$zeile.=strftime('%A', strtotime( $Dienstplan[$i]["Datum"][0]));
	echo $zeile;
	echo "</a></td>\n";

}
echo "\t\t\t\t\t</tr></thead><tbody><tr>";

for ($j=0; $j<$VKcount; $j++)
{
	if(isset($feiertag) && !isset($notdienst)){break 1;}
	echo "\t\t\t</tr><tr>\n";
	for ($i=0; $i<count($Dienstplan); $i++)
	{//Mitarbeiter
		$zeile="";
		echo "\t\t\t\t<td>";
		$zeile.="<select name=Dienstplan[".$i."][VK][".$j."] tabindex=".(($i*$VKcount*5) + ($j*5) + 1)."><option>";
		$zeile.="</option>";
		foreach ($Mitarbeiter as $k => $mitarbeiter)
		{
			if (isset($Dienstplan[$i]["VK"][$j]))
			{
				if ( isset($Mitarbeiter[$k]) and $Dienstplan[$i]["VK"][$j]!=$k ) //Dieser Ausdruck dient nur dazu, dass der vorgesehene  Mitarbeiter nicht zwei mal in der Liste auftaucht.
				{
					$zeile.="<option>".$k." ".$Mitarbeiter[$k]."</option>";
				}
				else
				{
					$zeile.="<option selected>".$k." ".$Mitarbeiter[$k]."</option>";
				}
			}
			elseif ( isset($Mitarbeiter[$k]) )
			{
					$zeile.="<option>".$k." ".$Mitarbeiter[$k]."</option>";
			}
		}
		$zeile.="</select>";
		//Dienstbeginn
		$zeile.=" <input type=time size=1 name=Dienstplan[".$i."][Dienstbeginn][".$j."] tabindex=".($i*$VKcount*5 + $j*5 + 2 )." value='";
		if (isset($Dienstplan[$i]["VK"][$j]))
		{
			$zeile.=strftime('%H:%M',strtotime($Dienstplan[$i]["Dienstbeginn"][$j]));
		}
		$zeile.="'> bis <input type=time size=1 name=Dienstplan[".$i."][Dienstende][".$j."] tabindex=".($i*$VKcount*5 + $j*5 + 3 )." value='";
		//Dienstende
		if (isset($Dienstplan[$i]["VK"][$j]))
		{
			$zeile.=strftime('%H:%M',strtotime($Dienstplan[$i]["Dienstende"][$j]));
		}
		$zeile.="'>";
		echo $zeile;

		echo "\t\t\t\t</td>\n";
	}
	echo "\t\t\t</tr><tr>\n";
	for ($i=0; $i<count($Dienstplan); $i++)
	{//Mittagspause
		$zeile="";
		echo "\t\t\t\t<td>";
		$zeile.="<div class='no-print kommentar_ersatz' style=display:inline><a onclick=unhide_kommentar() title='Kommentar anzeigen'>K+</a></div>";
		$zeile.="<div class='no-print kommentar_input' style=display:none><a onclick=rehide_kommentar() title='Kommentar ausblenden'>K-</a></div>";
		$zeile.=" Pause: <input type=time size=1 name=Dienstplan[".$i."][Mittagsbeginn][".$j."] tabindex=".($i*$VKcount*5 + $j*5 + 4 )." value='";
		if (isset($Dienstplan[$i]["VK"][$j]) and $Dienstplan[$i]["Mittagsbeginn"][$j] > 0 )
		{
			$zeile.= strftime('%H:%M', strtotime($Dienstplan[$i]["Mittagsbeginn"][$j]));
		}
		$zeile.="'> bis <input type=time size=1 name=Dienstplan[".$i."][Mittagsende][".$j."] tabindex=".($i*$VKcount*5 + $j*5 + 5 )." value='";
		if (isset($Dienstplan[$i]["VK"][$j]) and $Dienstplan[$i]["Mittagsbeginn"][$j] > 0 )
		{
			$zeile.= strftime('%H:%M', strtotime($Dienstplan[$i]["Mittagsende"][$j]));
		}
		$zeile.="'>";
		$zeile.="<div class=kommentar_input style=display:none><br>Kommentar: <input type=text name=Dienstplan[".$i."][Kommentar][".$j."] value=\"";
		if (isset($Dienstplan[$i]["Kommentar"][$j]))
		{
			$zeile.= $Dienstplan[$i]["Kommentar"][$j];
		}
		$zeile.="\"></div>";

		echo $zeile;
		echo "</td>";
	}
}
echo "\t\t\t</tr>\n";
echo "\t\t\t\t\t</tbody>\n";
//echo "\t\t\t\t</div>\n";
echo "\t\t\t\t\t<tfoot>"
//. "<tr class=page-break></tr>"
        . "\n";

//Wir werfen einen Blick in den Urlaubsplan und schauen, ob alle da sind.
echo "\t\t\t<tr>\n";
for ($i=0; $i<count($Dienstplan); $i++)
{
		require 'pruefe-abwesenheit.php';
		if (isset($Urlauber))
		{
			echo "\t\t<td><b>Urlaub</b><br>"; foreach($Urlauber as $value){echo $Mitarbeiter[$value]."<br>";}; echo "</td>\n";
		}
		else {
			echo "\t\t<td></td>\n";
		}
}
echo "\t\t</tr>\n";
echo "\t\t<tr>\n";
for ($i=0; $i<count($Dienstplan); $i++)
{
		require 'pruefe-abwesenheit.php';
		if (isset($Kranke))
		{
			echo "\t\t<td><b>Krank</b><br>"; foreach($Kranke as $value){echo $Mitarbeiter[$value]."<br>";}; echo "</td>\n";
		}
		else {
			echo "\t\t<td></td>\n";
		}
}
echo "\t\t</tr>\n";
echo "\t</table>\n";
echo "</form>\n";

//Hier beginnt die Fehlerausgabe. Es werden alle Fehler angezeigt, die wir in $Fehlermeldung gesammelt haben.
require_once 'src/php/build-warning-messages.php';
echo build_warning_messages($Fehlermeldung, $Warnmeldung);

require 'contact-form.php';
echo "</body>";
?>
