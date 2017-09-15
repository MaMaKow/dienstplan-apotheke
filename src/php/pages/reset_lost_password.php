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
require '../../../default.php';
require_once PDR_FILE_SYSTEM_APPLICATION_PATH . '/src/php/build-warning-messages.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . "/head.php";

if (filter_has_var(INPUT_GET, 'token') and filter_has_var(INPUT_GET, 'employee_id')) {
    $employee_id = filter_input(INPUT_GET, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
    $token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);

    /*
     * Remove expired tokens:
     */
    mysqli_query_verbose("DELETE FROM `users_lost_password_token` WHERE `time_created` <= NOW() - INTERVAL 1 DAY");
    $sql_query = "SELECT `employee_id` FROM `users_lost_password_token` WHERE `token` = UNHEX('$token')";
    //$sql_query = "SELECT *, HEX(token) FROM `users_lost_password_token`";
    $result = mysqli_query_verbose($sql_query);
    $row = mysqli_fetch_object($result);
    if (!empty($row->employee_id) and $employee_id === $row->employee_id) {
        $show_formular = true; //The form is shown
        //mysqli_query_verbose("DELETE FROM `users_lost_password_token` WHERE `employee_id` = $employee_id");
        $statement = $pdo->prepare("SELECT * FROM `users` WHERE `employee_id` = :employee_id");
        $statement->execute(array('employee_id' => $employee_id));
        $user_data = $statement->fetch();
        $user_name = $user_data['user_name'];
    } else {
        build_warning_messages(gettext("Invalid token"));
    }
} else {
    echo "no token";
    $error = false;
    $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
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
            $show_formular = false;
        } else {
            error_log('Beim Abspeichern ist leider ein Fehler aufgetreten' . $statement->errorInfo());
            $Error_message[] = 'Beim Abspeichern ist leider ein Fehler aufgetreten<br>';
        }
    }
}

if ($show_formular) {
    ?>
    <div class=centered_form_div>
        <H1><?= $config['application_name'] ?> </H1>
        <form action="?register = 1" method="post">
            <H2><?= $user_name ?></H2>
            <input type="hidden" name="employee_id" value="<? = $employee_id
                   ?>">
            <input type="password" size="40" name="password" required placeholder="Passwort"><br>
            <input type="password" size="40" maxlength="250" name="password2" required placeholder="Passwort wiederholen" title="Passwort wiederholen"><br><br>
            <?php
            echo build_warning_messages($Error_message, array());
            ?>
            <input type="submit" value="Abschicken">
        </form>
    </div>

    <?php
} //Ende von if($show_formular)

function send_mail_about_updated_password() {
    global $config;
    $message_subject = quoted_printable_encode('Your password has been updated');
    $message_text = quoted_printable_encode("<HTML><BODY>"
            . ""
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
        //print_debug_variable('$message_text', $message_text);
    }
}
?>

</BODY>
</HTML>
