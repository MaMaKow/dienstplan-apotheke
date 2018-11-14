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

$sql_query = "SELECT `receive_emails_on_changed_roster` FROM `users` WHERE employee_id = :employee_id";
$result = database_wrapper::instance()->run($sql_query, array('employee_id' => $_SESSION['user_employee_id']));
$receive_emails_setting = FALSE;
while ($row = $result->fetch(PDO::FETCH_OBJ)) {
    $receive_emails_setting = (bool) $row->receive_emails_on_changed_roster;
}

require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
?>
<main>
    <h1><?= sprintf(gettext('User page for user %1s'), $_SESSION['user_name']); ?></h1>
    <form action='' method='POST' id='user_form'></form>
    <fieldset id='email_consent'>
        <legend><?= gettext('Receive emails when the roster is changed') ?></legend>
        <?= form_element_builder::build_checkbox_switch('user_form', 'receive_emails_opt_in', $receive_emails_setting); ?>
        <img width="16px" height="16px" src="../../../img/information.svg"
             title="<?= gettext('Upon changes in the roster that are less than 2 weeks in the future a notification may be sent. A maximum of one mail per day will be sent.'); ?>"
             >
    </fieldset>
    <fieldset id='change_password'>
        <legend><?= gettext('Change password'); ?></legend>
        <label><?= gettext('Old password'); ?><br>
            <input type="password" name="user_password_old"/>
        </label><br>
        <label><?= gettext('New password'); ?><br>
            <input type="password" minlength="8" name="user_password_new"/>
            <img width="16px" height="16px" src="../../../img/information.svg"
                 title="<?= gettext('A secure password should be at least 8 characters long and not listed in any dictionary.') ?>"
                 >
        </label><br>
        <label><?= gettext('Repeat new password'); ?><br>
            <input type="password" minlength="8" name="user_password_repetition"/>
        </label><br>
        <input type="password" name="user_id" value="" hidden/>
    </fieldset>
    <fieldset>
        <legend><?= gettext('Privileges'); ?></legend>
    </fieldset>
</main>
<?php
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php';
?>
</body>
</html>
