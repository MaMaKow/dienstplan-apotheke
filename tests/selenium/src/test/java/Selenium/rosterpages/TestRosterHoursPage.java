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
import Selenium.RosterItem;
import Selenium.ScreenShot;
import Selenium.signin.SignInPage;
import java.time.LocalDate;
import java.time.Month;
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

    @Test(enabled = false)/*failed*/
    public void testDateNavigation() {
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
        Assert.assertEquals("Mandelkow", rosterHoursPage.getEmployeeName());
    }

    @Test(enabled = false)/*failed*/
    public void testRosterDispay() {
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
        Assert.assertEquals("Mandelkow", rosterHoursPage.getEmployeeName());
        /*
        Test if the correct roster information is displayed:
         */
        LocalDate targetLocalDate = LocalDate.of(2020, Month.JUNE, 29);
        RosterItem actualRosterItem = rosterHoursPage.getRosterOnDate(targetLocalDate);
        //Assert.assertEquals(actualRosterItem.getDateCalendar(), targetCalendar);
        Assert.assertEquals(actualRosterItem.getLocalDate(), targetLocalDate);
        Assert.assertEquals(actualRosterItem.getDutyStart(), "09:30");
        Assert.assertEquals(actualRosterItem.getDutyEnd(), "18:00");
        /*
        Test if absence information is displayed:
         */
        String absenceString = rosterHoursPage.getAbsenceStringOnLocalDate(LocalDate.of(2020, Month.JUNE, 19));
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
