<?php

/*
 * Copyright (C) 2015 Mandelkow
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
require_once '../../../bootstrap.php';
// Get the requested URL
$requestUri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];
// Remove query parameters (i.E. ?key=value)
$requestUriStripped = parse_url($requestUri, PHP_URL_PATH);
// Strip the base path to get only the API endpoint
$basePath = PDR_HTTP_SERVER_APPLICATION_PATH . 'src/php/restful-api';
$apiEndpoint = str_replace($basePath, '', $requestUriStripped);

//\PDR\Utility\GeneralUtility::printDebugVariable($method);
//\PDR\Utility\GeneralUtility::printDebugVariable($requestUri);
//\PDR\Utility\GeneralUtility::printDebugVariable($apiEndpoint);
// Routes registrieren
$router = new ApiRouter();
/**
 *  Authentication Routes
 */
$router->addRoute(
        'POST',
        '/auth/login',
        'AuthController',
        'login',
        'Benutzeranmeldung durchführen'
);

/**
 *  User Routes
 */
$router->addRoute(
        'GET',
        '/users',
        'UserController',
        'getAllUsers',
        'Alle Benutzer abrufen'
);

$router->addRoute(
        'GET',
        '/users/me',
        'UserController',
        'getMyUserData',
        'Daten des aktuell angemeldeten Benutzers abrufen'
);

$router->addRoute(
        'GET',
        '/users/{id}',
        'UserController',
        'getUserById',
        'Details eines bestimmten Benutzers abrufen'
);

/*
 * Employee Routes
 */
$router->addRoute(
        'GET',
        '/employees',
        'EmployeeController',
        'getAllEmployees',
        'Alle Mitarbeiter abrufen'
);

$router->addRoute(
        'GET',
        '/employees/{id}/absences/{year}',
        'AbsenceController',
        'getEmployeeAbsencesByYear',
        'Abwesenheiten eines Mitarbeiters in einem bestimmten Jahr abrufen'
);

$router->addRoute(
        'GET',
        '/employees/{id}/absences',
        'AbsenceController',
        'getEmployeeAbsences',
        'Alle Abwesenheiten eines Mitarbeiters abrufen'
);

$router->addRoute(
        'GET',
        '/employees/{id}/overtimes',
        'OvertimeController',
        'getOvertimesByEmployee',
        'Alle Überstunden eines Mitarbeiters abrufen'
);

/**
 * Absence Routes
 */
$router->addRoute(
        'GET',
        '/absences/{year}',
        'AbsenceController',
        'getAbsencesByYear',
        'Alle Abwesenheiten eines Jahres abrufen'
);

$router->addRoute(
        'GET',
        '/absences',
        'AbsenceController',
        'getAllAbsences',
        'Alle Abwesenheiten abrufen'
);

/**
 * Roster Routes
 *
 *
 * Optionaler Filter über Query-Parameter:
 * - employeeKey
 * - start
 * - end
 *
 * Beispiel:
 *   GET /rosters?employeeKey=123&start=2025-10-01&end=2025-10-31
 *
 */
$router->addRoute(
        'GET',
        '/rosters',
        'RosterController',
        'getRosters',
        'Dienstpläne abrufen (optional gefiltert nach Mitarbeiter und Zeitraum)'
);
$router->addRoute(
        'PUT',
        '/rosters/{branch_id}/{date_start}/{date_end}',
        'RosterController',
        'updateRoster',
        'Dienstplan aktualisieren'
);
$router->addRoute(
        'DELETE',
        '/rosters/{branch_id}/{date}',
        'RosterController',
        'deleteRoster',
        'Dienstplan löschen'
);

/**
 *
 *   Branch Routes
 */
$router->addRoute(
        'GET',
        '/branches',
        'BranchController',
        'getAllBranches',
        'Alle Filialen abrufen'
);

$router->addRoute(
        'GET',
        '/branches/{id}',
        'BranchController',
        'getBranchById',
        'Details einer bestimmten Filiale abrufen'
);

// Request verarbeiten
$router->handle($requestUri, $method);
