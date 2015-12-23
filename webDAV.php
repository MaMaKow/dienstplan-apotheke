<?php
//Wir erstellen eine umfassende Icalendar Datei (ICS). Diese kann dann von Kalenderprogrammen aboniert werden.
function schreiben_ics ($Dienstplan) 
{
$textICS="";
$textICS.="BEGIN:VCALENDAR\n";
$textICS.="VERSION:2.0\n";
$textICS.="VERSION:2.0\n";
$textICS.="PRODID:-//Dr. Martin Mandelkow/martin-mandelkow.de//Apotheke am Marienplatz//DE\n";
foreach(array_keys($Dienstplan) as $tag ) 
{
	$datum=$Dienstplan[$tag]["Datum"][0];
	foreach($Dienstplan[$tag]['VK'] as $key => $vk)
	{
		if ( !empty($vk) ) //Wir ignorieren die nicht ausgefüllten Felder
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

header("Content-type:text/calendar;charset=utf-8");
header("Content-Disposition:inline;filename=calendar.ics"); //Dies teilt dem "Browser" mit, dass er die Datei selbst ohne externes Programm öffnen soll. Alternative zu inline wäre attachment
echo "$textICS";
//$filename = "ics/wochenkalender_".strftime('%V', strtotime($datum))."_".$vk.".ics"; //Die Datei bekommt den Namen der Kalenderwoche und des Mitarbeiters.
//$myfile = fopen($filename, "w") or die("Unable to open file!");
//fwrite($myfile, $textICS);
//fclose($myfile);
//$textICS="";
//echo "<button type=button onclick=location='$filename'>Download ics Kalender Datei</button>";

}
?>
