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

import Selenium.HomePage;
import Selenium.PropertyFile;
import Selenium.RosterItem;
import Selenium.ScreenShot;
import Selenium.SignInPage.SignInPage;
import java.util.Calendar;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.apache.commons.codec.digest.DigestUtils;
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
public class TestRosterWeekTablePage {

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
        HomePage homePage = signInPage.loginValidUser(pdr_user_name, pdr_user_password);
        RosterWeekTablePage rosterWeekTablePage = new RosterWeekTablePage(driver);
        Assert.assertEquals(rosterWeekTablePage.getUserNameText(), pdr_user_name);
        /**
         * Move to specific date to get a specific roster:
         */
        rosterWeekTablePage.goToDate("01.07.2020"); //This date is a wednesday.
        Assert.assertEquals(rosterWeekTablePage.getDate(), "2020-06-29"); //This is the corresponding monday.
        /**
         * Get roster items and compare to assertions:
         */
        RosterItem rosterItem = rosterWeekTablePage.getRosterItem(2, 3);
        String employeeNameHash = DigestUtils.md5Hex(rosterItem.getEmployeeName());
        Assert.assertEquals(employeeNameHash, "3208b225b142f12b1f380b488837505f");
        Assert.assertEquals(30, rosterItem.getDate().get(Calendar.DAY_OF_MONTH));
        Assert.assertEquals(5, rosterItem.getDate().get(Calendar.MONTH)); //5 is June, 0 is January
        Assert.assertEquals("08:00", rosterItem.getDutyStart());
        Assert.assertEquals("16:30", rosterItem.getDutyEnd());
        Assert.assertEquals("12:00", rosterItem.getBreakStart());
        Assert.assertEquals("12:30", rosterItem.getBreakEnd());
    }

    @BeforeMethod
    public void setUp() {
        Selenium.driver.Wrapper.createNewDriver();
    }

    @AfterMethod
    public void tearDown(ITestResult testResult) {
        driver = Selenium.driver.Wrapper.getDriver();
        new ScreenShot(testResult);
        driver.quit();

    }

}
