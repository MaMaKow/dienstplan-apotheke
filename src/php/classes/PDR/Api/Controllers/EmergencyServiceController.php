<?php

/*
 * Copyright (C) 2026 Mandelkow
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

class EmergencyServiceController extends BaseController {

    /**
     * Get all emergency services for a specific year and branch
     *
     * @param array $params Named parameters extracted by ApiRouter ('year' and 'branch_id')
     * @return void
     */
    public function getEmergencyServicesByYear(array $params): void {
        try {
            $currentYear = (int) date('Y');
            $year = (int) ($params['year'] ?? 0);

            if ($year < $currentYear - 10 || $year > $currentYear + 10) {
                $this->sendError('Please provide a correct year between '
                        . ($currentYear - 10) . ' and '
                        . ($currentYear + 10) . '.');
                return;
            }

            $branchId = (int) ($params['branch_id'] ?? 0);
            $networkOfBranchOffices = new \PDR\Pharmacy\NetworkOfBranchOffices();

            if (!$networkOfBranchOffices->branch_exists($branchId)) {
                $this->sendError('Please provide a correct branchId.');
                return;
            }

            $listOfEmergencyServices = \PDR\Database\EmergencyServiceDatabaseHandler::getListOfEmergencyServicesInYear($year, $branchId);
            $this->sendJson($listOfEmergencyServices);
        } catch (Exception $exception) {
            $this->sendError($exception->getMessage());
        }
    }
}
