/*
 * Copyright (C) 2021 Mandelkow
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
package Selenium.administrationpages;

import Selenium.Branch;
import Selenium.RealData.RealNetworkOfBranchOffices;
import java.util.HashMap;
import org.testng.annotations.Test;
import org.testng.Assert;

/**
 *
 * @author Mandelkow
 */
public class TestBranchAdministrationPage extends Selenium.TestPage {

    @Test(enabled = true, dependsOnMethods = {"testInstallation"})
    public void testDeleteBranch() {
        /**
         * Sign in:
         */
        super.signIn();
        BranchAdministrationPage branchAdministrationPage = new BranchAdministrationPage();
        HashMap<Integer, String[]> openingTimesMap = new HashMap<>();
        openingTimesMap.put(1, new String[]{"8:00", "18:00"});
        Branch branch = new Branch(42, 36, "Test Branch for Deletion", "Test Branch", "Nowhere to be found", "Nobody Ever", openingTimesMap);
        branchAdministrationPage.createNewBranch(branch);
        Assert.assertTrue(branchAdministrationPage.exists(branch.getBranchId()));
        RealNetworkOfBranchOffices realNetworkOfBranchOffices = new RealNetworkOfBranchOffices();
        Branch foundBranch = realNetworkOfBranchOffices.getRealBranchById(branch.getBranchId());
        Assert.assertEquals(foundBranch.getBranchId(), branch.getBranchId());
        Assert.assertEquals(foundBranch.getBranchName(), branch.getBranchName());
        Assert.assertEquals(foundBranch.getBranchShortName(), branch.getBranchShortName());
        Assert.assertEquals(foundBranch.getBranchAddress(), branch.getBranchAddress());
        Assert.assertEquals(foundBranch.getBranchManager(), branch.getBranchManager());
        /**
         * remove branch
         */
        branchAdministrationPage.removeBranch(branch.getBranchId());
        Assert.assertFalse(branchAdministrationPage.exists(branch.getBranchId()));
    }

}
