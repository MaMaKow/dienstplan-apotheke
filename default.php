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
require_once 'bootstrap.php';

/*
 * session management
 */
$session = new sessions;

/*
 * Guess the navigator (=browser) language from HTTP_ACCEPT_LANGUAGE:
 * This is used in the head.php
 */
$navigator_language = "de-DE"; //default language
if (filter_has_var(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE')) {
    $navigator_languages = preg_split('/[,;]/', filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE', FILTER_SANITIZE_SPECIAL_CHARS));
    $navigator_language = $navigator_languages[0]; //ignore the other options
}
