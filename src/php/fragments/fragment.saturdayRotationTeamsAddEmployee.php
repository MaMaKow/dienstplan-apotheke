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
$network_of_branch_offices = new \PDR\Pharmacy\NetworkOfBranchOffices();
$List_of_branch_objects = $network_of_branch_offices->get_list_of_branch_objects();

$branch_id = user_input::get_variable_from_any_input('branch_id', FILTER_SANITIZE_NUMBER_INT, min(array_keys($List_of_branch_objects)));
$team_id = user_input::get_variable_from_any_input('team_id', FILTER_SANITIZE_NUMBER_INT, 0);

$saturday_rotation = new saturday_rotation($branch_id);
$html_string = $saturday_rotation->buildSaturdayRotationTeamsAddEmployee($team_id, $branch_id, $session);

echo $html_string;
