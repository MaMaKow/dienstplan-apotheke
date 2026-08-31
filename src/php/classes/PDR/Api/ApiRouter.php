<?php

/*
 * Copyright (C) 2025 Mandelkow
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
     * @param string $path Lesbarer Pfad mit Platzhaltern wie {id}, {year}, {branch_id}, ...
     * @param string $controller Controller-Klasse
     * @param string $action Methode im Controller
     * @param string $description Beschreibung für API-Consumer
     */
    public function addRoute($method, $path, $controller, $action, $description = '') {
        // Generischer Regex: Verwandelt jeden Platzhalter {param_name} in eine benannte Gruppe (?P<param_name>[^/]+)
        // Spezifische Validierung (z.B. 4 Ziffern fürs Jahr) erfolgt im Controller oder per Regex-Muster.
        $pattern = preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function ($matches) {
            $paramName = $matches[1];
            if ($paramName === 'year') {
                return '(?P<year>\d{4})';
            }
            if (str_contains($paramName, 'id')) {
                return '(?P<' . $paramName . '>\d+)';
            }
            return '(?P<' . $paramName . '>[^/]+)';
        }, $path);

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

                // Nur die benannten String-Schlüssel filtern, um $matches von numerischen Indizes zu säubern
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $controller = new $controllerClass();
                return $controller->$action($params);
            }
        }

        // Bei fehlendem Route-Match: Zeige API-Informationen
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
                "path" => $route['readable_path'],
                "description" => $route['description']
            ];
        }

        $response = [
            "name" => "Dienstplan-API",
            "version" => "0.3.0",
            "description" => "API zur Verwaltung von Dienstplänen in Apotheken",
            "endpoints" => $endpoints
        ];

        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
}
