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

if (filter_has_var(INPUT_GET, 'request_new_password')) {
    $token = sha1(uniqid());
    print_debug_variable('$token is build: ', $token);
    $identifier = filter_input(INPUT_POST, 'identifier', FILTER_SANITIZE_STRING);
    if (!empty($identifier)) {
        $statement = $pdo->prepare("SELECT * FROM `users` WHERE `employee_id` = :identifier OR `email` = :identifier OR `user_name` = :identifier");
        $statement->execute(array('identifier' => $identifier));
        $user_data = $statement->fetch();
        $session->write_lost_password_token_to_database($user_data['employee_id'], $token);
        $session->send_mail_about_lost_password($user_data['employee_id'], $user_data['user_name'], $user_data['email'], $token);
    }
}
require PDR_FILE_SYSTEM_APPLICATION_PATH . "/head.php";

echo "<div class=centered_form_div>";
echo "<H1>" . $config['application_name'] . "</H1>\n";
?>

<form action="?request_new_password=1" method="post">
    <p><?= gettext("Please enter your email address, user name or user id!") ?></p>
    <input type="text" size="25" maxlength="250" name="identifier" placeholder="<?= gettext("identifier") ?>"><br>
    <input type="submit"><br>
    <?php
    if (!empty($error_message)) {
        build_error_message($error_message);
    }
    ?>
</form>
</div>
</body>
</html>
