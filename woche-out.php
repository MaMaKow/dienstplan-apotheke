<?php
require 'default.php';
require 'schreiben-tabelle.php';
require 'db-lesen-abwesenheit.php';

$mandant=1;	//Wir zeigen den Dienstplan für die "Apotheke am Marienplatz"
$tage=6;	//Dies ist eine Wochenansicht ohne Sonntag

$datenübertragung="";
$dienstplanCSV="";

$error_message_html = "";
$warning_message_html = "";
$overlay_message_html = "";


$datum=date('Y-m-d'); //Dieser Wert wird überschrieben, wenn "$wochenauswahl und $woche per POST oder $datum per GET übergeben werden."
require 'cookie-auswertung.php'; //Auswerten der als COOKIE übergebenen Daten.
require 'get-auswertung.php'; //Auswerten der per GET übergebenen Daten.
require 'post-auswertung.php'; //Auswerten der per POST übergebenen Daten.
$montags_differenz=date("w", strtotime($datum))-1; //Wir wollen den Anfang der Woche
$montags_differenzString="-".$montags_differenz." day";
$datum=strtotime($montags_differenzString, strtotime($datum));
$datum=date('Y-m-d', $datum); $konstantes_datum=$datum;
for ($i = 0; $i < $tage; $i++){
    $Week_dates_unix[] = strtotime(' +' . $i . ' days', strtotime($datum));
    //echo date("d.m.Y", end($Week_dates_unix)) . "<br>\n";
}
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
$Dienstplan=db_lesen_tage($tage, $mandant); //Die Funktion ruft die Daten nur für den angegebenen Mandanten und für den angegebenen Zeitraum ab.

$VKcount=count($Mitarbeiter); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
$VKmax=max(array_keys($Mitarbeiter)); //Wir suchen nach der höchsten VK-Nummer VKmax. Diese wird für den <option>-Bereich benötigt.

//Build a div containing assignment of tasks:
require 'task-rotation.php';
//TODO: Works only for "Rezeptur" right now!
$task = "Rezeptur";
$weekly_rotation_div_html = task_rotation_main($Week_dates_unix, $task);




//Produziere die Ausgabe
require 'head.php';
require 'navigation.php';
$main_div_html = "\t\t<div id='main-area'>\n";
$date_info_line_html = "\t\t\t<div id=date_info_line class='no-print'>Kalenderwoche ".strftime('%V', strtotime($datum))."</div>\n";
$main_div_html .= $date_info_line_html;

//Support for various branch clients.
$branch_form_html = "";
$branch_form_html .= "\t\t<form id=branch_form method=post class='no-print'>\n";
$branch_form_html .= "\t\t\t<input type=hidden name=datum value=".$Dienstplan[0]["Datum"][0].">\n";
$branch_form_html .= "\t\t\t<select class='large' name=mandant onchange=this.form.submit()>\n";
foreach ($Mandant as $key => $value) //wir verwenden nicht die Variablen $filiale oder Mandant, weil wir diese jetzt nicht verändern wollen!
{
	if ($key!=$mandant)
	{
		$branch_form_html .= "\t\t\t\t<option value=".$key.">".$value."</option>\n";
	} else {
		$branch_form_html .= "\t\t\t\t<option value=".$key." selected>".$value."</option>\n";
	}
}
$branch_form_html .= "\t\t\t</select>\n\t\t</form>\n";
$main_div_html .= $branch_form_html;

$duty_roster_form_html = "\t\t<form id=duty_roster_form method=post>\n";
$buttons_div_html = "";
$buttons_div_html .= "<div id=buttons_div class=no-print>";
$buttons_div_html .= $rückwärts_button_week_img;
$buttons_div_html .= $vorwärts_button_week_img;
$buttons_div_html .= "<br><br>";
$buttons_div_html .= "\t\t\t\t\t<input name=tag type=date id=dateChooserInput class='datepicker' value=".date('Y-m-d', strtotime($datum)).">\n";
$buttons_div_html .= "\t\t\t\t\t<input type=submit name=tagesAuswahl value=Anzeigen>\n";
$buttons_div_html .= "<br><br>";
$buttons_div_html .= "\t\t\t\t<a href='woche-in.php?datum=".$datum."' class=no-print>[Bearbeiten]</a>\n";
$buttons_div_html .= "<br><br></div>";
$duty_roster_form_html .= $buttons_div_html;

$table_html = "\t\t\t\t<table id=duty-rooster-table>\n";
$head_table_html = "";
$head_table_html .= "\t\t\t\t\t<thead>\n";
$head_table_html .= "\t\t\t\t\t<tr>\n";
for ($i=0; $i<count($Dienstplan); $i++)
{//Datum
	$head_table_html .= "\t\t\t\t\t\t<td>";
	$head_table_html .= "<a href='tag-out.php?datum=".$Dienstplan[$i]["Datum"][0]."'>";
	$head_table_html .= strftime('%A', strtotime( $Dienstplan[$i]["Datum"][0]));
	$head_table_html .= " \n";
	$head_table_html .= "<input type=hidden size=2 name=Dienstplan[".$i."][Datum][0] value=".$Dienstplan[$i]["Datum"][0].">";
	$head_table_html .= "<input type=hidden name=mandant value=".$mandant.">";
	$head_table_html .= strftime('%d.%m.', strtotime($Dienstplan[$i]["Datum"][0]));
	$datum=($Dienstplan[$i]['Datum'][0]);
	require 'db-lesen-feiertag.php';
	if(isset($feiertag)){$head_table_html .= " <br>".$feiertag." ";}
	if (isset($feiertag) AND date('N', strtotime($datum))<6) {
		foreach ($Mandanten_mitarbeiter as $vk => $nachname) {
			if (!isset($bereinigte_Wochenstunden_Mitarbeiter[$vk])) {
					$bereinigte_Wochenstunden_Mitarbeiter[$vk] = $Stunden_mitarbeiter[$vk] - $Stunden_mitarbeiter[$vk] / 5;
			} else {
					$bereinigte_Wochenstunden_Mitarbeiter[$vk] = $bereinigte_Wochenstunden_Mitarbeiter[$vk] - $Stunden_mitarbeiter[$vk] / 5;
			}
		}
	}

	require 'db-lesen-notdienst.php';
	if(isset($notdienst)){$head_table_html .= "<br> <em>NOTDIENST</em> ";}
	$head_table_html .= "</a></td>\n";
}
$head_table_html .= "\t\t\t\t\t</tr></thead>";
$table_html .= $head_table_html;

$table_body_html  = "<tbody>";
$table_body_html .= schreiben_tabelle($Dienstplan);
if (isset($Overlay_message))
{
	$overlay_message_html .= "\t\t<div class='overlay no-print'>\n";
	$Overlay_message=array_unique($Overlay_message); 
	foreach($Overlay_message as $message)
	{
		$overlay_message_html .= "\t\t\t<H1>".$message."</H1>\n";
	}
	$overlay_message_html .= "\t\t</div>\n";
}
$table_html .= $table_body_html;
$datum=$konstantes_datum;
foreach ($Mandant as $filiale => $Name) {
	if ($mandant == $filiale) {
		continue 1;
	}
	$Filialplan[$filiale]=db_lesen_tage($tage, $filiale, '['.$mandant.']'); // Die Funktion schaut jetzt nach dem Arbeitsplan in der Helene.
	if (!empty(array_column($Filialplan[$filiale], 'VK'))) //array_column durchsucht alle Tage nach einem 'VK'.
	{
		$table_html .= "</tbody><tbody><tr><td colspan=$tage>".$Kurz_mandant[$mandant]." in ".$Kurz_mandant[$filiale]."</td></tr>";
		$table_body_html = schreiben_tabelle($Filialplan[$filiale]);
		$table_html .= $table_body_html;
	}
}

$table_html .= "\t\t\t\t\t</tbody>\n";
//echo "\t\t\t\t</div>\n";
$table_foot_html = "\t\t\t\t\t<tfoot>"
        //. "<tr class=page-break></tr>"
        . "\n\t\t\t\t\t\t<tr>\n";

//Wir werfen einen Blick in den Urlaubsplan und schauen, ob alle da sind.
for ($i=0; $i<count($Dienstplan); $i++)
{
	$datum=($Dienstplan[$i]['Datum'][0]);
	unset($Urlauber, $Kranke);
        list($Abwesende, $Urlauber, $Kranke)=db_lesen_abwesenheit($datum);
	require 'db-lesen-feiertag.php';
// TODO: I am not sure where to put the following line. There is an echo inside.
//	if (!isset($Dienstplan[$i]['VK'])) {echo "\t\t\t\t\t\t<td>"; continue;} //Tage an denen kein Dienstplan existiert werden nicht geprüft.
	if (isset($Abwesende)) {
		foreach ($Abwesende as $key => $vk) {
			if (!isset($feiertag) AND date('N', strtotime($datum))<6) {
					//An Feiertagen whaben wir die Stunden bereits abgezogen. Keine weiteren Abwesenheitsgründe notwendig.
					if (!isset($bereinigte_Wochenstunden_Mitarbeiter[$vk])) {
							$bereinigte_Wochenstunden_Mitarbeiter[$vk] = $Stunden_mitarbeiter[$vk] - $Stunden_mitarbeiter[$vk] / 5;
					} else {
							$bereinigte_Wochenstunden_Mitarbeiter[$vk] = $bereinigte_Wochenstunden_Mitarbeiter[$vk] - $Stunden_mitarbeiter[$vk] / 5;
					}
			}
		}
	}
	//Jetzt notieren wir die Urlauber und die Kranken Mitarbeiter unten in der Tabelle.
	if (isset($Urlauber))
	{
		$table_foot_html .= "\t\t\t\t\t<td><b>Urlaub</b><br>";
		foreach($Urlauber as $value){
			$table_foot_html .= "<a href='abwesenheit-out.php?datum=".$datum."&auswahl_mitarbeiter=".$value."'>".$Mitarbeiter[$value]."</a><br>";
		}
	}
	else
	{
		$table_foot_html .= "\t\t\t\t\t\t<td>";
	}
	if (isset($Kranke))
	{
		$table_foot_html .= "\t\t<br><b>Krank</b><br>";
		foreach($Kranke as $value){
			$table_foot_html .= "<a href='abwesenheit-out.php?datum=".$datum."&auswahl_mitarbeiter=".$value."'>".$Mitarbeiter[$value]."</a><br>";
		}
		$table_foot_html .= "</td>\n";
	}
	else
	{
		$table_foot_html .= "</td>\n";
	}
}
$table_foot_html .= "\t\t\t\t\t</tr>\n";
$table_foot_html .= "\t\t\t\t\t</tfoot>\n";

$table_html .= $table_foot_html;
$table_html .= "\t\t\t\t</table>\n\t\t\t"
        . "$weekly_rotation_div_html"
        . "</div>";

$table_div_html = "<div id=table_overlay_area>";
$table_div_html .= $overlay_message_html;
$table_div_html .= $table_html;


$duty_roster_form_html .= $table_div_html;

//Wir zeichnen jetzt die Wochenstunden der Mitarbeiter. In dieser Ansicht werden ausschließlich die Tage Montag bis Freitag betrachtet. Dies ist ein Unterschied zur Mitarbeiteransicht. Dort werden alle Wochentage berücksichtigt.
// TODO: $tag<5; should be a configurable variable. It might be 6 or seven in other pharmacies.
for ($tag=0; $tag<5; $tag++)
{
	if (!isset($Dienstplan[$tag]['Stunden'])) {continue;} //Tage an denen kein Dienstplan existiert werden nicht geprüft.
	foreach($Dienstplan[$tag]['Stunden'] as $key => $stunden)
	{
		$Stunden[$Dienstplan[$tag]['VK'][$key]][]=$stunden;
//		echo "$tag $mandant $key $stunden<br>\n";
	}
}
for ($tag=0; $tag<5; $tag++)
{
	foreach ($Mandant as $mandant_key => $value) //wir verwenden nicht die Variablen $filiale oder Mandant, weil wir diese jetzt nicht verändern wollen!
	{
		if ($mandant_key!=$mandant)
		{
			if (!isset($Filialplan[$mandant_key][$tag]['Stunden'])) {
				 continue 1;
			} //Tage an denen kein Dienstplan existiert werden nicht geprüft.
			foreach($Filialplan[$mandant_key][$tag]['Stunden'] as $key => $stunden)
			{
				$Stunden[$Filialplan[$mandant_key][$tag]['VK'][$key]][]=$stunden;
			}
		}
	}
}
//An leeren Wochen soll nicht gerechnet werden.
if (!empty(array_column($Dienstplan, 'VK')) AND isset($Stunden)) //array_column durchsucht alle Tage nach einem 'VK'.
{
        $week_hours_table_html = "\t\t\t\t<table>\n";
        $week_hours_table_html .= "\t\t\t\t\t<tr>\n";
        $week_hours_table_html .= "\t\t\t\t\t\t<td colspan=5>";
	$week_hours_table_html .= "<b>Wochenstunden</b>\n";
        $week_hours_table_html .= "\t\t\t\t\t\t</td>\n"
                . "\t\t\t\t\t<tr>\n";
	ksort($Stunden);
	$i=0;$j=1; //Zähler für den Stunden-Array (wir wollen nach je 5 Mitarbeitern einen Umbruch)
	foreach($Stunden as $mitarbeiter => $stunden)
	{
		if ( array_key_exists($mitarbeiter, $Mandanten_mitarbeiter)===false ){continue; /*Wir zeigen nur die Stunden von Mitarbeitern, die auch in den Mandanten gehören.*/}
		$i++; //Der Faktor gibt an, bei welcher VK-Nummer der Umbruch erfolgt.
		if($i>=$tage)
		{
			$week_hours_table_html .= "\t\t\t\t\t</tr><tr>\n";
			$i=0;//$j++;
		}
		$week_hours_table_html .= "\t\t\t\t\t\t<td>".$Mitarbeiter[$mitarbeiter]." ".array_sum($stunden);
		$week_hours_table_html .= " / ";
		if (isset($bereinigte_Wochenstunden_Mitarbeiter[$mitarbeiter])) {
				$week_hours_table_html .= round($bereinigte_Wochenstunden_Mitarbeiter[$mitarbeiter], 1)."\n";
		} else {
				$week_hours_table_html .= round($Stunden_mitarbeiter[$mitarbeiter], 1)."\n";
		}
		if (isset($bereinigte_Wochenstunden_Mitarbeiter[$mitarbeiter])) {
				if (round($bereinigte_Wochenstunden_Mitarbeiter[$mitarbeiter], 1) != round(array_sum($stunden), 1)) {
						$differenz = round(array_sum($stunden), 1) - round($bereinigte_Wochenstunden_Mitarbeiter[$mitarbeiter], 1);
						$week_hours_table_html .= " <b>( ".$differenz." )</b>\n";
					}
				} else {
						if (round($Stunden_mitarbeiter[$mitarbeiter], 1) != round(array_sum($stunden), 1)) {
								$differenz = round(array_sum($stunden), 1) - round($Stunden_mitarbeiter[$mitarbeiter], 1);
								$week_hours_table_html .= " <b>( ".$differenz." )</b>\n";
						}
				}

		$week_hours_table_html .= "\t\t\t\t\t\t</td>\n";
	}
        $week_hours_table_html .= "\t\t\t\t\t</tr>\n";
        $week_hours_table_html .= "\t\t\t\t</table>\n";
        $duty_roster_form_html .= $week_hours_table_html;
}
// echo $submit_button;
$duty_roster_form_html .= "\t\t\t</form>\n";
$main_div_html .= $duty_roster_form_html;

$main_div_html .= "</div>\n";

if (isset($Fehlermeldung))
{
	$error_message_html .= "\t\t<div class=errormsg>\n";
	$Fehlermeldung=array_unique($Fehlermeldung);
	foreach($Fehlermeldung as $fehler)
	{
		$error_message_html .= "\t\t\t<H1>".$fehler."</H1>\n";
	}
	$error_message_html .= "\t\t</div>";
}

if (isset($Warnmeldung))
{
	$warning_message_html .= "\t\t<div class=warningmsg>\n";
	$Warnmeldung=array_unique($Warnmeldung);
	foreach($Warnmeldung as $warnung)
	{
		$warning_message_html .= "\t\t\t<H2>".$warnung."</H2>\n";
	}
	$warning_message_html .= "\t\t</div>\n";
}


echo $error_message_html;
echo $warning_message_html;

echo $main_div_html;

require 'contact-form.php';





?>
	</BODY>
</HTML>
