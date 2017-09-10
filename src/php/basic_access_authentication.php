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

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="PDR"');
    header('HTTP/1.0 401 Unauthorized');
    echo "<H1>" . gettext("Forbidden") . "</H1>\n<p>" . gettext("You don't have permission to access this file.") . "</p>";
    exit;
} else {
    $session->login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], FALSE);
}
