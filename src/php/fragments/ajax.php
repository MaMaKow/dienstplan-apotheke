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

/**
 * The purpose of this file is to receive general POST information via AJAX.
 * This might be asynchronous delivery of form information.
 */
require_once '../../../default.php';

/**
 * This function is meant to distribute data to the user_page action functions.
 */
function form_input_user_page() {
    $receive_emails_opt_in = filter_input(INPUT_POST, 'receive_emails_opt_in', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if ('true' === $receive_emails_opt_in) {
        return toggle_receive_emails_opt_in(1);
    }
    if ('false' === $receive_emails_opt_in) {
        return toggle_receive_emails_opt_in(0);
    }
    return FALSE;
}

/**
 * Write receive_emails_opt_in into the `users` table of the database
 *
 * @param bool $receive_emails_opt_in If the user wishes to receive emails when the roster is changed.
 * @return bool Success of the database query
 */
//function toggle_receive_emails_opt_in(bool $receive_emails_opt_in) {
function toggle_receive_emails_opt_in(int $receive_emails_opt_in) {
    $user = $_SESSION['user_object'];
    if ($user->set_receive_emails_opt_in($receive_emails_opt_in)) {
        echo "success";
        return TRUE;
    }
    return FALSE;
}

if (filter_has_var(INPUT_POST, 'form')) {
    if ('user_form' === filter_input(INPUT_POST, 'form', FILTER_SANITIZE_FULL_SPECIAL_CHARS)) {
        form_input_user_page();
    }
} elseif (filter_has_var(INPUT_GET, 'saturdayRotationTeamsRemoveTeamId')) {
    $team_id_to_remove = filter_input(INPUT_GET, 'saturdayRotationTeamsRemoveTeamId', FILTER_SANITIZE_NUMBER_INT);
    $branch_id_to_remove = filter_input(INPUT_GET, 'saturdayRotationTeamsRemoveBranchId', FILTER_SANITIZE_NUMBER_INT);
    if (!$session->user_has_privilege(sessions::PRIVILEGE_CREATE_ROSTER)) {
        return null;
    }
    $network_of_branch_offices = new \PDR\Pharmacy\NetworkOfBranchOffices();
    if (false === $network_of_branch_offices->branch_exists($branch_id_to_remove)) {
        return null;
    }
    $saturday_rotation = new saturday_rotation($branch_id_to_remove);

    if (!array_key_exists($team_id_to_remove, $saturday_rotation->List_of_teams)) {
        return null;
    }
    $saturday_rotation->remove_team_from_database($branch_id_to_remove, $team_id_to_remove);
}
