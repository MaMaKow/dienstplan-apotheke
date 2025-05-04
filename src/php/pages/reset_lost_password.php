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
require '../../../default.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . "/head.php";

function clean_up_after_password_change($user_key) {
    database_wrapper::instance()->run("DELETE FROM `users_lost_password_token` WHERE `user_key` = :user_key", array('user_key' => $user_key));
}

function lost_password_token_is_valid($user_key, $token) {
    $user_dialog = new user_dialog();
    $sql_query = "SELECT `user_key` FROM `users_lost_password_token` WHERE `user_key` = :user_key and `token` = UNHEX(:token)";
    $result = database_wrapper::instance()->run($sql_query, array('user_key' => $user_key, 'token' => $token));
    $row = $result->fetch(PDO::FETCH_OBJ);
    if (!empty($row->user_key) and $user_key == $row->user_key) {
        return TRUE; //The form is shown
    } else {
        $user_dialog->add_message(gettext('Invalid token'), E_USER_ERROR);
        echo $user_dialog->build_messages();
        return FALSE;
    }
}

function build_lost_password_form($user_key, $token) {
    $user_dialog = new user_dialog();
    global $config;
    $user = new user($user_key);

    if (lost_password_token_is_valid($user_key, $token)) {
        ?>
        <div class=centered-form-div>
            <H1><?= $config['application_name'] ?> </H1>
            <form accept-charset='utf-8' action="reset_lost_password.php" method="post">
                <p><strong><?= $user->user_name ?></strong></p>
                <p><?= gettext('You can change your password here. Please enter your new password twice below.') ?></p>
                <input type='hidden' name='user_key' value='<?= $user_key ?>'>
                <input type='hidden' name='token' value='<?= $token ?>'>
                <input type="password" size="40" name="password" required placeholder="Passwort" minlength="8"><br>
                <input type="password" size="40" maxlength="250" name="password2" required placeholder="Passwort wiederholen" title="Passwort wiederholen"><br><br>
                <input type="submit" value="Abschicken">
            </form>
        </div>

        <?php
    } else {
        $user_dialog->add_message(gettext('Invalid token'));
    } //End of if($show_formular)
}

if (filter_has_var(INPUT_GET, 'token') and filter_has_var(INPUT_GET, 'user_key')) {
    $user_key = filter_input(INPUT_GET, 'user_key', FILTER_SANITIZE_NUMBER_INT);
    $token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_SPECIAL_CHARS);

    /*
     * Remove expired tokens:
     */
    database_wrapper::instance()->run("DELETE FROM `users_lost_password_token` WHERE `time_created` <= NOW() - INTERVAL 1 DAY");
    build_lost_password_form($user_key, $token);
} elseif (filter_has_var(INPUT_POST, 'user_key')) {
    $error = FALSE;
    $user_key = filter_input(INPUT_POST, 'user_key', FILTER_SANITIZE_NUMBER_INT);
    $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
    $password2 = filter_input(INPUT_POST, 'password2', FILTER_UNSAFE_RAW);

    if (strlen($password) == 0) {
        $user_dialog->add_message(gettext('Please enter a password!'));
        $error = TRUE;
    }
    if ($password !== $password2) {
        $user_dialog->add_message(gettext('The passwords must match!'));
        $error = TRUE;
    }

    /*
     * No error, we can update the password in the database.
     * TODO: Check if the password is secure!
     */
    if (!$error and lost_password_token_is_valid($user_key, $token)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        /*
         * TODO: Move this into the user class!
         */
        $sql_query = "UPDATE users SET password = :password WHERE `primary_key` = :primary_key";
        $result = database_wrapper::instance()->run($sql_query, array('primary_key' => $user_key, 'password' => $password_hash));

        if ($result) {
            clean_up_after_password_change($user_key);
            echo gettext('Your password has successfully been changed.'), " <a href='" . PDR_HTTP_SERVER_APPLICATION_PATH . "/src/php/login.php'>" . gettext("Login") . "</a>";
        } else {
            error_log(gettext('There was an error while saving the data.') . print_r($statement->errorInfo(), TRUE));
            $user_dialog->add_message(gettext('There was an error while saving the data.'));
            $user_dialog->add_message(gettext('Please see the error log for more details!'));
            build_lost_password_form($user_key, $token);
        }
    }
} else {
    $user_dialog->add_message(gettext('The input token is missing.'));
}
echo $user_dialog->build_messages();
?>
</BODY>
</HTML>
