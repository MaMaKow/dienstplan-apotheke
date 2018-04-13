<?php

//Wir erstellen eine Icalendar Datei (ICS). Diese kann dann in einen Kalender importiert werden.
/**
 *
 * @global array $List_of_employees
 * @param array $Dienstplan
 * @return string $textICS the ICS text file
 */
function schreiben_ics($Dienstplan) {
    global $List_of_employees, $List_of_branch_objects, $config;
    $administrator_email = $config['contact_email']; /* This is the email of the roster administrator. It is not specific to the branch. */
    $textICS = "";
    $textICS .= "BEGIN:VCALENDAR\n";
    $textICS .= "VERSION:2.0\n";
    $textICS .= "PRODID:-//Dr. Martin Mandelkow/martin-mandelkow.de//Apotheke am Marienplatz//DE\n";
//loop through the seven days of the week (might be less then seven)
    foreach (array_keys($Dienstplan) as $tag) {
        //Mostly this will be only one. But there can be more.

        $datum = $Dienstplan[$tag]["Datum"][0];
        $date_weekday_name = strftime('%A', strtotime($datum));
        $same_employee_count = array();
        //Loop through the working times.
        foreach ($Dienstplan[$tag]['VK'] as $key => $vk) {
            //Ignore fields without data.
            if (!empty($vk) and $Dienstplan[$tag]["Dienstbeginn"][$key] != '-') {
                //Processing the data
                if (!isset($same_employee_count[$vk])) {
                    $same_employee_count[$vk] = 0;
                }
                $same_employee_count[$vk] ++;
                $dienstbeginn = $Dienstplan[$tag]["Dienstbeginn"][$key];
                $dienstende = $Dienstplan[$tag]["Dienstende"][$key];
                $mittags_beginn = $Dienstplan[$tag]["Mittagsbeginn"][$key];
                $mittags_ende = $Dienstplan[$tag]["Mittagsende"][$key];
                $branch_id = $Dienstplan[$tag]["Mandant"][$key];
                $branch_name = $List_of_branch_objects[$branch_id]->name;
                $branch_address = $List_of_branch_objects[$branch_id]->address;
                $branch_manager = $List_of_branch_objects[$branch_id]->manager;
                //Output the data as ICS
                $textICS .= "BEGIN:VEVENT\n";
                $textICS .= "METHOD:REQUEST\n";
                $textICS .= "UID:$datum-$vk-$branch_id-$same_employee_count[$vk]@martin-mandelkow.de\n";
                $textICS .= "DTSTAMP:" . gmdate('YmdHis\Z') . "\n";
                $textICS .= "LAST-MODIFIED:" . gmdate('YmdHis\Z') . "\n";
                $textICS .= "ORGANIZER;CN=$branch_manager:MAILTO:$administrator_email\n";
                $textICS .= "DTSTART;TZID=Europe/Berlin:" . date('Ymd', strtotime($datum)) . "T" . date('His', strtotime($dienstbeginn)) . "\n";
                $textICS .= "DTEND;TZID=Europe/Berlin:" . date('Ymd', strtotime($datum)) . "T" . date('His', strtotime($dienstende)) . "\n";
                $textICS .= "SUMMARY:$branch_name\n";
                /*
                 * Start of description:
                 * New lines have to be escaped via \\n
                 */
                $textICS .= "DESCRIPTION:"
                        . gettext("Calendar file for employee ") . " " . $vk . " (" . $List_of_employees[$vk] . ") \\n"
                        . gettext("contains the roster for") . " $branch_name. \\n"
                        . gettext("Weekday") . ": $date_weekday_name\\n";
                if (!empty($mittags_beginn) and ! empty($mittags_ende)) {
                    $textICS .= "Mittag von $mittags_beginn bis $mittags_ende \\n";
                }
                $textICS .= "\n";
                /*
                 * End of description
                 */
                $textICS .= "LOCATION:$branch_address\n";
                $textICS .= "END:VEVENT\n";
            }
        }
    }
    $textICS .= "END:VCALENDAR\n";

    return $textICS;
}
