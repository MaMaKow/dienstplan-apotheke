<?php

/**
 * @param string $cookie_name Name of the cookie.
 * @param mixed $cookie_value Value to be stored inside the cookie.
 * @param int $days The number of days until expiration.
 * @return null
 */
function create_cookie($cookie_name, $cookie_value, $days = 7) {
    if (isset($cookie_name) AND isset($cookie_value)) {
        setcookie($cookie_name, $cookie_value, time() + (86400 * $days), "/"); // 86400 = 1 day
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

/**
 *
 * @global array $Mandanten_mitarbeiter
 * @param array $Dienstplan
 * @return int
 */
function calculate_VKcount($Dienstplan) {
    global $Mandanten_mitarbeiter;
    foreach ($Dienstplan as $key => $Dienstplantag) {
        if (isset($Dienstplantag['VK'])) {
            $Plan_anzahl[] = (count($Dienstplantag['VK']));
        } else {
            $Plan_anzahl[] = 0;
        }
    }
    $plan_anzahl = max($Plan_anzahl); //Die Anzahl der Zeilen der Tabelle richtet sich nach dem Tag mit den meisten Einträgen.
    $VKcount = max($plan_anzahl + 1, count($Mandanten_mitarbeiter)); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
    return $VKcount;
}

/**
 *
 * @param mixed $data
 * @return mixed
 */
function sanitize_user_input($data) {
    $clean_data = htmlspecialchars(stripslashes(trim($data)));
    return $clean_data;
}

/**
 *
 * @param array $Dienstplan
 * @return array A list of tie points where the number of employees might change.
 */
function calculate_changing_times($Dienstplan) {
    $Changing_times = array_merge_recursive($Dienstplan[0]['Dienstbeginn'], $Dienstplan[0]['Dienstende'], $Dienstplan[0]['Mittagsbeginn'], $Dienstplan[0]['Mittagsende']);
//    $Changing_times = array_merge_recursive($Dienstplan[0]['Dienstbeginn'],[--Beginn--] ,$Dienstplan[0]['Dienstende'],[--Ende--], $Dienstplan[0]['Mittagsbeginn'],[--Beginn--], $Dienstplan[0]['Mittagsende']);
//    echo "<pre>\$Changing_times:"; var_export($Changing_times); echo "</pre>\n";//die;
    sort($Changing_times);
    $Unique_changing_times = array_unique($Changing_times);
    //Remove empty and null values from the array:
    $Clean_changing_times = array_filter($Unique_changing_times, 'strlen');
    //echo "<pre>\$Clean_changing_times:"; var_export($Clean_changing_times); echo "</pre>\n";die;

    return $Clean_changing_times;
}

function hex2rgb($hexstring) {
    $hex = str_replace("#", "", $hexstring);

    if (strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    $rgb = array($r, $g, $b);
    return implode(",", $rgb); // returns the rgb values separated by commas
    //return $rgb; // returns an array with the rgb values
}

function escape_sql_value($value) {
    if ('NULL' === $value or 'null' === $value) {
        //echo "$value is null<br>\n";
        return $value;
    } elseif (NULL === $value) {
        return 'NULL';
    } else {
        return "'" . $value . "'";
    }
}

function null_from_post_to_mysql($value) {
    if ('' === $value) {
        return 'NULL';
    } else {
        return $value;
    }
}

function print_debug_variable($variable) {
    $argument_list = func_get_args();
    error_log(var_export($argument_list, TRUE));
    return true;
}

/**
 * Test if PHP is running on a Windows machine.
 *
 * @return boolean True if Operating system is Windows.
 */
function runing_on_windows() {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        return true;
    }
    return false;
}

/**
 * Returns the localized name of the month correctly on Windows and *nix
 *
 * On windows systems the function strftime() will not use utf8 encoding.
 * It ignores setlocale().
 *
 * @param int $date_unix unix time.
 * @return string $month_name month name.
 */
function get_utf8_month_name($date_unix) {
    $month_name = strftime("%B", $date_unix);
    if (runing_on_windows()) {
        return utf8_encode($month_name);
    }
    return $month_name;
}

/*
 * This function will guess the root folder
 *
 * Currently there are only two options for the position of php files.
 * They can be directly in the root folder, or they are in the folder ./src/php/ .
 *
 * @return string path of the root folder of the application
 */

function get_root_folder() {
    if (strpos(pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME), 'src/php')) {
        $root_folder = "../../";
    } else {
        $root_folder = "./";
    }
    return $root_folder;
}

/*
 * This function will check if a given string represents a valid date.
 *
 * @param $date_string string any string that is supposed to represent a date.
 * @return bool validity of the date.
 */

function is_valid_date($date_string) {
    return (bool) strtotime($date_string);
}

function tomorow_date_string($date_sql) {
    return date('Y-m-d', strtotime($date_sql, '+1 day'));
}

function yesterday_date_string($date_sql) {
    return date('Y-m-d', strtotime($date_sql, '-1 day'));
}
