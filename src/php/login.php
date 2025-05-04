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
/*
 * TODO: send referer via session?
 */

if (filter_has_var(INPUT_POST, 'login')) {
    $user_name = filter_input(INPUT_POST, 'user_name', FILTER_SANITIZE_SPECIAL_CHARS);
    $user_password = filter_input(INPUT_POST, 'user_password', FILTER_SANITIZE_SPECIAL_CHARS);

    $errorMessage = $session->login($user_name, $user_password, TRUE);
}
require "../../head.php";

echo "<div class=centered-form-div>";
if (isset($config['application_name'])) {
    $application_name = $config['application_name'];
} else {
    $application_name = 'PDR';
}
echo "<H1>" . $application_name . "</H1>\n";
$user_dialog = new user_dialog();
$user_dialog->build_messages();
?>

<form accept-charset='utf-8' action="" method="post">
    <input type="hidden" name="login" value="1">
    <input type="text" size="25" maxlength="250" name="user_name" placeholder="Benutzername" id="loginInputUserName"><br>
    <input type="password" size="25" name="user_password" placeholder="Passphrase" id="loginInputUserPassphrase" ><br>
    <input type="submit" id="loginButtonSubmit">
    <p class="hint" id="loginParagraphCapsWarning" >&nbsp;<!-- Warning! Caps lock is ON. --></p>
    <?php
    if (!empty($errorMessage)) {
        echo '<p>' . $errorMessage . '</p>';
    }
    ?>
</form>
<p class="unobtrusive"><a href="register.php"><?= gettext("Create new user account") ?></a></p>
<p class="unobtrusive"><a href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>/src/php/pages/lost_password.php"><?= gettext("Forgot password?") ?></a></p>
<?= '</div>' ?>
<script>
    var input_password = document.getElementById("loginInputUserPassphrase");
    var input_user = document.getElementById("loginInputUserName");
    /*
     * When the user presses any key on the keyboard, run the function
     */
    input_password.addEventListener("keyup", show_login_p_caps_warning);
    input_user.addEventListener("keyup", show_login_p_caps_warning);
    /*
     * Call the maintenance script on every login:
     * It will only execute it's code once a day.
     */
    query_webserver_without_response('<?= PDR_HTTP_SERVER_APPLICATION_PATH . 'src/php/background_maintenance.php' ?>');
</script>
</body>
</html>
