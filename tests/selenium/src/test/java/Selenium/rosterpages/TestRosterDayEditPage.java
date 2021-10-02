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

import Selenium.HomePage;
import Selenium.ReadPropertyFile;
import Selenium.RosterItem;
import Selenium.ScreenShot;
import Selenium.signinpage.SignInPage;
import java.util.Calendar;
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

    @Test(enabled = false)/*passed*/
    public void testDateNavigation() {
        try {
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

    @Test(enabled = false)/*passed*/
    public void testRosterDisplay() throws Exception {
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
        HomePage homePage = signInPage.loginValidUser(pdr_user_name, pdr_user_password);
        RosterDayEditPage rosterWeekTablePage = new RosterDayEditPage(driver);
        assertEquals(rosterWeekTablePage.getUserNameText(), pdr_user_name);
        /**
         * Move to specific date to get a specific roster:
         */
        rosterWeekTablePage.goToDate("01.07.2020"); //This date is a wednesday.
        assertEquals("2020-07-01", rosterWeekTablePage.getDate()); //This is the corresponding monday.
        /**
         * Get roster items and compare to assertions:
         */
        RosterItem rosterItem = rosterWeekTablePage.getRosterItem(2);
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

    @Test(enabled = false)/*new*/
    public void testRosterEdit() {
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
        driver.quit();

    }
}
