<?php

/*
 * Copyright (C) 2018 Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
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
 * The purpose of this file is to be called in the background to do stuff once in a while.
 * It is called upon login() of any user for example.
 * It is the responsibility of the classes to check if there is work to do and how much.
 */
chdir(dirname(__DIR__, 2));
require_once 'bootstrap.php';
$forceMaintenance = false;
if ('POST' === $_SERVER['REQUEST_METHOD']) {
    $forceMaintenanceInput = filter_input(INPUT_POST, 'forceMaintenance');
    if ($forceMaintenanceInput === 'true') {
        $forceMaintenance = true;
    }

    new update_database();
    \PDR\Utility\GeneralUtility::printDebugVariable($forceMaintenance);
    new maintenance($forceMaintenance);
    // new auto_upgrader();
    echo "Done with background maintenance.";
} else {
    http_response_code(405); // Method Not Allowed
    die("This endpoint only supports POST requests.");
}
