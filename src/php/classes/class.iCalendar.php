<?php

/*
 * Copyright (C) 2017 Mandelkow
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/*
 * This class enables the creation of iCalendar files.
 *
 * Currently it is meant to enable single employees to download their upcoming roster data.
 * This *.ical data can be imported into various calendaring applications.
 */

class iCalendar {

    /**
     * TODO: Enable the creation of an alert for lunch breaks.
     */
    const VALARM_NONE = 0;
    const VALARM_FOR_DUTY_START = 1;
    const VALARM_FOR_DUTY_END = 2;
    const VALARM_FOR_BREAK_START = 4;
    const VALARM_FOR_BREAK_END = 8;

    /**
     *
     * @param array $Roster
     * @return string $text_ics the ICS text file
     */
    public static function build_ics_roster_employee(array $Roster, $create_valarm = self::VALARM_NONE) {
        /**
         * @var $tzid Define the timezone
         * timezone must be a supported PHP timezone
         * (see http://php.net/manual/en/timezones.php )
         * Note: multi-word timezones must use underscore "_" separator
         * @todo Make timezone a configuration variable!
         */
        $tzid = "Europe/Berlin";
        $sqlDateFormat = "Y-m-d H:i:s";
        $icalDateFormat = "Ymd\THis";
// create the ical object
        require_once PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/3rdparty/icalendar/zapcallib.php';
        $icalobj = new ZCiCal();

        $dateTimeZone = new DateTimeZone($tzid);
        $firstDateInRoster = new DateTime('@' . min(array_keys($Roster)), $dateTimeZone);
        $lastDateInRoster = new DateTime('@' . max(array_keys($Roster)), $dateTimeZone);
        /**
         * Add timezone data to $icalobj:
         */
        ZCTimeZoneHelper::getTZNode($firstDateInRoster->format("Y"), $lastDateInRoster->format("Y"), $tzid, $icalobj->curnode);

        global $config;
        $administrator_email = $config['contact_email']; /* This is the email of the roster administrator. It is not specific to the branch. */
        $network_of_branch_offices = new \PDR\Pharmacy\NetworkOfBranchOffices;
        $List_of_branch_objects = $network_of_branch_offices->get_list_of_branch_objects();
        foreach ($Roster as $Roster_day_array) {
            /**
             * @var $same_employee_count array <p>This array has the format array(employee_key => int).<br>
             * It is used for the case, when a single employee has multiple duty start times.<br>
             * e.g. Start at 8:00, leave at 10:00 for a doctors meeting, come back at 13:00 and leave at 17:00.<br>
             * Without this the UID would not be unique. Therefore one VEVENT would overwrite the other in the calendaring application.</p>
             */
            $same_employee_count = array();
            foreach ($Roster_day_array as $roster_object) {
                if (!isset($roster_object->employee_key)) {
                    /*
                     * Ignore fields without data:
                     */
                    continue;
                }
                /*
                 * Processing the data:
                 */
                if (!isset($same_employee_count[$roster_object->employee_key])) {
                    $same_employee_count[$roster_object->employee_key] = 0;
                }
                $same_employee_count[$roster_object->employee_key]++;
                /*
                 * Output the data in iCalendar format:
                 */
                // create the event within the ical object
                $eventobj = new ZCiCalNode("VEVENT", $icalobj->curnode);
                $date_unix = $roster_object->date_unix;
                $branch_id = $roster_object->branch_id;
                $network_of_branch_offices = new \PDR\Pharmacy\NetworkOfBranchOffices;
                $List_of_branch_objects = $network_of_branch_offices->get_list_of_branch_objects();
                $branch_name = $List_of_branch_objects[$branch_id]->name;
                $branch_address = $List_of_branch_objects[$branch_id]->address;
                $branch_manager = $List_of_branch_objects[$branch_id]->manager;
                /**
                 * add title:
                 */
                $title = $branch_name;
                $eventobj->addNode(new ZCiCalDataNode("SUMMARY:" . $title));
                /**
                 * add start date
                 *
                 */
                $eventobj->addNode(new ZCiCalDataNode("DTSTART;TZID=" . $tzid . ":" . $roster_object->dutyStartDateTime->format($icalDateFormat)));
                $eventobj->addNode(new ZCiCalDataNode("DTEND;TZID=" . $tzid . ":" . $roster_object->dutyEndDateTime->format($icalDateFormat)));
                /**
                 *  UID is a required item in VEVENT, create unique string for this event
                 * Adding your domain to the end is a good way of creating uniqueness
                 */
                $eventobj->addNode(new ZCiCalDataNode("UID:" . $date_unix . "-" . $roster_object->employee_key . "-" . $branch_id . "-" . $same_employee_count[$roster_object->employee_key] . "@martin-mandelkow.de"));
                /**
                 *  DTSTAMP is a required item in VEVENT
                 */
                $now = time();
                $eventobj->addNode(new ZCiCalDataNode("DTSTAMP:" . ZDateHelper::fromUnixDateTimetoiCal($now)));
                $eventobj->addNode(new ZCiCalDataNode("LAST-MODIFIED:" . ZDateHelper::fromUnixDateTimetoiCal($now)));
                $eventobj->addNode(new ZCiCalDataNode("ORGANIZER;CN=$branch_manager:MAILTO:$administrator_email"));
                $eventobj->addNode(new ZCiCalDataNode("LOCATION:" . $branch_address));
                $eventobj->addNode(new ZCiCalDataNode("DESCRIPTION:" . self::build_simple_roster_employee_description($roster_object)));

                iCalendar::build_ics_roster_employee_valarms($roster_object, $create_valarm, $eventobj);
            }
        }

        return $icalobj->export();
    }

    private static function build_simple_roster_employee_description($roster_object) {
        $mittags_beginn = $roster_object->break_start_sql;
        $mittags_ende = $roster_object->break_end_sql;
        $date_unix = $roster_object->date_unix;
        $workforce = new workforce($roster_object->date_sql);
        $branch_id = $roster_object->branch_id;
        $network_of_branch_offices = new \PDR\Pharmacy\NetworkOfBranchOffices();
        $List_of_branch_objects = $network_of_branch_offices->get_list_of_branch_objects();
        $branch_name = $List_of_branch_objects[$branch_id]->name;

        $configuration = new \PDR\Application\configuration();
        $locale = $configuration->getLanguage(); // e.g. de-DE
        $formatter = new IntlDateFormatter($locale, IntlDateFormatter::FULL, IntlDateFormatter::NONE);
        $formatter->setPattern('EEEE'); // 'EEEE' represents the full weekday name
        $date_weekday_name = $formatter->format($date_unix);

        $text = '';
        $text .= "DESCRIPTION:"
                . gettext("Calendar file for employee") . " " . $roster_object->employee_key . " (" . $workforce->List_of_employees[$roster_object->employee_key]->full_name . ") \\r\\n"
                . gettext("contains the roster for") . " $branch_name. \n"
                . gettext("Weekday") . ": $date_weekday_name\n";
        if (!empty($mittags_beginn) and !empty($mittags_ende)) {
            $text .= sprintf(gettext('Lunch from %1$s to %2$s'), $mittags_beginn, $mittags_ende) . "\n";
        }
        $text .= "\n";
        return $text;
    }

    private static function build_ics_roster_employee_valarms($roster_object, $create_valarm, $event_object) {
        if (0 == $create_valarm) {
            return NULL;
        }
        if ($create_valarm & self::VALARM_FOR_DUTY_START and NULL !== $roster_object->duty_start_int) {
            $seconds_before_duty = 30 * 60;
            $description = gettext('Time to go to work');
            $trigger_time_string = self::time_int_to_utc_string($roster_object->duty_start_int - $seconds_before_duty);
            $trigger_date_time = date('Ymd', $roster_object->date_unix) . "T" . $trigger_time_string;
            self::build_ics_roster_employee_valarm($trigger_date_time, $description, $event_object);
        }
        if ($create_valarm & self::VALARM_FOR_DUTY_END and NULL !== $roster_object->duty_end_int) {
            $description = gettext('Time to leave');
            $trigger_time_string = self::time_int_to_utc_string($roster_object->duty_end_int);
            $trigger_date_time = date('Ymd', $roster_object->date_unix) . "T" . $trigger_time_string;
            self::build_ics_roster_employee_valarm($trigger_date_time, $description, $event_object);
        }
        if ($create_valarm & self::VALARM_FOR_BREAK_START and NULL !== $roster_object->break_start_int) {
            $description = gettext('Time for lunch break.');
            $trigger_time_string = self::time_int_to_utc_string($roster_object->break_start_int);
            $trigger_date_time = date('Ymd', $roster_object->date_unix) . "T" . $trigger_time_string;
            self::build_ics_roster_employee_valarm($trigger_date_time, $description, $event_object);
        }
        if ($create_valarm & self::VALARM_FOR_BREAK_END and NULL !== $roster_object->break_end_int) {
            $description = gettext('The Lunch break ends now.');
            $trigger_time_string = self::time_int_to_utc_string($roster_object->break_end_int);
            $trigger_date_time = date('Ymd', $roster_object->date_unix) . "T" . $trigger_time_string;
            self::build_ics_roster_employee_valarm($trigger_date_time, $description, $event_object);
        }
    }

    private static function build_ics_roster_employee_valarm($trigger_date_time, $description, $eventObject) {
        $alarmObject = new ZCiCalNode("VALARM", $eventObject);
        $alarmObject->addNode(new ZCiCalDataNode("TRIGGER;VALUE=DATE-TIME:$trigger_date_time"));
        $alarmObject->addNode(new ZCiCalDataNode("ACTION:DISPLAY"));
        $alarmObject->addNode(new ZCiCalDataNode("DESCRIPTION:$description"));
    }

    private static function time_int_to_utc_string($time_int) {
        $timezone_offset_in_seconds = date('Z');
        $time_int_utc = $time_int - $timezone_offset_in_seconds;
        $time_string_utc = gmdate('His\Z', $time_int_utc);
        return $time_string_utc;
    }

    /**
     *
     * @return string
     * @deprecated since version 0.15.0 This function needs to be removed when build_ics_roster_cancelled() is rewritten.
     */
    private static function getVTimeZoneBerlin() {


        $vTimeZoneString = "";
        $vTimeZoneString .= "BEGIN:VTIMEZONE\r\n";
        $vTimeZoneString .= "TZID:Europe/Berlin\r\n";
        $vTimeZoneString .= "X-LIC-LOCATION:Europe/Berlin\r\n";
        $vTimeZoneString .= "BEGIN:DAYLIGHT\r\n";
        $vTimeZoneString .= "TZOFFSETFROM:+0100\r\n";
        $vTimeZoneString .= "TZOFFSETTO:+0200\r\n";
        $vTimeZoneString .= "TZNAME:CEST\r\n";
        $vTimeZoneString .= "DTSTART:19700329T020000\r\n";
        $vTimeZoneString .= "RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=3\r\n";
        $vTimeZoneString .= "END:DAYLIGHT\r\n";
        $vTimeZoneString .= "BEGIN:STANDARD\r\n";
        $vTimeZoneString .= "TZOFFSETFROM:+0200\r\n";
        $vTimeZoneString .= "TZOFFSETTO:+0100\r\n";
        $vTimeZoneString .= "TZNAME:CET\r\n";
        $vTimeZoneString .= "DTSTART:19701025T030000\r\n";
        $vTimeZoneString .= "RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10\r\n";
        $vTimeZoneString .= "END:STANDARD\r\n";
        $vTimeZoneString .= "END:VTIMEZONE\r\n";
        return $vTimeZoneString;
    }
}
