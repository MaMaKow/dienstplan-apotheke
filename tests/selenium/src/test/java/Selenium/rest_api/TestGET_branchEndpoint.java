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
package Selenium.rest_api;

import Selenium.Branch;
import Selenium.PropertyFile;
import Selenium.Utilities.LogCollector;
import java.io.IOException;
import org.testng.Assert;
import org.testng.SkipException;
import org.testng.annotations.Listeners;
import org.testng.annotations.Test;
import org.testng.asserts.SoftAssert;

/**
 *
 * @author Mandelkow
 */
@Listeners(Selenium.Utilities.Listener.class)
public class TestGET_branchEndpoint extends Selenium.TestPage {

    private PropertyFile propertyFile;
    private SoftAssert softAssert = new SoftAssert();

    @Test()
    public void testGetBranch() throws IOException, Exception {
        LogCollector.debug("inside testGetBranch");
        if (Selenium.TestPage.someTestHasFailed) {
            throw new SkipException("Some Test has failed. Skipping all the other methods.");
        }
        LogCollector.debug("inside testGetBranch before try");
        try {
            LogCollector.debug("inside testGetBranch inside try");
            propertyFile = new PropertyFile();
            String testPageUrl = propertyFile.getTestPageUrl();
            LogCollector.debug("Check if we are logged in:");
            if (!POST_authenticateEndpoint.isAuthenticated()) {
                LogCollector.debug("We are not logged in. Try login:");
                String userName = propertyFile.getPdrUserName();
                String userPassphrase = propertyFile.getPdrUserPassword();
                new POST_authenticateEndpoint(userName, userPassphrase, testPageUrl);
                LogCollector.debug("After login");
            }
            LogCollector.debug("Create new GET_branchEndpoint:");
            GET_branchEndpoint branchEndpoint = new GET_branchEndpoint(testPageUrl, 1);
            Branch foundBranch = branchEndpoint.getBranch();
            // Replace the assertions in your test with these updated ones:
            softAssert.assertEquals(foundBranch.getBranchId(), 1);
            softAssert.assertEquals(foundBranch.getBranchName(), "Hauptapotheke am großen Platz");
            softAssert.assertEquals(foundBranch.getBranchShortName(), "Hauptapotheke");
            softAssert.assertEquals(foundBranch.getBranchManager(), "Zeidler");
            softAssert.assertEquals(foundBranch.getOpeningTimesMap().get(1)[0], "08:00"); // Start time for Monday
            softAssert.assertEquals(foundBranch.getOpeningTimesMap().get(1)[1], "18:00"); // End time for Monday
        } catch (Exception exception) {
            LogCollector.error(exception.getLocalizedMessage());
            exception.printStackTrace();
            Assert.fail();
            throw exception;
        }
        softAssert.assertAll();
    }

}
