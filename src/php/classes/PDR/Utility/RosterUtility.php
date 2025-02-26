<?php

/*
 * Copyright (C) 2025 Mandelkow
 *
 * Dienstplan Apotheke
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace PDR\Utility;

/**
 * Static Utility functions for the roster
 *
 * @author Mandelkow
 */
class RosterUtility {

    const NUMBER_OF_BUSINESS_DAYS = 5;

    /**
     * Calculates the total working weekly hours for each employee within a given time interval.
     *
     * This method processes the roster for each employee in the provided workforce between
     * the start and end dates. For each day in the interval, it determines:
     * - The actual working hours logged.
     * - The theoretical working hours (taking into account public holidays and specific absence reasons).
     *
     * The method then takes the higher value between the actual and theoretical hours for each day
     * and sums these values to compute the employee's total working hours for the period.
     *
     * An associative array is returned where the keys are employee identifiers and the values
     * represent the total calculated weekly working hours.
     *
     * @param \DateTime                        $dateStartObject  The start date of the time interval.
     * @param \DateTime                        $dateEndObject    The end date of the time interval.
     * @param \workforce                       $workforce        The workforce object containing the list of employees.
     * @param \PDR\Roster\AbsenceCollection    $listOfAbsences   Collection of absences to consider in the calculation.
     *
     * @return array An associative array mapping employee keys to their total working hours.
     *
     * @throws \Exception If the start date is later than the end date.
     */
    public static function calculateWorkingWeeklyHoursInTimeInterval(\DateTime $dateStartObject, \DateTime $dateEndObject, \workforce $workforce, \PDR\Roster\AbsenceCollection $listOfAbsences): array {
        /**
         * <p lang=de>Wir gehen jetzt durch den Plan und berechnen die gearbeiteten Stunden.
         * Dabei werden an jedem Tag auch die Arbeitsstunden beachtet,
         *  die durch Feiertage oder Krankheit gutgeschrieben werden.</p>
         */
        if ($dateStartObject > $dateEndObject) {
            throw new \Exception("The start date must be before the end date!");
        }
        $listOfEmployees = $workforce->List_of_employees;
        $WorkingWeekHours = array();
        /**
         * <p lang=de>Für jeden Mitarbeiter wird nun ein persönlicher Dienstplan aus der Datenbank gelesen.</p>
         */
        foreach ($listOfEmployees as $employee) {
            $employeeKey = $employee->get_employee_key();
            $WorkingWeekHours[$employeeKey] = 0;
            /**
             * <p lang=de>Der $employee_roster enthält alle Tage von $dateStartObject bis $dateEndObject.
             * Auch Tage, an denen der Mitarbeiter nicht arbeitet. An diesen ist ein roster_item_empty Objekt in den Array eigefügt.</p>
             */
            $employeeRoster = new \roster($dateStartObject, $dateEndObject, $employeeKey);
            $arrayOfDaysOfRosterItems = $employeeRoster->array_of_days_of_roster_items;
            foreach ($arrayOfDaysOfRosterItems as $arrayOfRosterItems) {
                foreach ($arrayOfRosterItems as $rosterItem) {
                    /**
                     * <p lang=de>Diese Schleife geht durch alle Dienstplan-Daten eines Mitarbeiters.</p>
                     */
                    $dateObject = $rosterItem->get_date_object();
                    $absence = $listOfAbsences->getAbsenceByEmployeeKeyOnDate($employeeKey, $dateObject);
                    $hoursWorkedReal = 0;
                    /**
                     *  <p lang=de>Die Mitarbeitende Person hat an diesem Tag tatsächlich $hoursWorkedReal Stunden gearbeitet.</p>
                     */
                    $hoursWorkedReal += $rosterItem->get_working_hours();
                    /**
                     * <p lang=de>Bei Feiertagen und bei bestimmten Abwesenheiten werden Stunden gemäß Grundplan angenommen.</p>
                     */
                    $hoursWorkedTheoretically = self::calculateHoursWorkedTheoretically($employee, $dateObject, $absence);

                    /**
                     * Für die Mitarbeiterin werden $hoursWorkedByLaw Stunden angenommen.
                     */
                    $hoursWorkedByLaw = max($hoursWorkedReal, $hoursWorkedTheoretically);
                    $WorkingWeekHours[$employeeKey] += $hoursWorkedByLaw;
                }
            }
            /**
             * <p lang=de>Im Zeitraum werden für die mitarbeitende Person $WorkingWeekHours[$employeeKey] Stunden insgesamt angenommen.
             * Auf zur nächsten Mitarbeiterin.</p>
             */
        }
        return $WorkingWeekHours;
    }

    /**
     * Calculates the theoretical working hours for an employee on a specific date.
     *
     * This method computes the expected working hours based on the following criteria:
     * - If the date is a public holiday, it calculates the working hours using holiday-specific logic.
     * - If an absence is registered due to sickness or paid leave of absence,
     *   the employee's standard principle hours for that date are used.
     *
     * If none of these conditions apply, the theoretical working hours remain at 0.
     *
     * @param \employee                 $employee   The employee for whom the calculation is performed.
     * @param \DateTime                 $dateObject The date for which the theoretical hours are calculated.
     * @param \PDR\Roster\Absence|null  $absence    The absence record for the employee on the given date, if any.
     *
     * @return float The calculated theoretical working hours for the employee on the specified date.
     */
    private static function calculateHoursWorkedTheoretically(\employee $employee, \DateTime $dateObject, ?\PDR\Roster\Absence $absence): float {
        $hoursWorkedTheoretically = 0;
        $isPublicHoliday = \holidays::is_holiday($dateObject);
        if ($isPublicHoliday) {
            $hoursWorkedTheoretically = self::calculateWorkingHoursOnHoliday($employee, $dateObject, $absence);
        }

        /**
         * <p lang=de>Wenn der Mitarbeiter mit Stundenanspruch abwesend ist, nehmen wir statt dessen den Grundplan.
         * Das ist der Fall bei REASON_SICKNESS oder REASON_PAID_LEAVE_OF_ABSENCE.</p>
         * § 4 Entgeltfortzahlungsgesetz Höhe des fortzuzahlenden Arbeitsentgelts
         * (1) Für den ... Zeitraum ist dem Arbeitnehmer ... regelmäßigen Arbeitszeit zustehende Arbeitsentgelt fortzuzahlen.
         */
        if (null !== $absence and (
                $absence->getReasonId() === \PDR\Utility\AbsenceUtility::REASON_PAID_LEAVE_OF_ABSENCE
                or $absence->getReasonId() === \PDR\Utility\AbsenceUtility::REASON_SICKNESS)) {
            /**
             * Die Mitarbeiterin hat an diesem Tag theoretisch $hoursWorkedTheoretically Stunden gearbeitet mit dem Grund $absence->getReasonId().
             */
            $hoursWorkedTheoretically = $employee->getPrincipleHoursOnDate($dateObject);
        }
        return $hoursWorkedTheoretically;
    }

    /**
     * Calculates the working hours credited to an employee on a public holiday.
     *
     * On holidays, employees are generally credited with their principle working hours for that day.
     * This method applies the following logic:
     * - If no absence is recorded, the employee's principle hours for the given date are returned.
     * - If an absence is recorded and its reason is NOT one of the following:
     *   - REASON_MATERNITY_LEAVE,
     *   - REASON_PARENTAL_LEAVE,
     *   - REASON_REMAINING_VACATION,
     *   - REASON_VACATION,
     *   then the employee's principle hours are credited even if absent.
     * - For absences with the above reasons, no additional hours are credited (resulting in 0 hours).
     *
     * @param \employee                 $employee   The employee for whom the holiday working hours are calculated.
     * @param \DateTime                 $dateObject The date of the public holiday.
     * @param \PDR\Roster\Absence|null  $absence    The absence record for the employee on this date, if any.
     *
     * @return float The credited working hours for the employee on the holiday.
     */
    private static function calculateWorkingHoursOnHoliday(\employee $employee, \DateTime $dateObject, ?\PDR\Roster\Absence $absence): float {
        $hoursWorkedTheoretically = 0;
        /**
         * An Feiertagen werden die Arbeitsstunden gemäß Grundplan angenommen.
         *
         * "§ 2 Entgeltzahlung an Feiertagen
         *  Für Arbeitszeit, die infolge eines gesetzlichen Feiertages ausfällt,
         *   hat der Arbeitgeber dem Arbeitnehmer das Arbeitsentgelt zu zahlen,
         *   das er ohne den Arbeitsausfall erhalten hätte."
         */
        if (null === $absence) {
            $hoursWorkedTheoretically = $employee->getPrincipleHoursOnDate($dateObject);
            return $hoursWorkedTheoretically;
        }
        /**
         * Bei einigen Abwesenheiten werden keine zusätzlichen Stunden gewährt.
         * Abwesenheiten ohne zusätzliche Stunden:
         * \PDR\Utility\AbsenceUtility::REASON_MATERNITY_LEAVE,
         * \PDR\Utility\AbsenceUtility::REASON_PARENTAL_LEAVE,
         * \PDR\Utility\AbsenceUtility::REASON_REMAINING_VACATION,
         * \PDR\Utility\AbsenceUtility::REASON_VACATION,

         * Abwesenheiten, bei denen die Stunden laut Grundplan gewährt werden sind:
         * \PDR\Utility\AbsenceUtility::REASON_PAID_LEAVE_OF_ABSENCE,
         * \PDR\Utility\AbsenceUtility::REASON_SICKNESS,
         * \PDR\Utility\AbsenceUtility::REASON_SICKNESS_OF_CHILD,
         * \PDR\Utility\AbsenceUtility::REASON_TAKEN_OVERTIME,
         */
        if (null !== $absence and (!in_array($absence->getReasonId(), array(
                    \PDR\Utility\AbsenceUtility::REASON_MATERNITY_LEAVE,
                    \PDR\Utility\AbsenceUtility::REASON_PARENTAL_LEAVE,
                    \PDR\Utility\AbsenceUtility::REASON_REMAINING_VACATION,
                    \PDR\Utility\AbsenceUtility::REASON_VACATION,
                )))) {
            /**
             * Die Mitarbeitende Person an diesem Tag theoretisch Stunden gearbeitet obwohl ohnehin abwesend.
             * Denn außer den oben genannten Gründen werden alle anderen Gründe anerkannt.
             */
            $hoursWorkedTheoretically = $employee->getPrincipleHoursOnDate($dateObject);
        }
        return $hoursWorkedTheoretically;
    }

    public static function calculateWorkingWeekHoursShould(array $roster, \workforce $workforce): array {
        $workingWeekHoursShould = array();
        foreach ($workforce->List_of_employees as $employeeObject) {
            $workingHoursEmployeeShould = self::calculateWorkingHoursEmployeeShould($roster, $employeeObject);
            $workingWeekHoursShould[$employeeObject->get_employee_key()] = $workingHoursEmployeeShould;
        }
        return $workingWeekHoursShould;
    }

    private static function calculateWorkingHoursEmployeeShould(array $Roster, \employee $employeeObject): float {
        $workingHoursDayShould = 0;
        foreach (array_keys($Roster) as $dateUnix) {
            $dateSql = date('Y-m-d', $dateUnix);
            $dateObject = new \DateTime;
            $dateObject->setTimestamp($dateUnix);
            $absenceCollection = \PDR\Database\AbsenceDatabaseHandler::readAbsenteesOnDate($dateSql);
            $workingHoursDayShould += self::calculateWorkingHoursDayEmployeeShould($dateObject, $employeeObject, $absenceCollection);
        }
        return $workingHoursDayShould;
    }

    /**
     * Calculate the expected working hours for an employee on a given date.
     *
     * @param DateTime $dateObject - The date for which to calculate working hours.
     * @param employee $employeeObject - The employee for whom to calculate working hours.
     * @param PDR\Roster\AbsenceCollection $absenceCollection - Collection of absences for the employee.
     * @return float - The calculated working hours for the employee on the specified date.
     */
    private static function calculateWorkingHoursDayEmployeeShould(\DateTime $dateObject, \employee $employeeObject, \PDR\Roster\AbsenceCollection $absenceCollection): float {
        if ($absenceCollection->containsEmployeeKey($employeeObject->get_employee_key())) {
            /**
             * Those who are absent do not have to work.
             * Exception: Those who reduce overtime REASON_TAKEN_OVERTIME are credited with target hours.
             * @todo "§ 11 Bundesurlaubsgesetz Urlaubsentgelt (1) Das Urlaubsentgelt bemißt sich nach dem durchschnittlichen Arbeitsverdienst..."
             * Entsprechend muss hier ein Fünftel oder ein Sechstel der Wochenarbeitszeit angesetzt werden.
             *   const REASON_VACATION = 1;
             *   const REASON_REMAINING_VACATION = 2;
             * Im Falle von URLAUB muss anders gerechnet werden.
             * @see Vergleich: https://www.mep24software.de/blog/urlaubsberechnung-teil-2
             *
             * Während der Elternzeit besteht kein Arbeitsverhältnis, das bedeutet, dass während dieser Zeit weder Soll- noch Ist-Stunden anfallen.
             *  const REASON_PARENTAL_LEAVE = 8;
             * Während des Mutterschutz werden die Sollstunden reduziert.
             *  const REASON_MATERNITY_LEAVE = 7;

             */
            /**
             * @var array $noWorkAbsenceReasonIds
             * @see AbsenceUtility::$List_of_absence_reasons for a full list of absence reason ids (paid and unpaid)
             */
            $noWorkAbsenceReasonIds = array(
                \PDR\Utility\AbsenceUtility::REASON_VACATION,
                \PDR\Utility\AbsenceUtility::REASON_REMAINING_VACATION,
                \PDR\Utility\AbsenceUtility::REASON_SICKNESS_OF_CHILD,
                \PDR\Utility\AbsenceUtility::REASON_MATERNITY_LEAVE,
                \PDR\Utility\AbsenceUtility::REASON_PARENTAL_LEAVE,
            );

            if (in_array(
                            $absenceCollection->getAbsenceByEmployeeKey($employeeObject->get_employee_key())->getReasonId(),
                            $noWorkAbsenceReasonIds)) {
                return 0;
            }
        }

        /**
         * Work is calculated to have been worked on holidays.
         * We do not check this here.
         * It is managed by the calculateWorkingWeeklyHoursInTimeInterval function in the class \PDR\Utility\RosterUtility.
         */
        /**
         *  Check for a special case where the employee works only on specific days (e.g., Tue/Thu).
         *  TODO: Consider handling scenarios when a holiday falls on a Friday.
         *  Is it fair to treat such employees differently?
         */
        if (\roster::is_empty_roster_day_array($employeeObject->get_principle_roster_on_date($dateObject))
                and !empty($employeeObject->working_week_days)) {
            return 0;
        }
        /**
         * The hours from the principle roster:
         * @todo <p lang=de>
         * In diesem Fall muss auf den Ausgleich im Grundplan geachtet werden.
         * Wenn der Grundplan statt 40 Stunden nur 37 tatsächlich fest verplant, dann entsteht hier ein Nachteil für den Arbeitgeber.
         * Oder für den Mitarbeiter?
         * Beispiel, 8+8+8+8+5+(Samstag 6 Stunden alle zwei Wochen)
         * In diesem Fall würde dem Mitarbeiter bei einem Feiertag am Freitag nur...
         * </p>
         */
        $principleHoursOnDate = $employeeObject->getPrincipleHoursOnDate($dateObject);
        if (0 !== $principleHoursOnDate) {
            return $principleHoursOnDate;
        }
        if (!empty($employeeObject->working_week_days)) {
            /*
             * In case we do know the exact working_week_days we divide by them.
             */
            return $employeeObject->working_week_hours / $employeeObject->working_week_days;
        }
        /**
         * If nothing else fits, then we take the proportion of the general business days
         * This happens, if there are no days in the principle roster for this employee:
         */
        return $employeeObject->working_week_hours / self::NUMBER_OF_BUSINESS_DAYS;
    }
}
