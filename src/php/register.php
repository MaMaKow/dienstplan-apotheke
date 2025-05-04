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

/**
 * @todo Write Selenium tests for registration including errors and DDoS attacks
 * @todo Move the methods into a class called \PDR\Input\RegistrationInputHandler
 */
require '../../default.php';
require "../../head.php";
$show_form = true; //Variable ob das Registrierungsformular anezeigt werden soll
$user_dialog = new user_dialog();

if (filter_has_var(INPUT_GET, 'register')) {
    if (filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_SPECIAL_CHARS) !== $_SESSION['csrfToken']) {
        // Token does not match; possible CSRF attack
        error_log('Invalid CSRF token. Possible cross site scripting attack. Quitting execution of script.');
        die('Invalid CSRF token. Possible cross site scripting attack. Quitting execution of script.');
    }
    $error = false;
    $user_name = trim(filter_input(INPUT_POST, 'user_name', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
    $password2 = filter_input(INPUT_POST, 'password2', FILTER_UNSAFE_RAW);
    $math_problem_solution_sent = filter_input(INPUT_POST, 'math_problem_solution', FILTER_SANITIZE_NUMBER_INT);

    if ($math_problem_solution_sent != $_SESSION['math_problem_solution']) {
        $user_dialog->add_message(gettext('Please enter the correct calculation result!'));
        $error = true;
    }
    if (strlen($password) == 0) {
        $user_dialog->add_message(gettext('Please enter a password!'));
        $error = true;
    }
    if ($password != $password2) {
        $user_dialog->add_message(gettext('The passwords must match!'));
        $error = true;
    }
    try {
        $have_i_been_pwned = new \have_i_been_pwned();
        if (!$have_i_been_pwned->password_is_secure($password)) {
            $user_dialog->add_message($have_i_been_pwned->get_user_information_string());
            $error = true;
        }
    } catch (Exception $exception) {
        /**
         * Well I am sad. But we will be fine.
         * @TODO: Perhaps send a mail to pdr@martin-mandelkow.de to make me check if anything is wrong with the api.
         * No, better: Build a test against the API. The user does not have to be botherd sending messages to the developer.
         * The same lines are found in class.user.php. Change them there and here together!
         */
    }

    //Überprüfe, dass der Benutzer noch nicht registriert wurde
    if (false === $error) {
        $sql_query = "SELECT * FROM users WHERE `user_name` = :user_name";
        $result = database_wrapper::instance()->run($sql_query, array('user_name' => $user_name));
        $user = $result->fetch();

        if ($user !== false) {
            $user_dialog->add_message(gettext('This username is already taken.'));
            $error = true;
        }
    }

    if (false === $error) {
        /**
         * No errors, we can try to register the user in the database:
         */
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        unset($password, $password2);

        $sql_query = "INSERT INTO `users` (user_name, password, email, status) VALUES (:user_name, :password, :email, 'inactive')";
        $result = database_wrapper::instance()->run($sql_query, array('user_name' => $user_name, 'password' => $password_hash, 'email' => $email));
        if ($result) {
            send_mail_about_registration($user_name, $email);
            echo gettext('You have been successfully registered.')
            . " "
            . sprintf(gettext('Once your user is unlocked, you can %1$s log in.%2$s'), '<a href="login.php">', '</a>');
            $show_form = false;
        } else {
            error_log('Unfortunately, an error occurred while saving.' . var_export($statement->errorInfo(), TRUE));
            $user_dialog->add_message(gettext('Unfortunately, an error occurred while saving.'), E_USER_ERROR);
        }
    }
}

if ($show_form) {
    if (empty($user_name)) {
        $user_name = "";
    }
    if (empty($email)) {
        $email = "";
    }
    if (isset($config['application_name'])) {
        $application_name = $config['application_name'];
    } else {
        $application_name = 'PDR';
    }
    /**
     * In order to prevent DDoS attacks against the registration form we define a simple math problem:
     */
    $summand1 = rand(2, 9);
    $summand2 = rand(2, 9);
    $math_problem_solution = $summand1 + $summand2;
    $_SESSION['math_problem_solution'] = $math_problem_solution;

    echo "<div class=centered-form-div>";
    echo "<H1>" . $application_name . "</H1>\n";
    ?>
    <form accept-charset='utf-8' action="?register=1" method="post">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrfToken']; ?>" />
        <input type="text" size="40" maxlength="250" name="user_name" required placeholder="<?= gettext("Username") ?>" value="<?= $user_name ?>"><br>
        <input type="email" size="40" maxlength="250" name="email" required placeholder="<?= gettext("Email") ?>" value="<?= $email ?>"><br>
        <input type="password" size="40" maxlength="4096" name="password" required placeholder="<?= gettext("Passphrase") ?>"><br>
        <input type="password" size="40" maxlength="4096" name="password2" required placeholder="<?= gettext("Repeat passphrase") ?>" title="Passwort wiederholen"><br><br>
        <label for="mathProblemSolution"><?= sprintf(gettext('What does %1$s + %2$s equal?'), $summand1, $summand2) ?></label><br><input type="number" size="40" maxlength="250" name="math_problem_solution" id="mathProblemSolution" required placeholder="<?= gettext("Solution") ?>"><br><br>
        <?php
        echo $user_dialog->build_messages();
        ?>
        <br>
        <input type="submit" value="Abschicken">
    </form>
    <p class="hint"><?= gettext("The user account will be verified after the registration. This may take a while. You will be informed by email after the verification is complete.") ?></p>
    </div>

    <?php
//Ende von if($show_form)
} else {
    echo $user_dialog->build_messages();
}

/**
 *
 * @param string $userName
 * @param string $userEmail
 * @return void
 * @todo Write selenium test for this email
 */
function send_mail_about_registration(string $userName, string $userEmail): void {
    $configuration = new PDR\Application\configuration();
    $messageSubject = quoted_printable_encode(gettext('New user has been registered'));
    $applicationName = $configuration->getApplicationName();
    $url = PDR_HTTP_SERVER_APPLICATION_PATH . "src/php/register_approve.php";
    $registrationTime = date("Y-m-d H:i:s");
    $messageText = "<HTML><BODY>"
            . gettext('Dear Administrator,') . "<br><br>"
            . sprintf(gettext('A user has registered to the duty roster application %1$s.'), $applicationName)
            . '<br><br>'
            . gettext('Registration details:') . ":" . "<br>"
            . gettext('Username') . ": " . $userName . "<br>"
            . gettext('Email') . ": " . $userEmail . "<br>"
            . gettext('Registration time') . ": " . $registrationTime . "<br><br>"
            . sprintf(gettext('Please <a href=‘%1$s’>confirm</a> the registration.'), $url)
            . gettext('Users cannnot login before confirmation.')
            . "</BODY></HTML>";
    $messageTextEncoded = quoted_printable_encode($messageText);
    unset($messageText);
    $userDialogEmail = new user_dialog_email();
    $mailSuccess = $userDialogEmail->send_email($configuration->getContactEmail(), $messageSubject, $messageTextEncoded);
    if (true === $mailSuccess) {
        echo "Die Nachricht wurde versendet. Vielen Dank!<br>\n";
    } else {
        echo "Fehler beim Versenden der Nachricht. Das tut mir Leid.<br>\n";
    }
}
?>

</body>
</html>
