<?php

/**
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

namespace PDR\Output\ICalendar;

class ICalendar {

    // Alarm flags
    const VALARM_NONE = 0;
    const VALARM_FOR_DUTY_START = 1;
    const VALARM_FOR_DUTY_END = 2;
    const VALARM_FOR_BREAK_START = 4;
    const VALARM_FOR_BREAK_END = 8;

    /**
     * Build an iCalendar string for an employee’s roster.
     *
     * @param array $Roster         An array of roster data.
     * @param int   $createValarm  Bitmask to determine if and which alarms to create.
     * @return string  The ICS content
     */
    public static function buildIcsRosterEmployee(array $Roster, $createValarm = self::VALARM_NONE) {
        $configuration = new \PDR\Application\configuration();
        $tzid = $configuration->getTimezone();
        $dateTimeZone = new \DateTimeZone($tzid);
        // Figure out the overall date range (not used directly by eluceo/iCal, but kept for compatibility)
        $allTimestamps = [];
        foreach ($Roster as $RosterDayArray) {
            foreach ($RosterDayArray as $rosterObject) {
                if (isset($rosterObject->date_unix)) {
                    $allTimestamps[] = $rosterObject->date_unix;
                }
            }
        }
        if (empty($allTimestamps)) {
            return '';
        }
        $firstDateInRoster = new \DateTime('@' . min($allTimestamps));
        $firstDateInRoster->setTimezone($dateTimeZone);
        // Extract year from the first roster date
// Set the range to the whole year.
        $lastDateInRoster = new \DateTime('@' . max($allTimestamps));
        $lastDateInRoster->setTimezone($dateTimeZone);
        // Set the desired timezone (must be supported by PHP)
        $firstYear = (int) $firstDateInRoster->format("Y");
        $lastYear = (int) $lastDateInRoster->format("Y");
        $firstDayOfYear = new \DateTime("$firstYear-01-01", $dateTimeZone);
        $lastDayOfYear = new \DateTime("$lastYear-12-31", $dateTimeZone);

        $timeZone = \Eluceo\iCal\Domain\Entity\TimeZone::createFromPhpDateTimeZone($dateTimeZone, $firstDayOfYear, $lastDayOfYear);

        // Create a new Calendar. The URL provided as a parameter may be used by clients.
        $calendar = new \Eluceo\iCal\Domain\Entity\Calendar();
        $calendar->addTimeZone($timeZone);
        $administratorEmail = $configuration->getContactEmail();
        $networkOfBranchOffices = new \PDR\Pharmacy\NetworkOfBranchOffices;
        $ListOfBranchObjects = $networkOfBranchOffices->get_list_of_branch_objects();

        // Iterate through each day in the roster
        foreach ($Roster as $RosterDayArray) {
            // Track duplicate events for the same employee (if multiple events occur on one day)
            $sameEmployeeCount = array();
            foreach ($RosterDayArray as $rosterObject) {
                if (!isset($rosterObject->employee_key)) {
                    continue; // skip if no employee key is set
                }
                if (!isset($sameEmployeeCount[$rosterObject->employee_key])) {
                    $sameEmployeeCount[$rosterObject->employee_key] = 0;
                }
                // Set the summary using the branch name
                $branchId = $rosterObject->branch_id;
                $branchName = $ListOfBranchObjects[$branchId]->getName();
                $sameEmployeeCount[$rosterObject->employee_key]++;
                // Build and set a unique identifier (UID)
                $uid = $rosterObject->date_unix . "-" . $rosterObject->employee_key . "-" . $branchId . "-" . $sameEmployeeCount[$rosterObject->employee_key] . "@martin-mandelkow.de";
                $uniqueIdentifier = new \Eluceo\iCal\Domain\ValueObject\UniqueIdentifier($uid);
                // Create a new event from eluceo/iCal
                $event = new \Eluceo\iCal\Domain\Entity\Event($uniqueIdentifier);

                // Set start and end times (ensure these are DateTime objects, already set with the correct timezone)
                $dutyStart = new \Eluceo\iCal\Domain\ValueObject\DateTime($rosterObject->dutyStartDateTime, true);
                $dutyEnd = new \Eluceo\iCal\Domain\ValueObject\DateTime($rosterObject->dutyEndDateTime, true);
                $occurrence = new \Eluceo\iCal\Domain\ValueObject\TimeSpan($dutyStart, $dutyEnd);
                $event->setOccurrence($occurrence);
                $event->setSummary($branchName);

                // Set creation and modification times (using now)
                $now = new \DateTime("now", $dateTimeZone);
                $event->setLastModified(new \Eluceo\iCal\Domain\ValueObject\Timestamp($now));

                // Set organizer info (the branch manager’s name is passed via the CN parameter)
                $organizerCN = $ListOfBranchObjects[$branchId]->getManager();
                $organizer = new \Eluceo\iCal\Domain\ValueObject\Organizer(
                        new \Eluceo\iCal\Domain\ValueObject\EmailAddress($administratorEmail),
                        $organizerCN
                );
                $event->setOrganizer($organizer);
                // Set location from branch address
                $branchAddress = $ListOfBranchObjects[$branchId]->getAddress();
                $location = new \Eluceo\iCal\Domain\ValueObject\Location($branchAddress);
                $event->setLocation($location);

                // Set description (the helper method returns a description string)
                $description = self::buildSimpleRosterEmployeeDescription($rosterObject);
                $event->setDescription($description);

                // If alarms are to be added, we add VALARM blocks to the event.
                self::addIcsRosterEmployeeValarms($event, $rosterObject, $createValarm, $tzid);

                // Add the event to the calendar
                $calendar->addEvent($event);
            }
        }
        // Transform calendar domain object into a presentation object
        $iCalendarComponent = (new \Eluceo\iCal\Presentation\Factory\CalendarFactory())->createCalendar($calendar);
        // Transform iCalendarComponent to string:
        $iCalendarString = "" . $iCalendarComponent . "";
        return $iCalendarString;
    }

    /**
     * Build a simple description for the employee roster event.
     *
     * @param object $rosterObject
     * @return string
     */
    private static function buildSimpleRosterEmployeeDescription($rosterObject) {
        $mittagsBeginn = $rosterObject->break_start_sql;
        $mittagsEnde = $rosterObject->break_end_sql;
        $dateUnix = $rosterObject->date_unix;
        $workforce = new \workforce($rosterObject->date_sql);
        $branchId = $rosterObject->branch_id;
        $networkOfBranchOffices = new \PDR\Pharmacy\NetworkOfBranchOffices();
        $ListOfBranchObjects = $networkOfBranchOffices->get_list_of_branch_objects();
        $branchName = $ListOfBranchObjects[$branchId]->getName();

        $configuration = new \PDR\Application\configuration();
        $locale = $configuration->getLanguage(); // e.g. "de-DE"
        $formatter = new \IntlDateFormatter($locale, \IntlDateFormatter::FULL, \IntlDateFormatter::NONE);
        $formatter->setPattern('EEEE'); // Full weekday name
        $dateWeekdayName = $formatter->format($dateUnix);

        $text = "Calendar file for employee " . $rosterObject->employee_key . " (" .
                $workforce->getEmployeeFullName($rosterObject->employee_key) . ") \\r\\n";
        $text .= "contains the roster for $branchName.\n";
        $text .= "Weekday: $dateWeekdayName\n";
        if (!empty($mittagsBeginn) && !empty($mittagsEnde)) {
            $text .= sprintf('Lunch from %1$s to %2$s', $mittagsBeginn, $mittagsEnde) . "\n";
        }
        $text .= "\n";
        return $text;
    }

    /**
     * Build VALARM blocks and add to the event
     *
     * @param object $rosterObject
     * @param int    $createValarm Bitmask for desired alarms.
     * @param string $tzid          Timezone identifier.
     * @return string   Raw VALARM blocks (or empty string if none)
     */
    private static function addIcsRosterEmployeeValarms($event, $rosterObject, $createValarm, $tzid): void {
        if ($createValarm == self::VALARM_NONE) {
            return;
        }
        if (($createValarm & self::VALARM_FOR_DUTY_START) && isset($rosterObject->duty_start_int)) {
            $description = 'Time to go to work';
            $alarm = new \Eluceo\iCal\Domain\ValueObject\Alarm('DISPLAY', '-PT30M');
            $alarm->setDescription($description);
            $event->addComponent($alarm);
        }
        if (($createValarm & self::VALARM_FOR_DUTY_END) && isset($rosterObject->duty_end_int)) {
            $description = 'Time to leave';
            $alarm = new \Eluceo\iCal\Domain\ValueObject\Alarm('DISPLAY', '-PT0M');
            $alarm->setDescription($description);
            $event->addComponent($alarm);
        }
        if (($createValarm & self::VALARM_FOR_BREAK_START) && isset($rosterObject->break_start_int)) {
            $description = 'Time for lunch break.';
            $alarm = new \Eluceo\iCal\Domain\ValueObject\Alarm('DISPLAY', '-PT0M');
            $alarm->setDescription($description);
            $event->addComponent($alarm);
        }
        if (($createValarm & self::VALARM_FOR_BREAK_END) && isset($rosterObject->break_end_int)) {
            $description = 'The Lunch break ends now.';
            $alarm = new \Eluceo\iCal\Domain\ValueObject\Alarm('DISPLAY', '-PT0M');
            $alarm->setDescription($description);
            $event->addComponent($alarm);
        }
        return;
    }
}
