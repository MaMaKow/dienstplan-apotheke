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
 * This PHP class, named `configuration`, serves as a container for managing configuration settings in an application.
 * It encapsulates the logic for loading configuration parameters from an external file, provides getter methods
 * for accessing specific configuration values, and includes default values for parameters that may not be set.
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 * @namespace PDR\Application
 */
class configuration {

    private static $loadedConfig; // This will store the loaded configuration

    /**
     * Constructor method to load the configuration.
     * If the configuration has not been loaded yet, it includes the external configuration file,
     * assigns the loaded configuration to the class property, and ensures subsequent calls to this constructor
     * do not reload the configuration.
     */
    public function __construct() {
        if (null == self::$loadedConfig) {
            // Load the configuration file
            $config = array();
            require PDR_FILE_SYSTEM_APPLICATION_PATH . "/config/config.php";

            // Assign the loaded configuration to the class property
            self::$loadedConfig = $config;
        }
    }

    /**
     * Retrieves the application name from the loaded configuration.
     * If not set, falls back to the default value.
     *
     * @return string The application name.
     */
    public function getApplicationName(): string {
        if (isset(self::$loadedConfig['application_name'])) {
            return self::$loadedConfig['application_name'];
        } else {
            return self::$List_of_configuration_parameters['application_name'];
        }
    }

    /**
     * Retrieves the database management system from the loaded configuration.
     * If not set, falls back to the default value.
     *
     * @return string The database management system.
     */
    public function getDatabaseManagementSystem(): string {
        if (isset(self::$loadedConfig['database_management_system'])) {
            return self::$loadedConfig['database_management_system'];
        } else {
            return self::$List_of_configuration_parameters['database_management_system'];
        }
    }

    /**
     * Retrieves the database host from the loaded configuration.
     * If not set, falls back to the default value.
     *
     * @return string The database host.
     */
    public function getDatabaseHost(): string {
        if (isset(self::$loadedConfig['database_host'])) {
            return self::$loadedConfig['database_host'];
        } else {
            return self::$List_of_configuration_parameters['database_host'];
        }
    }

    /**
     * Retrieves the database name from the loaded configuration.
     * If not set, falls back to the default value.
     *
     * @return string The database name.
     */
    public function getDatabaseName(): string {
        if (isset(self::$loadedConfig['database_name'])) {
            return self::$loadedConfig['database_name'];
        } else {
            return self::$List_of_configuration_parameters['database_name'];
        }
    }

    /**
     * Retrieves the database port from the loaded configuration.
     * If not set, falls back to the default integer value.
     *
     * @return int The database port.
     */
    public function getDatabasePort(): int {
        if (isset(self::$loadedConfig['database_port'])) {
            return (int) self::$loadedConfig['database_port'];
        } else {
            return (int) self::$List_of_configuration_parameters['database_port'];
        }
    }

    /**
     * Retrieves the database user from the loaded configuration.
     * If not set, falls back to the default value.
     *
     * @return string The database user.
     */
    public function getDatabaseUser(): string {
        if (isset(self::$loadedConfig['database_user'])) {
            return self::$loadedConfig['database_user'];
        } else {
            return self::$List_of_configuration_parameters['database_user'];
        }
    }

    /**
     * Retrieves the database password from the loaded configuration.
     * If not set, falls back to the default value.
     *
     * @return string The database password.
     */
    public function getDatabasePassword(): string {
        if (isset(self::$loadedConfig['database_password'])) {
            return self::$loadedConfig['database_password'];
        } else {
            return self::$List_of_configuration_parameters['database_password'];
        }
    }

    /**
     * Retrieves the session secret from the loaded configuration.
     * If not set, falls back to the default value.
     *
     * @return string The session secret.
     */
    public function getSessionSecret(): string {
        if (isset(self::$loadedConfig['session_secret'])) {
            return self::$loadedConfig['session_secret'];
        } else {
            return self::$List_of_configuration_parameters['session_secret'];
        }
    }

    public function getSecretKey() {
        if (isset(self::$loadedConfig['secret_key'])) {
            return self::$loadedConfig['secret_key'];
        } else {
            $secretKey = bin2hex(random_bytes(64));
            $this->write_new_config_entry('secret_key', $secretKey);
        }
    }

    /**
     * Retrieves the error reporting level from the loaded configuration.
     * If not set, falls back to the default integer value.
     *
     * @return int The error reporting level.
     */
    public function getErrorReporting(): int {
        if (isset(self::$loadedConfig['error_reporting'])) {
            return (int) self::$loadedConfig['error_reporting'];
        } else {
            return (int) self::$List_of_configuration_parameters['error_reporting'];
        }
    }

    /**
     * Retrieves the display errors setting from the loaded configuration.
     * If not set, falls back to the default boolean value.
     *
     * @return bool The display errors setting.
     */
    public function getDisplayErrors(): bool {
        if (isset(self::$loadedConfig['display_errors'])) {
            return (bool) self::$loadedConfig['display_errors'];
        } else {
            return (bool) self::$List_of_configuration_parameters['display_errors'];
        }
    }

    /**
     * Retrieves the log errors setting from the loaded configuration.
     * If not set, falls back to the default boolean value.
     *
     * @return bool The log errors setting.
     */
    public function getLogErrors(): bool {
        if (isset(self::$loadedConfig['log_errors'])) {
            return self::$loadedConfig['log_errors'];
        } else {
            return self::$List_of_configuration_parameters['log_errors'];
        }
    }

    /**
     * Retrieves the error log file path from the loaded configuration.
     * If not set, falls back to the default value.
     *
     * @return string The error log file path.
     */
    public function getErrorLog(): string {
        if (isset(self::$loadedConfig['error_log'])) {
            return self::$loadedConfig['error_log'];
        } else {
            return self::$List_of_configuration_parameters['error_log'];
        }
    }

    /**
     * Retrieves the LC_TIME setting from the loaded configuration.
     * If not set, falls back to the default value.
     *
     * @return string The LC_TIME setting.
     */
    public function getLC_TIME(): string {
        if (isset(self::$loadedConfig['LC_TIME'])) {
            return self::$loadedConfig['LC_TIME'];
        } else {
            return self::$List_of_configuration_parameters['LC_TIME'];
        }
    }

    /**
     * Retrieves the timezone setting from the loaded configuration.
     * If not set, falls back to the default value.
     *
     * @return string The timezone setting.
     */
    public function getTimezone(): string {
        if (isset(self::$loadedConfig['timezone'])) {
            return self::$loadedConfig['timezone'];
        } else {
            return self::$List_of_configuration_parameters['timezone'];
        }
    }

    /**
     * Retrieves the language setting from the loaded configuration.
     * If not set, falls back to the default value.
     *
     * @return string The language setting (e.g. de-DE).
     */
    public function getLanguage(): string {
        if (isset(self::$loadedConfig['language'])) {
            return self::$loadedConfig['language'];
        } else {
            return self::$List_of_configuration_parameters['language'];
        }
    }

    /**
     * Retrieves the mb_internal_encoding setting from the loaded configuration.
     * If not set, falls back to the default value.
     *
     * @return string The mb_internal_encoding setting.
     */
    public function getMb_internal_encoding(): string {
        if (isset(self::$loadedConfig['mb_internal_encoding'])) {
            return self::$loadedConfig['mb_internal_encoding'];
        } else {
            return self::$List_of_configuration_parameters['mb_internal_encoding'];
        }
    }

    /**
     * Retrieves the contact email setting from the loaded configuration.
     * If not set, falls back to the default value.
     *
     * @return string The contact email setting.
     */
    public function getContactEmail(): string {
        if (isset(self::$loadedConfig['contact_email'])) {
            return self::$loadedConfig['contact_email'];
        } else {
            return self::$List_of_configuration_parameters['contact_email'];
        }
    }

    /**
     * Retrieves the hide_disapproved setting from the loaded configuration.
     * If not set, falls back to the default value.
     *
     * @return bool The hide_disapproved setting.
     */
    public function getHideDisapproved(): bool {
        if (isset(self::$loadedConfig['hide_disapproved'])) {
            return self::$loadedConfig['hide_disapproved'];
        } else {
            return self::$List_of_configuration_parameters['hide_disapproved'];
        }
    }

    /**
     * Retrieves the email method setting from the loaded configuration.
     * If not set, falls back to the default value.
     *
     * @return string The email method setting.
     */
    public function getEmailMethod(): string {
        if (isset(self::$loadedConfig['email_method'])) {
            return self::$loadedConfig['email_method'];
        } else {
            return self::$List_of_configuration_parameters['email_method'];
        }
    }

    /**
     * Retrieves the SMTP host for email from the loaded configuration.
     * This value is only relevant, if the email method is SMTP.
     * If not set, falls back to the default value.
     *
     * @return string|null The SMTP host for email.
     */
    public function getEmailSmtpHost(): ?string {
        if (isset(self::$loadedConfig['email_smtp_host'])) {
            return self::$loadedConfig['email_smtp_host'];
        } else {
            return self::$List_of_configuration_parameters['email_smtp_host'];
        }
    }

    /**
     * Retrieves the SMTP port for email from the loaded configuration.
     * This value is only relevant, if the email method is SMTP.
     * If not set, falls back to the default value.
     *
     * @return int|null The SMTP port for email.
     */
    public function getEmailSmtpPort(): ?int {
        if (isset(self::$loadedConfig['email_smtp_port'])) {
            return self::$loadedConfig['email_smtp_port'];
        } else {
            return self::$List_of_configuration_parameters['email_smtp_port'];
        }
    }

    /**
     * Retrieves the SMTP username for email from the loaded configuration.
     * This value is only relevant if the email method is SMTP.
     * If not set, falls back to the default value.
     *
     * @return string|null The SMTP username for email.
     */
    public function getEmailSmtpUsername(): ?string {
        if (isset(self::$loadedConfig['email_smtp_username'])) {
            return self::$loadedConfig['email_smtp_username'];
        } else {
            return self::$List_of_configuration_parameters['email_smtp_username'];
        }
    }

    /**
     * Retrieves the SMTP password for email from the loaded configuration.
     * This value is only relevant if the email method is SMTP.
     * If not set, falls back to the default value.
     *
     * @return string|null The SMTP password for email.
     */
    public function getEmailSmtpPassword(): ?string {
        if (isset(self::$loadedConfig['email_smtp_password'])) {
            return self::$loadedConfig['email_smtp_password'];
        } else {
            return self::$List_of_configuration_parameters['email_smtp_password'];
        }
    }

    /**
     * @var array $List_of_configuration_parameters <p>
     * The array contains all available configuration parameters and their default values.
     * These default values serve as a reference for configuration and can be overridden
     * by specific configurations loaded during runtime. This array serves as a centralized
     * point for managing default configuration values.
     * </p>
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

    private function write_new_config_entry(string $key, $value): void {
        $configuration_file = PDR_FILE_SYSTEM_APPLICATION_PATH . 'config/config.php';
        self::$loadedConfig[$key] = $value;
        if (file_exists($configuration_file)) {
            rename($configuration_file, $configuration_file . '_' . date(\DateTime::ATOM));
        }
        $result = file_put_contents($configuration_file, '<?php' . PHP_EOL . ' $config = ' . var_export(self::$loadedConfig, true) . ';' . PHP_EOL);
        chmod($configuration_file, 0660);
    }
}
