<!DOCTYPE html>
<?php
$language_ISO_639_1 = getLanguage();

function getLanguage() {// Do not use :string return type declarations here to support PHP below 7.4.0 until this point!
    $language_ISO_639 = filter_input(INPUT_GET, "language", FILTER_SANITIZE_SPECIAL_CHARS);
    if ("en" === $language_ISO_639 or "eng" === $language_ISO_639) {
        $language_ISO_639_1 = "en";
    } elseif ("de" === $language_ISO_639 or "deu" === $language_ISO_639 or "ger" === $language_ISO_639) {
        $language_ISO_639_1 = "de";
    } else {
        $language_ISO_639_1 = "en";
    }
    return $language_ISO_639_1;
}

/**
 * @todo Respect the language choice of the user!
 */
?>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PDR Installation Wizard</title>
    </head>
    <body>
        <h1>Welcome to the PDR Installation Wizard</h1>

        <h2>Database Settings</h2>
        <p>In order to proceed with the installation, you will need the following database settings:</p>
        <ul>
            <li>Database Type: [e.g., MySQL, MariaDB]</li>
            <li>Database Server Hostname or DSN: [e.g., localhost]</li>
            <li>Database Server Port: [e.g. 3306]</li>
            <li>Database Name: [e.g., pdr_database]</li>
            <li>Database Username: [e.g., pdr_user]</li>
            <li>Database Passphrase: [your secure passphrase]</li>
        </ul>

        <p>Please ensure you have this information ready before proceeding.</p>

        <h2>Supported Database Systems</h2>
        <p>PDR currently supports the following database systems:</p>
        <ul>
            <li>MySQL / MariaDB 5.1 or above</li>
        </ul>

        <h2>Database User Creation</h2>
        <p>During installation, a special database user prefixed with 'pdr' will be created with limited privileges specifically for PDR.
            If the database does not exist, the installer will attempt to create it.</p>
        <p>This ensures secure access to the database while minimizing potential risks.</p>
        <p> If the pdr user can not be created, then the installer will fallback to using the given administrator user and password.
            This user might have more than the necessary privileges.</p>

        <form action="install_page_check_requirements.php" method="post">
            <input type="submit" id="InstallPageWelcomeFormButton" value="Next">
        </form>
    </body>
</html>
