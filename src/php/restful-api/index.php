<?php

require_once '../../../bootstrap.php';
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
    case preg_match('~^/absences/$~', $requestUriStripped, $matches) && $method === 'GET':
        // Get all absences in the current year:
        $now = new DateTime();
        $startDateObject = new DateTime()->setDate($now->format('Y'), 1, 1);
        $endDateObject = new DateTime()->setDate($now->format('Y'), 12, 31);
        $listOfAbsences = \PDR\Database\AbsenceDatabaseHandler::getAllAbsenceObjectsInPeriod($startDateObject, $endDateObject);
        $jsonEncodedAbsences = $listOfAbsences->getAbsencesAsJson();
        echo $jsonEncodedAbsences;
        break;
    case preg_match('~^/employees$~', $requestUriStripped) && $method === 'GET':
        // Get all employees
        $workforce = new \workforce(date('Y-m-d'));
        $jsonEncodedWorkforce = $workforce->getEmployeesAsJson();
        echo $jsonEncodedWorkforce;
        break;
    case preg_match('~^/branches$~', $requestUriStripped) && $method === 'GET':
        // Get all branches
        try {
            $networkOfBranchOffices = new \PDR\Pharmacy\NetworkOfBranchOffices();
            // Assuming there's a method to get all branches as JSON
            // If not available, you'll need to implement it
            $jsonEncodedBranches = $networkOfBranchOffices->getAllBranchesAsJson();
            echo $jsonEncodedBranches;
        } catch (Exception $e) {
            PDR\Utility\GeneralUtility::printDebugVariable($e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
    case preg_match('~^/branches/(\d+)$~', $requestUriStripped, $matches) && $method === 'GET':
        // Get specific branch (branchId in $matches[1])
        $branchId = (int) $matches[1];
        try {
            $networkOfBranchOffices = new \PDR\Pharmacy\NetworkOfBranchOffices();
            if (false === $networkOfBranchOffices->branch_exists($branchId)) {
                throw new Exception("Branch with that ID does not exist.");
            }
            $branchObject = new PDR\Pharmacy\Branch($branchId);
            $branchData = $branchObject->encodeToJson();
            echo $branchData;
        } catch (Exception $e) {
            PDR\Utility\GeneralUtility::printDebugVariable($e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
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
                [
                    "method" => "GET",
                    "path" => "/branches",
                    "description" => "Listet alle Filialen auf"
                ],
                [
                    "method" => "GET",
                    "path" => "/branches/{id}",
                    "description" => "Gibt Informationen zu einer bestimmten Filiale zurück"
                ]
            ]
        ];
// JSON-Antwort zurückgeben
        echo json_encode($response, JSON_PRETTY_PRINT);
}