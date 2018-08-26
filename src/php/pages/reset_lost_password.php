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

function clean_up_after_password_change($employee_id) {
    database_wrapper::instance()->run("DELETE FROM `users_lost_password_token` WHERE `employee_id` = :employee_id", array('employee_id' => $employee_id));
}

function lost_password_token_is_valid($employee_id, $token) {
    $sql_query = "SELECT `employee_id` FROM `users_lost_password_token` WHERE `employee_id` = :employee_id and `token` = UNHEX(:token)";
    $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id, 'token' => $token));
    $row = $result->fetch(PDO::FETCH_OBJ);
    if (!empty($row->employee_id) and $employee_id == $row->employee_id) {
        return TRUE; //The form is shown
    } else {
        user_dialog::add_message(gettext('Invalid token'), E_USER_ERROR);
        echo user_dialog::build_messages();
        return FALSE;
    }
}

function build_lost_password_form($employee_id, $user_name, $token) {
    global $config;

    if (lost_password_token_is_valid($employee_id, $token)) {
        ?>
        <div class=centered_form_div>
            <H1><?= $config['application_name'] ?> </H1>
            <form accept-charset='utf-8' action="reset_lost_password.php" method="post">
                <p><strong><?= $user_name ?></strong></p>
                <p><?= gettext('You can change your password here. Please enter your new password twice below.') ?></p>
                <input type='hidden' name='employee_id' value='<?= $employee_id ?>'>
                <input type='hidden' name='token' value='<?= $token ?>'>
                <input type="password" size="40" name="password" required placeholder="Passwort"><br>
                <input type="password" size="40" maxlength="250" name="password2" required placeholder="Passwort wiederholen" title="Passwort wiederholen"><br><br>
                <input type="submit" value="Abschicken">
            </form>
        </div>

        <?php
    } else {
        user_dialog::add_message(gettext('Invalid token'));
    } //End of if($show_formular)
}

if (filter_has_var(INPUT_GET, 'token') and filter_has_var(INPUT_GET, 'employee_id')) {
    $employee_id = filter_input(INPUT_GET, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
    $token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);
    $sql_query = "SELECT * FROM `users` WHERE `employee_id` = :employee_id";
    $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id));
    $User_data = $result->fetch(PDO::FETCH_OBJ);
    $user_name = $User_data->user_name;

    /*
     * Remove expired tokens:
     */
    database_wrapper::instance()->run("DELETE FROM `users_lost_password_token` WHERE `time_created` <= NOW() - INTERVAL 1 DAY");
    build_lost_password_form($employee_id, $user_name, $token);
} elseif (filter_has_var(INPUT_POST, 'employee_id')) {
    $error = FALSE;
    $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
    $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
    $password2 = filter_input(INPUT_POST, 'password2', FILTER_UNSAFE_RAW);

    if (strlen($password) == 0) {
        user_dialog::add_message(gettext('Please enter a password!'));
        $error = TRUE;
    }
    if ($password !== $password2) {
        user_dialog::add_message(gettext('The passwords must match!'));
        $error = TRUE;
    }

    /*
     * No error, we can update the password in the database.
     */
    if (!$error and lost_password_token_is_valid($employee_id, $token)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $sql_query = "UPDATE users SET password = :password WHERE `employee_id` = :employee_id";
        $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id, 'password' => $password_hash));

        if ($result) {
            clean_up_after_password_change($employee_id);
            echo gettext('Your password has successfully been changed.'), " <a href='" . PDR_HTTP_SERVER_APPLICATION_PATH . "/src/php/login.php'>" . gettext("Login") . "</a>";
        } else {
            error_log(gettext('There was an error while saving the data.') . print_r($statement->errorInfo(), TRUE));
            user_dialog::add_message(gettext('There was an error while saving the data.'));
            user_dialog::add_message(gettext('Please see the error log for more details!'));
            build_lost_password_form($employee_id, $user_name, $token);
        }
    }
} else {
    user_dialog::add_message(gettext('Missing input token'));
}
echo user_dialog::build_messages();
?>
</BODY>
</HTML>
