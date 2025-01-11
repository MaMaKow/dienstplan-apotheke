<?php

require_once '../../../../bootstrap.php';

// Get the requested URL
$requestUri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Remove query parameters (i.E. ?key=value)
$requestUriStripped = parse_url($requestUri, PHP_URL_PATH);
\PDR\Utility\GeneralUtility::printDebugVariable($method);
\PDR\Utility\GeneralUtility::printDebugVariable($requestUri);
\PDR\Utility\GeneralUtility::printDebugVariable($requestUriStripped);
switch (true) {
    case preg_match('~^/auth/login$~', $requestUriStripped) && $method === 'POST':
        // Start JWT authentication
        require_once './authentication/POST-authenticate.php';
        break;
}
$allowUnauthorized = true;
$session = new sessions($allowUnauthorized);
$session->verifyAccessToken();
/**
 * Routing
 * @todo: Write different functions and put them in specialized files.
 */
switch (true) {
    case preg_match('~^/users$~', $requestUriStripped) && $method === 'GET':
        // Get all users
        if (!$session->user_has_privilege(\sessions::PRIVILEGE_ADMINISTRATION)) {
            die(json_encode(['error' => 'You need administrative privileges to view user information.']));
        }
        $userBase = new PDR\Workforce\user_base();
        //getAllUsers();
        break;

    case preg_match('~^/users/(\d+)$~', $requestUriStripped, $matches) && $method === 'GET':
        // Get specific user (userKey in $matches[1])
        if (!$session->user_has_privilege(\sessions::PRIVILEGE_ADMINISTRATION)) {
            die(json_encode(['error' => 'You need administrative privileges to view user information.']));
        }
        $user = new \user($matches[1]);
        //Make sure, that no secret information is leaked!
        break;

    case preg_match('~^/employees$~', $requestUriStripped) && $method === 'GET':
        // Get all employees
        $workforce = new \workforce(date('Y-m-d'));
        $jsonEncodedWorkforce = $workforce->getEmployeesAsJson();
        echo $jsonEncodedWorkforce;
        break;

    default:
        header('Content-Type: application/json');

// API-Metadaten und verfügbare Endpunkte
        $response = [
            "name" => "Dienstplan-API",
            "version" => "0.1.0",
            "description" => "API zur Verwaltung von Dienstplänen in Apotheken",
            "endpoints" => [
                [
                    "method" => "GET",
                    "path" => "/users",
                    "description" => "Listet alle Benutzer auf"
                ],
                [
                    "method" => "GET",
                    "path" => "/employees",
                    "description" => "Listet alle Mitarbeiter auf"
                ],
            ]
        ];

// JSON-Antwort zurückgeben
        echo json_encode($response, JSON_PRETTY_PRINT);
}
