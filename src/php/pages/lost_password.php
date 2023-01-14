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

if (filter_has_var(INPUT_GET, 'request_new_password')) {
    $token = sha1(uniqid());
    $identifier = filter_input(INPUT_POST, 'identifier', FILTER_SANITIZE_STRING);
    $user_dialog = new user_dialog();
    if (!empty($identifier)) {
        $user_base = new \PDR\Workforce\user_base();
        $user = $user_base->guess_user_by_identifier($identifier);
        if (FALSE !== $user and $user instanceof user) {
            $session->write_lost_password_token_to_database($user, $token);
            $session->send_mail_about_lost_password($user, $token);
        }
        unset($token);
        $user_dialog->add_message(gettext("If the user exists, an email has been sent."), E_USER_NOTICE);
    }
}
require PDR_FILE_SYSTEM_APPLICATION_PATH . "/head.php";

echo "<div class=centered_form_div>";
if (isset($config['application_name'])) {
    $application_name = $config['application_name'];
} else {
    $application_name = 'PDR';
}

echo "<H1>" . $application_name . "</H1>\n";
?>

<form accept-charset='utf-8' action="?request_new_password=1" method="post">
    <p><?= gettext("Please enter your email address, user name or user id!") ?></p>
    <input type="text" size="25" maxlength="250" name="identifier" placeholder="<?= gettext("identifier") ?>"><br>
    <input type="submit"><br>
    <?php
    $user_dialog = new user_dialog();
    echo $user_dialog->build_messages();
    ?>
</form>
</div>
</body>
</html>
