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

/**
 * Container class for the functions neseccary for configuration
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class configuration {
    /*
     * TODO: Documenation for the new array SMTP
     * Configuration GUI for SMTP
     */

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
        'language' => 'de_DE',
        'mb_internal_encoding' => 'UTF-8',
        'contact_email' => '',
        'hide_disapproved' => FALSE, //We set it up to false in order not to disconcert new administrators.
        'email_method' => 'mail',
        'email_smtp_host' => NULL, /* e.g. smtp.example.com */
        'email_smtp_port' => 587, /* 587 for ssl */
        'email_smtp_username' => NULL,
        'email_smtp_password' => NULL,
    );
    private static $List_of_configuration_parameter_types = array(
        'application_name' => FILTER_SANITIZE_STRING,
        'database_management_system' => FILTER_SANITIZE_STRING,
        'database_host' => FILTER_SANITIZE_STRING,
        'database_name' => FILTER_SANITIZE_STRING,
        'database_port' => FILTER_SANITIZE_NUMBER_INT,
        'database_user' => FILTER_SANITIZE_STRING,
        'database_password' => FILTER_UNSAFE_RAW,
        'session_secret' => FILTER_UNSAFE_RAW,
        'error_reporting' => FILTER_SANITIZE_NUMBER_INT,
        'display_errors' => FILTER_SANITIZE_NUMBER_INT,
        'log_errors' => FILTER_SANITIZE_NUMBER_INT,
        'error_log' => FILTER_SANITIZE_STRING,
        'LC_TIME' => FILTER_SANITIZE_STRING,
        'timezone' => FILTER_SANITIZE_STRING,
        'language' => FILTER_SANITIZE_STRING,
        'mb_internal_encoding' => FILTER_SANITIZE_STRING,
        'contact_email' => FILTER_SANITIZE_EMAIL,
        'hide_disapproved' => FILTER_SANITIZE_NUMBER_INT,
        'email_method' => FILTER_SANITIZE_STRING,
        'email_smtp_host' => FILTER_SANITIZE_URL,
        'email_smtp_port' => FILTER_SANITIZE_NUMBER_INT,
        'email_smtp_username' => FILTER_SANITIZE_STRING,
        'email_smtp_password' => FILTER_UNSAFE_RAW,
    );

    /**
     *
     * @var array $List_of_supported_languages <p>a list of language that have translations via gettext.
     * The array has the format language code => language name (e.g. en_GB => English)
     * </p>
     *
     */
    public static $List_of_supported_languages = array(
        'en_GB' => 'English',
        'de_DE' => 'Deutsch',
    );

    const ERROR_ERROR = E_ERROR | E_USER_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_PARSE;
    const ERROR_WARNING = self::ERROR_ERROR | E_WARNING | E_USER_WARNING | E_CORE_WARNING | E_COMPILE_WARNING;
    const ERROR_NOTICE = self::ERROR_WARNING | E_NOTICE | E_USER_NOTICE | E_DEPRECATED | E_USER_DEPRECATED;
    const ERROR_ALL = self::ERROR_NOTICE | E_STRICT;

    /**
     * Translate error classes to human readable strings.
     *
     * @param int $type
     * @return string|int
     */
    function friendly_error_type(int $type) {
        switch ($type) {
            case E_ERROR: // 1 //
                return 'E_ERROR';
            case E_WARNING: // 2 //
                return 'E_WARNING';
            case E_PARSE: // 4 //
                return 'E_PARSE';
            case E_NOTICE: // 8 //
                return 'E_NOTICE';
            case E_CORE_ERROR: // 16 //
                return 'E_CORE_ERROR';
            case E_CORE_WARNING: // 32 //
                return 'E_CORE_WARNING';
            case E_CORE_ERROR: // 64 //
                return 'E_COMPILE_ERROR';
            case E_CORE_WARNING: // 128 //
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR: // 256 //
                return 'E_USER_ERROR';
            case E_USER_WARNING: // 512 //
                return 'E_USER_WARNING';
            case E_USER_NOTICE: // 1024 //
                return 'E_USER_NOTICE';
            case E_STRICT: // 2048 //
                return 'E_STRICT';
            case E_RECOVERABLE_ERROR: // 4096 //
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED: // 8192 //
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED: // 16384 //
                return 'E_USER_DEPRECATED';
        }
        return $type;
    }

    /**
     * Get a list of supported encodings:
     *
     * @return string HTML datalist of supported encodings
     */
    public static function build_supported_encodings_datalist() {
        $datalist_encodings = "<datalist id='encodings'>\n";
        $supported_encodings = mb_list_encodings();
        foreach ($supported_encodings as $key => $supported_encoding) {
            $datalist_encodings .= "<option value='$supported_encoding'>\n";
        }
        $datalist_encodings .= "</datalist>\n";
        return $datalist_encodings;
    }

    /**
     * Get a list of supported locales:
     *
     * @return string HTML datalist of supported locales
     */
    public static function build_supported_locales_datalist() {
        $datalist_locales = "<datalist id='locales'>\n";
        exec("locale -a", $exec_result);
        foreach ($exec_result as $key => $installed_locale) {
            $datalist_locales .= "<option value='$installed_locale'>\n";
        }
        $datalist_locales .= "</datalist>\n";
        return $datalist_locales;
    }

    /**
     * <p>Takes the old $config, the user input and the default configuration,
     * writes it to the configuration file
     * and then returns the new configuration array.</p>
     *
     * @param array $config
     * @return array $new_config
     */
    public static function handle_user_input($config) {
        $user_dialog = new user_dialog();
        $configuration_file = PDR_FILE_SYSTEM_APPLICATION_PATH . 'config/config.php';
        /*
         * Read the POST values:
         */
        foreach (self::$List_of_configuration_parameters as $key => $default_value) {
            if (isset($_POST[$key]) and '' !== $_POST[$key]) {
                if ('database_password' === $key) {
                    if ($_POST['database_password'] !== $_POST['database_password_second']) {
                        $user_dialog->add_message(gettext('Passwords do not match!'));
                        $new_config[$key] = $config[$key];
                        continue;
                    }
                }
                /*
                 * $key will be taken from POST:
                 */
                $new_config[$key] = filter_input(INPUT_POST, $key, self::$List_of_configuration_parameter_types[$key]);
            } elseif (isset($config[$key]) and '' !== $config[$key]) {
                /*
                 * $key will be taken from old $config:
                 */
                $new_config[$key] = $config[$key];
            } else {
                /*
                 * $key will be taken from default value:
                 */
                $new_config[$key] = $default_value;
            }
        }
        if (file_exists($configuration_file)) {
            rename($configuration_file, $configuration_file . '_' . date(DateTime::ATOM));
        }
        $result = file_put_contents($configuration_file, '<?php' . PHP_EOL . ' $config = ' . var_export($new_config, true) . ';');
        chmod($configuration_file, 0660);
        return $new_config;
    }

}
