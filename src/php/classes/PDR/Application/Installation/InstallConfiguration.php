<?php

/*
 * Copyright (C) 2024 Mandelkow
 *
 * Dienstplan Apotheke
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace PDR\Application\Installation;

/**
 * Description of installConfiguration
 *
 * @author Mandelkow
 */
class InstallConfiguration {

    private static $configuration;
    private static $pdrFileSystemApplicationPath;
    private static $databaseUserSelf;
    private static $databasePassphraseSelf;

    public function __construct() {
        $installUtility = new InstallUtility();
        self::$pdrFileSystemApplicationPath = $installUtility->getPdrFileSystemApplicationPath();
        $this->readConfigFromSession();
    }

    function __destruct() {
        $this->writeConfigToSession();
    }

    public function getPdrFileSystemApplicationPath(): string {
        return self::$pdrFileSystemApplicationPath;
    }

    public function setDatabaseUserSelf(string $userName): void {
        self::$databaseUserSelf = $userName;
    }

    public function setDatabaseUser(string $userName): void {
        self::$configuration['database_user'] = $userName;
    }

    public function setDatabasePassphrase(string $passphrase): void {
        self::$configuration['database_password'] = $passphrase;
    }

    public function unsetDatabaseUserSelf(): void {
        self::$databaseUserSelf = null;
    }

    public function getDatabaseUserSelf(): string {
        return self::$databaseUserSelf;
    }

    public function getDatabaseName(): string {
        return self::$configuration['database_name'];
    }

    public function getDatabasePassphrase(): string {
        return self::$configuration['database_password'];
    }

    public function getDatabaseUser(): string {
        return self::$configuration['database_user'];
    }

    public function getDatabaseHost(): string {
        return self::$configuration['database_host'];
    }

    public function setDatabasePassphraseSelf(string $passphrase): void {
        self::$databasePassphraseSelf = $passphrase;
    }

    public function unsetDatabasePassphraseSelf(): void {
        self::$databasePassphraseSelf = null;
    }

    public function getDatabasePassphraseSelf(): string {
        return self::$databasePassphraseSelf;
    }

    public function getConfiguration(): array {
        return self::$configuration;
    }

    public function setConfiguration($config): void {
        if (!empty($config)) {
            foreach ($config as $key => $value) {
                self::$configuration[$key] = $value;
            }
        }
    }

    private function readConfigFromSession() {
        session_start();
        if (!empty($_SESSION['configuration'])) {
            foreach ($_SESSION['configuration'] as $key => $value) {
                if (!isset(self::$configuration[$key])) {
                    self::$configuration[$key] = $value;
                }
            }
        }
    }

    public function writeConfigToSession() {
        $_SESSION['configuration'] = self::$configuration;
    }

    public function writeConfigToFile(): bool {
        self::$configuration["contact_email"] = self::$configuration["admin"]["email"];
        self::$configuration["session_secret"] = \bin2hex(\random_bytes(8)); //In case there are several instances of the program on the same machine
        unset(self::$configuration["admin"]);
        $result = \file_put_contents(self::$pdrFileSystemApplicationPath . 'config/config.php', '<?php' . \PHP_EOL . '$config =' . \var_export(self::$configuration, true) . ';');
        if (FALSE === $result) {
            $installUtility = new InstallUtility();
            $installUtility->addErrorMessage(\gettext("Error while writing the configuration to the filesystem."));
            return FALSE;
        }
        return TRUE;
    }

    public function configExistsInFile() {
        $configFilename = self::$pdrFileSystemApplicationPath . 'config/config.php';
        if (file_exists($configFilename)) {
            include $configFilename;
            if (!empty($config)) {
                /*
                 * Config file was written.
                 * And the config array is not empty.
                 */
                return TRUE;
            }
        }
        return FALSE;
    }
}
