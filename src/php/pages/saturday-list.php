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
$table_head .= "</tr>\n";
$table_head .= "</thead>\n";
$table_body = "<tbody>\n";
for ($dateObject = clone $dateObjectStart; $dateObject <= $dateObjectEnd; $dateObject->add(new DateInterval('P7D'))) {
    $table_row = PDR\Output\HTML\SaturdayListHtmlBuilder::buildTableRow($dateObject, $branch_id);
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
