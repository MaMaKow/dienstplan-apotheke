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

class ApiRouter {

    private $routes = [];

    /**
     * Fügt eine neue Route hinzu.
     *
     * @param string $method HTTP-Methode (GET, POST, ...)
     * @param string $path Lesbarer Pfad mit Platzhaltern wie {id}, {year}, ...
     * @param string $controller Controller-Klasse
     * @param string $action Methode im Controller
     * @param string $description Beschreibung für API-Consumer
     */
    public function addRoute($method, $path, $controller, $action, $description = '') {
        // Platzhalter in Regex umwandeln: {id} -> (\d+), {year} -> (\d{4}), {any} -> (.+)
        $pattern = preg_replace([
            '/\{id\}/',
            '/\{year\}/',
            '/\{any\}/'
                ], [
            '(\d+)',
            '(\d{4})',
            '(.+)'
                ], $path);

        // Regex für preg_match
        $pattern = '~^' . $pattern . '$~';

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'controller' => $controller,
            'action' => $action,
            'description' => $description,
            'readable_path' => $path
        ];
    }

    public function handle($requestUri, $method) {
        $apiEndpoint = $this->extractApiEndpoint($requestUri);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $apiEndpoint, $matches)) {
                $controllerClass = $route['controller'];
                $action = $route['action'];
                //error_log("We found a match:");
                //PDR\Utility\GeneralUtility::printDebugVariable($controllerClass);
                //PDR\Utility\GeneralUtility::printDebugVariable($action);

                $controller = new $controllerClass();
                return $controller->$action($matches);
            }
        }

// Default: API-Info anzeigen
        //PDR\Utility\GeneralUtility::printDebugVariable($requestUri . " does not match any registered route.");
        //PDR\Utility\GeneralUtility::printDebugVariable($this->routes);
        $this->showApiInfo();
    }

    private function extractApiEndpoint($requestUri) {
        $requestUriStripped = parse_url($requestUri, PHP_URL_PATH);
        $basePath = PDR_HTTP_SERVER_APPLICATION_PATH . 'src/php/restful-api';
        return str_replace($basePath, '', $requestUriStripped);
    }

    private function showApiInfo() {
        $endpoints = [];
        foreach ($this->routes as $route) {
            $endpoints[] = [
                "method" => $route['method'],
                "path" => $route['readable_path'], // lesbare Version
                "description" => $route['description']
            ];
        }

        $response = [
            "name" => "Dienstplan-API",
            "version" => "0.1.0",
            "description" => "API zur Verwaltung von Dienstplänen in Apotheken",
            "endpoints" => $endpoints
        ];

        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
}
