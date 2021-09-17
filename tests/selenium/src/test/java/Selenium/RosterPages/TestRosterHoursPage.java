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
package Selenium.RosterPages;

import Selenium.ReadPropertyFile;
import Selenium.RosterItem;
import Selenium.ScreenShot;
import Selenium.SignInPage.SignInPage;
import java.io.File;
import java.io.IOException;
import java.nio.file.Files;
import java.util.Calendar;
import java.util.logging.Level;
import java.util.logging.Logger;
//import org.junit.Test;
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
public class TestRosterHoursPage {

    WebDriver driver;

    @Test(enabled = false)/*passed*/
    public void testDateNavigation() {
        driver = Selenium.driver.Wrapper.getDriver();
        ReadPropertyFile readPropertyFile = new ReadPropertyFile();
        String urlPageTest = readPropertyFile.getUrlPageTest();
        driver.get(urlPageTest);

        /**
         * Sign in:
         */
        SignInPage signInPage = new SignInPage(driver);
        String pdr_user_password = readPropertyFile.getPdrUserPassword();
        String pdr_user_name = readPropertyFile.getPdrUserName();
        signInPage.loginValidUser(pdr_user_name, pdr_user_password);
        RosterHoursPage rosterHoursPage = new RosterHoursPage(driver);
        Assert.assertEquals(rosterHoursPage.getUserNameText(), pdr_user_name);

        /**
         * Move to specific month:
         */
        rosterHoursPage.selectMonth("Juni");
        rosterHoursPage.selectYear("2020");
        rosterHoursPage.selectEmployee("Mandelkow");
        Assert.assertEquals("Juni", rosterHoursPage.getMonth());
        Assert.assertEquals("2020", rosterHoursPage.getYear());
        Assert.assertEquals("Mandelkow", rosterHoursPage.getEmployee());
    }

    @Test(enabled = false)/*passed*/
    public void testRosterDispay() {
        driver = Selenium.driver.Wrapper.getDriver();
        ReadPropertyFile readPropertyFile = new ReadPropertyFile();
        String urlPageTest = readPropertyFile.getUrlPageTest();
        driver.get(urlPageTest);

        /**
         * Sign in:
         */
        SignInPage signInPage = new SignInPage(driver);
        String pdr_user_password = readPropertyFile.getPdrUserPassword();
        String pdr_user_name = readPropertyFile.getPdrUserName();
        signInPage.loginValidUser(pdr_user_name, pdr_user_password);
        RosterHoursPage rosterHoursPage = new RosterHoursPage(driver);
        Assert.assertEquals(rosterHoursPage.getUserNameText(), pdr_user_name);

        /**
         * Move to specific month:
         */
        rosterHoursPage.selectMonth("Juni");
        rosterHoursPage.selectYear("2020");
        rosterHoursPage.selectEmployee("Mandelkow");
        Assert.assertEquals("Juni", rosterHoursPage.getMonth());
        Assert.assertEquals("2020", rosterHoursPage.getYear());
        Assert.assertEquals("Mandelkow", rosterHoursPage.getEmployee());
        /*
        Test if the correct roster information is displayed:
         */
        Calendar targetCalendar = Calendar.getInstance();
        targetCalendar.set(2020, Calendar.JUNE, 29);
        RosterItem actualRosterItem = rosterHoursPage.getRosterOnDate(targetCalendar);
        Assert.assertEquals(actualRosterItem.getDate(), targetCalendar);
        Assert.assertEquals(actualRosterItem.getDutyStart(), "09:30");
        Assert.assertEquals(actualRosterItem.getDutyEnd(), "18:00");
        /*
        Test if absence information is displayed:
         */
        targetCalendar.set(2020, Calendar.JUNE, 19);
        String absenceString = rosterHoursPage.getAbsenceStringOnDate(targetCalendar);
        Assert.assertEquals(absenceString, "Elternzeit");
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
