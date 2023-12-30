<?php

/*
 * Copyright (C) 2018 Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
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

/**
 * Description of class
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
abstract class examine_attendance {

    public static function checkForAttendantAbsentees(array $Roster, PDR\Roster\AbsenceCollection $absenceCollection, workforce $workforce) {
        $userDialog = new user_dialog();
        if (array() === $Roster) {
            return FALSE;
        }

        foreach ($Roster as $RosterDay) {
            foreach ($RosterDay as $rosterItem) {
                if (NULL !== $rosterItem->employee_key and $absenceCollection->containsEmployeeKey($rosterItem->employee_key)) {
                    $scheduledEmployeeWithAbsenceKey = $rosterItem->employee_key;
                    $messageUnsafe = sprintf(gettext('%1$s is absent (%2$s) and should not be in the roster.'),
                            $workforce->List_of_employees[$scheduledEmployeeWithAbsenceKey]->first_name . " " . $workforce->List_of_employees[$scheduledEmployeeWithAbsenceKey]->last_name,
                            \PDR\Utility\AbsenceUtility::getReasonStringLocalized($absenceCollection->getAbsenceByEmployeeKey($scheduledEmployeeWithAbsenceKey)->getReasonId()
                    ));
                    $messageSafe = htmlspecialchars($messageUnsafe);
                    /**
                     * @todo Add a function to build the button. Make it viable for the insert and the remove button.
                     */
                    $messageSafe .= "&nbsp"
                            . "<form method=POST id='removeAbsentEmployeeForm'>"
                            . "<button type='submit' value='removeAbsentEmployee' class='button_small no_print'>"
                            . "<img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/md_delete_forever.svg' title='" . gettext("Remove") . "'>"
                            . "</button>"
                            . "<input type='hidden' name='rosterActionCommand' value='removeAbsentEmployee'>"
                            . "<input type='hidden' name='rosterItemObject' value='" . json_encode($rosterItem) . "' >"
                            . "</form>"
                    ;

                    $userDialog->add_message($messageSafe, E_USER_ERROR, TRUE);
                }
            }
        }
    }

    /**
     * Check if any employee is not in the roster although there is no known absence:
     * @param array $Roster Roster array of scheduled roster_items
     * @param array $PrincipleRoster Roster array of normal/principle roster_items
     * @param array $absenceCollection Array of absent employees and the reason of absence
     * @param integer $dateUnix Unix timestamp of the current day.
     * @return void <p>This function does not return anything.
     *  It uses user_dialog->add_message with it's results.</p>
     */
    public static function checkForAbsentEmployees(array $Roster, array $PrincipleRoster, PDR\Roster\AbsenceCollection $absenceCollection, int $dateUnix) {
        $dateObject = new DateTime();
        $dateObject->setTimestamp($dateUnix);
        $userDialog = new user_dialog();
        $RosterWorkers = self::get_roster_workers($Roster);
        $PrincipleRosterWorkers = self::get_roster_workers($PrincipleRoster);
        $workforce = new workforce($dateObject->format('Y-m-d'));

        $EmployeeDifference = array_diff($PrincipleRosterWorkers, $RosterWorkers);
        self::trimAbsentEmployees($EmployeeDifference, $absenceCollection);
        /*
         * Stop processing if nobody is left
         */
        if (empty($EmployeeDifference)) {
            return NULL;
        }
        /*
         * Check if that worker is scheduled in any of the other branches:
         */
        self::trim_rescheduled_employees($EmployeeDifference, $dateObject);
        /*
         * Stop processing if nobody is left
         */
        if (empty($EmployeeDifference)) {
            return NULL;
        }

        $message = gettext('The following employees are not scheduled:');
        $userDialog->add_message($message, E_USER_WARNING);
        foreach ($EmployeeDifference as $arbeiter) {
            foreach ($PrincipleRoster[$dateUnix] as $principle_roster_object) {
                if ($arbeiter == $principle_roster_object->employee_key) {
                    //TODO: Set a link to add the employee via JavaScript?
                    $duty_start = $principle_roster_object->duty_start_sql;
                    $duty_end = $principle_roster_object->duty_end_sql;
                    $message_unsafe = $workforce->List_of_employees[$arbeiter]->first_name . " " . $workforce->List_of_employees[$arbeiter]->last_name;
                    $message_unsafe .= " ($duty_start - $duty_end)";
                    $message_safe = htmlspecialchars($message_unsafe);
                    $message_safe .= "&nbsp"
                            . "<form method=POST id='insertMissingEmployeeForm'>"
                            . "<button type='submit' value='insertMissingEmployee' class='button_small  no_print'>"
                            . "<img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/copy.svg' title='" . gettext("Insert") . "'>"
                            . "</button>"
                            . "<input type='hidden' name='rosterActionCommand' value='insertMissingEmployee'>"
                            . "<input type='hidden' name='principleRosterObject' value='" . json_encode($principle_roster_object) . "'>"
                            . "</form>"
                    ;
                    $userDialog->add_message($message_safe, E_USER_WARNING, true);
                    break;
                }
            }
        }
    }

    /**
     * Check if that worker is scheduled in any of the other branches.
     *
     * @param type $Mitarbeiter_differenz
     * @param type $date_object
     */
    private static function trim_rescheduled_employees(&$Mitarbeiter_differenz, $date_object) {
        foreach ($Mitarbeiter_differenz as $key => $employee_key) {
            $working_hours = roster::get_working_hours_in_all_branches($date_object->format('Y-m-d'), $employee_key);
            $working_hours_should = principle_roster::get_working_hours_should(clone $date_object, $employee_key);
            if ($working_hours >= $working_hours_should) {
                unset($Mitarbeiter_differenz[$key]);
            }
        }
    }

    /**
     * Check if that worker is absent
     *
     * @param array $Mitarbeiter_differenz
     * @param PDR\Roster\AbsenceCollection $absenceCollection
     */
    private static function trimAbsentEmployees(array &$Mitarbeiter_differenz, PDR\Roster\AbsenceCollection $absenceCollection) {
        if (isset($absenceCollection)) {
            $Mitarbeiter_differenz = array_diff($Mitarbeiter_differenz, $absenceCollection->getListOfEmployeeKeys());
        }
    }

    private static function get_roster_workers($Roster) {
        $Roster_workers = array();
        foreach ($Roster as $Roster_day) {
            foreach ($Roster_day as $roster_object) {
                $Roster_workers[] = $roster_object->employee_key;
            }
        }
        return $Roster_workers;
    }
}
