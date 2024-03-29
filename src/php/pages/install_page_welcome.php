<!DOCTYPE html>
<?php
require_once '../classes/class.localization.php';
require_once '../classes/PDR/Application/Installation/InstallUtility.php';
require_once '../classes/PDR/Utility/GeneralUtility.php';
$installUtility = new \PDR\Application\Installation\InstallUtility();
$languageInput = filter_input(INPUT_GET, "language", FILTER_SANITIZE_SPECIAL_CHARS);
$languageBCP47 = localization::getLanguage($languageInput);
localization::initialize_gettext($languageBCP47);

/**
 * @todo Respect the language choice of the user!
 */
?>
<html lang="<?= $languageBCP47 ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PDR Installation Wizard</title>
    </head>
    <body>
        <h1><?= gettext("Welcome to the PDR Installation Wizard") ?></h1>

        <h2><?= gettext("Database Settings") ?></h2>
        <p><?= gettext("In order to proceed with the installation, you will need the following database settings:") ?></p>
        <ul>
            <li><?= gettext("Database Type: [e.g., MySQL, MariaDB]") ?></li>
            <li><?= gettext("Database Server Hostname or DSN: [e.g., localhost]") ?></li>
            <li><?= gettext("Database Server Port: [e.g. 3306]") ?></li>
            <li><?= gettext("Database Name: [e.g., pdr_database]") ?></li>
            <li><?= gettext("Database Username: [e.g., pdr_user]") ?></li>
            <li><?= gettext("Database Passphrase: [your secure passphrase]") ?></li>
        </ul>

        <p><?= gettext("Please ensure you have this information ready before proceeding.") ?></p>

        <h2><?= gettext("Supported Database Systems") ?></h2>
        <p><?= gettext("PDR currently supports the following database systems:") ?></p>
        <ul>
            <li><?= gettext("MySQL / MariaDB 5.1 or above") ?></li>
        </ul>

        <h2><?= gettext("Database User Creation") ?></h2>
        <p><?= gettext("During installation, a special database user prefixed with 'pdr' will be created with limited privileges specifically for PDR.
            If the database does not exist, the installer will attempt to create it.") ?></p>
        <p><?= gettext("This ensures secure access to the database while minimizing potential risks.") ?></p>
        <p><?= gettext("If the pdr user can not be created, then the installer will fallback to using the given administrator user and password.
            This user might have more than the necessary privileges.") ?></p>

        <form action="install_page_check_requirements.php?language=<?= $languageBCP47 ?>" method="post">
            <input type="submit" id="InstallPageWelcomeFormButton" value="Next">
        </form>
    </body>
</html>
