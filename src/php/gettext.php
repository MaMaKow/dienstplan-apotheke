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

/*
 * This file is meant to be used by JavaScript. It will take a GET parameter string_to_translate and return the translated string.
 */
require_once '../../default.php';

//TODO: support for ngettext might be added.
$string_to_translate = filter_input(INPUT_GET, 'string_to_translate', FILTER_SANITIZE_STRING);
$translated_string = gettext($string_to_translate);
echo $translated_string;
