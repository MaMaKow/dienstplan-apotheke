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

    public static function check_for_attendant_absentees($Roster, $Abwesende) {
        $user_dialog = new user_dialog();
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
                if ($abwesender == $anwesender and NULL !== $anwesender) {
                    $Arbeitende_abwesende[] = $anwesender;
                }
            }
        }
        if (isset($Arbeitende_abwesende)) {
            foreach ($Arbeitende_abwesende as $arbeitender_abwesender) {
                $message = sprintf(gettext("%1s is absent (%2s) and should not be in the roster."), $workforce->List_of_employees[$arbeitender_abwesender]->last_name, pdr_gettext($Abwesende[$arbeitender_abwesender]));
                $user_dialog->add_message($message);
            }
        }
    }

    /**
     * Check if any employee is not in the roster although there is no known absence:
     * @param array $Roster Roster array of scheduled roster_items
     * @param array $Principle_roster Roster array of normal/principle roster_items
     * @param array $Abwesende Array of absent employees and the reason of absence
     * @param integer $date_unix Unix timestamp of the current day.
     * @return void <p>This function does not return anything.
     *  It uses user_dialog->add_message with it's results.</p>
     */
    public static function check_for_absent_employees($Roster, $Principle_roster, $Abwesende, $date_unix) {
        $user_dialog = new user_dialog();
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
            /*
             * Check if that worker is scheduled in any of the other branches:
             */
            foreach ($Mitarbeiter_differenz as $key => $employee_id) {
                $working_hours_should = 0;
                $working_hours = 0;
                $sql_query = "SELECT sum(`Stunden`) as `working_hours` FROM `Dienstplan` WHERE `Datum` = :date and `VK` = :employee_id";
                $result = database_wrapper::instance()->run($sql_query, array('date' => date('Y-m-d', $date_unix), 'employee_id' => $employee_id));
                while ($row = $result->fetch(PDO::FETCH_OBJ)) {
                    $working_hours = $row->working_hours;
                }
                $sql_query = "SELECT sum(`Stunden`) as `working_hours_should` FROM `Grundplan` WHERE `Wochentag` = :weekday and `VK` = :employee_id";
                $result = database_wrapper::instance()->run($sql_query, array('weekday' => date('w', $date_unix), 'employee_id' => $employee_id));
                while ($row = $result->fetch(PDO::FETCH_OBJ)) {
                    $working_hours_should = $row->working_hours_should;
                }
                if ($working_hours >= $working_hours_should) {
                    unset($Mitarbeiter_differenz[$key]);
                }
            }
        }
        if (!empty($Mitarbeiter_differenz)) {
            $message = gettext('The following employees are not scheduled:');
            $user_dialog->add_message($message, E_USER_WARNING);
            foreach ($Mitarbeiter_differenz as $arbeiter) {
                foreach ($Principle_roster[$date_unix] as $principle_roster_object) {
                    if ($arbeiter == $principle_roster_object->employee_id) {
                        $duty_start = $principle_roster_object->duty_start_sql;
                        $duty_end = $principle_roster_object->duty_end_sql;
                        $message = $workforce->List_of_employees[$arbeiter]->last_name;
                        $message .= " ($duty_start - $duty_end)";
                        $user_dialog->add_message($message, E_USER_WARNING);
                        break;
                    }
                }
            }
        }
    }

}
