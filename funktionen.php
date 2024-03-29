<?php

/*
 * Copyright (C) 2017 Mandelkow
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
 * @param string $cookie_name Name of the cookie.
 * @param mixed $cookie_value Value to be stored inside the cookie.
 * @param int $days The number of days until expiration.
 * @return null
 */
function create_cookie(string $cookie_name, $cookie_value, float $days = 7) {
    if (isset($cookie_name) AND isset($cookie_value)) {
        $minutes = round($days * 24 * 60);
        $Expire_obj = (new DateTime())->add(new DateInterval('PT' . $minutes . 'M'));
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

/**
 *
 * @param string $time_string
 * @return float time in hours
 */
function time_from_text_to_int($time_string) {
    list($hour, $minute, $second) = explode(":", $time_string);
    $time_float = $hour + $minute / 60 + $second / 3600;
    return $time_float;
}

/**
 * @param array $arr An array of numbers.
 * @param int $percentile The number of the percentile (usually an integer between 0 and 100).
 * @return float The nth percentile of $arr
 */
function calculate_percentile($arr, $percentile) {
    sort($arr);
    $count = count($arr); //total numbers in array
    $middleval = floor(($count - 1) * $percentile / 100); // find the middle value, or the lowest middle value
    if ($count % 2) { // odd number, middle is the median
        $median = $arr[$middleval];
    } else { // even number, calculate avg of 2 medians
        $low = $arr[$middleval];
        $high = $arr[$middleval + 1];
        $median = (($low + $high) / 2);
    }
    return $median;
}

function print_debug_variable_to_screen($variable) {
    $argument_list = func_get_args();
    echo "<br>"
    . "<pre>";
    //var_export($variable);
    var_dump($argument_list);
    echo "</pre>"
    . "<br>";
}

function print_debug_variable($variable) {
    /*
     * Enhanced with https://stackoverflow.com/a/19788805/2323627
     */
    $line = 0;
    $name = '';
    $argument_list = func_get_args();
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
    /*
     *  $result = error_log('in file: ' . $backtrace['file'] . "\n on line: " . $backtrace['line'] . "\n variable: " . $variable_name . "\n value:\n " . var_export($argument_list, TRUE));
     *  if (FALSE === $result) {
     *     echo "<H1>could not write to error_log</H1>";
     * }
     *
     */
}

/**
 * Test if PHP is running on a Windows machine.
 *
 * @return boolean True if Operating system is Windows.
 */
function running_on_windows() {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        return true;
    }
    return false;
}

/**
 * The current running PHP binary
 *
 * <p>
 * If PHP_BINARY is set and not empty, then the constant is used.
 * If the constant is empty, we use the directory PHP_BINARY and append "php" to it.
 * If that constant also is empty, we resort to simply return "php" and hope, that the shell will handle it.
 * </p>
 *
 * @return string the path of the php binary running this script.
 */
function get_php_binary() {
    if (!empty(PHP_BINARY)) {
        return PHP_BINARY;
    } else {
        if (!empty(PHP_BINDIR)) {
            return PHP_BINDIR . '/php';
        } else {
            return 'php';
        }
    }
}
