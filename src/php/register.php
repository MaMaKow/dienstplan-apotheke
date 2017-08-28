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
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
    $password2 = filter_input(INPUT_POST, 'password2', FILTER_UNSAFE_RAW);

    if (strlen($password) == 0) {
        $Error_message[] = 'Bitte ein Passwort angeben<br>';
        $error = true;
    }
    if ($password != $password2) {
        $Error_message[] = 'Die Passwörter müssen übereinstimmen';
        $error = true;
    }

    //Überprüfe, dass der Benutzer noch nicht registriert wurde
    if (!$error) {
        $statement = $pdo->prepare("SELECT * FROM users WHERE user_name = :user_name");
        $result = $statement->execute(array('user_name' => $user_name));
        $user = $statement->fetch();

        if ($user !== false) {
            $Error_message[] = 'Dieser Benutzername ist bereits vergeben<br>';
            $error = true;
        }
    }

    //Keine Fehler, wir können den Nutzer registrieren
    if (!$error) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $statement = $pdo->prepare("INSERT INTO users (user_name, employee_id, password, email, status) VALUES (:user_name, :employee_id, :password, :email, 'inactive')");
        $result = $statement->execute(array('user_name' => $user_name, 'employee_id' => $employee_id, 'password' => $password_hash, 'email' => $email));

        if ($result) {
            send_mail_about_registration();
            echo 'Sie wurden erfolgreich registriert. Sobald Ihr Benutzer freigeschaltet ist, können Sie sich <a href="login.php">einloggen.</a>';
            $showFormular = false;
        } else {
            error_log('Beim Abspeichern ist leider ein Fehler aufgetreten' . $statement->errorInfo());
            $Error_message[] = 'Beim Abspeichern ist leider ein Fehler aufgetreten<br>';
        }
    }
}

if ($showFormular) {
    echo "<div class=centered_form_div>";
    echo "<H1>" . $config['application_name'] . "</H1>\n";
    ?>
    <form action="?register=1" method="post">
        <input type="text" size="40" maxlength="250" name="user_name" required placeholder="Benutzername" value="<?= $user_name ?>"><br>
        <input type="text" size="40" maxlength="250" name="employee_id" required placeholder="VK Nummer" value="<?= $employee_id ?>"><br>
        <input type="email" size="40" maxlength="250" name="email" required placeholder="Email" value="<?= $email ?>"><br>
        <input type="password" size="40" name="password" required placeholder="Passwort"><br>
        <input type="password" size="40" maxlength="250" name="password2" required placeholder="Passwort wiederholen" title="Passwort wiederholen"><br><br>
        <?php
        require_once PDR_FILE_SYSTEM_APPLICATION_PATH . '/src/php/build-warning-messages.php';
        echo build_warning_messages($Error_message, array());
        ?>
        <input type="submit" value="Abschicken">
    </form>
    <p class="hint">Nach der Anmeldung wird der Benutzer zunächst überprüft. Dies kann eine Weile dauern. Wir informieren Sie nach Abschluss der Prüfung per Email.</p>
    </div>

    <?php
} //Ende von if($showFormular)

function send_mail_about_registration() {
    global $config;
    $message_subject = quoted_printable_encode('Neuer Benutzer wurde angelegt');
    $message_text = quoted_printable_encode("<HTML><BODY>"
            . "Sehr geehrter Administrator,\n\n Im Dienstplanprogramm '"
            . $config["application_name"]
            . "' hat sich ein Benutzer angemeldet. Die Anmeldung muss zunächst <a href='"
            . "https://www." . $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]) . "/register_approve.php'>bestätigt werden.</a>" 
            . "</BODY></HTML>");
    $headers = 'From: ' . $config['contact_email'] . "\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: quoted-printable";

    $sent_result = mail($config['contact_email'], $message_subject, $message_text, $headers);
    if ($sent_result) {
        echo "Die Nachricht wurde versendet. Vielen Dank!<br>\n";
    } else {
        echo "Fehler beim Versenden der Nachricht. Das tut mir Leid.<br>\n";
    }
}
?>

</body>
</html>
