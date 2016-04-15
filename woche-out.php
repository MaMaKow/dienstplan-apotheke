<?php
require 'default.php';
require 'db-verbindung.php';

$mandant=1;	//Wir zeigen den Dienstplan für die "Apotheke am Marienplatz"
$filiale=2;	//Am unteren Rand werden auch unsere Mitarbeiter in dieser Filale angezeigt.
$tage=6;	//Dies ist eine Wochenansicht ohne Wochenende

$datenübertragung="";
$dienstplanCSV="";


//$Dienstbeginn=array( "8:00", "8:30", "9:00", "9:30", "10:00", "11:30", "12:00", "18:30" );
$heute=date('Y-m-d');
$datum=$heute; //Dieser Wert wird überschrieben, wenn "$wochenauswahl und $woche per POST oder $datum per GET übergeben werden."
require 'cookie-auswertung.php'; //Auswerten der per GET übergebenen Daten.
require 'get-auswertung.php'; //Auswerten der per GET übergebenen Daten.
require 'post-auswertung.php'; //Auswerten der per POST übergebenen Daten.
$montagsDifferenz=date("w", strtotime($datum))-1; //Wir wollen den Anfang der Woche
$montagsDifferenzString="-".$montagsDifferenz." day";
$datum=strtotime($montagsDifferenzString, strtotime($datum));
$datum=date('Y-m-d', $datum);
if (isset($datum))
{
	create_cookie("datum", $datum); //Diese Funktion wird von cookie-auswertung.php bereit gestellt. Sie muss vor dem ersten echo durchgeführt werden.
}
if (isset($mandant))
{
	create_cookie("mandant", $mandant); //Diese Funktion wird von cookie-auswertung.php bereit gestellt. Sie muss vor dem ersten echo durchgeführt werden.
}

//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';
//Hole eine Liste aller Mandanten (Filialen)
require 'db-lesen-mandant.php';
require 'db-lesen-tage.php'; //Lesen der in der Datenbank gespeicherten Daten.
$Dienstplan=db_lesen_tage($tage, $mandant); //Die Funktion ruft die Daten nur für den angegebenen Mandanten und für den angegebenen Zeitraum ab.
$Filialplan=db_lesen_tage($tage, $filiale, '[^'.$filiale.']'); // Die Funktion schaut jetzt nach dem Arbeitsplan in der Helene.

$VKcount=count($Mitarbeiter); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
$VKmax=max(array_keys($Mitarbeiter)); //Wir suchen nach der höchsten VK-Nummer VKmax. Diese wird für den <option>-Bereich benötigt.




//Produziere die Ausgabe
?>
<html moznomarginboxes> <!-- Wir wollen beim Ausdrucken keinen Header mit auf dem Papier. -->
	<head>
		<meta charset=UTF-8>
		<link rel="stylesheet" type="text/css" href="style.css" media="all">
		<link rel="stylesheet" type="text/css" href="print.css" media="print" />
	</head>
	<body>
<?php
require 'navigation.php';
echo "\t\t<div class='no-image'>\n";
echo "\t\t\t<div class='no-print'>Kalenderwoche ".strftime('%V', strtotime($datum))."</div>\n";

//Support for various branch clients.
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

echo "\t\t<form id=myform method=post>\n";
$RückwärtsButton="\t\t\t\t<input type=submit 	class=no-print	value='1 Woche Rückwärts'	name='submitWocheRückwärts'>\n";
echo $RückwärtsButton;
$VorwärtsButton="\t\t\t\t<input type=submit 	class=no-print	value='1 Woche Vorwärts'	name='submitWocheVorwärts'>\n";
echo $VorwärtsButton;
echo "\t\t\t\t<table border=0 rules=groups>\n";
//echo "\t\t\t\t<div class=stretch-on-print>\n";
echo "\t\t\t\t\t<thead>\n";
echo "\t\t\t\t\t<tr>\n";
for ($i=0; $i<count($Dienstplan); $i++)
{//Datum
	echo "\t\t\t\t\t\t<td>";
	echo "<a href=tag-out.php?datum=".$Dienstplan[$i]["Datum"][0].">";
	echo strftime('%A', strtotime( $Dienstplan[$i]["Datum"][0]));
	echo " \n";
	echo "<input type=hidden size=2 name=Dienstplan[".$i."][Datum][0] value=".$Dienstplan[$i]["Datum"][0].">";
	echo "<input type=hidden name=mandant value=".$mandant.">";
	echo strftime('%d.%m.', strtotime($Dienstplan[$i]["Datum"][0]));
	$datum=($Dienstplan[$i]['Datum'][0]);
	require 'db-lesen-feiertag.php';
	if(isset($feiertag)){echo " <br>".$feiertag." ";}
	if (isset($feiertag) AND date('N', strtotime($datum))<6) {
		foreach ($MandantenMitarbeiter as $vk => $nachname) {
			if (!isset($bereinigte_Wochenstunden_Mitarbeiter[$vk])) {
					$bereinigte_Wochenstunden_Mitarbeiter[$vk] = $StundenMitarbeiter[$vk] - $StundenMitarbeiter[$vk] / 5;
			} else {
					$bereinigte_Wochenstunden_Mitarbeiter[$vk] = $bereinigte_Wochenstunden_Mitarbeiter[$vk] - $StundenMitarbeiter[$vk] / 5;
			}
		}
	}

	require 'db-lesen-notdienst.php';
	if(isset($notdienst)){echo "<br> NOTDIENST ";}
	echo "</td></a>\n";
}
echo "\t\t\t\t\t</tr></thead><tbody>";


require 'schreiben-tabelle.php';
schreiben_tabelle($Dienstplan);
if (!empty(array_column($Filialplan, 'VK'))) //array_column durchsucht alle Tage nach einem 'VK'.
{
	echo "</tbody><tbody><tr><td colspan=$tage>".$KurzMandant[$mandant]." in ".$KurzMandant[$filiale]."</td></tr>";
	schreiben_tabelle($Filialplan);
}
echo "\t\t\t\t\t</tbody>\n";
//echo "\t\t\t\t</div>\n";
echo "\t\t\t\t\t<tfoot><tr class=page-break></tr>\n";

//Wir werfen einen Blick in den Urlaubsplan und schauen, ob alle da sind.
for ($i=0; $i<count($Dienstplan); $i++)
{
	$datum=($Dienstplan[$i]['Datum'][0]);
	unset($Urlauber, $Kranke);
	require 'db-lesen-abwesenheit.php';
	require 'db-lesen-feiertag.php';
	if (!isset($Dienstplan[$i]['VK'])) {echo "\t\t\t\t\t\t<td>"; continue;} //Tage an denen kein Dienstplan existiert werden nicht geprüft.
	foreach ($Abwesende as $key => $vk) {
		if (!isset($feiertag) AND date('N', strtotime($datum))<6) {
				//An Feiertagen whaben wir die Stunden bereits abgezogen. Keine weiteren Abwesenheitsgründe notwendig.
				if (!isset($bereinigte_Wochenstunden_Mitarbeiter[$vk])) {
						$bereinigte_Wochenstunden_Mitarbeiter[$vk] = $StundenMitarbeiter[$vk] - $StundenMitarbeiter[$vk] / 5;
				} else {
						$bereinigte_Wochenstunden_Mitarbeiter[$vk] = $bereinigte_Wochenstunden_Mitarbeiter[$vk] - $StundenMitarbeiter[$vk] / 5;
				}
		}
	}
	//Jetzt notieren wir die Urlauber und die Kranken Mitarbeiter unten in der Tabelle.
	if (isset($Urlauber))
	{
		echo "\t\t\t\t\t<td align=left><b>Urlaub</b><br>"; foreach($Urlauber as $value){echo "<a href=abwesenheit-out.php?datum=".$datum."&auswahlMitarbeiter=".$value.">".$Mitarbeiter[$value]."</a><br>";};
	}
	else
	{
		echo "\t\t\t\t\t\t<td>";
	}
	if (isset($Kranke))
	{
		echo "\t\t<br><b>Krank</b><br>"; foreach($Kranke as $value){echo "<a href=abwesenheit-out.php?datum=".$datum."&auswahlMitarbeiter=".$value.">".$Mitarbeiter[$value]."</a><br>";}; echo "</td>\n";
	}
	else
	{
		echo "</td>\n";
	}
}
echo "\t\t\t\t\t</tr>\n";
echo "\t\t\t\t</table>\n";
echo "\t\t\t\t<table border=0 rules=groups>\n";

//Wir zeichnen jetzt die Wochenstunden der Mitarbeiter. In dieser Ansicht werden ausschließlich die Tage Montag bis Freitag betrachtet. Dies ist ein Unterschied zur Mitarbeiteransicht. Dort werden alle Wochentage berücksichtigt.
echo "\t\t\t\t\t<tr>\n";
echo "\t\t\t\t\t\t<td colspan=5>\n";
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

//An leeren Wochen soll nicht gerechnet werden.
if (!empty(array_column($Dienstplan, 'VK'))) //array_column durchsucht alle Tage nach einem 'VK'.
{
	echo "<b>Wochenstunden</b><tr>";
	ksort($Stunden);
	$i=0;$j=1; //Zähler für den Stunden-Array (wir wollen nach je 5 Mitarbeitern einen Umbruch)
	foreach($Stunden as $mitarbeiter => $stunden)
	{
		if ( array_key_exists($mitarbeiter, $MandantenMitarbeiter)===false ){continue; /*Wir zeigen nur die Stunden von Mitarbeitern, die auch in den Mandanten gehören.*/}
		$i++; //Der Faktor gibt an, bei welcher VK-Nummer der Umbruch erfolgt.
		if($i>=$tage)
		{
			echo "</tr><tr>";
			$i=0;//$j++;
		}
		echo "<td>".$Mitarbeiter[$mitarbeiter]." ".array_sum($stunden);
		echo " / ";
		if (isset($bereinigte_Wochenstunden_Mitarbeiter[$mitarbeiter])) {
				echo $bereinigte_Wochenstunden_Mitarbeiter[$mitarbeiter];
		} else {
				echo $StundenMitarbeiter[$mitarbeiter];
		}
		if (isset($bereinigte_Wochenstunden_Mitarbeiter[$mitarbeiter])) {
				if ($bereinigte_Wochenstunden_Mitarbeiter[$mitarbeiter] != array_sum($stunden)) {
						$differenz = array_sum($stunden) - $bereinigte_Wochenstunden_Mitarbeiter[$mitarbeiter];
						echo ' <b>( '.$differenz.' )</b>';
					}
				} else {
						if ($StundenMitarbeiter[$mitarbeiter] != array_sum($stunden)) {
								$differenz = array_sum($stunden) - $StundenMitarbeiter[$mitarbeiter];
								echo ' <b>( '.$differenz.' )</b>';
						}
				}

		echo "</td>";
	}
	echo "</tr>";
}
echo "\t\t\t\t\t\t</td>\n";
echo "\t\t\t\t\t</tr>\n";
echo "\t\t\t\t\t</tfoot>\n";
echo "\t\t\t\t</table>\n";
// echo $submitButton;
echo "\t\t\t</form>\n";
echo "</div>\n";
require 'contact-form.php';




//echo "<pre>";	var_export($Urlauber);    	echo "</pre>";

?>
	</body>
<html>
