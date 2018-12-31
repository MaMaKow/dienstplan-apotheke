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
require_once '../../../default.php';
$Roster = array();
$day_iterator = user_input::get_variable_from_any_input('day_iterator', FILTER_SANITIZE_NUMBER_INT);
$roster_row_iterator = user_input::get_variable_from_any_input('roster_row_iterator', FILTER_SANITIZE_NUMBER_INT);
$maximum_number_of_rows = user_input::get_variable_from_any_input('maximum_number_of_rows', FILTER_SANITIZE_NUMBER_INT, min(array_keys($List_of_branch_objects)));
$branch_id = user_input::get_variable_from_any_input('branch_id', FILTER_SANITIZE_NUMBER_INT, min(array_keys($List_of_branch_objects)));


$html_string = build_html_roster_views::build_roster_input_row($Roster, $day_iterator, $roster_row_iterator, $maximum_number_of_rows, $branch_id, array('add_select_employee'));
echo $html_string;
