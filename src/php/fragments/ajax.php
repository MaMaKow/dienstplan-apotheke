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

/**
 * The purpose of this file is to receive general POST information via AJAX.
 * This might be asynchronous delivery of form information.
 */
require_once '../../../default.php';

/**
 * Write receive_emails_opt_in into the `users` table of the database
 *
 * @param bool $receive_emails_opt_in If the user wishes to receive emails when the roster is changed.
 * @return bool Success of the database query
 */
//function toggle_receive_emails_opt_in(bool $receive_emails_opt_in) {
function toggle_receive_emails_opt_in(int $receive_emails_opt_in) {
    $sql_query = "UPDATE `users` "
            . "SET `receive_emails_on_changed_roster` = :receive_emails_opt_in "
            . "WHERE `employee_id` = :employee_id";
    $result = database_wrapper::instance()->run($sql_query, array(
        'receive_emails_opt_in' => $receive_emails_opt_in,
        'employee_id' => $_SESSION['user_employee_id']
    ));
    if ('00000' === $result->errorInfo()[0]) {
        echo "success";
        return TRUE;
    }
    return FALSE;
}

/**
 * This function is meant to distribute data to the user_page action functions.
 */
function form_input_user_page() {
    $receive_emails_opt_in = filter_input(INPUT_POST, 'receive_emails_opt_in', FILTER_SANITIZE_STRING);
    if ('true' === $receive_emails_opt_in) {
        return toggle_receive_emails_opt_in(1);
    }
    if ('false' === $receive_emails_opt_in) {
        return toggle_receive_emails_opt_in(0);
    }
    return FALSE;
}

if (filter_has_var(INPUT_POST, 'form')) {
    if ('user_form' === filter_input(INPUT_POST, 'form', FILTER_SANITIZE_STRING)) {
        form_input_user_page();
    }
}
