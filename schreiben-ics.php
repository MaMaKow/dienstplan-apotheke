<?php
//Wir erstellen eine Icalendar Datei (ICS). Diese kann dann in einen Kalender importiert werden.
function schreiben_ics ($Dienstplan) 
{
global $Mitarbeiter;
$textICS="";
$textICS.="BEGIN:VCALENDAR\n";
$textICS.="VERSION:2.0\n";
$textICS.="PRODID:-//Dr. Martin Mandelkow/martin-mandelkow.de//Apotheke am Marienplatz//DE\n";
foreach(array_keys($Dienstplan) as $tag ) 
{
	$datum=$Dienstplan[$tag]["Datum"][0];
	foreach($Dienstplan[$tag]['VK'] as $key => $vk)
	{
		if ( !empty($vk) and $Dienstplan[$tag]["Dienstbeginn"][$key]!='-') //Wir ignorieren die nicht ausgefüllten Felder
		{
			//Verarbeiten der Daten
			$dienstbeginn=$Dienstplan[$tag]["Dienstbeginn"][$key];
			$dienstende=$Dienstplan[$tag]["Dienstende"][$key];
			//Ausgabe der Daten
			$textICS.="BEGIN:VEVENT\n";
			$textICS.="UID:$datum-$vk@martin-mandelkow.de\n";
			$textICS.="DTSTAMP:".gmdate('Ymd\THis\Z')."\n";
			$textICS.="LAST-MODIFIED:".gmdate('Ymd\THis\Z')."\n";
			$textICS.="ORGANIZER;CN=Dr. Martin Mandelkow:MAILTO:dienstplan@martin-mandelkow.de\n";
			$textICS.="DTSTART;TZID=Europe/Berlin:".date('Ymd', strtotime($datum))."T".date('His', strtotime($dienstbeginn))."\n";
			$textICS.="DTEND;TZID=Europe/Berlin:".date('Ymd', strtotime($datum))."T".gmdate('His', strtotime($dienstende))."\n";
			$textICS.="SUMMARY:Apotheke am Marienplatz\n";
			$textICS.="DESCRIPTION:Kalenderdatei für VK ".$vk." (".$Mitarbeiter[$vk].") beinhaltet den Dienstplan für die Apotheke am Marienplatz.\n";
			$textICS.="END:VEVENT\n";
		}
	}
}
$textICS.="END:VCALENDAR";


$filename = "ics/wochenkalender_".strftime('%V', strtotime($datum))."_".$vk.".ics"; //Die Datei bekommt den Namen der Kalenderwoche und des Mitarbeiters.
$myfile = fopen($filename, "w") or die("Unable to open file!");
fwrite($myfile, $textICS);
fclose($myfile);
$textICS="";
echo "<button type=button class=no-print onclick=location='$filename'>Download ics Kalender Datei</button>\n";
//echo "<pre>\n"; var_dump($Dienstplan); echo "</pre>";
}
?>
