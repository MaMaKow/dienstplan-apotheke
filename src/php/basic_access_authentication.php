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
        const PDR_HTTP_401_UNAUTHORIZED_RESPONSE_TEXT = "<H1>Unauthorized</H1><p>This server could not verify that you are authorized to access the document you requested. Either you supplied the wrong credentials (e.g., bad password), or your browser doesn't understand how to supply the credentials required.</p>";
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="PDR"');
    header('HTTP/1.0 401 Unauthorized');
    die(PDR_HTTP_401_UNAUTHORIZED_RESPONSE_TEXT);
} else {
    $login_success = $session->login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], FALSE);
    if (TRUE !== $login_success) {
        header('WWW-Authenticate: Basic realm="PDR"');
        header('HTTP/1.0 401 Unauthorized');
        die(PDR_HTTP_401_UNAUTHORIZED_RESPONSE_TEXT);
    }
}
