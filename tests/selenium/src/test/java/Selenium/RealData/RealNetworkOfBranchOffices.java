/*
 * Copyright (C) 2024 Mandelkow
 *
 * Dienstplan Apotheke
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
package Selenium.RealData;

import Selenium.Branch;
import java.util.Map;

/**
 *
 * @author Mandelkow
 */
public class RealNetworkOfBranchOffices {

    private Map<Integer, Branch> realListOfBranches;

    public Map<Integer, Branch> getRealListOfBranches() {
        return realListOfBranches;
    }

    public void add(Branch branch) {
        realListOfBranches.put(branch.getBranchId(), branch);
    }

    public Branch getRealBranchById(int branchId) {
        if (0 == branchId && !realListOfBranches.containsKey(0)) {
            return getEmptyBranch();
        }
        return realListOfBranches.get(branchId);
    }

    private Branch getEmptyBranch() {
        Branch emptyBranch = new Branch(0, 0, "", "", "", "", null);
        return emptyBranch;
    }

}
