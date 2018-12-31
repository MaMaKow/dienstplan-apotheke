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
ini_set('display_errors', TRUE); //Display errors to the end user?
require_once '../../../../bootstrap.php';
$user_dialog = new user_dialog();
$_SESSION['user_object'] = new user(5);
$_SESSION['user_object']->email = $config['contact_email'];
$response = $user_dialog->contact_form_send_mail();
unset($_SESSION['user_object']);
if ($response) {
    /*
     * used by PHPUnit test file to check if the test was passed
     */
    echo "passed";
} else {
    echo "failed";
}
