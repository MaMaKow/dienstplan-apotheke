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

require_once '../../../default.php';
require_once PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
$session->exit_on_missing_privilege('administration');

$test_htaccess = new test_htaccess();

echo $user_dialog->build_messages();

if (TRUE === $test_htaccess->all_folders_are_secure) {
    echo "All hidden folders are secure.";
}
