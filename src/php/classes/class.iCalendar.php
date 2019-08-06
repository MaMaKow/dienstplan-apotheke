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
    /*
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
    public static function build_ics_roster_employee($Roster, $create_valarm = self::VALARM_NONE) {

        $text_ics = "";
        $text_ics .= "BEGIN:VCALENDAR\r\n";
        $text_ics .= "VERSION:2.0\r\n";
        $text_ics .= "PRODID:-//MaMaKow/martin-mandelkow.de//PDR//DE\r\n";
        foreach ($Roster as $Roster_day_array) {
            /**
             * @var $same_employee_count array <p>This array has the format array(employee_id => int).<br>
             * It is used for the case, when a single employee has multiple duty start times.<br>
             * e.g. Start at 8:00, leave at 10:00 for a doctors meeting, come back at 13:00 and leave at 17:00.<br>
             * Without this the UID would not be unique. Therefore one VEVENT would overwrite the other in the calendaring application.</p>
             */
            $same_employee_count = array();
            foreach ($Roster_day_array as $roster_object) {
                if (!isset($roster_object->employee_id)) {
                    /*
                     * Ignore fields without data:
                     */
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
                $text_ics .= "BEGIN:VEVENT\r\n";
                $text_ics .= iCalendar::build_ics_roster_employee_head($roster_object, $same_employee_count);
                $text_ics .= iCalendar::build_ics_roster_employee_description($roster_object);
                $text_ics .= iCalendar::build_ics_roster_employee_valarms($roster_object, $create_valarm);
                $text_ics .= "END:VEVENT\r\n";
            }
        }

        $text_ics .= "END:VCALENDAR\r\n";

        return $text_ics;
    }

    private static function build_ics_roster_employee_head($roster_object, $same_employee_count) {
        global $config;

        $administrator_email = $config['contact_email']; /* This is the email of the roster administrator. It is not specific to the branch. */

        $date_unix = $roster_object->date_unix;
        /*
         * duty_start and duty_end are strings representing the UTC time of the given time
         */
        $duty_start_string = self::time_int_to_utc_string($roster_object->duty_start_int);
        $duty_end_string = self::time_int_to_utc_string($roster_object->duty_end_int);

        $branch_id = $roster_object->branch_id;
        $List_of_branch_objects = branch::get_list_of_branch_objects();
        $branch_name = $List_of_branch_objects[$branch_id]->name;
        $branch_address = $List_of_branch_objects[$branch_id]->address;
        $branch_manager = $List_of_branch_objects[$branch_id]->manager;

        $text_ics = '';
        $text_ics .= "METHOD:REQUEST\r\n";
        $text_ics .= "UID:" . $date_unix . "-" . $roster_object->employee_id . "-" . $branch_id . "-" . $same_employee_count[$roster_object->employee_id] . "@martin-mandelkow.de\r\n";
        $text_ics .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
        $text_ics .= "LAST-MODIFIED:" . gmdate('Ymd\THis\Z') . "\r\n";
        $text_ics .= "ORGANIZER;CN=$branch_manager:MAILTO:$administrator_email\r\n";
        /*
          $text_ics .= "DTSTART;TZID=Europe/Berlin:" . date('Ymd', $date_unix) . "T" . $dienstbeginn . "\r\n";
          $text_ics .= "DTEND;TZID=Europe/Berlin:" . date('Ymd', $date_unix) . "T" . $dienstende . "\r\n";
         */
        $text_ics .= "DTSTART:" . date('Ymd', $date_unix) . 'T' . $duty_start_string . "\r\n";
        $text_ics .= "DTEND:" . date('Ymd', $date_unix) . 'T' . $duty_end_string . "\r\n";
        $text_ics .= "SUMMARY:$branch_name" . "\r\n";
        $text_ics .= "LOCATION:$branch_address" . "\r\n";
        return $text_ics;
    }

    /**
     * @param $roster_object object An object of the class roster_item
     */
    private static function build_ics_roster_employee_description($roster_object) {
        $mittags_beginn = $roster_object->break_start_sql;
        $mittags_ende = $roster_object->break_end_sql;
        $date_unix = $roster_object->date_unix;
        $workforce = new workforce($roster_object->date_sql);
        $branch_id = $roster_object->branch_id;
        $List_of_branch_objects = \branch::get_list_of_branch_objects();
        $branch_name = $List_of_branch_objects[$branch_id]->name;
        $date_weekday_name = strftime('%A', $date_unix);

        /*
         * New lines have to be escaped via \\r\\n
         */
        $text_ics = '';
        $text_ics .= "DESCRIPTION:"
                . gettext("Calendar file for employee ") . " " . $roster_object->employee_id . " (" . $workforce->List_of_employees[$roster_object->employee_id]->full_name . ") \\r\\n"
                . gettext("contains the roster for") . " $branch_name. \\r\\n"
                . gettext("Weekday") . ": $date_weekday_name\\r\\n";
        if (!empty($mittags_beginn) and ! empty($mittags_ende)) {
            $text_ics .= sprintf(gettext('Lunch from %1s to %2s'), $mittags_beginn, $mittags_ende) . "\\r\\n";
        }
        $text_ics .= "\r\n";
        /*
         * RFC 5545 3.1. Content Lines
         * Lines of text SHOULD NOT be longer than 75 octets, excluding the line break.
         * Long content lines SHOULD be split into a multiple line representations using a line "folding" technique.
         * That is, a long line can be split between any two characters by inserting a CRLF immediately followed by
         *  a single linear white-space character (i.e., SPACE or HTAB).
         * Any sequence of CRLF followed immediately by a single linear white-space character is ignored (i.e., removed)
         *  when processing the content type.
         */
        $Array_ICS = str_split($text_ics, 70);
        return implode("\r\n ", $Array_ICS);
    }

    private static function build_ics_roster_employee_valarms($roster_object, $create_valarm) {
        if (0 == $create_valarm) {
            return NULL;
        }
        $text_ics = "";
        if ($create_valarm & self::VALARM_FOR_DUTY_START and NULL !== $roster_object->duty_start_int) {
            $seconds_before_duty = 30 * 60;
            $description = gettext('Time to go to work');
            $trigger_time_string = self::time_int_to_utc_string($roster_object->duty_start_int - $seconds_before_duty);
            $trigger_date_time = date('Ymd', $roster_object->date_unix) . "T" . $trigger_time_string;
            $text_ics .= self::build_ics_roster_employee_valarm($trigger_date_time, $description);
        }
        if ($create_valarm & self::VALARM_FOR_DUTY_END and NULL !== $roster_object->duty_end_int) {
            $description = gettext('Time to leave');
            $trigger_time_string = self::time_int_to_utc_string($roster_object->duty_end_int);
            $trigger_date_time = date('Ymd', $roster_object->date_unix) . "T" . $trigger_time_string;
            $text_ics .= self::build_ics_roster_employee_valarm($trigger_date_time, $description);
        }
        if ($create_valarm & self::VALARM_FOR_BREAK_START and NULL !== $roster_object->break_start_int) {
            $description = gettext('Time for lunch break');
            $trigger_time_string = self::time_int_to_utc_string($roster_object->break_start_int);
            $trigger_date_time = date('Ymd', $roster_object->date_unix) . "T" . $trigger_time_string;
            $text_ics .= self::build_ics_roster_employee_valarm($trigger_date_time, $description);
        }
        if ($create_valarm & self::VALARM_FOR_BREAK_END and NULL !== $roster_object->break_end_int) {
            $description = gettext('Lunch break ends now');
            $trigger_time_string = self::time_int_to_utc_string($roster_object->break_end_int);
            $trigger_date_time = date('Ymd', $roster_object->date_unix) . "T" . $trigger_time_string;
            $text_ics .= self::build_ics_roster_employee_valarm($trigger_date_time, $description);
        }
        return $text_ics;
    }

    private static function build_ics_roster_employee_valarm($trigger_date_time, $description) {
        $text_ics = "";
        $text_ics .= "BEGIN:VALARM\r\n";
        $text_ics .= "TRIGGER;VALUE=DATE-TIME:$trigger_date_time\r\n";
        $text_ics .= "ACTION:DISPLAY\r\n";
        $text_ics .= "DESCRIPTION:$description\r\n";
        $text_ics .= "END:VALARM\r\n";
        return $text_ics;
    }

    private static function time_int_to_utc_string($time_int) {
        $timezone_offset_in_seconds = date('Z');
        $time_int_utc = $time_int - $timezone_offset_in_seconds;
        $time_string_utc = gmdate('His\Z', $time_int_utc);
        return $time_string_utc;
    }

    public static function build_ics_roster_cancelled($roster_item_object) {
        /**
         * @var int $same_employee_count is part of the UID of each VEVENT.
         * @todo <p>
         * If we really want to work with iCalendar, then a real unique identifier might be a totally good thing to have.
         * Perhaps it could be a new primary key on the roster table.
         * Also entries would not be deleted, but rather marked as CANCELLED.
          </p>
         */
        $same_employee_count = 0;
        $text_ics = "";
        $text_ics .= "BEGIN:VCALENDAR\r\n";
        $text_ics .= "VERSION:2.0\r\n";
        $text_ics .= "PRODID:-//MaMaKow/martin-mandelkow.de//PDR//DE\r\n";
        $text_ics .= "BEGIN:VEVENT\r\n";
        $text_ics .= "STATUS:CANCELLED\r\n";
        $text_ics .= iCalendar::build_ics_roster_employee_head($roster_item_object, $same_employee_count);
        $text_ics .= iCalendar::build_ics_roster_employee_description($roster_item_object);
        $text_ics .= "END:VEVENT\r\n";
        $text_ics .= "END:VCALENDAR\r\n";
        return $text_ics;
    }

}
