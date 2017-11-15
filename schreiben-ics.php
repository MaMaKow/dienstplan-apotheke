<?php
//Wir erstellen eine Icalendar Datei (ICS). Diese kann dann in einen Kalender importiert werden.
/**
 * 
 * @global array $List_of_employees 
 * @param array $Dienstplan
 * @return string $textICS the ICS text file
 */
function schreiben_ics ($Dienstplan)
{
global $List_of_employees, $Branch_name;
$textICS="";
$textICS.="BEGIN:VCALENDAR\n";
$textICS.="VERSION:2.0\n";
$textICS.="PRODID:-//Dr. Martin Mandelkow/martin-mandelkow.de//Apotheke am Marienplatz//DE\n";
//loop through the seven days of the week (might be less then seven)
foreach(array_keys($Dienstplan) as $tag )
{
        //Mostly this will be only one. But there can be more.

        $datum = $Dienstplan[$tag]["Datum"][0];
        $same_employee_count = array();
        //Loop through the working times.
        foreach($Dienstplan[$tag]['VK'] as $key => $vk) 
	{
                //Ignore fields without data.
		if ( !empty($vk) and $Dienstplan[$tag]["Dienstbeginn"][$key]!='-') 
		{
			//Processing the data
                        $same_employee_count[$vk]++;
			$dienstbeginn=$Dienstplan[$tag]["Dienstbeginn"][$key];
			$dienstende=$Dienstplan[$tag]["Dienstende"][$key];
			$mittags_beginn=$Dienstplan[$tag]["Mittagsbeginn"][$key];
			$mittags_ende=$Dienstplan[$tag]["Mittagsende"][$key];
                        $mandant=$Branch_name[$Dienstplan[$tag]["Mandant"][$key]];
                        $branch_address=$Branch_address[$Dienstplan[$tag]["Mandant"][$key]];
                        $mandant_number=$Dienstplan[$tag]["Mandant"][$key];
			//Output the data as ICS
			$textICS.="BEGIN:VEVENT\n";
			$textICS.="METHOD:REQUEST\n";
			$textICS.="UID:$datum-$vk-$mandant_number-$same_employee_count[$vk]@martin-mandelkow.de\n";
			$textICS.="DTSTAMP:".gmdate('Ymd\THis\Z')."\n";
			$textICS.="LAST-MODIFIED:".gmdate('Ymd\THis\Z')."\n";
			$textICS.="ORGANIZER;CN=Dr. Martin Mandelkow:MAILTO:dienstplan@martin-mandelkow.de\n";
			$textICS.="DTSTART;TZID=Europe/Berlin:".date('Ymd', strtotime($datum))."T".date('His', strtotime($dienstbeginn))."\n";
			$textICS.="DTEND;TZID=Europe/Berlin:".date('Ymd', strtotime($datum))."T".date('His', strtotime($dienstende))."\n";
			$textICS.="SUMMARY:$mandant\n";
			$textICS.="DESCRIPTION:Kalenderdatei für VK ".$vk." (".$List_of_employees[$vk].") ";
                        if (!empty($mittags_beginn) and !empty($mittags_ende)) 
                        {
                            $textICS.="Mittag von $mittags_beginn bis $mittags_ende ";
                        }
                        $textICS.="beinhaltet den Dienstplan für die $mandant.\n";
			$textICS.="LOCATION:$branch_address\n";
			$textICS.="END:VEVENT\n";
		}
	}
}
$textICS.="END:VCALENDAR\n";

    return $textICS;
}
