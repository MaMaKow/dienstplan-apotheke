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
require '../../default.php';
require "../../head.php";
$showFormular = true; //Variable ob das Registrierungsformular anezeigt werden soll

if (isset($_GET['register'])) {
    $error = false;
    $user_name = filter_input(INPUT_POST, 'user_name', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
    $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    if (strlen($password) == 0) {
        echo 'Bitte ein Passwort angeben<br>';
        $error = true;
    }
    if ($password != $password2) {
        echo 'Die Passwörter müssen übereinstimmen<br>';
        $error = true;
    }

    //Überprüfe, dass der Benutzer noch nicht registriert wurde
    if (!$error) {
        $statement = $pdo->prepare("SELECT * FROM users WHERE user_name = :user_name");
        $result = $statement->execute(array('user_name' => $user_name));
        $user = $statement->fetch();

        if ($user !== false) {
            echo 'Dieser Benutzername ist bereits vergeben<br>';
            $error = true;
        }
    }

    //Keine Fehler, wir können den Nutzer registrieren
    if (!$error) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $statement = $pdo->prepare("INSERT INTO users (user_name, employee_id, password, email, status) VALUES (:user_name, :employee_id, :password, :email, 'inactive')");
        $result = $statement->execute(array('user_name' => $user_name, 'employee_id' => $employee_id, 'password' => $password_hash, 'email' => $email));

        if ($result) {
            echo 'Sie wurden erfolgreich registriert. Sobald Ihr Benutzer freigeschaltet ist, können Sie sich <a href="login.php">einloggen.</a>';
            $showFormular = false;
        } else {
            echo 'Beim Abspeichern ist leider ein Fehler aufgetreten<br>';
        }
    }
}

if ($showFormular) {
    echo "<div class=centered_form_div>";
    echo "<H1>" . $config['application_name'] . "</H1>\n";
    ?>
    <form action="?register=1" method="post">
        <input type="text" size="40" maxlength="250" name="user_name" required placeholder="Benutzername"><br>
        <input type="text" size="40" maxlength="250" name="employee_id" required placeholder="VK Nummer"><br>
        <input type="email" size="40" maxlength="250" name="email" required placeholder="Email"><br>
        <input type="password" size="40" name="password" required placeholder="Passwort"><br>
        <input type="password" size="40" maxlength="250" name="password2" required placeholder="Passwort wiederholen" title="Passwort wiederholen"><br><br>
        <input type="submit" value="Abschicken">
    </form>
<p class="hint">Nach der Anmeldung wird der Benutzer zunächst überprüft. Dies kann eine Weile dauern. Wir informieren Sie nach Abschluss der Prüfung per Email.</p>
    </div>

    <?php
} //Ende von if($showFormular)
function send_mail_about_registration($user_name) {
    global $config;
    $message_subject = 'Neuer Benutzer wurde angelegt';
    $message_text = "Sehr geehrter Administrator,\n\n Im Dienstplanprogramm '"
            . $config["application_name"]
            . "' hat sich ein Benutzer angemeldet. Die Anmeldung muss zunächst <a href='"
            . dirname($_SERVER["PHP_SELF"]) . "register_approve.php'>bestätigt werden.</a>";/*TODO: Insert hostname maybe?*/
    $header = 'From: ' . $config['contact_email'] . "\r\n";
    $header.= 'X-Mailer: PHP/' . phpversion();
    $sent_result = mail($config['contact_email'], $message_subject, $message_text, $header);
    if ($sent_result) {
        echo "Die Nachricht wurde versendet. Vielen Dank!<br>\n";
    } else {
        echo "Fehler beim Versenden der Nachricht. Das tut mir Leid.<br>\n";
    }
}

?>

</body>
</html>