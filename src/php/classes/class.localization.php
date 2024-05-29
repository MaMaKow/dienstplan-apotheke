<?php

/*
 * Copyright (C) 2019 Mandelkow <netbeans@martin-mandelkow.de>
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
 * Description of class
 *
 * @author Martin Mandelkow <netbeans@martin-mandelkow.de>
 */
abstract class localization {

    /**
     * gettext function that does return empty strings if empty strings are inserted
     *
     * There is a known bug in gettext:
     * When an empty string is used for msgid, the functions may return a nonempty string.
     * As a result gettext returns the headers from .mo files if the message parameter is set to empty.
     *
     * @var string english input string
     * @return string localized string
     */
    public static function gettext($text) {
        if (empty($text)) {
            return "";
        } else {
            return gettext($text);
        }
    }

    /**
     *  public static
     */
    public static function initialize_gettext($locale) {
        if (\PDR\Utility\GeneralUtility::runningOnWindows()) {
            /*
             * Windows accepts the locale string as en-GB while linux accepts en_GB.
             * These lines replace the underscore _ by the dash - and vice versa.
             */
            $locale = preg_replace('~([a-z]{2,3})_([A-Z]{2,3})~', '\1-\2', $locale);
        } else {
            $locale = preg_replace('~([a-z]{2,3})-([A-Z]{2,3})~', '\1_\2', $locale);
        }
        if (FALSE === putenv("LANGUAGE=$locale")) {
            error_log('putenv failed');
        }
        if (FALSE === putenv("LANG=$locale")) {
            error_log('putenv failed');
        }

//setlocale(LC_ALL, $locale);
        if (defined('LANGUAGE')) {
            if (FALSE === setlocale(LANGUAGE, $locale, $locale . ".UTF-8")) {
                error_log('setlocale failed: locale function is not available on this platform, or the given local (' . $locale . ') does not exist in this environment');
            }
        }
        if (defined('LC_CTYPE')) {
            if (FALSE === setlocale(LC_CTYPE, $locale, $locale . ".UTF-8")) {
                error_log('setlocale failed: locale function is not available on this platform, or the given local (' . $locale . ') does not exist in this environment');
            }
        }
        if (defined('LC_COLLATE')) {
            if (FALSE === setlocale(LC_COLLATE, $locale, $locale . ".UTF-8")) {
                error_log('setlocale failed: locale function is not available on this platform, or the given local (' . $locale . ') does not exist in this environment');
            }
        }
        if (defined('LANG')) {
            if (FALSE === setlocale(LANG, $locale, $locale . ".UTF-8")) {
                error_log('setlocale failed: locale function is not available on this platform, or the given local (' . $locale . ') does not exist in this environment');
            }
        }
        if (defined('LC_MESSAGES')) {
            if (FALSE === setlocale(LC_MESSAGES, $locale, $locale . ".UTF-8")) {
                error_log('setlocale failed: locale function is not available on this platform, or the given local (' . $locale . ') does not exist in this environment');
            }
        }
        if (FALSE === setlocale(LC_COLLATE, $locale, $locale . ".UTF-8")) {
            error_log('setlocale failed: locale function is not available on this platform, or the given local (' . $locale . ') does not exist in this environment');
        }
        if (FALSE === bindtextdomain("messages", PDR_FILE_SYSTEM_APPLICATION_PATH . "locale")) {
            error_log('bindtextdomain failed: maybe the file does not exist');
        }
        if (FALSE === textdomain("messages")) {
            error_log('textdomain failed');
        }
        if (FALSE === bind_textdomain_codeset("messages", 'UTF-8')) {
            error_log('bind_textdomain_codeset failed');
        }
    }

    public static function get_weekday_names() {
        $Weekday_names = array(
            1 => gettext('Monday'),
            2 => gettext('Tuesday'),
            3 => gettext('Wednesday'),
            4 => gettext('Thursday'),
            5 => gettext('Friday'),
            6 => gettext('Saturday'),
            7 => gettext('Sunday'),
        );
        return $Weekday_names;
    }

    public static function get_month_names() {
        $Month_names = array(
            1 => gettext('January'),
            2 => gettext('February'),
            3 => gettext('March'),
            4 => gettext('April'),
            5 => gettext('May'),
            6 => gettext('June'),
            7 => gettext('July'),
            8 => gettext('August'),
            9 => gettext('September'),
            10 => gettext('October'),
            11 => gettext('November'),
            12 => gettext('December'),
        );
        return $Month_names;
    }

    /**
     * Standardizes various language code inputs to their BCP47 language code equivalents.
     *
     * This function takes a language code input in various formats and standardizes it
     * to the corresponding BCP47 language code. It supports multiple input formats such as
     * ISO 639-1, ISO 639-2, and other common language code representations.
     *
     * Supported input formats include:
     * - ISO 639-1 (e.g., "en", "de")
     * - ISO 639-2 (e.g., "eng", "deu")
     * - Other common formats (e.g., "en-GB", "de-DE", "en_GB", "de_DE")
     *
     * If the input does not match any known format, it defaults to "de-DE".
     *
     * @param string $languageInput The language code input to be standardized.
     * @return string The standardized BCP47 language code.
     */
    public static function standardizeLanguageCode($languageInput) {// Do not use :string return type declarations here to support PHP below 7.4.0 until this point!
        switch ($languageInput) {
            case "en-GB":
            case "en_GB":
            case "eng":
            case "en":
                return "en-GB";

            case "de-DE":
            case "de_DE":
            case "ger":
            case "deu":
            case "de":
                return "de-DE";
            default:
                return "de-DE";
        }
    }
}
