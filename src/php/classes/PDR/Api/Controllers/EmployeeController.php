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
require_once 'BaseController.php';

class EmployeeController extends BaseController {

    public function getAllEmployees($matches) {
        try {
            $workforce = new \PDR\Workforce\Workforce(date('Y-m-d'));
            $jsonEncodedWorkforce = $workforce->getEmployeesAsJson();
            $this->sendJson(json_decode($jsonEncodedWorkforce));
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function getEmployeeAbsencesByYear($matches) {
        try {
            $employeeKey = (int) $matches[1];
            $year = (int) $matches[2];

            $startDateObject = (new DateTime())->setDate($year, 1, 1);
            $endDateObject = (new DateTime())->setDate($year, 12, 31);

            $listOfAbsences = \PDR\Database\AbsenceDatabaseHandler::getAbsenceObjectsByEmployeeKeyInPeriod(
                    $startDateObject,
                    $endDateObject,
                    $employeeKey
            );

            $jsonEncodedAbsences = $listOfAbsences->getAbsencesAsJson();
            $this->sendJson(json_decode($jsonEncodedAbsences));
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function getEmployeeAbsences($matches) {
        try {
            $employeeKey = (int) $matches[1];
            $currentYear = date('Y');

            $startDateObject = (new DateTime())->setDate($currentYear, 1, 1);
            $endDateObject = (new DateTime())->setDate($currentYear, 12, 31);

            $listOfAbsences = \PDR\Database\AbsenceDatabaseHandler::getAbsenceObjectsByEmployeeKeyInPeriod(
                    $startDateObject,
                    $endDateObject,
                    $employeeKey
            );

            $jsonEncodedAbsences = $listOfAbsences->getAbsencesAsJson();
            $this->sendJson(json_decode($jsonEncodedAbsences));
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
}
