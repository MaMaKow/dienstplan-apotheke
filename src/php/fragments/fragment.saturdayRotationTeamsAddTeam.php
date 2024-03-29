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
$branch_id = user_input::get_variable_from_any_input('mandant', FILTER_SANITIZE_NUMBER_INT, $network_of_branch_offices->get_main_branch_id());
$team_id = user_input::get_variable_from_any_input('team_id', FILTER_SANITIZE_NUMBER_INT, 0);
$saturday_date_string = user_input::get_variable_from_any_input('saturday_date_string', FILTER_SANITIZE_NUMBER_INT, (new DateTime("this saturday"))->format('Y-m-d'));
$saturday_date_object = new DateTime($saturday_date_string);

$saturday_rotation = new saturday_rotation($branch_id);
$html_string = $saturday_rotation->buildSaturdayRotationTeamsAddTeam($team_id, $branch_id, $saturday_date_object, $session);

echo $html_string;
