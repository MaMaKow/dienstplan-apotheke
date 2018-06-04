<?php

/*
 * Copyright (C) 2018 Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
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
 * @author Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class configuration {

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
     */
    public static function build_supported_encodings_datalist() {
        /*
         *  TODO: is perhaps /usr/share/i18n/SUPPORTED better for supported encodings?
         */
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
        $configuration_file = PDR_FILE_SYSTEM_APPLICATION_PATH . 'config/config.php';
        /*
         * Read the POST values:
         */
        foreach (self::$List_of_configuration_parameters as $key => $value) {
            if (isset($_POST[$key]) and '' !== $_POST[$key]) {
                if ('database_password' === $key) {
                    if ($_POST['database_password'] !== $_POST['database_password_second']) {
                        global $Fehlermeldung;
                        $Fehlermeldung[] = 'Passwords do not match!';
                        $new_config[$key] = $config[$key];
                        continue;
                    }
                }
                //print_debug_variable($key . ' from POST: ' . $_POST[$key]);
                $new_config[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
            } elseif (isset($config[$key]) and '' !== $config[$key]) {
                //print_debug_variable($key . ' from $config: ' . $config[$key]);
                $new_config[$key] = $config[$key];
            } else {
                //print_debug_variable($key . ' from default: ' . $value);
                $new_config[$key] = $value;
            }
        }
        if (file_exists($configuration_file)) {
            rename($configuration_file, $configuration_file . '_' . date(DateTime::ATOM));
        }
        $result = file_put_contents($configuration_file, '<?php' . PHP_EOL . ' $config = ' . var_export($new_config, true) . ';');
        chmod($configuration_file, 0664);
        return $new_config;
    }

}