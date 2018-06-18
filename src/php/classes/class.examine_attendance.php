<?php

/*
 * Copyright (C) 2018 Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
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
 * @author Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
abstract class examine_attendance {

    public static function check_for_attendant_absentees($Roster, $Abwesende) {
        if (array() === $Roster) {
            return FALSE;
        }
        global $workforce;
        $Roster_workers = array();
        foreach ($Roster as $Roster_day) {
            foreach ($Roster_day as $roster_object) {
                $Roster_workers[] = $roster_object->employee_id;
            }
        }
        foreach (array_keys($Abwesende) as $abwesender) {
            foreach ($Roster_workers as $anwesender) {
                if ($abwesender == $anwesender) {
                    $Arbeitende_abwesende[] = $anwesender;
                }
            }
        }
        if (isset($Arbeitende_abwesende)) {
            foreach ($Arbeitende_abwesende as $arbeitender_abwesender) {
                $message = sprintf(gettext("%1s is absent (%2s) and should not be in the roster."), $workforce->List_of_employees[$arbeitender_abwesender]->last_name, pdr_gettext($Abwesende[$arbeitender_abwesender]));
                user_dialog::add_message($message);
            }
        }
    }

    public static function check_for_absent_employees($Roster, $Principle_roster, $Abwesende, $date_unix) {
        $Roster_workers = array();
        $Principle_roster_workers = array();
        global $workforce;
        foreach ($Principle_roster as $Principle_roster_day) {
            foreach ($Principle_roster_day as $principle_roster_object) {
                $Principle_roster_workers[] = $principle_roster_object->employee_id;
            }
        }
        foreach ($Roster as $Roster_day) {
            foreach ($Roster_day as $roster_object) {
                $Roster_workers[] = $roster_object->employee_id;
            }
        }
        $Mitarbeiter_differenz = array_diff($Principle_roster_workers, $Roster_workers);
        if (isset($Abwesende)) {
            $Mitarbeiter_differenz = array_diff($Mitarbeiter_differenz, array_keys($Abwesende));
        }
        if (!empty($Mitarbeiter_differenz)) {
            $message = gettext('The following employees are not scheduled:');
            user_dialog::add_message($message, E_USER_WARNING);
            foreach ($Mitarbeiter_differenz as $arbeiter) {
                foreach ($Principle_roster[$date_unix] as $principle_roster_object) {
                    if ($arbeiter == $principle_roster_object->employee_id) {
                        $duty_start = $principle_roster_object->duty_start_sql;
                        $duty_end = $principle_roster_object->duty_end_sql;
                        $message = $workforce->List_of_employees[$arbeiter]->last_name;
                        $message .= " ($duty_start - $duty_end)";
                        user_dialog::add_message($message, E_USER_WARNING);
                        break;
                    }
                }
            }
        }
    }

}
