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
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
?>
<main>
    <h1><?= sprintf(gettext('User page for user %1s'), $_SESSION['user_name']); ?></h1>
    <form action='' method='POST' id='user_form'></form>
    <fieldset id='email_consent'>
        <legend>Emails erhalten bei Änderungen des Dienstplanes</legend>
        <!-- Rectangular switch -->
        <label class="switch">
            <input type="checkbox">
            <span class="slider"></span>
            <span class="text"></span>
        </label>
        <img width="16px" height="16px" src="../../../img/information.svg"
             title="Bei Änderungen im Dienstplan, die weniger als 2 Wochen in der Zukunft liegen, kann eine Benachrichtigung versandt werden.&#10; Es wird maximal eine Mail pro Tag versandt."
             >
    </fieldset>
    <fieldset id='change_password'>
        <legend>Passwort ändern</legend>
        <label>Altes Passwort:<br>
            <input type="password" name="user_password_old"/>
        </label><br>
        <label>Neues Passwort:<br>
            <input type="password" minlength="8" name="user_password_new"/>
            <img width="16px" height="16px" src="../../../img/information.svg"
                 title="Ein sicheres Passwort sollte mindestens 8 Zeichen lang sein und in keinem Wörterbuch stehen."
                 >
        </label><br>
        <label>Neues Passwort wiederholen:<br>
            <input type="password" minlength="8" name="user_password_repetition"/>
        </label><br>
        <input type="password" name="user_id" value="" hidden/>
    </fieldset>
</main>
<?php
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php';
?>
</body>
</html>
