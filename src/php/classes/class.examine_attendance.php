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

    public static function check_for_attendant_absentees($Roster, $datum, $Abwesende, &$Fehlermeldung) {
        global $List_of_employees;
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
                $Fehlermeldung[] = $List_of_employees[$arbeitender_abwesender] . " ist abwesend (" . $Abwesende[$arbeitender_abwesender] . ") und sollte nicht im Dienstplan stehen.";
            }
        }
    }

    public static function check_for_absent_employees($Roster, $Principle_roster, $Abwesende, $date_unix, &$Warnmeldung) {
        global $List_of_employees, $Mandanten_mitarbeiter;
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
        //$Available_roster_workers = array_unique(array_merge(array_keys($Mandanten_mitarbeiter), $Principle_roster_workers)); //We combine the employees in the branch and the employees in the principle roster.
        $Mitarbeiter_differenz = array_diff($Principle_roster_workers, $Roster_workers);
        if (isset($Abwesende)) {
            $Mitarbeiter_differenz = array_diff($Mitarbeiter_differenz, array_keys($Abwesende));
        }
        if (!empty($Mitarbeiter_differenz)) {
            $separator = "";
            $fehler = "Es sind folgende Mitarbeiter nicht eingesetzt: <br>\n";
            foreach ($Mitarbeiter_differenz as $arbeiter) {
                foreach ($Principle_roster[$date_unix] as $principle_roster_object) {
                    print_debug_variable('$principle_roster_object->employee_id', $principle_roster_object->employee_id);
                    if ($arbeiter == $principle_roster_object->employee_id) {
                        $duty_start = $principle_roster_object->duty_start_sql;
                        $duty_end = $principle_roster_object->duty_end_sql;
                        $fehler .= $separator . $List_of_employees[$arbeiter];
                        $fehler .= " ($duty_start - $duty_end)";
                        $separator = ", <br>";
                        break;
                    }
                }
            }
            $Warnmeldung[] = $fehler;
        }
    }

}
