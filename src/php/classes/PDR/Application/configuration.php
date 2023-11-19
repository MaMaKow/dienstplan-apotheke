<?php

/*
 * Copyright (C) 2018 Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
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

namespace PDR\Application;

/**
 * Container class for the functions neseccary for configuration
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class configuration {
    /*
     * TODO: Documentation for the new array SMTP
     * Configuration GUI for SMTP
     */

    private static $loadedConfig; // This will store the loaded configuration

    // Constructor to load the configuration
    public function __construct() {
        if (null == self::$loadedConfig) {
            // Load the configuration file
            $config = array();
            require PDR_FILE_SYSTEM_APPLICATION_PATH . "/config/config.php";

            // Assign the loaded configuration to the class property
            self::$loadedConfig = $config;
        }
    }

    // Getter method for 'application_name'
    public function getApplicationName() {
        if (isset(self::$loadedConfig['application_name'])) {
            return self::$loadedConfig['application_name'];
        } else {
            return self::$List_of_configuration_parameters['application_name'];
        }
    }

    public function getDatabaseManagementSystem() {
        if (isset(self::$loadedConfig['database_management_system'])) {
            return self::$loadedConfig['database_management_system'];
        } else {
            return self::$List_of_configuration_parameters['database_management_system'];
        }
    }

    public function getDatabaseHost() {
        if (isset(self::$loadedConfig['database_host'])) {
            return self::$loadedConfig['database_host'];
        } else {
            return self::$List_of_configuration_parameters['database_host'];
        }
    }

    public function getDatabaseName() {
        if (isset(self::$loadedConfig['database_name'])) {
            return self::$loadedConfig['database_name'];
        } else {
            return self::$List_of_configuration_parameters['database_name'];
        }
    }

    public function getDatabasePort() {
        if (isset(self::$loadedConfig['database_port'])) {
            return self::$loadedConfig['database_port'];
        } else {
            return self::$List_of_configuration_parameters['database_port'];
        }
    }

    public function getDatabaseUser() {
        if (isset(self::$loadedConfig['database_user'])) {
            return self::$loadedConfig['database_user'];
        } else {
            return self::$List_of_configuration_parameters['database_user'];
        }
    }

    public function getDatabasePassword() {
        if (isset(self::$loadedConfig['database_password'])) {
            return self::$loadedConfig['database_password'];
        } else {
            return self::$List_of_configuration_parameters['database_password'];
        }
    }

    public function getSessionSecret() {
        if (isset(self::$loadedConfig['session_secret'])) {
            return self::$loadedConfig['session_secret'];
        } else {
            return self::$List_of_configuration_parameters['session_secret'];
        }
    }

    public function getErrorReporting() {
        if (isset(self::$loadedConfig['error_reporting'])) {
            return self::$loadedConfig['error_reporting'];
        } else {
            return self::$List_of_configuration_parameters['error_reporting'];
        }
    }

    public function getDisplayErrors() {
        if (isset(self::$loadedConfig['display_errors'])) {
            return self::$loadedConfig['display_errors'];
        } else {
            return self::$List_of_configuration_parameters['display_errors'];
        }
    }

    public function getLogErrors() {
        if (isset(self::$loadedConfig['log_errors'])) {
            return self::$loadedConfig['log_errors'];
        } else {
            return self::$List_of_configuration_parameters['log_errors'];
        }
    }

    public function getErrorLog() {
        if (isset(self::$loadedConfig['error_log'])) {
            return self::$loadedConfig['error_log'];
        } else {
            return self::$List_of_configuration_parameters['error_log'];
        }
    }

    public function getLC_TIME() {
        if (isset(self::$loadedConfig['LC_TIME'])) {
            return self::$loadedConfig['LC_TIME'];
        } else {
            return self::$List_of_configuration_parameters['LC_TIME'];
        }
    }

    public function getTimezone() {
        if (isset(self::$loadedConfig['timezone'])) {
            return self::$loadedConfig['timezone'];
        } else {
            return self::$List_of_configuration_parameters['timezone'];
        }
    }

    public function getLanguage() {
        if (isset(self::$loadedConfig['language'])) {
            return self::$loadedConfig['language'];
        } else {
            return self::$List_of_configuration_parameters['language'];
        }
    }

    public function getMb_internal_encoding() {
        if (isset(self::$loadedConfig['mb_internal_encoding'])) {
            return self::$loadedConfig['mb_internal_encoding'];
        } else {
            return self::$List_of_configuration_parameters['mb_internal_encoding'];
        }
    }

    public function getContactEmail() {
        if (isset(self::$loadedConfig['contact_email'])) {
            return self::$loadedConfig['contact_email'];
        } else {
            return self::$List_of_configuration_parameters['contact_email'];
        }
    }

    public function getHideDisapproved() {
        if (isset(self::$loadedConfig['hide_disapproved'])) {
            return self::$loadedConfig['hide_disapproved'];
        } else {
            return self::$List_of_configuration_parameters['hide_disapproved'];
        }
    }

    public function getEmailMethod() {
        if (isset(self::$loadedConfig['email_method'])) {
            return self::$loadedConfig['email_method'];
        } else {
            return self::$List_of_configuration_parameters['email_method'];
        }
    }

    public function getEmailSmtpHost() {
        if (isset(self::$loadedConfig['email_smtp_host'])) {
            return self::$loadedConfig['email_smtp_host'];
        } else {
            return self::$List_of_configuration_parameters['email_smtp_host'];
        }
    }

    public function getEmailSmtpPort() {
        if (isset(self::$loadedConfig['email_smtp_port'])) {
            return self::$loadedConfig['email_smtp_port'];
        } else {
            return self::$List_of_configuration_parameters['email_smtp_port'];
        }
    }

    public function getEmailSmtpUsername() {
        if (isset(self::$loadedConfig['email_smtp_username'])) {
            return self::$loadedConfig['email_smtp_username'];
        } else {
            return self::$List_of_configuration_parameters['email_smtp_username'];
        }
    }

    public function getEmailSmtpPassword() {
        if (isset(self::$loadedConfig['email_smtp_password'])) {
            return self::$loadedConfig['email_smtp_password'];
        } else {
            return self::$List_of_configuration_parameters['email_smtp_password'];
        }
    }

    /**
     * @var array $List_of_configuration_parameters <p>The array contains all available configuration paramaters and their default values.</p>
     */
    public static $List_of_configuration_parameters = array(
        'application_name' => 'PDR',
        'database_management_system' => 'mysql',
        'database_host' => 'localhost',
        'database_name' => '',
        'database_port' => 3306,
        'database_user' => '',
        'database_password' => '',
        'session_secret' => '',
        'error_reporting' => E_ALL,
        'display_errors' => 0,
        'log_errors' => 1,
        'error_log' => PDR_FILE_SYSTEM_APPLICATION_PATH . 'error.log',
        'LC_TIME' => 'C',
        'timezone' => 'Europe/Berlin',
        'language' => 'de-DE',
        'mb_internal_encoding' => 'UTF-8',
        'contact_email' => '',
        'hide_disapproved' => FALSE, //We set it up to false in order not to disconcert new administrators.
        'email_method' => 'mail',
        'email_smtp_host' => NULL, /* e.g. smtp.example.com */
        'email_smtp_port' => 587, /* 587 for ssl */
        'email_smtp_username' => NULL,
        'email_smtp_password' => NULL,
    );
}
