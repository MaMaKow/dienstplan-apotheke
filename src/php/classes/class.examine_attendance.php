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

    public static function check_for_attendant_absentees(array $Roster, PDR\Roster\AbsenceCollection $absenceCollection) {
        $user_dialog = new user_dialog();
        if (array() === $Roster) {
            return FALSE;
        }
        global $workforce;
        $Roster_workers = array();
        foreach ($Roster as $Roster_day) {
            foreach ($Roster_day as $roster_object) {
                $Roster_workers[] = $roster_object->employee_key;
            }
        }
        foreach ($absenceCollection->getListOfEmployeeKeys() as $abwesender) {
            foreach ($Roster_workers as $anwesender) {
                if ($abwesender == $anwesender and NULL !== $anwesender) {
                    $Arbeitende_abwesende[] = $anwesender;
                }
            }
        }
        if (isset($Arbeitende_abwesende)) {
            foreach ($Arbeitende_abwesende as $arbeitender_abwesender) {
                $message = sprintf(gettext('%1$s is absent (%2$s) and should not be in the roster.'),
                        $workforce->List_of_employees[$arbeitender_abwesender]->last_name,
                        absence::get_reason_string_localized($absenceCollection->getAbsenceByEmployeeKey($arbeitender_abwesender)->getReasonId()
                ));
                $user_dialog->add_message($message);
                /**
                 * TODO: Add a button to directly remove the employee from the roster!
                 *   That button shoud have a symbol and a descriptive caption.
                 */
            }
        }
    }

    /**
     * Check if any employee is not in the roster although there is no known absence:
     * @param array $Roster Roster array of scheduled roster_items
     * @param array $Principle_roster Roster array of normal/principle roster_items
     * @param array $absenceCollection Array of absent employees and the reason of absence
     * @param integer $date_unix Unix timestamp of the current day.
     * @return void <p>This function does not return anything.
     *  It uses user_dialog->add_message with it's results.</p>
     */
    public static function check_for_absent_employees(array $Roster, array $Principle_roster, PDR\Roster\AbsenceCollection $absenceCollection, int $date_unix) {
        $date_object = new DateTime();
        $date_object->setTimestamp($date_unix);
        $user_dialog = new user_dialog();
        $Roster_workers = self::get_roster_workers($Roster);
        $Principle_roster_workers = self::get_roster_workers($Principle_roster);
        $workforce = new workforce($date_object->format('Y-m-d'));

        $Mitarbeiter_differenz = array_diff($Principle_roster_workers, $Roster_workers);
        self::trimAbsentEmployees($Mitarbeiter_differenz, $absenceCollection);
        /*
         * Stop processing if nobody is left
         */
        if (empty($Mitarbeiter_differenz)) {
            return NULL;
        }
        /*
         * Check if that worker is scheduled in any of the other branches:
         */
        self::trim_rescheduled_employees($Mitarbeiter_differenz, $date_object);
        /*
         * Stop processing if nobody is left
         */
        if (empty($Mitarbeiter_differenz)) {
            return NULL;
        }

        $message = gettext('The following employees are not scheduled:');
        $user_dialog->add_message($message, E_USER_WARNING);
        foreach ($Mitarbeiter_differenz as $arbeiter) {
            foreach ($Principle_roster[$date_unix] as $principle_roster_object) {
                if ($arbeiter == $principle_roster_object->employee_key) {
                    //TODO: Set a link to add the employee via JavaScript?
                    $duty_start = $principle_roster_object->duty_start_sql;
                    $duty_end = $principle_roster_object->duty_end_sql;
                    $message_unsafe = $workforce->List_of_employees[$arbeiter]->last_name;
                    $message_unsafe .= " ($duty_start - $duty_end)";
                    $message_safe = htmlentities($message_unsafe);
                    $message_safe .= "&nbsp"
                            . "<button type='submit' value='insertMissingEmployee'  form='insertMissingEmployeeForm'>"
                            . "<img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/copy.svg'>"
                            . "<span class='hint' >"
                            . "&nbsp" . gettext("Insert")
                            . "</span>"
                            . "</button>"
                            . "<input type='hidden' name='rosterActionCommand' value='insertMissingEmployee' form='insertMissingEmployeeForm'>"
                            . "<input type='hidden' name='principleRosterObject' value='" . json_encode($principle_roster_object) . "' form='insertMissingEmployeeForm'>"
                            . "<form method=POST id='insertMissingEmployeeForm'></form>"
                    ;
                    $user_dialog->add_message($message_safe, E_USER_WARNING, true);
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
