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

class BranchController extends BaseController {

    public function getAllBranches(array $params) {
        try {
            $networkOfBranchOffices = new \PDR\Pharmacy\NetworkOfBranchOffices();
            $jsonEncodedBranches = $networkOfBranchOffices->getAllBranchesAsJson();
            $this->sendJson(json_decode($jsonEncodedBranches));
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function getBranchById(array $params) {
        try {
            $branchId = (int) $params['id'];

            $networkOfBranchOffices = new \PDR\Pharmacy\NetworkOfBranchOffices();
            if (!$networkOfBranchOffices->branch_exists($branchId)) {
                $this->sendError("Branch with ID {$branchId} does not exist.", 404);
            }

            $branchObject = new PDR\Pharmacy\Branch($branchId);
            $branchData = $branchObject->encodeToJson();
            $this->sendJson(json_decode($branchData));
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
}
