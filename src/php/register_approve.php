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

if($approve_id = filter_input(INPUT_POST, 'approve', FILTER_SANITIZE_NUMBER_INT)){
    $statement = $pdo->prepare("UPDATE `users` SET `status` = 'active' WHERE `id` = :id");
    $result = $statement->execute(array('id' => $approve_id));
} elseif($disapprove_id = filter_input(INPUT_POST, 'disapprove', FILTER_SANITIZE_NUMBER_INT)) {
    $statement = $pdo->prepare("UPDATE `users` SET `status` = 'blocked' WHERE `id` = :id");
    $result = $statement->execute(array('id' => $disapprove_id));
}

$statement = $pdo->prepare("SELECT * FROM users WHERE `status`= 'inactive'");
$result = $statement->execute();
if ($statement->rowCount() > 0) {
    echo "<H1>Inaktive Benutzer</H1>";
    echo "<form method='POST' id='register_approve'>";
    while ($User = $statement->fetch()) {
        echo "<p>" . $User["user_name"] . ", " . $User["email"] . ", erstellt: " . $User["created_at"]
        . " <button type='submit' form='register_approve' name=approve value=" . $User["id"] . " title='Benutzer bestätigen'>Bestätigen</button>"
        . " <button type='submit' form='register_approve' name=disapprove value=" . $User["id"] . " title='Benutzer löschen'>Löschen</button>"
        . "</p>";
    }
    echo "</form>";
} else {
    echo "Hier gibt es nichts zu tun.";
}
 