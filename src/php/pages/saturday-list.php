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

$year = user_input::get_variable_from_any_input('year', FILTER_SANITIZE_STRING, date('Y'));
create_cookie("year", $year, 1);
$date_object_start = new DateTime("first sat of jan $year");
$date_object_end = new DateTime("last sat of dec $year");

$network_of_branch_offices = new \PDR\Pharmacy\NetworkOfBranchOffices;
$branch_id = user_input::get_variable_from_any_input("mandant", FILTER_SANITIZE_NUMBER_INT, $network_of_branch_offices->get_main_branch_id());
create_cookie("mandant", $branch_id, 30);

$user_dialog = new user_dialog();
$user_dialog->add_message("Saturday rotation is a deprecated feature. This page will be removed in a later version. Please write an email to pdr-issues@martin-mandelkow.de if you depend on this feature.", E_USER_DEPRECATED);

$html_select_year = form_element_builder::build_html_select_year($year);
$List_of_branch_objects = $network_of_branch_offices->get_list_of_branch_objects();
$html_select_branch = build_html_navigation_elements::build_select_branch($branch_id, $List_of_branch_objects);

$table_head = "<thead>\n";
$table_head .= "<tr>";
$table_head .= "<th>" . gettext("Date") . "</th>";
$table_head .= "<th>" . gettext("Team") . "</th>";
$table_head .= "<th>" . gettext("Team members") . "</th>";
$table_head .= "<th>" . gettext("Scheduled in roster") . "</th>\n";
$table_head .= "</tr>\n";
$table_head .= "</thead>\n";
$table_body = "<tbody>\n";
for ($date_object = clone $date_object_start; $date_object <= $date_object_end; $date_object->add(new DateInterval('P7D'))) {
    $table_row = \build_table_row($date_object, $branch_id);
    $table_body .= $table_row;
}
$table_body .= "</tbody>\n";

$table = "<table id=saturday_list>\n";
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

function get_saturday_rotation_team_member_names(saturday_rotation $saturday_rotation, workforce $workforce, array $Absentees) {
    $Saturday_rotation_team_member_ids = array();
    $saturday_rotation_team_id = $saturday_rotation->team_id;
    if (NULL !== $saturday_rotation_team_id and FALSE !== $saturday_rotation_team_id) {
        $Saturday_rotation_team_member_ids = $saturday_rotation->List_of_teams[$saturday_rotation_team_id];
    }

    $Saturday_rotation_team_member_names = array();
    foreach ($Saturday_rotation_team_member_ids as $employee_id) {

        if (isset($workforce->List_of_employees[$employee_id]->last_name)) {
            $prefix = '<span>';
            $suffix = '</span>';
            if (in_array($employee_id, array_keys($Absentees))) {
                $prefix = '<span class="absent">';
                $suffix = "&nbsp;(" . gettext($Absentees[$employee_id]) . ')</span>';
            }

            $Saturday_rotation_team_member_names[] = $prefix . $workforce->List_of_employees[$employee_id]->last_name . $suffix;
        } else {
            $Saturday_rotation_team_member_names[] = "$employee_id???";
        }
    }
    return $Saturday_rotation_team_member_names;
}

function get_rostered_employees_names(array $Roster, workforce $workforce, array $Absentees) {
    $Rostered_employees = array();
    foreach ($Roster as $Roster_day_array) {
        foreach ($Roster_day_array as $roster_item) {
            if (isset($workforce->List_of_employees[$roster_item->employee_id]->last_name)) {
                $prefix = '<span>';
                $suffix = '</span>';
                if (in_array($roster_item->employee_id, array_keys($Absentees))) {
                    $prefix = '<span class="absent">';
                    $suffix = "&nbsp;(" . gettext($Absentees[$roster_item->employee_id]) . ')</span>';
                }
                $Rostered_employees[$roster_item->employee_id] = $prefix . $workforce->List_of_employees[$roster_item->employee_id]->last_name . $suffix;
            }
        }
    }
    return $Rostered_employees;
}

function build_table_row(DateTime $date_object, int $branch_id) {
    $saturday_rotation = new saturday_rotation($branch_id);
    $saturday_rotation->get_participation_team_id($date_object);
    $workforce = new workforce($date_object->format('Y-m-d'));
    $Absentees = absence::read_absentees_from_database($date_object->format('Y-m-d'));
    $Roster = roster::read_roster_from_database($branch_id, $date_object->format('Y-m-d'));


    $table_row = "";
    $holiday = holidays::is_holiday($date_object);
    $date_string = strftime('%a %x', $date_object->getTimestamp());
    if (FALSE !== $holiday) {
        $table_row .= "<tr class='saturday_list_row_holiday'>";
        $table_row .= "<td colspan='99'>";
        $table_row .= $date_string;
        $table_row .= "&nbsp;<span>" . $holiday . "</span>";
        $table_row .= "</td>";
        $table_row .= "</tr>\n";
    } else {
        $Rostered_employees_names = get_rostered_employees_names($Roster, $workforce, $Absentees);
        $rostered_employees_names_string = implode(', ', $Rostered_employees_names);
        $Saturday_rotation_team_member_names = get_saturday_rotation_team_member_names($saturday_rotation, $workforce, $Absentees);
        $saturday_rotation_team_member_names_string = implode(', ', $Saturday_rotation_team_member_names);
        $table_row .= "<tr>";
        $table_row .= "<td>";
        $table_row .= $date_string;
        $table_row .= "</td>";
        $table_row .= "<td>" . $saturday_rotation->team_id . "</td>";
        $table_row .= "<td>" . $saturday_rotation_team_member_names_string . "</td>";
        $table_row .= "<td>" . $rostered_employees_names_string . "</td>";
        $table_row .= "</tr>\n";
    }
    return $table_row;
}
