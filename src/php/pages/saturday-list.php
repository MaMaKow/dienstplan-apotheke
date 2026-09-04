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

require '../../../default.php';
$year = user_input::get_variable_from_any_input('year', FILTER_SANITIZE_SPECIAL_CHARS, date('Y'));
\PDR\Utility\GeneralUtility::createCookie("year", $year, 1);
$dateObjectStart = new DateTime("first sat of jan $year");
$dateObjectEnd = new DateTime("last sat of dec $year");
$holidays = new \PDR\DateTime\Holidays();
$network_of_branch_offices = new \PDR\Pharmacy\NetworkOfBranchOffices;
$branch_id = user_input::get_variable_from_any_input("mandant", FILTER_SANITIZE_NUMBER_INT, $network_of_branch_offices->get_main_branch_id());
\PDR\Utility\GeneralUtility::createCookie("mandant", $branch_id, 30);

$user_dialog = new user_dialog();

$html_select_year = form_element_builder::build_html_select_year($year);
$List_of_branch_objects = $network_of_branch_offices->get_list_of_branch_objects();
$html_select_branch = build_html_navigation_elements::build_select_branch($branch_id, $List_of_branch_objects);

$table_head = "<thead>\n";
$table_head .= "<tr>";
$table_head .= "<th>" . gettext("Date") . "</th>";
$table_head .= "<th>" . gettext("Team") . "</th>";
$table_head .= "<th>" . gettext("Team members") . "</th>";
$table_head .= "<th>" . gettext("Scheduled in roster") . "</th>\n";
$table_head .= "<th>" . gettext("Absent") . "</th>\n";
$table_head .= "</tr>\n";
$table_head .= "</thead>\n";
$table_body = "<tbody>\n";
for ($dateObject = clone $dateObjectStart; $dateObject <= $dateObjectEnd; $dateObject->add(new DateInterval('P7D'))) {
    $table_row = PDR\Output\HTML\SaturdayListHtmlBuilder::buildTableRow($dateObject, $branch_id, $holidays);
    $table_body .= $table_row;
}
$table_body .= "</tbody>\n";

$table = "<table id=saturdayList>\n";
$table .= $table_head;
$table .= $table_body;
$table .= "</table>\n";

$html = '';
$html .= $html_select_year;
$html .= $html_select_branch;
$html .= $user_dialog->build_messages();
$html .= $table;

require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';

echo $html;

function get_saturday_rotation_team_member_names_span(saturday_rotation $saturdayRotation, PDR\Workforce\Workforce $workforce, PDR\Roster\AbsenceCollection $absenceCollection) {
    $SaturdayRotationTeamMemberIds = array();
    $saturdayRotationTeamId = $saturdayRotation->team_id;
    if (NULL !== $saturdayRotationTeamId and FALSE !== $saturdayRotationTeamId and array_key_exists($saturdayRotationTeamId, $saturdayRotation->List_of_teams)) {
        $SaturdayRotationTeamMemberIds = $saturdayRotation->List_of_teams[$saturdayRotationTeamId];
    }

    $SaturdayRotationTeamMemberNames = array();
    foreach ($SaturdayRotationTeamMemberIds as $employeeKey) {

        if (isset($workforce->getListOfEmployees()[$employeeKey]) and !empty($workforce->getListOfEmployees()[$employeeKey]->getLastName())) {
            $prefix = '<span>';
            $suffix = '</span>';
            if ($absenceCollection->containsEmployeeKey($employeeKey)) {
                $prefix = '<span class="absent">';
                $suffix = "&nbsp;(" . \PDR\Utility\AbsenceUtility::getReasonStringLocalized($absenceCollection->getAbsenceByEmployeeKey($employeeKey)->getReasonId()) . ')</span>';
            }

            $SaturdayRotationTeamMemberNames[] = $prefix . $workforce->getListOfEmployees()[$employeeKey]->getLastName() . $suffix;
        } else {
            $SaturdayRotationTeamMemberNames[] = "$employeeKey???";
        }
    }
    return $SaturdayRotationTeamMemberNames;
}

function getRosteredEmployeesNames(array $Roster, PDR\Workforce\Workforce $workforce, PDR\Roster\AbsenceCollection $absenceCollection): array {
    $RosteredEmployees = array();
    foreach ($Roster as $RosterDayArray) {
        foreach ($RosterDayArray as $rosterItem) {
            if (isset($workforce->getListOfEmployees()[$rosterItem->employee_key]) and !empty($workforce->getListOfEmployees()[$rosterItem->employee_key]->getLastName())) {
                $prefix = '<span>';
                $suffix = '</span>';
                if ($absenceCollection->containsEmployeeKey($rosterItem->employee_key)) {
                    $prefix = '<span class="absent">';
                    $suffix = "&nbsp;(" . \PDR\Utility\AbsenceUtility::getReasonStringLocalized($absenceCollection->getAbsenceByEmployeeKey($rosterItem->employee_key)->getReasonId()) . ')</span>';
                }
                $RosteredEmployees[$rosterItem->employee_key] = $prefix . $workforce->getListOfEmployees()[$rosterItem->employee_key]->getLastName() . $suffix;
            }
        }
    }
    return $RosteredEmployees;
}

function getAbsentEmployeesInfo(\DateTime $date_object, int $branch_id, PDR\Workforce\Workforce $workforce, PDR\Roster\AbsenceCollection $absenceCollection): array {
    $absentEmployees = array();

    // Mitarbeiter mit regulären Abwesenheiten (Urlaub, Krankheit, etc.)
    foreach ($absenceCollection->getIterator() as $absence) {
        $employeeKey = $absence->getEmployeeKey();
        if (isset($workforce->getListOfEmployees()[$employeeKey])) {
            $reasonString = \PDR\Utility\AbsenceUtility::getReasonStringLocalized($absence->getReasonId());
            $absentEmployees[$employeeKey] = $workforce->getListOfEmployees()[$employeeKey]->getLastName() . " (" . $reasonString . ")";
        }
    }

    // Mitarbeiter, die am Vortag Notdienst hatten
    if (\PDR\Database\EmergencyServiceDatabaseHandler::isOurServiceDawn($date_object)) {
        try {
            $emergencyService = \PDR\Database\EmergencyServiceDatabaseHandler::readEmergencyServiceOnDawn($date_object);
            $employeeKey = $emergencyService->getEmployeeKey();

            if (NULL !== $employeeKey && isset($workforce->getListOfEmployees()[$employeeKey])) {
                // Nur hinzufügen, wenn nicht bereits wegen anderer Abwesenheit erfasst
                if (!isset($absentEmployees[$employeeKey])) {
                    $absentEmployees[$employeeKey] = $workforce->getListOfEmployees()[$employeeKey]->getLastName() . " (" . gettext("Emergency service dawn") . ")";
                }
            }
        } catch (\Exception $e) {
            // Kein Notdienst gefunden, ignorieren
        }
    }

    return $absentEmployees;
}

function build_table_row(\DateTime $date_object, int $branch_id) {
    $saturday_rotation = new \saturday_rotation($branch_id);
    $saturday_rotation->get_participation_team_id($date_object);
    $workforce = new PDR\Workforce\Workforce($date_object->format('Y-m-d'));
    $absenceCollection = PDR\Database\AbsenceDatabaseHandler::readAbsenteesOnDate($date_object->format('Y-m-d'));

    $Roster = roster::read_roster_from_database($branch_id, $date_object->format('Y-m-d'));

    $table_row = "";
    $holiday = holidays::is_holiday($date_object);
    $configuration = new \PDR\Application\configuration();
    $locale = $configuration->getLanguage();
    $dayFormatter = new \IntlDateFormatter($locale, \IntlDateFormatter::FULL, \IntlDateFormatter::NONE);
    $dayFormatter->setPattern('EEE dd.MM.YYYY');

    $date_string = $dayFormatter->format($date_object->getTimestamp());
    if (FALSE !== $holiday) {
        $table_row .= "<tr class='saturday-list-row-holiday'>";
        $table_row .= "<td colspan='99'>";
        $table_row .= $date_string;
        $table_row .= "&nbsp;<span>" . $holiday . "</span>";
        $table_row .= "</td>";
        $table_row .= "</tr>\n";
    } else {
        $Rostered_employees_names = getRosteredEmployeesNames($Roster, $workforce, $absenceCollection);
        $rostered_employees_names_string = implode(', ', $Rostered_employees_names);
        $Saturday_rotation_team_member_names = get_saturday_rotation_team_member_names_span($saturday_rotation, $workforce, $absenceCollection);
        $saturday_rotation_team_member_names_string = implode(', ', $Saturday_rotation_team_member_names);

        // Abwesende Mitarbeiter ermitteln
        $absentEmployees = getAbsentEmployeesInfo($date_object, $branch_id, $workforce, $absenceCollection);
        $absent_employees_string = !empty($absentEmployees) ? implode(', ', $absentEmployees) : '&nbsp;';

        $table_row .= "<tr>";
        $table_row .= "<td>";
        $table_row .= $date_string;
        $table_row .= "</td>";
        $table_row .= "<td>" . $saturday_rotation->team_id . "</td>";
        $table_row .= "<td>" . $saturday_rotation_team_member_names_string . "</td>";
        $table_row .= "<td>" . $rostered_employees_names_string . "</td>";
        $table_row .= "<td><del>" . $absent_employees_string . "</del></td>";
        $table_row .= "</tr>\n";
    }
    return $table_row;
}
