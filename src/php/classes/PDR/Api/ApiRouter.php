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

    public function addRoute($method, $pattern, $controller, $action) {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'controller' => $controller,
            'action' => $action
        ];
    }

    public function handle($requestUri, $method) {
        $apiEndpoint = $this->extractApiEndpoint($requestUri);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $apiEndpoint, $matches)) {
                $controllerClass = $route['controller'];
                $action = $route['action'];

                $controller = new $controllerClass();
                return $controller->$action($matches);
            }
        }

        // Default: API-Info anzeigen
        $this->showApiInfo();
    }

    private function extractApiEndpoint($requestUri) {
        $requestUriStripped = parse_url($requestUri, PHP_URL_PATH);
        $basePath = PDR_HTTP_SERVER_APPLICATION_PATH . 'src/php/restful-api';
        return str_replace($basePath, '', $requestUriStripped);
    }

    private function showApiInfo() {
        $response = [
            "name" => "Dienstplan-API",
            "version" => "0.1.0",
            "description" => "API zur Verwaltung von Dienstplänen in Apotheken",
            "endpoints" => [
                ["method" => "POST", "path" => "/auth/login", "description" => "Benutzer authentifizieren"],
                ["method" => "GET", "path" => "/users", "description" => "Alle Benutzer auflisten"],
                ["method" => "GET", "path" => "/users/{id}", "description" => "Bestimmten Benutzer abrufen"],
                ["method" => "GET", "path" => "/employees", "description" => "Alle Mitarbeiter auflisten"],
                ["method" => "GET", "path" => "/employees/{id}/absences", "description" => "Abwesenheiten eines Mitarbeiters"],
                ["method" => "GET", "path" => "/absences", "description" => "Alle Abwesenheiten"],
                ["method" => "GET", "path" => "/branches", "description" => "Alle Filialen auflisten"],
                ["method" => "GET", "path" => "/branches/{id}", "description" => "Bestimmte Filiale abrufen"]
            ]
        ];
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
    }
}
