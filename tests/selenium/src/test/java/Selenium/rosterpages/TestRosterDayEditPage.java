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
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Locale;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.apache.commons.codec.digest.DigestUtils;
import org.testng.annotations.Test;

import org.openqa.selenium.WebDriver;
import org.testng.Assert;
import static org.testng.Assert.assertEquals;
import org.testng.ITestResult;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;

/**
 *
 * @author Mandelkow
 */
public class TestRosterDayEditPage {

    WebDriver driver;

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
            RosterDayEditPage rosterWeekTablePage = new RosterDayEditPage(driver);
            assertEquals(rosterWeekTablePage.getUserNameText(), pdr_user_name);
            /**
             * Move to specific date and go foreward and backward from there:
             */
            rosterWeekTablePage.goToDate("01.07.2020"); //This date is a wednesday.
            assertEquals("2020-07-01", rosterWeekTablePage.getDate()); //This is the corresponding monday.
            rosterWeekTablePage.moveDayBackward();
            assertEquals("2020-06-30", rosterWeekTablePage.getDate()); //This is the corresponding monday.
            rosterWeekTablePage.moveDayForward();
            assertEquals("2020-07-01", rosterWeekTablePage.getDate()); //This is the corresponding monday.
        } catch (Exception exception) {
            Logger.getLogger(TestRosterDayEditPage.class.getName()).log(Level.SEVERE, null, exception);
        }
    }

    @Test(enabled = true, dependsOnMethods = {"testDateNavigation", "testRosterEdit"})/*passed*/
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
        RosterDayEditPage rosterDayEditPage = new RosterDayEditPage(driver);
        assertEquals(rosterDayEditPage.getUserNameText(), pdr_user_name);
        /**
         * Move to specific date to get a specific roster:
         */
        rosterDayEditPage.goToDate("01.07.2020"); //This date is a wednesday.
        assertEquals("2020-07-01", rosterDayEditPage.getDate());
        /**
         * Get roster items and compare to assertions:
         */
        RosterItem rosterItem = rosterDayEditPage.getRosterItem(2);
        //assertEquals("Tuesday 30.06.", rosterItem.getDateString());
        String employeeNameHash = DigestUtils.md5Hex(rosterItem.getEmployeeName());
        assertEquals("74f66fde3d90d47d20c8402fec499fb8", employeeNameHash);
        assertEquals(1, rosterItem.getDate().get(Calendar.DAY_OF_MONTH));
        assertEquals(6, rosterItem.getDate().get(Calendar.MONTH)); //5 is June, 0 is January
        assertEquals("08:00", rosterItem.getDutyStart());
        assertEquals("16:30", rosterItem.getDutyEnd());
        assertEquals("12:00", rosterItem.getBreakStart());
        assertEquals("12:30", rosterItem.getBreakEnd());
    }

    @Test(enabled = true)/*new*/
    public void testRosterEdit() {
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
        RosterDayEditPage rosterDayEditPage = new RosterDayEditPage(driver);
        assertEquals(rosterDayEditPage.getUserNameText(), pdr_user_name);
        /**
         * Move to specific date to get a specific roster:
         */
        SimpleDateFormat sqlDateFormat = new SimpleDateFormat("yyyy-MM-dd", Locale.GERMANY);
        Calendar calendar = Calendar.getInstance(Locale.GERMANY);
        calendar.set(2020, Calendar.JULY, 1, 0, 0, 0);//Time incl. seconds must be set to 0:00:00 to match the timestamp in the page src.
        rosterDayEditPage.goToDate(calendar); //This date is a wednesday.
        assertEquals(sqlDateFormat.format(calendar.getTime()), rosterDayEditPage.getDate());
        RosterItem rosterItem = new RosterItem("Mandelkow", calendar, "08:00", "16:30", "11:30", "12:00", "Hauptapotheke", "Dies ist ein Kommentar");
        /**
         * <p lang=de>TODO: Das RosterItem enthält den Namen als String.
         * rosterDayEditPage.changeRosterInputEmployee braucht die EmployeeId
         * als int. RosterItem bräuchte eine Funktion um das intern selbst
         * bestimmen zu können.
         * </p>
         */
        rosterDayEditPage.changeRosterInputEmployee(calendar.getTimeInMillis() / 1000, 0, 5);
        rosterDayEditPage.changeRosterInputDutyStart(calendar.getTimeInMillis() / 1000, 0, rosterItem.getDutyStart());
        rosterDayEditPage.changeRosterInputDutyEnd(calendar.getTimeInMillis() / 1000, 0, rosterItem.getDutyEnd());
        rosterDayEditPage.changeRosterInputBreakStart(calendar.getTimeInMillis() / 1000, 0, rosterItem.getBreakStart());
        rosterDayEditPage.changeRosterInputBreakEnd(calendar.getTimeInMillis() / 1000, 0, rosterItem.getBreakEnd());
        rosterDayEditPage.rosterFormSubmit();
        RosterItem rosterItem2 = new RosterItem("Albrecht", calendar, "08:00", "16:30", "12:00", "12:30", "Hauptapotheke", "");
        RosterItem rosterItem3 = new RosterItem("Lange", calendar, "09:00", "18:00", "12:30", "13:00", "Hauptapotheke", "");
        RosterItem rosterItem4 = new RosterItem("Zander", calendar, "09:30", "18:00", "13:00", "13:30", "Hauptapotheke", "");
        rosterDayEditPage.rosterInputAddRow(rosterItem2);
        rosterDayEditPage.rosterInputAddRow(rosterItem3);
        rosterDayEditPage.rosterInputAddRow(rosterItem4);
        /**
         * Diese Funktion muss noch implementiert werden.
         */
        Assert.assertEquals(false, true);
    }

    @BeforeMethod
    public void setUp() {
        /*driver = */
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
