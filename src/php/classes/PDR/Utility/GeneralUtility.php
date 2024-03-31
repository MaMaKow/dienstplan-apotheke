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

namespace PDR\Utility;

/**
 * General utility functions
 *
 * @author Mandelkow
 */
abstract class GeneralUtility {

    /**
     * Test if PHP is running on a Windows machine.
     *
     * @return boolean True if Operating system is Windows.
     */
    public static function runningOnWindows() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return true;
        }
        return false;
    }

    /**
     * Logs a variable along with its name and context to the error log.
     *
     * @param mixed $variable The variable to log.
     */
    public static function printDebugVariable($variable) {
        /*
         * Enhanced with https://stackoverflow.com/a/19788805/2323627
         */
        $line = 0;
        $name = '';
        $argument_list = func_get_args();
        /**
         * Retrieve caller information.
         */
        $backtrace = debug_backtrace()[0];
        /*
         * Open the source file returned by debug_backtrace and find the line which called this function:
         */
        $fh = fopen($backtrace['file'], 'r');
        while (++$line <= $backtrace['line']) {
            $code = fgets($fh);
        }
        fclose($fh);
        /*
         * In the found line of source code, grep for the argument (= variable name):
         */
        preg_match('/' . __FUNCTION__ . '\s*\((.*)\)\s*;/u', $code, $name);
        $variable_name = trim($name[1]);
        /*
         * Write a structured output to the standard error log:
         */
        error_log('in file: ' . $backtrace['file'] . "\n on line: " . $backtrace['line'] . "\n variable: " . $variable_name . "\n value:\n " . var_export($argument_list, TRUE));
    }

    /**
     * @param string $cookie_name Name of the cookie.
     * @param mixed $cookie_value Value to be stored inside the cookie.
     * @param int $days The number of days until expiration.
     * @return null
     */
    public static function createCookie(string $cookie_name, $cookie_value, float $days = 7) {
        if (isset($cookie_name) AND isset($cookie_value)) {
            $minutes = round($days * 24 * 60);
            $Expire_obj = (new \DateTime())->add(new \DateInterval('PT' . $minutes . 'M'));
            //function setcookie(string $name, string $value = "", int $expires = 0, string $path = "", string $domain = "", bool $secure = FALSE, bool $httponly = FALSE): bool {}
            $name = $cookie_name;
            $value = $cookie_value;
            $expires = $Expire_obj->getTimestamp();
            $path = PDR_HTTP_SERVER_APPLICATION_PATH;
            $domain = "." . PDR_HTTP_SERVER_DOMAIN; //The dot is necessary for all domains, which are no subdomains, at least for some browsers.
            $arr_cookie_options = array(
                'expires' => $expires,
                'path' => $path,
                'domain' => $domain, // leading dot for compatibility or use subdomain
                'secure' => true,
                'httponly' => true,
                'samesite' => "Strict", // None || Lax  || Strict
            );
            setcookie($name, $value, $arr_cookie_options);
        }
    }
}
