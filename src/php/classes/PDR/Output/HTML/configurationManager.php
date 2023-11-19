<?php

/*
 * Copyright (C) 2023 Mandelkow
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

namespace PDR\Output\HTML;

/**
 * Description of configurationManager
 *
 * @author Mandelkow
 */
class configurationManager {

    private static $List_of_configuration_parameter_types = array(
        'application_name' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'database_management_system' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'database_host' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'database_name' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'database_port' => FILTER_SANITIZE_NUMBER_INT,
        'database_user' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'database_password' => FILTER_UNSAFE_RAW,
        'session_secret' => FILTER_UNSAFE_RAW,
        'error_reporting' => FILTER_SANITIZE_NUMBER_INT,
        'display_errors' => FILTER_SANITIZE_NUMBER_INT,
        'log_errors' => FILTER_SANITIZE_NUMBER_INT,
        'error_log' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'LC_TIME' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'timezone' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'language' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'mb_internal_encoding' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'contact_email' => FILTER_SANITIZE_EMAIL,
        'hide_disapproved' => FILTER_SANITIZE_NUMBER_INT,
        'email_method' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'email_smtp_host' => FILTER_SANITIZE_URL,
        'email_smtp_port' => FILTER_SANITIZE_NUMBER_INT,
        'email_smtp_username' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
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
        'en-GB' => 'English',
        'de-DE' => 'Deutsch',
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
        $exec_result = array();
        exec("locale -a", $exec_result);
        foreach ($exec_result as $installed_locale) {
            /*
             * For security reasons we clean the result of the shell execution.
             * This might be paranoid. But I count this as user input.
             */
            $installed_locale_clean = filter_var($installed_locale, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $datalist_locales .= "<option value='" . htmlentities($installed_locale_clean, ENT_QUOTES) . "'>\n";
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
        $user_dialog = new \user_dialog();
        $configuration_file = PDR_FILE_SYSTEM_APPLICATION_PATH . 'config/config.php';
        /*
         * Read the POST values:
         */
        foreach (\PDR\Application\configuration::$List_of_configuration_parameters as $key => $default_value) {
            if (isset($_POST[$key]) and '' !== $_POST[$key]) {
                if ('database_password' === $key) {
                    if ($_POST['database_password'] !== $_POST['database_password_second']) {
                        $user_dialog->add_message(gettext('The passwords do not match.'));
                        $new_config[$key] = $config[$key]; // revert to old password
                        continue;
                    }
                    $have_i_been_pwned = new \have_i_been_pwned();
                    if (!$have_i_been_pwned->password_is_secure($_POST['database_password'])) {
                        $user_dialog->add_message($have_i_been_pwned->get_user_information_string());
                        $new_config[$key] = $config[$key]; // revert to old password
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
            rename($configuration_file, $configuration_file . '_' . date(\DateTime::ATOM));
        }
        $result = file_put_contents($configuration_file, '<?php' . PHP_EOL . ' $config = ' . var_export($new_config, true) . ';');
        chmod($configuration_file, 0660);
        return $new_config;
    }
}
