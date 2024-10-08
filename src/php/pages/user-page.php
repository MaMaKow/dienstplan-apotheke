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
require_once '../../../default.php';
/*
 * Get a list of all employees:
 */
$workforce = new workforce();

$user = $_SESSION['user_object'];

function build_permission_list($user) {
    $text = (string) "";
    foreach (sessions::$Pdr_list_of_privileges as $privilege) {

        $privilege_name = localization::gettext(str_replace('_', ' ', $privilege));
        $text .= "<label for='$privilege'>" . $privilege_name . ": </label>";
        $text .= "<input type='checkbox' name='privilege[]' value='$privilege' id='$privilege' ";
        if (in_array($privilege, $user->privileges)) {
            $text .= " checked='checked'";
        }
        $text .= " disabled>";
        $text .= "<br>";
    }
    return $text;
}

/**
 *
 * Attempt to change the user passphrase.
 *
 * @return boolean success of the passphrase change operation
 */
function change_password_on_input() {
    $user = $_SESSION['user_object'];
    $user_dialog = new \user_dialog();
    $user_password_old = filter_input(INPUT_POST, 'user_password_old', FILTER_UNSAFE_RAW);
    if (!$user->password_verify($user_password_old)) {
        $user_dialog->add_message(gettext('The passphrase was not correct.'));
        return FALSE;
    }
    $user_passphrase_new = filter_input(INPUT_POST, 'user_password_new', FILTER_UNSAFE_RAW);
    $user_passphrase_repetition = filter_input(INPUT_POST, 'user_password_repetition', FILTER_UNSAFE_RAW);
    if ($user_passphrase_new !== $user_passphrase_repetition) {
        $user_dialog->add_message(gettext('The passphrases must match!'));
        return FALSE;
    }
    $success = $user->change_passphrase($user_passphrase_old, $user_passphrase_new);
    if (TRUE === $success) {
        $user_dialog->add_message(gettext('The passphrase was successfully changed.'), E_USER_NOTICE);
    }
    return $success;
}

if (filter_has_var(INPUT_POST, 'user_password_old')) {
    \change_password_on_input();
}
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
$user_dialog = new \user_dialog;
echo $user_dialog->build_messages();
?>
<main>
    <h1><?= sprintf(gettext('User page for user %1$s'), $_SESSION['user_object']->user_name); ?></h1>
    <form action='' method='POST' id='user_form'></form>
    <form action='' method='POST' id="userPagePasswordForm"></form>
    <fieldset id='email_consent'>
        <legend><?= gettext('Receive emails when the roster is changed') ?></legend>
        <?= form_element_builder::build_checkbox_switch('user_form', 'receive_emails_opt_in', $user->wants_emails_on_changed_roster()); ?>
        <img width="16px" height="16px" src="../../../img/information.svg"
             title="<?= gettext('Upon changes in the roster that are less than 2 weeks in the future a notification may be sent. A maximum of one mail per day will be sent.'); ?>"
             >
    </fieldset>
    <fieldset id='change_password'>
        <legend><?= gettext('Change passhrase'); ?></legend>
        <label><?= gettext('Old passphrase'); ?><br>
            <input type="password" name="user_password_old" form="userPagePasswordForm"/>
        </label><br>
        <label><?= gettext('New passphrase'); ?><br>
            <input type="password" minlength="8" name="user_password_new" form="userPagePasswordForm"/>
            <img width="16px" height="16px" src="../../../img/information.svg"
                 title="<?= gettext('A secure passphrase should be at least 8 characters long and not listed in any dictionary.') ?>"
                 >
        </label><br>
        <label><?= gettext('Repeat new passphrase'); ?><br>
            <input type="password" minlength="8" name="user_password_repetition" form="userPagePasswordForm"/>
        </label><br>
        <input type="password" name="user_id" value="" hidden/>
        <input type="submit" form="userPagePasswordForm"/>
    </fieldset>
    <fieldset>
        <legend><?= gettext('Privileges'); ?></legend>
        <?= build_permission_list($user) ?>
    </fieldset>
</main>
<?php
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php';
?>
</body>
</html>
