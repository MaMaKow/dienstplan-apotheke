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
require '../../navigation.php';
require '../../src/php/pages/menu.php';
if (!$session->user_has_privilege('administration')) {
    echo build_warning_messages("", ["Die notwendige Berechtigung zur Administration fehlt. Bitte wenden Sie sich an einen Administrator."]);
    die();
}


if ($approve_id = filter_input(INPUT_POST, 'approve', FILTER_SANITIZE_NUMBER_INT)) {
    //activate the user account:
    $statement = $pdo->prepare("UPDATE `users` SET `status` = 'active' WHERE `employee_id` = :employee_id");
    $result = $statement->execute(array('employee_id' => $approve_id));
    //Get information about the user:
    $statement = $pdo->prepare("SELECT * FROM users WHERE `employee_id` = :employee_id");
    $result = $statement->execute(array('employee_id' => $approve_id));
    $User = $statement->fetch();
    send_mail_about_registration_approval($User["user_name"], $User["email"]);
} elseif ($disapprove_id = filter_input(INPUT_POST, 'disapprove', FILTER_SANITIZE_NUMBER_INT)) {
    $statement = $pdo->prepare("UPDATE `users` SET `status` = 'blocked' WHERE `employee_id` = :employee_id");
    $result = $statement->execute(array('employee_id' => $disapprove_id));
}

$statement = $pdo->prepare("SELECT * FROM users WHERE `status`= 'inactive'");
$result = $statement->execute();
if ($statement->rowCount() > 0) {
    echo "<H1>Inaktive Benutzer</H1>";
    echo "<form method='POST' id='register_approve'>";
    while ($User = $statement->fetch()) {
        echo "<p>" . $User["user_name"] . ", VK " . $User["employee_id"] . ", " . $User["email"] . ", erstellt: " . $User["created_at"]
        . " <button type='submit' form='register_approve' name=approve value='" . $User["employee_id"] . "' title='Benutzer bestätigen'>Bestätigen</button>"
        . " <button type='submit' form='register_approve' name=disapprove value=" . $User["employee_id"] . " title='Benutzer löschen'>Löschen</button>"
        . "</p>";
    }
    echo "</form>";
} else {
    echo "Hier gibt es nichts zu tun.";
}

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
            . "https://www." . $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]) . "/login.php' target='_blank'>anmelden.</a>"
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
