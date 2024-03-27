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
class AdministrationInputHandler {

    public function handleUserInputAdministration() {
        $installUtility = new \PDR\Application\Installation\InstallUtility();
        $installConfiguration = new InstallConfiguration();
        $configInput["admin"]["user_name"] = filter_input(INPUT_POST, "user_name", FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_NULL_ON_FAILURE);
        $configInput["admin"]["email"] = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL, FILTER_NULL_ON_FAILURE);
        $configInput["admin"]["password"] = filter_input(INPUT_POST, "password", FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_NULL_ON_FAILURE);
        $configInput["admin"]["password2"] = filter_input(INPUT_POST, "password2", FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_NULL_ON_FAILURE);

        if ($configInput["admin"]["password"] !== $configInput["admin"]["password2"]) {
            $installUtility->addErrorMessage(gettext("The passwords aren't the same."));
            unset($configInput["admin"]["password"], $configInput["admin"]["password2"]); //We get rid of this values as fast as possible.
            return FALSE;
        }
        $password_hash = password_hash($configInput["admin"]["password"], PASSWORD_DEFAULT);
        unset($configInput["admin"]["password"]);
        unset($configInput["admin"]["password2"]);

        try {
            $installConfiguration->getDatabaseName();
        } catch (\TypeError) {
            header("Location: install_page_database.php");
            die("The database connection needs to be setup first.");
        }
        $installDatabase = new InstallDatabase();
        $installDatabase->connectToDatabaseFromConfig($installConfiguration);

        $installConfiguration->setConfiguration($configInput);
        /**
         * Create a user object:
         */
        /**
         * \PDR\Workforce\user_base(); needs to access $config to connect to the database.
         * We create the config variable and make it globally accessible.
         */
        global $config;
        $config = $installConfiguration->getConfiguration();
        $user_base = new \PDR\Workforce\user_base();
        $user = $user_base->guess_user_by_identifier($configInput["admin"]["user_name"]);
        if (false === $user or !$user->exists()) {
            $user_creation_result = $user_base->create_new_user($configInput["admin"]["user_name"], $password_hash, $configInput["admin"]["email"], 'active');
            if (FALSE === $user_creation_result) {
                /*
                 * We were not able to create the administrative user.
                 */
                $installUtility->addErrorMessage(gettext("Error while trying to create administrative user."));
                return FALSE;
            }
            $_SESSION['user_object'] = $user_creation_result;
        } else {
            /*
             * The administrative user already exists.
             * We will not delete it.
             */
            $_SESSION['user_object'] = $user;
            $installUtility->addErrorMessage(gettext("Administrative user already exists."));
        }

        require_once $installUtility->getPdrFileSystemApplicationPath() . 'src/php/classes/class.sessions.php';
        /*
         * The __construct method already calls session_regenerate_id();
         * As long as that stays this way, we do not have to repeat that here.
         * session_regenerate_id(); //To prevent session fixation attacks we regenerate the session id right before setting up login details.
         */
        /**
         * Brute force method of login:
         */
        $_SESSION['user_object'] = $user_base->guess_user_by_identifier($configInput["admin"]["user_name"]);
        if (!$_SESSION['user_object'] instanceof \user) {
            throw new \Exception("User object could not be created from identifier.");
        }
        /*
         * Grant all privileges to the administrative user:
         */
        $resultSetAdministratorPrivileges = $installDatabase->setAdministratorPrivileges($installUtility);
        if (FALSE === $resultSetAdministratorPrivileges) {
            return FALSE;
        }
        $installConfiguration->writeConfigToSession();

        if (FALSE === $installConfiguration->writeConfigToFile()) {
            echo $installUtility->buildErrorMessageDiv();
        } else {
            header("Location: ../../../src/php/pages/user-management.php");
            die("Please move on to the <a href = ../../../src/php/pages/user-management.php>user management</a>");
            return TRUE;
        }
    }
}
