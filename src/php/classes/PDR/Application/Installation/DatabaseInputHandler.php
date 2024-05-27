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
 * Description of AdministrationInputHandler
 *
 * @author Mandelkow
 */
class DatabaseInputHandler {

    public function handleUserInputDatabase() {
        $configuration = new \PDR\Application\Installation\InstallConfiguration();
        $installUtility = new \PDR\Application\Installation\InstallUtility();
        $config["database_management_system"] = filter_input(INPUT_POST, "database_management_system", FILTER_SANITIZE_SPECIAL_CHARS, FILTER_NULL_ON_FAILURE);
        $config["database_host"] = filter_input(INPUT_POST, "database_host", FILTER_SANITIZE_SPECIAL_CHARS, FILTER_NULL_ON_FAILURE);
        $config["database_name"] = filter_input(INPUT_POST, "database_name", FILTER_SANITIZE_SPECIAL_CHARS, FILTER_NULL_ON_FAILURE);
        $config["database_port"] = filter_input(INPUT_POST, "database_port", FILTER_SANITIZE_NUMBER_INT, FILTER_NULL_ON_FAILURE);
        // Ensure port an integer or null
        $config["database_port"] = is_numeric($config["database_port"]) ? (int) $config["database_port"] : 3306;
        $config["database_user"] = filter_input(INPUT_POST, "database_user", FILTER_SANITIZE_SPECIAL_CHARS, FILTER_NULL_ON_FAILURE);
        $config["database_password"] = filter_input(INPUT_POST, "database_password", FILTER_SANITIZE_SPECIAL_CHARS, FILTER_NULL_ON_FAILURE);
        $configuration->setConfiguration($config);
        if (!in_array($config["database_management_system"], \PDO::getAvailableDrivers())) {
            $installUtility->addErrorMessage(htmlspecialchars($this->Config["database_management_system"]) . "is not available on this server. Please check the configuration!");
            return FALSE;
        }
        $installDatabase = new \PDR\Application\Installation\InstallDatabase();
        $databaseName = $configuration->getConfiguration()['database_name'];
        $userName = $configuration->getConfiguration()['database_user'];
        $passphrase = $configuration->getConfiguration()['database_password'];
        $databaseManagementSystem = $configuration->getConfiguration()['database_management_system'];
        $host = $configuration->getConfiguration()['database_host'];
        $port = $configuration->getConfiguration()['database_port'];
        $connect_error_info = $installDatabase->connectToDatabase($databaseName, $userName, $passphrase, $databaseManagementSystem, $host, $port);

        if (is_null($connect_error_info) or $connect_error_info[1] === 1049) {
            /*
             * Unknown database
             * Maybe we are able to just create that database.
             * We are using the $database_name.
             */
            if (FALSE === $installDatabase->setupMysqlDatabase()) {
                /**
                 * There was a serious error while trying to create the database.
                 */
                $installUtility->addErrorMessage(gettext("Error while trying to create the database."));
                return FALSE;
            }
        }
        if (FALSE === $installDatabase->createTables($configuration)) {
            /**
             * There was a serious error while trying to create the database tables.
             */
            $installUtility->addErrorMessage(gettext("Error while trying to create the database tables."));
            return FALSE;
        }
        if (FALSE === $installDatabase->fillDatabaseTables()) {
            /**
             * There was a serious error while trying to fill the database tables.
             */
            $installUtility->addErrorMessage(gettext("Error while trying to fill the database tables."));
            return FALSE;
        }
        /**
         * After creating all the tables, we store the state of the table structure in form of a hash inside the database:
         */
        $installDatabase->writePdrDatabaseVersionHash($installUtility);

        if (!$installUtility->hasErrorMessages()) {
            $configuration->writeConfigToSession();
            /*
             * Success, we move to the next page.
             */
            $languageInput = filter_input(INPUT_GET, "language", FILTER_SANITIZE_SPECIAL_CHARS);
            $languageBCP47 = \localization::getLanguage($languageInput);
            header("Location: install_page_admin.php?language=" . $languageBCP47);
            die("<a href='install_page_admin.php?language=" . $languageBCP47 . "'>Please move on to administrative user configuration!</a>");
        } else {
            return FALSE;
        }
    }
}
