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

namespace PDR\Output\HTML;

/**
 * Description of SaturdayListHtmlBuilder
 *
 * @author Mandelkow
 */
class SaturdayListHtmlBuilder {

    public static function buildTableRow(\DateTime $dateObject, int $branchId, \PDR\DateTime\Holidays $holidays): string {

        $saturdayRotation = new \saturday_rotation($branchId);
        $saturdayRotation->get_participation_team_id($dateObject, $holidays);
        $workforce = new \workforce($dateObject->format('Y-m-d'));
        $absenceCollection = \PDR\Database\AbsenceDatabaseHandler::readAbsenteesOnDate($dateObject->format('Y-m-d'));

        $Roster = \roster::read_roster_from_database($branchId, $dateObject->format('Y-m-d'));
        $tableRow = "";
        $configuration = new \PDR\Application\Configuration();
        $locale = $configuration->getLanguage();
        $dayFormatter = new \IntlDateFormatter($locale, \IntlDateFormatter::FULL, \IntlDateFormatter::NONE);
        $dayFormatter->setPattern('EEE dd.MM.YYYY'); // 'EEEE' represents the full weekday name

        $dateString = $dayFormatter->format($dateObject->getTimestamp());
        if ($holidays->isHoliday($dateObject)) {
            $holiday = $holidays->getHolidayOnDate($dateObject);
            $tableRow .= "<tr class='saturday-list-row-holiday'>";
            $tableRow .= "<td colspan='99'>";
            $tableRow .= $dateString;
            $tableRow .= "&nbsp;<span>" . $holiday->getName() . "</span>";
            $tableRow .= "</td>";
            $tableRow .= "</tr>\n";
        } else {
            $RosteredEmployeesNames = self::getRosteredEmployeesNames($Roster, $workforce, $absenceCollection);
            $rosteredEmployeesNamesString = implode(', ', $RosteredEmployeesNames);
            $SaturdayRotationTeamMemberNames = self::getSaturdayRotationTeamMemberNamesSpan($saturdayRotation, $workforce, $absenceCollection);
            $saturdayRotationTeamMemberNamesString = implode(', ', $SaturdayRotationTeamMemberNames);
            $tableRow .= "<tr>";
            $tableRow .= "<td>";
            $tableRow .= $dateString;
            $tableRow .= "</td>";
            $tableRow .= "<td>" . $saturdayRotation->team_id . "</td>";
            $tableRow .= "<td>" . $saturdayRotationTeamMemberNamesString
                    . self::getEmergencyServiceHint($dateObject)
                    . "</td>";
            $tableRow .= "<td>" . $rosteredEmployeesNamesString . "</td>";
            $tableRow .= "</tr>\n";
        }
        return $tableRow;
    }

    private static function getSaturdayRotationTeamMemberNamesSpan(\saturday_rotation $saturdayRotation, \workforce $workforce, \PDR\Roster\AbsenceCollection $absenceCollection) {
        $SaturdayRotationTeamMemberIds = array();
        $saturdayRotationTeamId = $saturdayRotation->team_id;
        if (NULL !== $saturdayRotationTeamId and FALSE !== $saturdayRotationTeamId and array_key_exists($saturdayRotationTeamId, $saturdayRotation->List_of_teams)) {
            /**
             * <p lang=de>Es ist möglich, dass eine größere Zahl an Teams existiert hat, z.B. 6.
             * Wenn die Zuweisung der Teams bereits erfolgt ist, wurde z.B. das Team 6 in der Datenbank hinterlegt.
             * Wenn nun nur noch 4 Teams existieren, gibt $saturday_rotation->team_id;
             *   durch die Funktion get_participation_team_id(), welche read_participation_from_database() aufruft, die gespeicherte Team id zurück.
             * Die ist in dem array $saturday_rotation->List_of_teams aber gar nicht mehr enthalten.
             * Wir geben in diesem Fall einen leeren Array weiter.
             * </p>
             */
            $SaturdayRotationTeamMemberIds = $saturdayRotation->List_of_teams[$saturdayRotationTeamId];
        }

        $SaturdayRotationTeamMemberNames = array();
        foreach ($SaturdayRotationTeamMemberIds as $employeeKey) {

            if ($workforce->employee_exists($employeeKey)) {
                $prefix = '<span>';
                $suffix = '</span>';
                if ($absenceCollection->containsEmployeeKey($employeeKey)) {
                    $prefix = '<span class="absent">';
                    $suffix = "&nbsp;(" . \PDR\Utility\AbsenceUtility::getReasonStringLocalized($absenceCollection->getAbsenceByEmployeeKey($employeeKey)->getReasonId()) . ')</span>';
                }

                $SaturdayRotationTeamMemberNames[] = $prefix . $workforce->getListOfEmployees()[$employeeKey]->last_name . $suffix;
            } else {
                $SaturdayRotationTeamMemberNames[] = "$employeeKey???";
            }
        }
        return $SaturdayRotationTeamMemberNames;
    }

    private static function getRosteredEmployeesNames(array $Roster, \workforce $workforce, \PDR\Roster\AbsenceCollection $absenceCollection): array {
        $RosteredEmployees = array();
        foreach ($Roster as $RosterDayArray) {
            foreach ($RosterDayArray as $rosterItem) {
                if ($workforce->employee_exists($rosterItem->employee_key)) {
                    $prefix = '<span>';
                    $suffix = '</span>';
                    if ($absenceCollection->containsEmployeeKey($rosterItem->employee_key)) {
                        $prefix = '<span class="absent">';
                        $suffix = "&nbsp;(" . \PDR\Utility\AbsenceUtility::getReasonStringLocalized($absenceCollection->getAbsenceByEmployeeKey($rosterItem->employee_key)->getReasonId()) . ')</span>';
                    }
                    $RosteredEmployees[$rosterItem->employee_key] = $prefix . $workforce->get_employee_last_name($rosterItem->employee_key) . $suffix;
                }
            }
        }
        return $RosteredEmployees;
    }

    private static function getEmergencyServiceHint(\DateTime $dateObject): string {
        $fridayText = "";
        $saturdayText = "";
        $sundayText = "";
        $isEmergencyFriday = \PDR\Database\EmergencyServiceDatabaseHandler::isOurServiceDawn($dateObject);
        if ($isEmergencyFriday) {
            $emergencyFriday = \PDR\Database\EmergencyServiceDatabaseHandler::readEmergencyServiceOnDawn($dateObject);
            $employeeLastName = $emergencyFriday->getEmployeeLastName();
            $fridayText = "<br><span class=hint>" . sprintf(gettext('Emergency Service on Friday for %1$s'), $employeeLastName) . "</span>" . PHP_EOL;
        }
        $isEmergencySaturday = \PDR\Database\EmergencyServiceDatabaseHandler::isOurServiceDay($dateObject);
        if ($isEmergencySaturday) {
            $emergencySaturday = \PDR\Database\EmergencyServiceDatabaseHandler::readEmergencyServiceOnDate($dateObject);
            $employeeLastName = $emergencySaturday->getEmployeeLastName();
            $saturdayText = "<br><span class=hint>" . sprintf(gettext('Emergency Service on Saturday for %1$s'), $employeeLastName) . "</span>" . PHP_EOL;
        }
        $isEmergencySunday = \PDR\Database\EmergencyServiceDatabaseHandler::isOurServiceDay((clone $dateObject)->add(new \DateInterval('P1D')));
        if ($isEmergencySunday) {
            $emergencySunday = \PDR\Database\EmergencyServiceDatabaseHandler::readEmergencyServiceOnDate((clone $dateObject)->add(new \DateInterval('P1D')));
            $employeeLastName = $emergencySunday->getEmployeeLastName();
            $sundayText = "<br><span class=hint>" . sprintf(gettext('Emergency Service on Sunday for %1$s'), $employeeLastName) . "</span>" . PHP_EOL;
        }
        $emergencyServiceHintText = $fridayText . $saturdayText . $sundayText;
        return $emergencyServiceHintText;
    }
}
