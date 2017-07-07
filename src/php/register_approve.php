<?php

/*
 * Copyright (C) 2017 Mandelkow
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
require_once "../../default.php";
require_once "../../head.php";
require '../../navigation.php';
require '../../src/php/pages/menu.php';

if(!$session->user_has_privilege('administration')){
    echo build_warning_messages("",["Die notwendige Berechtigung zur Administration fehlt. Bitte wenden Sie sich an einen Administrator."]);
    die();
}


if ($approve_id = filter_input(INPUT_POST, 'approve', FILTER_SANITIZE_NUMBER_INT)) {
    //activate the user account:
    $statement = $pdo->prepare("UPDATE `users` SET `status` = 'active' WHERE `id` = :id");
    $result = $statement->execute(array('id' => $approve_id));
    //Get information about the user:
    $statement = $pdo->prepare("SELECT * FROM users WHERE `id` = :id");
    $result = $statement->execute();
    $User = $statement->fetch();
    send_mail_about_registration_approval($User["user_name"], $User["email"]);
} elseif ($disapprove_id = filter_input(INPUT_POST, 'disapprove', FILTER_SANITIZE_NUMBER_INT)) {
    $statement = $pdo->prepare("UPDATE `users` SET `status` = 'blocked' WHERE `id` = :id");
    $result = $statement->execute(array('id' => $disapprove_id));
}

$statement = $pdo->prepare("SELECT * FROM users WHERE `status`= 'inactive'");
$result = $statement->execute();
if ($statement->rowCount() > 0) {
    echo "<H1>Inaktive Benutzer</H1>";
    echo "<form method='POST' id='register_approve'>";
    while ($User = $statement->fetch()) {
        echo "<p>" . $User["user_name"] . ", VK " . $User["employee_id"]  . ", " . $User["email"] . ", erstellt: " . $User["created_at"]
        . " <button type='submit' form='register_approve' name=approve value=" . $User["id"] . " title='Benutzer bestätigen'>Bestätigen</button>"
        . " <button type='submit' form='register_approve' name=disapprove value=" . $User["id"] . " title='Benutzer löschen'>Löschen</button>"
        . "</p>";
    }
    echo "</form>";
} else {
    echo "Hier gibt es nichts zu tun.";
}

function send_mail_about_registration_approval($user_name, $recipient) {
    global $config;
    $message_subject = 'Benutzer wurde aktiviert';
    $message_text = "Hallo " . $user_name . ", Sie haben sich im Dienstplanprogramm '"
            . $config["application_name"]
            . "' angemeldet. Die Anmeldung wurde bestätigt. Sie können sich jetzt <a href='"
            . dirname($_SERVER["PHP_SELF"]) . "login.php'>anmelden.</a>"; /*TODO: Insert hostname maybe?*/
    $header = 'From: ' . $config['contact_email'] . "\r\n";
    $header.= 'X-Mailer: PHP/' . phpversion();
    $sent_result = mail($recipient, $message_subject, $message_text, $header);
    if ($sent_result) {
        echo "Die Nachricht wurde versendet. Vielen Dank!<br>\n";
    } else {
        echo "Fehler beim Versenden der Nachricht. Das tut mir Leid.<br>\n";
    }
}

/*
TODO: Add Mail to user here
TODO: Add Mail to admin inside login script
 
 */
