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
$configurationManager = new \PDR\Output\HTML\configurationManager();
$configurationManager->checkErrorLogPath();

/*
 * TODO: Handle all the configuration parameters
 */
$session->exit_on_missing_privilege('administration');
if (isset($_POST) && !empty($_POST)) {
    $config = \PDR\Output\HTML\configurationManager::handle_user_input($config);
    // POST data has been submitted
    $location = PDR_HTTP_SERVER_APPLICATION_PATH . 'src/php/pages/configuration.php' . "?user_input=handled";
    header('Location:' . $location);
    die("<p>Redirect to: <a href=$location>$location</a></p>");
}
$configuration = new \PDR\Application\configuration();
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
$other_error = NULL;

if (\PDR\Output\HTML\configurationManager::ERROR_ALL <= $configuration->getErrorReporting()) {
    $error_all_checked = "checked";
} elseif (\PDR\Output\HTML\configurationManager::ERROR_NOTICE <= $configuration->getErrorReporting()) {
    $error_notice_checked = "checked";
} elseif (\PDR\Output\HTML\configurationManager::ERROR_WARNING <= $configuration->getErrorReporting()) {
    $error_warning_checked = "checked";
} elseif (\PDR\Output\HTML\configurationManager::ERROR_ERROR <= $configuration->getErrorReporting()) {
    $error_error_checked = "checked";
} else {
    $other_error = \PDR\Output\HTML\configurationManager::friendly_error_type($configuration->getErrorReporting());
}
$datalist_encodings = \PDR\Output\HTML\configurationManager::build_supported_encodings_datalist();
$datalist_locales = \PDR\Output\HTML\configurationManager::build_supported_locales_datalist();
$error_error = \PDR\Output\HTML\configurationManager::ERROR_ERROR;

$email_method = $configuration->getEmailMethod();

require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
$user_dialog = new user_dialog();
echo $user_dialog->build_messages();
?>
<div id="configurationFormDiv" style=font-size:larger>
    <H1><?= gettext('Configuration') ?></H1>
    <form accept-charset='utf-8' id="configurationForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div id="configurationInputDiv">
            <input type="submit" class="configuration-input-button-submit" form="configurationForm">
            <fieldset>
                <legend>Database settings</legend>
                <p class="hint">
                    <?= gettext('The installation script will create a new MySQL database.') ?>
                    <?= gettext('All the information about the duty rosters will be stored password protected in this database.') ?>
                </p>
                <label><?= gettext('Application name') ?></label>
                <br><input type="text" name="application_name" value="<?php echo $configuration->getApplicationName(); ?>">
                <br>
                <label><?= gettext('Database name') ?></label>
                <br><input type="text" name="database_name" value="<?php echo $configuration->getDatabaseName(); ?>">
                <br>
                <label><?= gettext('Database user') ?></label>
                <br><input type="text" name="database_user" value="<?php echo $configuration->getDatabaseUser(); ?>">
                <br>
                <label><?= gettext('Database user passphrase') ?></label>
                <!-- Confuse the browser in order to stop it from auto-inserting the user password in the database password field-->
                <input type="password" name="fake_password_input" id="fake_pass" hidden="true" style="display: none;">
                <br>
                <input type="password" name="database_password" id="first_pass" autocomplete="new-password"
                       onchange="compare_passwords()"
                       onkeyup="compare_passwords()"
                       onkeydown="compare_passwords()"
                       onclick="compare_passwords()"
                       onblur="compare_passwords()"
                       onpaste="compare_passwords()"
                       >
                <br>
                <img id="approvePassImage"    alt="passwords match"       style="display:none" src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_thumb_up-24px.svg" height="20">
                <img id="disapprovePassImage" alt="passwords don't match" style="display:none" src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_thumb_down-24px.svg" height="20">
                <br>
                <label><?= gettext('Repeat passphrase') ?></label>
                <br>
                <input type="password" name="database_password_second" id="second_pass"
                       onchange="compare_passwords()"
                       onkeyup="compare_passwords()"
                       onkeydown="compare_passwords()"
                       onclick="compare_passwords()"
                       onblur="compare_passwords()"
                       onpaste="compare_passwords()"
                       >
            </fieldset>
            <fieldset>
                <legend>
                    <?= gettext('Contact information') ?></legend>
                <p class="hint">
                    <?= gettext('Viewing users will be invited to address wishes and suggestions to the editor of the duty rosters.') ?>
                </p>
                <label><?= gettext('Email') ?>
                </label>
                <br><input type="email" name="contact_email" value="<?php echo $configuration->getContactEmail(); ?>">
            </fieldset>
            <fieldset>
                <legend>
                    <?= gettext('Language and encoding') ?></legend>
                <p class="hint">
                    <?= gettext("The messages in this application and the documentation exist in different languages.") ?>
                </p>
                <label><?= gettext('Language') ?></label><br>
                <select name="language"><?php
                    foreach (\PDR\Output\HTML\configurationManager::$List_of_supported_languages as $language_code => $language_name) {
                        ?>
                        <option value=<?=
                        $language_code === $configuration->getLanguage() ? '"' . $language_code . '" selected' : '"' . $language_code . '"';
                        ?>><?= $language_name ?></option>
                                <?php
                            }
                            ?>
                </select>
                <br>
                <p class="hint">
                    <?= gettext("Time values can be adapted to various local user's environments.") ?>
                    <?= gettext('They depend on language and cultural conventions.') ?>
                </p>
                <label><?= gettext('Time locale') ?></label>
                <br><input list="locales" name="LC_TIME" value="<?php echo $configuration->getLC_TIME(); ?>" >
                <?= $datalist_locales; ?>
                <br>
                <label><?= gettext('Charset') ?>
                </label>
                <br><input list="encodings" name="mb_internal_encoding" value="<?php echo $configuration->getMb_internal_encoding(); ?>" >
                <?php echo "$datalist_encodings"; ?>
            </fieldset>
            <!-- Debugging settings -->
            <fieldset>
                <legend> <?= gettext('Debugging') ?></legend>
                <p class="hint"> <?= gettext('Which types of errors should be reported to the user?') ?></p>
                <input type="radio" id="errorReportingError" name="error_reporting" value="<?= \PDR\Output\HTML\configurationManager::ERROR_ERROR . '" ' . $error_error_checked; ?>">
                <label for="errorReportingError"><?= gettext('Only fatal errors') ?></label>
                <br>
                <br>
                <input type="radio" id="errorReportingWarning" name="error_reporting" value="<?= \PDR\Output\HTML\configurationManager::ERROR_WARNING . '" ' . $error_warning_checked; ?>">
                <label for="errorReportingWarning"><?= gettext('Also warnings') ?></label>
                <br>
                <br>
                <input type="radio" id="error_reporting_notice" name="error_reporting" value="<?= \PDR\Output\HTML\configurationManager::ERROR_NOTICE . '" ' . $error_notice_checked; ?>">
                <label for="error_reporting_notice"><?= gettext('And notices') ?></label>
                <br>
                <br>
                <input type="radio" id="error_reporting_all" name="error_reporting" value="<?= \PDR\Output\HTML\configurationManager::ERROR_ALL . '" ' . $error_all_checked; ?>">
                <label for="error_reporting_all"><?= gettext('Everything') ?></label>
                <br>
                <br>
                <?php
                if (FALSE and !empty($other_error)) {
                    ?>
                    <input type="radio" id="error_reporting_<?= $other_error ?>" name="error_reporting" value="<?= $configuration->getErrorReporting() . '" checked'; ?>">
                    <label for="error_reporting_<?= $other_error ?>"><?= $other_error . ' ' . gettext('(current value)') ?></label>
                <?php }
                ?>
                <label><?= gettext('Error Log Path') ?></label>
                <br><input type="text" name="error_log" value="<?php echo $configuration->getErrorLog(); ?>">
            </fieldset>

            <!-- Roster approval settings: -->
            <fieldset>
                <legend>Approval</legend>
                <p class="hint">
                    <?= gettext('After a duty roster is planned, it has to be approved, before it is in effect.') ?>
                    <?= gettext('Should viewers be able to see duty rosters before they are finally approved?') ?>
                </p>
                <input type="radio" name="hide_disapproved" value=0 <?= $hide_disapproved_no ?>><?= gettext("Show"); ?><br>
                <input type="radio" name="hide_disapproved" value=1 <?= $hide_disapproved_yes ?>><?= gettext("Hide"); ?><br>
            </fieldset>

            <!-- Email settings: -->
            <fieldset onchange="configuration_toggle_show_smtp_options();">
                <legend><?= gettext('Email settings') ?></legend>
                <div class="hint">
                    <?= gettext("Emails are sent in some cases:"); ?>
                    <ul>
                        <li><?= gettext("When new users are registered"); ?></li>
                        <li><?= gettext("When users want to comment on the roster"); ?></li>
                        <li><?= gettext("When there are acute changes to the roster (optional for the distinct users)"); ?></li>
                    </ul>
                    <?= gettext("How should these emails be sent?"); ?>
                </div>
                <input type="radio" name="email_method" value="mail" <?= $email_method === 'mail' ? 'checked="checked"' : '' ?>>
                <?= gettext('Simple mail') ?> <br><span class="hint"><?= gettext("(Uses sendmail on Linux/Mac)"); ?></span><br>
                <input type="radio" name="email_method" value="sendmail" <?= $email_method === 'sendmail' ? 'checked="checked"' : '' ?>><?= gettext('Sendmail') ?><br>
                <input type="radio" name="email_method" value="qmail" <?= $email_method === 'qmail' ? 'checked="checked"' : '' ?>><?= gettext('qmail') ?><br>
                <input type="radio" name="email_method" value="smtp" <?= $email_method === 'smtp' ? 'checked="checked"' : '' ?>><?= gettext('SMTP') ?><br>

                <!-- SMTP email settings -->
                <fieldset class="configuration-smtp-settings-fieldset" style="display: <?= $email_method === 'smtp' ? 'inline' : 'none' ?>">
                    <legend><?= gettext('SMTP settings') ?>
                    </legend>
                    <label for="emailSmtpHost">Host</label><br>
                    <input type="text" name="email_smtp_host" id="emailSmtpHost" value="<?= $configuration->getEmailSmtpHost(); ?>"><br>
                    <label for="emailSmtpPort">Port</label><br>
                    <input type="text" name="email_smtp_port" id="emailSmtpPort" value="<?= $configuration->getEmailSmtpPort(); ?>"><br>
                    <label for="emailSmtpUsername">User name</label><br>
                    <input type="text" name="email_smtp_username" id="emailSmtpUsername" value="<?= $configuration->getEmailSmtpUsername(); ?>"><br>
                    <label for="emailSmtpPassword">Password</label><br>
                    <input type="password" name="email_smtp_password" id="emailSmtpPassword" value=""  autocomplete="new-password"><br>
                </fieldset>
            </fieldset><br>
            <input type="submit" class="configuration-input-button-submit" form="configurationForm"><br>
        </div><!-- id="configurationInputDiv" -->
    </form>
</div><!-- id="configurationFormDiv" -->
</body>
</html>
