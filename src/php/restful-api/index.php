<?php

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
switch (true) {
    case preg_match('~^/auth/login$~', $apiEndpoint) && $method === 'POST':
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
    case preg_match('~^/users$~', $apiEndpoint) && $method === 'GET':
        // Get all users
        if (!$session->user_has_privilege(\sessions::PRIVILEGE_ADMINISTRATION)) {
            die(json_encode(['error' => 'You need administrative privileges to view user information.']));
        }
        $userBase = new PDR\Workforce\user_base();
        //getAllUsers();
        break;
    case preg_match('~^/users/(\d+)$~', $apiEndpoint, $matches) && $method === 'GET':
        //\PDR\Utility\GeneralUtility::printDebugVariable("Inside ~^/users/(\d+)$~");
        // Get specific user (userKey in $matches[1])
        if (!$session->user_has_privilege(\sessions::PRIVILEGE_ADMINISTRATION)) {
            die(json_encode(['error' => 'You need administrative privileges to view user information.']));
        }
        $user = new \user($matches[1]);
        //Make sure, that no secret information is leaked!
        break;
    case preg_match('~^/employees/(\d+)/absences/(\d{4})$~', $apiEndpoint, $matches) && $method === 'GET':
        //\PDR\Utility\GeneralUtility::printDebugVariable("Inside ~^/employees/(\d+)/absences/(\d{4})$~");
        // Get absences for specific employee in specific year
        $employeeKey = (int) $matches[1];
        $year = (int) $matches[2];
        try {
            $startDateObject = (new DateTime())->setDate($year, 1, 1);
            $endDateObject = (new DateTime())->setDate($year, 12, 31);

            $listOfAbsences = \PDR\Database\AbsenceDatabaseHandler::getAbsenceObjectsByEmployeeKeyInPeriod($startDateObject, $endDateObject, $employeeKey);
            $jsonEncodedAbsences = $listOfAbsences->getAbsencesAsJson();
            echo $jsonEncodedAbsences;
        } catch (Exception $e) {
            PDR\Utility\GeneralUtility::printDebugVariable($e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
    case preg_match('~^/employees/(\d+)/absences/?$~', $apiEndpoint, $matches) && $method === 'GET':
        //\PDR\Utility\GeneralUtility::printDebugVariable("Inside ~^/employees/(\d+)/absences/?");
        // Get all absences for specific employee in current year
        $employeeKey = (int) $matches[1];
        try {
            $currentYear = date('Y');
            $startDateObject = (new DateTime())->setDate($currentYear, 1, 1);
            $endDateObject = (new DateTime())->setDate($currentYear, 12, 31);

            $listOfAbsences = \PDR\Database\AbsenceDatabaseHandler::getAbsenceObjectsByEmployeeKeyInPeriod($startDateObject, $endDateObject, $employeeKey);
            $jsonEncodedAbsences = $listOfAbsences->getAbsencesAsJson();
            echo $jsonEncodedAbsences;
        } catch (Exception $e) {
            PDR\Utility\GeneralUtility::printDebugVariable($e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
    case preg_match('~^/employees$~', $apiEndpoint) && $method === 'GET':
        //\PDR\Utility\GeneralUtility::printDebugVariable("Inside ~^/employees$~");
        // Get all employees
        $workforce = new \workforce(date('Y-m-d'));
        $jsonEncodedWorkforce = $workforce->getEmployeesAsJson();
        echo $jsonEncodedWorkforce;
        break;
    case preg_match('~^/absences/(\d{4})$~', $apiEndpoint, $matches) && $method === 'GET':
        //\PDR\Utility\GeneralUtility::printDebugVariable("Inside ~^/absences/(\d{4})$~");
        // Get all absences in specific year
        $year = (int) $matches[1];
        try {
            $startDateObject = (new DateTime())->setDate($year, 1, 1);
            $endDateObject = (new DateTime())->setDate($year, 12, 31);

            $listOfAbsences = \PDR\Database\AbsenceDatabaseHandler::getAllAbsenceObjectsInPeriod($startDateObject, $endDateObject);
            $jsonEncodedAbsences = $listOfAbsences->getAbsencesAsJson();
            echo $jsonEncodedAbsences;
        } catch (Exception $e) {
            PDR\Utility\GeneralUtility::printDebugVariable($e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
    case preg_match('~^/absences/?$~', $apiEndpoint) && $method === 'GET':
        //\PDR\Utility\GeneralUtility::printDebugVariable("Inside ~^/absences/?$~");
        // Get all absences in current year
        try {
            $currentYear = date('Y');
            $startDateObject = (new DateTime())->setDate($currentYear, 1, 1);
            $endDateObject = (new DateTime())->setDate($currentYear, 12, 31);

            $listOfAbsences = \PDR\Database\AbsenceDatabaseHandler::getAllAbsenceObjectsInPeriod($startDateObject, $endDateObject);
            $jsonEncodedAbsences = $listOfAbsences->getAbsencesAsJson();
            echo $jsonEncodedAbsences;
        } catch (Exception $e) {
            PDR\Utility\GeneralUtility::printDebugVariable($e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
    case preg_match('~^/branches$~', $apiEndpoint) && $method === 'GET':
        PDR\Utility\GeneralUtility::printDebugVariable("Inside ~^/branches$~");
        // Get all branches
        try {
            $networkOfBranchOffices = new \PDR\Pharmacy\NetworkOfBranchOffices();
            $jsonEncodedBranches = $networkOfBranchOffices->getAllBranchesAsJson();
            //\PDR\Utility\GeneralUtility::printDebugVariable($jsonEncodedBranches);
            echo $jsonEncodedBranches;
        } catch (Exception $e) {
            PDR\Utility\GeneralUtility::printDebugVariable($e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
    case preg_match('~^/branches/(\d+)$~', $apiEndpoint, $matches) && $method === 'GET':
        // Get specific branch (branchId in $matches[1])
        //\PDR\Utility\GeneralUtility::printDebugVariable("Inside ~^/branches/(\d+)$~");
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
                    "path" => "/absences",
                    "description" => "Listet alle Abwesenheiten des aktuellen Jahres auf"
                ],
                [
                    "method" => "GET",
                    "path" => "/absences/{year}",
                    "description" => "Listet alle Abwesenheiten eines bestimmten Jahres auf"
                ],
                [
                    "method" => "GET",
                    "path" => "/employees/{id}/absences",
                    "description" => "Listet alle Abwesenheiten eines Mitarbeiters im aktuellen Jahr auf"
                ],
                [
                    "method" => "GET",
                    "path" => "/employees/{id}/absences/{year}",
                    "description" => "Listet alle Abwesenheiten eines Mitarbeiters in einem bestimmten Jahr auf"
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
//\//\PDR\Utility\GeneralUtility::printDebugVariable("After switch statement");
