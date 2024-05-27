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
 * @todo Make sure, that the necessary version og PHP is displayed and checked as early as possible.
 *
 * @author Mandelkow
 */
class SystemRequirementsValidator {

    private $installUtility;
    private $configuration;

    /**
     * @var int PHP_VERSION_ID_REQUIRED
     * The requirements have been calculated by phpcompatinfo-5.0.12
     * for commit cd2423025433eeedf8d504c5fdeb05602ce71c24
     */
    const PHP_VERSION_ID_REQUIRED = 80000;

    public function __construct(InstallUtility $installUtility, InstallConfiguration $configuration) {
        $this->installUtility = $installUtility;
        $this->configuration = $configuration;
    }

    public function databaseDriverIsInstalled(): bool {
        $pdrSupportedDatabaseManagementSystems = array("mysql");
        if (empty(array_intersect(\PDO::getAvailableDrivers(), $pdrSupportedDatabaseManagementSystems))) {
            $this->installUtility->addErrorMessage("No compatible database driver found. Please install one of the following database management systems and the corresponding PHP driver!");
            $this->installUtility->addErrorMessage(explode(", ", $pdrSupportedDatabaseManagementSystems));
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function webserverSupportsHttps(string $pdrFileSystemApplicationPath): bool {
        require_once $pdrFileSystemApplicationPath . 'src/php/classes/class.sessions.php';
        $this->tryHttps();
        $https = filter_input(INPUT_SERVER, "HTTPS", FILTER_SANITIZE_SPECIAL_CHARS);

        if (!empty($https) and $https === "on") {
            return TRUE;
        } else {
            $this->installUtility->addErrorMessage("This webserver does not seem to support HTTPS. Please enable Hypertext Transfer Protocol Secure (HTTPS)!");
            return FALSE;
        }
    }

    private function tryHttps(): bool {
        if (!isset($_SESSION['numberOfTimesRedirected'])) {
            $_SESSION['numberOfTimesRedirected'] = 0;
        }
        $https_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
            if (!headers_sent() and ( ++$_SESSION['numberOfTimesRedirected'] ) < 3) {
                header("Status: 301 Moved Permanently");
                header("Location: $https_url");
            } elseif (( ++$_SESSION['numberOfTimesRedirected'] ) < 3) {
                echo '<script type="javascript">document.location.href="' . $https_url . '";</script>';
            }
            return FALSE;
        }
        return TRUE;
    }

    public function pdrDirectoriesAreWritable() {
        $listOfDirectories = array(
            "upload",
            "tmp",
            "config",
        );
        foreach ($listOfDirectories as $directoryName) {
            if (!is_writable($this->configuration->getPdrFileSystemApplicationPath() . $directoryName)) {
                $listOfNonWritableDirectories[] = $directoryName;
            }
        }
        if (!empty($listOfNonWritableDirectories)) {
            $this->installUtility->addErrorMessage(
                    sprintf(
                            ngettext('The directory %1$s is not writable.', 'The directories %1$s are not writable.',
                                    count($listOfNonWritableDirectories)
                            ),
                            $this->fancy_implode($listOfNonWritableDirectories, ", ")
                    )
            );

            $this->installUtility->addErrorMessage(gettext("Make sure that the directories are writable by pdr!"));
            if (function_exists('posix_getpwuid')) {
                /**
                 * Unix / Linux / MacOS:
                 */
                $currentWwwUser = posix_getpwuid(posix_geteuid())["name"];
            } else {
                /**
                 * Windows:
                 */
                $currentWwwUser = getenv('USERNAME');
            }
            $this->installUtility->addErrorMessage(
                    "<pre class='install_cli'>"
                    . "sudo chown -R " . $currentWwwUser . ":" . $currentWwwUser . " " . $this->configuration->getPdrFileSystemApplicationPath()
                    . "</pre>\n");
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function pdrSecretDirectoriesAreNotVisible(): bool {
        $testHtaccess = new \PDR\Application\Installation\TestHtaccess();
        $insecureFolders = $testHtaccess->getInsecureFoldersList();

        foreach ($insecureFolders as $insecureFolder) {
            $this->installUtility->addErrorMessage("Folder " . "$insecureFolder" . " is not secure.");
        }
        return $testHtaccess->allFoldersAreSecure();
    }

    /**
     * @todo This function does not seem to work properly? Test manually!
     * @return bool
     */
    public function phpExtensionRequirementsAreFulfilled() {
        /*
         * The requirements have been calculated by phpcompatinfo-5.0.12
         * for commit cd2423025433eeedf8d504c5fdeb05602ce71c24
         */
        $loadedExtensions = get_loaded_extensions();
        /**
         * 'posix' is not required.
         * Windows does not and can not have it. Linux has it by default.
         */
        $requiredExtensions = array(
            'calendar',
            'core',
            'ctype',
            'curl',
            'date',
            'filter',
            'gettext',
            'hash',
            'iconv',
            'imap',
            'intl',
            'json',
            'mbstring',
            'openssl',
            'pcre',
            'pdo',
            'posix',
            'session',
            'spl',
            'standard',
        );
        $success = TRUE;
        foreach ($requiredExtensions as $requiredExtension) {
            // Convert both strings to lowercase for case-insensitive comparison
            $requiredExtensionLower = strtolower($requiredExtension);
            $found = false;
            foreach ($loadedExtensions as $loadedExtension) {
                if (strtolower($loadedExtension) === $requiredExtensionLower) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $this->installUtility->addErrorMessage("PHP extension $requiredExtension is missing.");
                $success = false;
            }
        } return $success;
    }

    public function phpVersionRequirementIsFulfilled($version_required = self::PHP_VERSION_ID_REQUIRED) {
        $versionRequiredStringMajor = \round($version_required / 10000, 0);
        $versionRequiredStringMinor = \round($version_required % 10000 / 100, 0);
        $versionRequiredStringRelease = \round($version_required % 100, 0);
        $versionRequiredString = $versionRequiredStringMajor
                . "." . $versionRequiredStringMinor
                . "." . $versionRequiredStringRelease;
        /*
         * The requirements have been calculated by phpcompatinfo-5.0.12
         * for commit cd2423025433eeedf8d504c5fdeb05602ce71c24
         * usage:
         * php phpcompatinfo-5.0.12.phar analyser:run ..
         * with phpcompatinfo-5.0.12.phar lying in the folder tests/
         */
        /*
         *  PHP_VERSION_ID is available as of PHP 5.2.7,
         *  if our version is lower than that, then emulate it:
         */
        if (!defined('PHP_VERSION_ID')) {
            $version = explode('.', PHP_VERSION);
            define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
        }

        /* PHP_VERSION_ID is defined as a number, where the higher the number is,
         * the newer a PHP version is used. It's defined as used in the above expression:
         *
         * $version_id = $major_version * 10000 + $minor_version * 100 + $release_version;
         *
         * Now with PHP_VERSION_ID we can check for features this PHP version
         * may have, this doesn't require to use version_compare() everytime
         * you check if the current PHP version may not support a feature.
         *
         */
        if (PHP_VERSION_ID < $version_required) {
            $this->installUtility->addErrorMessage("The PHP version running on this webserver is " . PHP_VERSION
                    . " but the application requires at least version " . $versionRequiredString . ".");
            return FALSE;
        } else {
            return TRUE;
        }
    }
}
