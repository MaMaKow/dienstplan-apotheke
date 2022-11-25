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
package Selenium.rosterpages;

import Selenium.PropertyFile;
import Selenium.Roster;
import Selenium.RosterItem;
import Selenium.ScreenShot;
import Selenium.signin.SignInPage;
import java.time.LocalDate;
import java.util.HashMap;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.testng.annotations.Test;
import org.testng.Assert;

import org.openqa.selenium.WebDriver;
import org.testng.ITestResult;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;
import org.testng.asserts.SoftAssert;

/**
 *
 * @author Mandelkow
 */
public class TestRosterWeekTablePage {

    WebDriver driver;
    SoftAssert softAssert = new SoftAssert();

    @Test(enabled = true)/*passed*/
    public void testDateNavigation() {
        try {
            driver = Selenium.driver.Wrapper.getDriver();
            PropertyFile propertyFile = new PropertyFile();
            String urlPageTest = propertyFile.getUrlPageTest();
            driver.get(urlPageTest);

            /**
             * Sign in:
             */
            SignInPage signInPage = new SignInPage(driver);
            String pdr_user_password = propertyFile.getPdrUserPassword();
            String pdr_user_name = propertyFile.getPdrUserName();
            signInPage.loginValidUser(pdr_user_name, pdr_user_password);
            RosterWeekTablePage rosterWeekTablePage = new RosterWeekTablePage(driver);
            Assert.assertEquals(rosterWeekTablePage.getUserNameText(), pdr_user_name);
            /**
             * Move to specific date and go foreward and backward from there:
             */
            rosterWeekTablePage.goToDate("01.07.2020"); //This date is a wednesday.
            Assert.assertEquals(rosterWeekTablePage.getDate(), "2020-06-29"); //This is the corresponding monday.
            rosterWeekTablePage.moveWeekBackward();
            Assert.assertEquals(rosterWeekTablePage.getDate(), "2020-06-22"); //This is the corresponding monday.
            rosterWeekTablePage.moveWeekForward();
            Assert.assertEquals(rosterWeekTablePage.getDate(), "2020-06-29"); //This is the corresponding monday.
        } catch (Exception exception) {
            Logger.getLogger(TestRosterWeekTablePage.class.getName()).log(Level.SEVERE, null, exception);
        }
    }

    @Test(enabled = true)/*passed*/
    public void testRosterDisplay() throws Exception {
        driver = Selenium.driver.Wrapper.getDriver();
        PropertyFile propertyFile = new PropertyFile();
        String urlPageTest = propertyFile.getUrlPageTest();
        driver.get(urlPageTest);
        /**
         * Sign in:
         */
        SignInPage signInPage = new SignInPage(driver);
        String pdr_user_password = propertyFile.getPdrUserPassword();
        String pdr_user_name = propertyFile.getPdrUserName();
        signInPage.loginValidUser(pdr_user_name, pdr_user_password);
        RosterWeekTablePage rosterWeekTablePage = new RosterWeekTablePage(driver);
        Assert.assertEquals(rosterWeekTablePage.getUserNameText(), pdr_user_name);

        Roster roster = new Roster();
        HashMap<LocalDate, HashMap> listOfRosterDays = roster.getListOfRosterDays();
        for (HashMap<Integer, RosterItem> listOfRosterItems : listOfRosterDays.values()) {
            for (RosterItem rosterItemFromPrediction : listOfRosterItems.values()) {
                /**
                 * Move to specific date to get a specific roster:
                 */
                rosterWeekTablePage.goToDate(rosterItemFromPrediction.getLocalDate());
                RosterItem rosterItemReadOnPage = rosterWeekTablePage.getRosterItemByEmployeeId(
                        rosterItemFromPrediction.getLocalDate().getDayOfWeek(),
                        rosterItemFromPrediction.getEmployeeId()
                );

                softAssert.assertEquals(rosterItemFromPrediction.getEmployeeName(), rosterItemReadOnPage.getEmployeeName());
                softAssert.assertEquals(rosterItemFromPrediction.getLocalDate(), rosterItemReadOnPage.getLocalDate());
                softAssert.assertEquals(rosterItemFromPrediction.getDutyStart(), rosterItemReadOnPage.getDutyStart());
                softAssert.assertEquals(rosterItemFromPrediction.getDutyEnd(), rosterItemReadOnPage.getDutyEnd());
                softAssert.assertEquals(rosterItemFromPrediction.getBreakStart(), rosterItemReadOnPage.getBreakStart());
                softAssert.assertEquals(rosterItemFromPrediction.getBreakEnd(), rosterItemReadOnPage.getBreakEnd());
                softAssert.assertAll();
            }
        }

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
