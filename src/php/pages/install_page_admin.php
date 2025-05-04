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
require_once '../classes/PDR/Application/Installation/InstallUtility.php';
require_once '../classes/class.localization.php';
require_once '../classes/PDR/Utility/GeneralUtility.php';
$installUtility = new \PDR\Application\Installation\InstallUtility();
$languageInput = filter_input(INPUT_GET, "language", FILTER_SANITIZE_SPECIAL_CHARS);
$languageBCP47 = localization::standardizeLanguageCode($languageInput);
localization::initialize_gettext($languageBCP47);
$administrationInputHandler = new \PDR\Application\Installation\AdministrationInputHandler();
if (filter_has_var(INPUT_POST, "user_name")) {
    $administrationInputHandler->handleUserInputAdministration();
}
require_once 'install_head.php';
?>
<h1><?= gettext("Administrator configuration") ?></h1>

<form accept-charset='utf-8' method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <p><?= gettext("Username") ?>:<br>
        <input type="text" name="user_name" placeholder="Administrator username" required value="<?= (!empty($_SESSION['configuration']["user_name"])) ? $_SESSION['configuration']["user_name"] : "" ?>" />
    </p>
    <p>
        <?= gettext("Contact email address") ?>:<br>
        <input type="email" name="email" placeholder="Contact email address:" required value="<?= (!empty($_SESSION['configuration']["email"])) ? $_SESSION['configuration']["email"] : "" ?>" />
    </p>
    <p>
        <?= gettext("Administrator passphrase") ?>:<br>
        <input type="password" name="password" minlength="8" placeholder="Administrator password:" required />
        <br>
        <?php
        //TODO: Build a visible sign for evaluating if the passwords match!
        ?>
        <?= gettext("Please enter a password with a minimum length of 8 characters.") ?>
    </p>
    <p>
        <?= gettext("Confirm administrator passphrase") ?>:<br>
        <input type="password" name="password2" minlength="8" placeholder="Confirm administrator password:" required />
    </p>

    <input type="submit" id="InstallPageAdministratorFormButton"/>
</form>
<?php
echo $installUtility->buildErrorMessageDiv();
?>
</body>
</html>
