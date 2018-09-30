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
 * TODO: Handle all the configuration parameters
 */
$session->exit_on_missing_privilege('administration');
if (!empty($_POST)) {
    $config = configuration::handle_user_input($config);
}

/*
 * Check the hide_disapproved value
 */
if (FALSE != $config['hide_disapproved']) {
    $hide_disapproved_yes = 'checked';
    $hide_disapproved_no = '';
} else {
    $hide_disapproved_yes = '';
    $hide_disapproved_no = 'checked';
}

/*
 * Check which error reporting strength has been set roughly. This is not precise!
 */
$error_all_checked = "";
$error_notice_checked = "";
$error_warning_checked = "";
$error_error_checked = "";
if (configuration::ERROR_ALL <= $config['error_reporting']) {
    $error_all_checked = "checked";
} elseif (configuration::ERROR_NOTICE <= $config['error_reporting']) {
    $error_notice_checked = "checked";
} elseif (configuration::ERROR_WARNING <= $config['error_reporting']) {
    $error_warning_checked = "checked";
} elseif (configuration::ERROR_ERROR <= $config['error_reporting']) {
    $error_error_checked = "checked";
} else {
    $other_error = configuration::friendly_error_type($config['error_reporting']);
    $other_error_html = '<tr><td>
        <input type="radio" name="error_reporting" value="' . $config['error_reporting'] . '" checked>
        ' . $other_error . ' (current value)
      </td></tr>';
}

$datalist_encodings = configuration::build_supported_encodings_datalist();
$datalist_locales = configuration::build_supported_locales_datalist();
$error_error = configuration::ERROR_ERROR;


require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
echo user_dialog::build_messages();
?>
<div style=font-size:larger>
    <H1><?= gettext('Configuration') ?></H1>
    <form accept-charset='utf-8' class="" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <table>
            <tr>
                <th colspan="2">
                    Database settings
                    <p class="hint">
                        <?= gettext('The installation script will create a new MySQL database.') ?>
                        <br>
                        <?= gettext('All the information about the duty rosters will be stored password protected in this database.') ?>
                    </p>
                </th>
            </tr>
            <tr>
                <td><?= gettext('Application name') ?></td>
                <td><input type="text" name="application_name" value="<?php echo isset($config['application_name']) ? $config['application_name'] : '' ?>">
                </td>
            </tr>
            <tr>
                <td><?= gettext('Database name') ?></td>
                <td><input type="text" name="database_name" value="<?php echo isset($config['database_name']) ? $config['database_name'] : '' ?>">
                </td>
            </tr>
            <tr>
                <td><?= gettext('Database user') ?></td>
                <td><input type="text" name="database_user" value="<?php echo isset($config['database_user']) ? $config['database_user'] : '' ?>">
                </td>
            </tr>
            <tr>
                <td><?= gettext('Database user password') ?></td>
                <td><input type="password" name="database_password" id="first_pass"
                           onchange="compare_passwords()"
                           onkeyup="compare_passwords()"
                           onkeydown="compare_passwords()"
                           onclick="compare_passwords()"
                           onblur="compare_passwords()"
                           onpaste="compare_passwords()"
                           >
                </td>
                <td>
                    <img id="approve_pass_img"    alt="passwords match"       style="display:none" src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/approve.png" height="20">
                    <img id="disapprove_pass_img" alt="passwords don't match" style="display:none" src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/disapprove.png" height="20">
                </td>
            </tr>
            <tr>
                <td><?= gettext('Repeat password') ?>
                </td>
                <td><input type="password" name="database_password_second" id="second_pass"
                           onchange="compare_passwords()"
                           onkeyup="compare_passwords()"
                           onkeydown="compare_passwords()"
                           onclick="compare_passwords()"
                           onblur="compare_passwords()"
                           onpaste="compare_passwords()"
                           >
                </td>
            </tr>
            <tr>
                <th colspan="2">
                    <?= gettext('Contact information') ?>
                    <p class="hint">
                        <?= gettext('Viewing users will be invited to address wishes and suggestions to the editor of the duty rosters.') ?>
                    </p>
                </th>
            </tr>
            <tr>
                <td><?= gettext('Email') ?>
                </td>
                <td><input type="email" name="contact_email" value="<?php echo isset($config['contact_email']) ? $config['contact_email'] : '' ?>">
                </td>
            </tr>
            <tr>
                <th colspan="2">
                    <?= gettext('Technical details') ?>
                    <p class="hint">
                        <?= gettext("Time values can be adapted to various local user's environments.") ?>
                        <br>
                        <?= gettext('They depend on language and cultural conventions.') ?>
                    </p>
                </th>
            </tr>
            <tr>
                <td><?= gettext('Locale') ?>
                </td>
                <td><input list="locales" name="LC_TIME" value="<?php echo isset($config['LC_TIME']) ? $config['LC_TIME'] : '' ?>" >
                    <?php echo "$datalist_locales"; ?>
                </td>
            </tr>
            <tr>
                <td><?= gettext('Charset') ?>
                </td>
                <td><input list="encodings" name="mb_internal_encoding" value="<?php echo isset($config['mb_internal_encoding']) ? $config['mb_internal_encoding'] : '' ?>" >
                    <?php echo "$datalist_encodings"; ?>
                </td>
            </tr>
            <tr>
                <th colspan="2"> <?= gettext('Debugging') ?>
                    <p class="hint"> <?= gettext('Which types of errors should be reported to the user?') ?></p>
                </th>
            </tr>
            <tr><td>
                    <input type="radio" name="error_reporting" value="<?= configuration::ERROR_ERROR . '" ' . $error_error_checked; ?>>
                    <?= gettext('Only fatal errors') ?>
                           <br>
                           <input type="radio" name="error_reporting" value="<?= configuration::ERROR_WARNING . '" ' . $error_warning_checked; ?>>
                           <?= gettext('Also warnings') ?>
                           <br>
                    <input type="radio" name="error_reporting" value="<?= configuration::ERROR_NOTICE . '" ' . $error_notice_checked; ?>>
                    <?= gettext('And notices') ?>
                           <br>
                           <input type="radio" name="error_reporting" value="<?= configuration::ERROR_ALL . '" ' . $error_all_checked; ?>>
                           <?= gettext('Everything') ?>
                </td></tr>
            <?php
            if (!empty($other_error_html)) {
                echo "$other_error_html";
            }
            ?>
            <tr>
                <th colspan="2">Approval
                    <p class="hint">
                        After a duty roster is planned, it has to be approved, before it is in effect.
                        <br>
                        Should viewers be able to see duty rosters before they are finally approved?
                    </p>
                </th>
            </tr>
            <tr>
                <td><input type="radio" name="hide_disapproved" value=1 <?= $hide_disapproved_yes ?>>Hide
                    <br>
                    <input type="radio" name="hide_disapproved" value=0 <?= $hide_disapproved_no ?>>Show
                </td>
            </tr>
        </table>
        <input type="submit">
    </form>
</div>
</body>
</html>
