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
require '../../default.php';
require "../../head.php";
$show_form = true; //Variable ob das Registrierungsformular anezeigt werden soll
$user_dialog = new user_dialog();

if (filter_has_var(INPUT_GET, 'register')) {
    $error = false;
    $user_name = filter_input(INPUT_POST, 'user_name', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
    $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
    $password2 = filter_input(INPUT_POST, 'password2', FILTER_UNSAFE_RAW);

    if (strlen($password) == 0) {
        $user_dialog->add_message(gettext('Please enter a password!'));
        $error = true;
    }
    if ($password != $password2) {
        $user_dialog->add_message(gettext('The passwords must match!'));
        $error = true;
    }

    //Überprüfe, dass der Benutzer noch nicht registriert wurde
    if (!$error) {
        $sql_query = "SELECT * FROM users WHERE `user_name` = :user_name";
        $result = database_wrapper::instance()->run($sql_query, array('user_name' => $user_name));
        $user = $result->fetch();

        if ($user !== false) {
            $user_dialog->add_message(gettext('This username is already taken.'));
            $error = true;
        }
        $sql_query = "SELECT * FROM users WHERE `employee_id` = :employee_id";
        $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id));
        $user = $result->fetch();

        if ($user !== false) {
            $user_dialog->add_message(gettext('There is already a user existing for this employee id.'));
            $error = true;
        }
    }

    if (!$error) {
        /**
         * No errors, we can try to register the user in the database:
         */
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        unset($password, $password2);

        $sql_query = "INSERT INTO `users` (user_name, employee_id, password, email, status) VALUES (:user_name, :employee_id, :password, :email, 'inactive')";
        $result = database_wrapper::instance()->run($sql_query, array('user_name' => $user_name, 'employee_id' => $employee_id, 'password' => $password_hash, 'email' => $email));
        if ($result) {
            send_mail_about_registration();
            echo gettext('You have been successfully registered.')
            . " "
            . sprintf(gettext('Once your user is unlocked, you can %1s log in.%2s'), '<a href="login.php">', '</a>');
            $show_form = false;
        } else {
            error_log('Unfortunately, an error occurred while saving.' . var_export($statement->errorInfo(), TRUE));
            $user_dialog->add_message(gettext('Unfortunately, an error occurred while saving.'), E_USER_ERROR);
        }
    }
}

if ($show_form) {
    if(empty($user_name)){
        $user_name = "";
    }
    if(empty($employee_id)){
        $employee_id = "";
    }
    if(empty($email)){
        $email = "";
    }
    if (isset($config['application_name'])) {
        $application_name = $config['application_name'];
    } else {
        $application_name = 'PDR';
    }


    echo "<div class=centered_form_div>";
    echo "<H1>" . $application_name . "</H1>\n";
    ?>
    <form accept-charset='utf-8' action="?register=1" method="post">
        <input type="text" size="40" maxlength="250" name="user_name" required placeholder="Benutzername" value="<?= $user_name ?>"><br>
        <input type="text" size="40" maxlength="250" name="employee_id" required placeholder="VK Nummer" value="<?= $employee_id ?>"><br>
        <input type="email" size="40" maxlength="250" name="email" required placeholder="Email" value="<?= $email ?>"><br>
        <input type="password" size="40" name="password" required placeholder="Passwort"><br>
        <input type="password" size="40" maxlength="250" name="password2" required placeholder="Passwort wiederholen" title="Passwort wiederholen"><br><br>
        <?php
        echo $user_dialog->build_messages();
        ?>
        <input type="submit" value="Abschicken">
    </form>
    <p class="hint"><?= gettext("The user account will be verified after the registration. This may take a while. You will be informed by email after the verification is complete.") ?></p>
    </div>

    <?php
//Ende von if($show_form)
} else {
    echo $user_dialog->build_messages();
}

function send_mail_about_registration() {
    global $config;
    $message_subject = quoted_printable_encode('Neuer Benutzer wurde angelegt');
    $message_text = quoted_printable_encode("<HTML><BODY>"
            . "Sehr geehrter Administrator,\n\n Im Dienstplanprogramm '"
            . $config["application_name"]
            . "' hat sich ein Benutzer angemeldet. Die Anmeldung muss zunächst <a href='"
            . "https://" . $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]) . "/register_approve.php'>bestätigt werden.</a>"
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
