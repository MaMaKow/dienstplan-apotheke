<?php

/*
 * Copyright (C) 2017 Mandelkow
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
require_once "../../default.php";
require_once "../../head.php";
require '../../src/php/pages/menu.php';
$session->exit_on_missing_privilege('administration');


if ($approve_id = filter_input(INPUT_POST, 'approve', FILTER_SANITIZE_NUMBER_INT)) {
    //activate the user account:
    $user = new user($approve_id);
    if ($user->activate()) {
        send_mail_about_registration_approval($user->user_name, $user->email);
    }
} elseif ($disapprove_id = filter_input(INPUT_POST, 'disapprove', FILTER_SANITIZE_NUMBER_INT)) {
    $user = new user($disapprove_id);
    $user->block();
}

$sql_query = "SELECT * FROM `users`";
$result = database_wrapper::instance()->run($sql_query);
echo "<H1>Inaktive Benutzer</H1>";
echo "<form method='POST' id='register_approve'>";
echo "<table id='register_approve_table'>";
echo "<tr>"
 . "<th>" . gettext('user name') . "</th>"
 . "<th>" . gettext('employee id') . "</th>"
 . "<th> " . gettext('email') . "</th>"
 . "<th>" . gettext('created at') . "</th>"
 . "<th>" . gettext('status') . "</th>"
 . "</tr>";
while ($user = $result->fetch(PDO::FETCH_OBJ)) {
    $approve_button_activity = 'disabled';
    if ('inactive' === $user->status) {
        $approve_button_activity = '';
    }
    $disapprove_button_activity = 'disabled';
    if ('active' === $user->status) {
        $disapprove_button_activity = '';
    }
    echo "<tr>"
    . "<td>" . $user->user_name . "</td>"
    . "<td>" . $user->employee_id . "</td>"
    . "<td> " . $user->email . "</td>"
    . "<td>" . $user->created_at . "</td>"
    . "<td>" . $user->status . "</td>"
    . "<td><button type='submit' form='register_approve' name=approve value='" . $user->employee_id . "' title='Benutzer bestätigen' $approve_button_activity>Bestätigen</button></td>"
    . "<td><button type='submit' form='register_approve' name=disapprove value=" . $user->employee_id . " title='Benutzer löschen'>Löschen</button></td>"
    . "</tr>";
}
echo "</table>";
echo "</form>";

function send_mail_about_registration_approval($user_name, $recipient) {
    global $config;
    if (isset($config['application_name'])) {
        $application_name = $config['application_name'];
    } else {
        $application_name = 'PDR';
    }


    $message_subject = quoted_printable_encode('Benutzer wurde aktiviert');
    $message_text = quoted_printable_encode("<HTML><BODY>"
            . "Hallo!\n Der Benutzer " . $user_name . ", ist im Dienstplanprogramm '"
            . $application_name
            . "' angemeldet. Die Anmeldung wurde durch einen Administrator bestätigt. Sie können sich jetzt <a href='"
            . "https://" . $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]) . "/login.php' target='_blank'>anmelden.</a>"
            . "</BODY></HTML>");
    $headers = 'From: ' . $config['contact_email'] . "\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: quoted-printable";

    $sent_result = mail($recipient, $message_subject, $message_text, $headers);
    if ($sent_result) {
        echo "Die Nachricht wurde versendet. Vielen Dank!<br>\n";
    } else {
        echo "Fehler beim Versenden der Nachricht. Das tut mir Leid.<br>\n";
    }
}
