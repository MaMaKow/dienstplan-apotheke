<?php

/*
 * This class enables the creation of iCalendar files.
 *
 * Currently it is meant to enable single employees to download their upcoming roster data.
 * This *.ical data can be imported into various calendaring applications.
 */

class iCalendar {
    /*
     * TODO: Enable the creation of an alert for lunch breaks.
     */

    /**

     *
     * @param array $Roster
     * @return string $textICS the ICS text file
     */
    public static function build_ics_roster_employee($Roster) {
        $textICS = "";
        $textICS .= "BEGIN:VCALENDAR\n";
        $textICS .= "VERSION:2.0\n";
        $textICS .= "PRODID:-//Dr. Martin Mandelkow/martin-mandelkow.de//Apotheke am Marienplatz//DE\n"; /* TODO: Place the main branch name here! */
        foreach ($Roster as $date_unix => $Roster_day_array) {
            /*
             * @var $same_employee_count array This array has the format array(employee_id => int).
             * It is used for the case, when a single employee has multiple duty start times.
             * e.g. Start at 8:00, leave at 10:00 for a doctors meeting, come back at 13:00 and leave at 17:00.
             * Without this the UID would not be unique. Therefore one VEVENT would overwrite the other in the calendaring application.
             */
            $same_employee_count = array();
            foreach ($Roster_day_array as $roster_object) {
                if (!isset($roster_object->employee_id)) {
                    //Ignore fields without data.
                    continue;
                }
                /*
                 * Processing the data:
                 */
                if (!isset($same_employee_count[$roster_object->employee_id])) {
                    $same_employee_count[$roster_object->employee_id] = 0;
                }
                $same_employee_count[$roster_object->employee_id] ++;
                /*
                 * Output the data in iCalendar format:
                 */
                $textICS .= iCalendar::build_ics_roster_employee_head($roster_object, $same_employee_count);
                $textICS .= iCalendar::build_ics_roster_employee_description($roster_object);
                $textICS .= "END:VEVENT\n";
            }
        }

        $textICS .= "END:VCALENDAR\n";

        return $textICS;
    }

    private static function build_ics_roster_employee_head($roster_object, $same_employee_count) {
        global $List_of_branch_objects, $config;
        $administrator_email = $config['contact_email']; /* This is the email of the roster administrator. It is not specific to the branch. */

        $date_unix = $roster_object->date_unix;
        $dienstbeginn = roster_item::format_time_integer_to_string($roster_object->duty_start_int, 'His');
        $dienstende = roster_item::format_time_integer_to_string($roster_object->duty_end_int, 'His');

        $branch_id = $roster_object->branch_id;
        $branch_name = $List_of_branch_objects[$branch_id]->name;
        $branch_address = $List_of_branch_objects[$branch_id]->address;
        $branch_manager = $List_of_branch_objects[$branch_id]->manager;

        $textICS = '';
        $textICS .= "BEGIN:VEVENT\n";
        $textICS .= "METHOD:REQUEST\n";
        $textICS .= "UID:" . $date_unix . "-" . $roster_object->employee_id . "-" . $branch_id . "-" . $same_employee_count[$roster_object->employee_id] . "@martin-mandelkow.de\n";
        $textICS .= "DTSTAMP:" . gmdate('YmdHis\Z') . "\n";
        $textICS .= "LAST-MODIFIED:" . gmdate('YmdHis\Z') . "\n";
        $textICS .= "ORGANIZER;CN=$branch_manager:MAILTO:$administrator_email\n";
        $textICS .= "DTSTART;TZID=Europe/Berlin:" . date('Ymd', $date_unix) . "T" . $dienstbeginn . "\n";
        $textICS .= "DTEND;TZID=Europe/Berlin:" . date('Ymd', $date_unix) . "T" . $dienstende . "\n";
        $textICS .= "SUMMARY:$branch_name\n";
        $textICS .= "LOCATION:$branch_address\n";
        return $textICS;
    }

    /*
     * @param $roster_object object An object of the class roster_item
     * @global object $workforce
     */

    private static function build_ics_roster_employee_description($roster_object) {
        global $List_of_branch_objects, $workforce;
        $mittags_beginn = $roster_object->break_start_sql;
        $mittags_ende = $roster_object->break_end_sql;
        $date_unix = $roster_object->date_unix;
        $branch_id = $roster_object->branch_id;
        $branch_name = $List_of_branch_objects[$branch_id]->name;
        $date_weekday_name = strftime('%A', $date_unix);

        /*
         *  New lines have to be escaped via \\n
         */
        $textICS = '';
        $textICS .= "DESCRIPTION:"
                . gettext("Calendar file for employee ") . " " . $roster_object->employee_id . " (" . $workforce->List_of_employees[$roster_object->employee_id]->full_name . ") \\n"
                . gettext("contains the roster for") . " $branch_name. \\n"
                . gettext("Weekday") . ": $date_weekday_name\\n";
        if (!empty($mittags_beginn) and ! empty($mittags_ende)) {
            $textICS .= "Mittag von $mittags_beginn bis $mittags_ende \\n";
        }
        $textICS .= "\n";
        return $textICS;
    }

}
