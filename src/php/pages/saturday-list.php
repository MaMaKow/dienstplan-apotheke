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

require '../../../default.php';

$year = user_input::get_variable_from_any_input('year', FILTER_SANITIZE_STRING, date('Y'));
create_cookie("year", $year, 1);
$start_date_unix = strtotime("first sat of jan $year");
$end_date_unix = strtotime("last sat of dec $year");
$date_unix = $start_date_unix;
$date_sql = date('Y-m-d', $date_unix);

$branch_id = user_input::get_variable_from_any_input("mandant", FILTER_SANITIZE_NUMBER_INT, min(array_keys($List_of_branch_objects)));
create_cookie("mandant", $branch_id, 30);


$saturday_rotation = new saturday_rotation($branch_id);

$html_select_year = absence::build_html_select_year($year);
$html_select_branch = build_html_navigation_elements::build_select_branch($branch_id, $date_sql);

$table_head = "<thead>\n";
$table_head .= "<tr>";
$table_head .= "<th>" . gettext("Date") . "</th>";
$table_head .= "<th>" . gettext("Team") . "</th>";
$table_head .= "<th>" . gettext("Team members") . "</th>";
$table_head .= "<th>" . gettext("Scheduled in roster") . "</th>\n";
$table_head .= "</tr>\n";
$table_head .= "</thead>\n";
$table_body = "<tbody>\n";
for ($date_unix = $start_date_unix; $date_unix <= $end_date_unix; $date_unix += PDR_ONE_DAY_IN_SECONDS * 7) {
    $date_string = strftime('%a %x', $date_unix);
    $date_sql = date('Y-m-d', $date_unix);

    $workforce = new workforce($date_sql);
    $Absentees = absence::read_absentees_from_database($date_sql);
    $Roster = roster::read_roster_from_database($branch_id, $date_sql);

    $saturday_rotation_team_id = $saturday_rotation->get_participation_team_id($date_sql);

    $Saturday_rotation_team_member_ids = $saturday_rotation->List_of_teams[$saturday_rotation_team_id];
    $Saturday_rotation_team_member_names = array();
    foreach ($Saturday_rotation_team_member_ids as $employee_id) {
        if (isset($workforce->List_of_employees[$employee_id]->last_name)) {
            $prefix = '<span>';
            $suffix = '</span>';
            if (in_array($employee_id, array_keys($Absentees))) {
                $prefix = '<span class="absent">';
                $suffix = "&nbsp;(" . substr($Absentees[$employee_id], 0, 4) . ')</span>';
            }

            $Saturday_rotation_team_member_names[] = $prefix . $workforce->List_of_employees[$employee_id]->last_name . $suffix;
        } else {
            $Saturday_rotation_team_member_names[] = "$employee_id???";
        }
    }

    $Rostered_employees = array();
    foreach ($Roster as $Roster_day_array) {
        foreach ($Roster_day_array as $roster_item) {
            if (isset($workforce->List_of_employees[$roster_item->employee_id]->last_name)) {
                $prefix = '<span>';
                $suffix = '</span>';
                if (in_array($roster_item->employee_id, array_keys($Absentees))) {
                    $prefix = '<span class="absent">';
                    $suffix = "&nbsp;(" . substr($Absentees[$roster_item->employee_id], 0, 4) . ')</span>';
                }
                $Rostered_employees[$roster_item->employee_id] = $prefix . $workforce->List_of_employees[$roster_item->employee_id]->last_name . $suffix;
            }
        }
    }

    $saturday_rotation_team_member_names_string = implode(', ', $Saturday_rotation_team_member_names);
    $rostered_employees_names_string = implode(', ', $Rostered_employees);
    //print_debug_variable($saturday_rotation);

    $table_row = "";
    $table_row .= "<tr>";
    $table_row .= "<td>" . $date_string . "</td>";
    $table_row .= "<td>" . $saturday_rotation->team_id . "</td>";
    $table_row .= "<td>" . $saturday_rotation_team_member_names_string . "</td>";
    $table_row .= "<td>" . $rostered_employees_names_string . "</td>";
    $table_row .= "</tr>\n";
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
$html .= $table;


require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';

echo $html;
