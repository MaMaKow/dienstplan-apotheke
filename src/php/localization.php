<?php

/*
 * Copyright (C) 2017 Mandelkow
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


if (isset($config["language"])) {
    $locale = $config["language"];
} else {
    $locale = "de_DE";
}
if (runing_on_windows()) {
    /*
     * Windows accepts the locale string as en-GB while linux accepts en_GB.
     * These lines replace the underscore _ by the dash - and vice versa.
     */
    $locale = preg_replace('~([a-z]{2,3})_([A-Z]{2,3})~', '\1-\2', $locale);
} else {
    $locale = preg_replace('~([a-z]{2,3})-([A-Z]{2,3})~', '\1_\2', $locale);
}
//$locale = $locale . ".UTF-8";
//putenv("LC_ALL=$locale");
putenv("LANGUAGE=$locale");
putenv("LANG=$locale");
//setlocale(LC_ALL, $locale);
           setlocale(LC_MESSAGES, $locale);
$results = setlocale(LC_COLLATE, $locale);
if (!$results) {
    exit('setlocale failed: locale function is not available on this platform, or the given local (' . $locale . ') does not exist in this environment');
}
//TODO: Remove the following line:
bindtextdomain("messages", "./locale/nocache"); //This is only for debugging
bindtextdomain("messages", "./locale");
textdomain("messages");
bind_textdomain_codeset("messages", 'UTF-8');
