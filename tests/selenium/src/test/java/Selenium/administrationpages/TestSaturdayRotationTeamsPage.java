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

import Selenium.PropertyFile;
import Selenium.ScreenShot;
import Selenium.signin.SignInPage;
import java.util.HashSet;
import org.testng.annotations.Test;
import org.testng.Assert;

import org.openqa.selenium.WebDriver;
import org.testng.ITestResult;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;

/**
 *
 * @author Mandelkow
 */
public class TestSaturdayRotationTeamsPage {

    WebDriver driver;

    @Test(enabled = true)/*passed*/
    public void testBranchNavigation() {
        driver = Selenium.driver.Wrapper.getDriver();
        PropertyFile propertyFile = new PropertyFile();
        String urlPageTest = propertyFile.getUrlPageTest();
        driver.get(urlPageTest);

        /**
         * Sign in:
         */
        SignInPage signInPage = new SignInPage(driver);
        String pdr_user_name = propertyFile.getPdrUserName();
        signInPage.loginValidUser();
        SaturdayRotationTeamsPage saturdayRotationTeamsPage = new SaturdayRotationTeamsPage(driver);
        Assert.assertEquals(saturdayRotationTeamsPage.getUserNameText(), pdr_user_name);
        saturdayRotationTeamsPage.selectBranch(2);
        Assert.assertEquals(saturdayRotationTeamsPage.getBranchId(), 2);
        saturdayRotationTeamsPage.selectBranch(1);
        Assert.assertEquals(saturdayRotationTeamsPage.getBranchId(), 1);
    }

    @Test(enabled = true)
    public void testAddingTeams() {
        driver = Selenium.driver.Wrapper.getDriver();
        PropertyFile propertyFile = new PropertyFile();
        String urlPageTest = propertyFile.getUrlPageTest();
        driver.get(urlPageTest);

        /**
         * Sign in:
         */
        SignInPage signInPage = new SignInPage(driver);
        String pdr_user_name = propertyFile.getPdrUserName();
        signInPage.loginValidUser();
        SaturdayRotationTeamsPage saturdayRotationTeamsPage = new SaturdayRotationTeamsPage(driver);
        Assert.assertEquals(saturdayRotationTeamsPage.getUserNameText(), pdr_user_name);

        HashSet<Integer> saturdayRotationTeamMembers;
        saturdayRotationTeamMembers = new HashSet<>();
        saturdayRotationTeamMembers.add(1);
        saturdayRotationTeamMembers.add(13);
        /**
         * <p lang=de>CAVE: Wir definieren hier die TeamId. Aber wir haben
         * keinen Einfluss darauf, dass diese TeamId auch angelegt wird.</p>
         */
        int saturdayRotationId = 0;

        SaturdayRotationTeam saturdayRotationTeamShould;
        saturdayRotationTeamShould = new SaturdayRotationTeam(saturdayRotationId, saturdayRotationTeamMembers);
        saturdayRotationTeamsPage.addTeam(saturdayRotationTeamShould);
        SaturdayRotationTeam saturdayRotationTeamFound = saturdayRotationTeamsPage.getTeamById(saturdayRotationId);
        Assert.assertEquals(saturdayRotationTeamFound.getListOfTeamMembers(), saturdayRotationTeamShould.getListOfTeamMembers()); //TODO: Werden die Objekte ordentlich verglichen?

    }

    @BeforeMethod
    public void setUp() {
        Selenium.driver.Wrapper.createNewDriver();
    }

    @AfterMethod
    public void tearDown(ITestResult testResult) {
        driver = Selenium.driver.Wrapper.getDriver();
        new ScreenShot(testResult);
        if (testResult.getStatus() != ITestResult.FAILURE) {
            driver.quit();
        }
    }

}
