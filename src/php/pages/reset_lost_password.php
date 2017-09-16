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

function clean_up_after_password_change($employee_id) {
    mysqli_query_verbose("DELETE FROM `users_lost_password_token` WHERE `employee_id` = $employee_id");
}

function lost_password_token_is_valid($employee_id, $token) {
    $sql_query = "SELECT `employee_id` FROM `users_lost_password_token` WHERE `employee_id` = $employee_id and `token` = UNHEX('$token')";
    $result = mysqli_query_verbose($sql_query);
    $row = mysqli_fetch_object($result);
    if (!empty($row->employee_id) and $employee_id === $row->employee_id) {
        return TRUE; //The form is shown
    } else {
        echo build_warning_messages(gettext("Invalid token"));
        return FALSE;
    }
}

if (filter_has_var(INPUT_GET, 'token') and filter_has_var(INPUT_GET, 'employee_id')) {
    $employee_id = filter_input(INPUT_GET, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
    $token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);
    $statement = $pdo->prepare("SELECT * FROM `users` WHERE `employee_id` = :employee_id");
    $statement->execute(array('employee_id' => $employee_id));
    $User_data = $statement->fetch();
    $user_name = $User_data['user_name'];

    /*
     * Remove expired tokens:
     */
    mysqli_query_verbose("DELETE FROM `users_lost_password_token` WHERE `time_created` <= NOW() - INTERVAL 1 DAY");
    $show_formular = TRUE;
} elseif (filter_has_var(INPUT_POST, 'employee_id')) {
    $error = FALSE;
    $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
    $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
    $password2 = filter_input(INPUT_POST, 'password2', FILTER_UNSAFE_RAW);

    if (strlen($password) == 0) {
        $Error_message[] = 'Bitte ein Passwort angeben<br>';
        $error = TRUE;
    }
    if ($password !== $password2) {
        $Error_message[] = 'Die Passwörter müssen übereinstimmen';
        $error = TRUE;
    }

    //No error, we can update the password in the database.
    if (!$error and lost_password_token_is_valid($employee_id, $token)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $statement = $pdo->prepare("UPDATE users SET password = :password WHERE `employee_id` = :employee_id");
        $result = $statement->execute(array('employee_id' => $employee_id, 'password' => $password_hash));

        if ($result) {
            clean_up_after_password_change($employee_id);
            echo gettext('Your password has successfully been changed.'), " <a href='" . PDR_HTTP_SERVER_APPLICATION_PATH . "/src/php/login.php'>" . gettext("Login") . "</a>";
            $show_formular = FALSE;
        } else {
            error_log('Beim Abspeichern ist leider ein Fehler aufgetreten' . print_r($statement->errorInfo(), TRUE));
            //$Error_message[] = 'Beim Abspeichern ist leider ein Fehler aufgetreten<br>';
            $Error_message[] = gettext("There was an error while saving the data.");
            $show_formular = TRUE;
        }
    }
}

if (TRUE === $show_formular and lost_password_token_is_valid($employee_id, $token)) {
    ?>
    <div class=centered_form_div>
        <H1><?= $config['application_name'] ?> </H1>
        <form action="reset_lost_password.php" method="post">
            <H2><?= $user_name ?></H2>
            <input type='hidden' name='employee_id' value='<?= $employee_id ?>'>
            <input type='hidden' name='token' value='<?= $token ?>'>
            <input type="password" size="40" name="password" required placeholder="Passwort"><br>
            <input type="password" size="40" maxlength="250" name="password2" required placeholder="Passwort wiederholen" title="Passwort wiederholen"><br><br>
            <?php
            echo build_warning_messages($Error_message, array());
            ?>
            <input type="submit" value="Abschicken">
        </form>
    </div>

    <?php
} //End of if($show_formular)
?>

</BODY>
</HTML>
