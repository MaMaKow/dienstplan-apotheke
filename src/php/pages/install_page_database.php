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
$languageBCP47 = localization::getLanguage($languageInput);
localization::initialize_gettext($languageBCP47);
if (filter_has_var(INPUT_POST, "database_user")) {
    $databaseInputHandler = new \PDR\Application\Installation\DatabaseInputHandler();
    if (FALSE === $databaseInputHandler->handleUserInputDatabase()) {
        $installUtility->addErrorMessage(gettext("There was an error while trying to create the database."));
        $installUtility->addErrorMessage(gettext("Please see the error log for details!"));
    }
}
require_once 'install_head.php';
$installUtility->buildErrorMessageDiv();

if (isset($_SESSION['configuration']["database_host"])) {
    $database_host = $_SESSION['configuration']["database_host"];
} else {
    $database_host = "localhost";
}
if (isset($_SESSION['configuration']["database_port"])) {
    $database_port = $_SESSION['configuration']["database_port"];
} else {
    $database_port = "";
}
if (isset($_SESSION['configuration']["database_user"])) {
    $database_user = $_SESSION['configuration']["database_user"];
} else {
    $database_user = "";
}
if (isset($_SESSION['configuration']["database_name"])) {
    $database_name = $_SESSION['configuration']["database_name"];
} else {
    $database_name = "";
}
?>
<H1><?= gettext("Database configuration") ?></H1>

<form accept-charset='utf-8' method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <p>
        <LABEL for="database_management_system"><?= gettext("Database type (DBMS)") ?>:</LABEL><br>
        <select name="database_management_system" id="database_management_system">
            <option value="mysql">MySQL</option>
        </select>
    </p><p>
        <LABEL for="database_host"><?= gettext("Database server hostname") ?>:</LABEL><br>
        <input type="text" id="database_host" name="database_host" value="<?= htmlspecialchars($database_host) ?>" />
        <BR>
    </p><p>

        <LABEL for="database_port"><?= gettext("Database server port") ?>:</LABEL><br>
        <input type="text" id="database_port" name="database_port" value="<?= htmlspecialchars($database_port) ?>" /><!--standard value 3306-->
        <br><span class="hint">Leave this blank unless you know the server operates on a non-standard port.<span>
            </p><p>

            <LABEL for="database_user"><?= gettext("Database username") ?>:</LABEL><br>
            <input type="text" id="database_user" name="database_user" value="<?= htmlspecialchars($database_user) ?>" />
        </p><p>

            <LABEL for="database_password"><?= gettext("Database passphrase") ?>:</LABEL><br>
            <input type="password" id="database_password" name="database_password" value="" />
        </p><p>

            <LABEL for="database_name"><?= gettext("Database name") ?>:</LABEL><br>
            <input type="text" id="database_name" name="database_name" value="<?= htmlspecialchars($database_name) ?>" />
        </p><p>
            <?php
            $installUtility->buildErrorMessageDiv();
            ?>
        </p><p>
            <input type="submit" id="InstallPageDatabaseFormButton" />
        </p>
