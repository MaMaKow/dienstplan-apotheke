/*
 * Copyright (C) 2021 Martin Mandelkow <netbeans@martin-mandelkow.de>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
package Selenium.PrincipleRosterPages;

import Selenium.ReadPropertyFile;
import Selenium.RosterItem;
import Selenium.ScreenShot;
import Selenium.SignInPage.SignInPage;
import java.text.ParseException;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.openqa.selenium.WebDriver;
import org.testng.Assert;
import org.testng.ITestResult;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;

/**
 *
 * @author Martin Mandelkow <netbeans@martin-mandelkow.de>
 */
public class TestDayPage {

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
        DayPage dayPage = new DayPage(driver);
        Assert.assertEquals(dayPage.getUserNameText(), pdr_user_name);

        /**
         * Move to specific month:
         */
        dayPage.goToBranch(2);
        dayPage.goToBranch(1);
        dayPage.goToAlternation(1);
        dayPage.goToAlternation(0);
        dayPage.goToWeekday(1);
        dayPage.goToWeekday(7);
        dayPage.goToWeekday(5);
        Assert.assertEquals(5, dayPage.getWeekdayIndex());
        Assert.assertEquals(0, dayPage.getAlternationId());
        Assert.assertEquals(1, dayPage.getBranchId());
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
        DayPage dayPage = new DayPage(driver);
        Assert.assertEquals(dayPage.getUserNameText(), pdr_user_name);

        /**
         * Move to specific month:
         */
        dayPage.goToBranch(1);
        dayPage.goToAlternation(0);
        dayPage.goToWeekday(1);
        try {
            RosterItem rosterItem = dayPage.getRosterItem(2);
            //Assert.assertEquals(rosterItem.getBranchName(),"Apotheke");
            Assert.assertEquals(rosterItem.getDutyStart(), "08:00");
            Assert.assertEquals(rosterItem.getDutyEnd(), "18:00");
            Assert.assertEquals(rosterItem.getBreakStart(), "11:30");
            Assert.assertEquals(rosterItem.getBreakEnd(), "12:00");
            //Assert.assertEquals("", rosterItem.getComment(),);
            Assert.assertEquals(rosterItem.getEmployeeName().length(), 10);
        } catch (ParseException ex) {
            Logger.getLogger(TestDayPage.class.getName()).log(Level.SEVERE, null, ex);
        }
    }

    @Test(enabled = false)/*new*/
    public void testRosterChange() {
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
        DayPage dayPage = new DayPage(driver);
        Assert.assertEquals(dayPage.getUserNameText(), pdr_user_name);

        /**
         * Move to specific month:
         */
        dayPage.goToBranch(1);
        dayPage.goToAlternation(0);
        dayPage.goToWeekday(1);

        dayPage.changeRosterInputDutyStart(dayPage.getUnixTime(), 2, "11:00");
        dayPage.changeRosterInputDutyEnd(dayPage.getUnixTime(), 2, "16:00");
        dayPage.changeRosterInputBreakStart(dayPage.getUnixTime(), 2, "12:30");
        dayPage.changeRosterInputBreakEnd(dayPage.getUnixTime(), 2, "13:00");
        dayPage.changeRosterInputEmployee(dayPage.getUnixTime(), 2, 11);
        dayPage.rosterFormSubmit();
        try {
            RosterItem rosterItem = dayPage.getRosterItem(2);
            Assert.assertEquals(rosterItem.getDutyStart(), "11:00");
            Assert.assertEquals(rosterItem.getDutyEnd(), "16:00");
            Assert.assertEquals(rosterItem.getBreakStart(), "12:30");
            Assert.assertEquals(rosterItem.getBreakEnd(), "13:00");
            Assert.assertEquals(rosterItem.getEmployeeName().length(), 9);
        } catch (ParseException ex) {
            Logger.getLogger(TestDayPage.class.getName()).log(Level.SEVERE, null, ex);
        }
        /**
         * Revert the changes for the next test:
         */
        dayPage.changeRosterInputDutyStart(dayPage.getUnixTime(), 2, "08:00");
        dayPage.changeRosterInputDutyEnd(dayPage.getUnixTime(), 2, "18:00");
        dayPage.changeRosterInputBreakStart(dayPage.getUnixTime(), 2, "11:30");
        dayPage.changeRosterInputBreakEnd(dayPage.getUnixTime(), 2, "12:00");
        dayPage.changeRosterInputEmployee(dayPage.getUnixTime(), 2, 12);
        dayPage.rosterFormSubmit();
    }

    @Test(enabled = true)/*new*/
    public void testRosterChangeDragAndDrop() throws Exception {
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
        DayPage dayPage = new DayPage(driver);
        Assert.assertEquals(dayPage.getUserNameText(), pdr_user_name);

        /**
         * Move to specific month:
         */
        dayPage.goToBranch(1);
        dayPage.goToAlternation(0);
        dayPage.goToWeekday(1);

        try {
            dayPage.changeRosterByDragAndDrop(dayPage.getUnixTime(), 1, 30, "duty");
            dayPage.changeRosterByDragAndDrop(dayPage.getUnixTime(), 1, -30, "duty");
            dayPage.changeRosterByDragAndDrop(dayPage.getUnixTime(), 1, 360, "duty");
            dayPage.changeRosterByDragAndDrop(dayPage.getUnixTime(), 1, 90, "break");
            Thread.sleep(500);
            dayPage.rosterFormSubmit();
            RosterItem rosterItem = dayPage.getRosterItem(1);
            System.out.println(rosterItem.getDutyStart());
            System.out.println(rosterItem.getBreakStart());
            /**
             * Revert the changes for the next test:
             */
            dayPage.changeRosterByDragAndDrop(dayPage.getUnixTime(), 1, -360, "duty");
            dayPage.changeRosterByDragAndDrop(dayPage.getUnixTime(), 1, -90, "break");
            dayPage.rosterFormSubmit();
            /**
             * <p lang=en>CAVE: rosterItem is read after the forst dragAndDrop.
             * Those changes are reverted afterwards. The Assertions work on the
             * firstly changed values.</p>
             */
            Assert.assertEquals(rosterItem.getDutyStart(), "09:00");
            Assert.assertEquals(rosterItem.getDutyEnd(), "19:00");
            Assert.assertEquals(rosterItem.getBreakStart(), "13:00");
            Assert.assertEquals(rosterItem.getBreakEnd(), "13:30");
        } catch (ParseException ex) {
            Logger.getLogger(TestDayPage.class.getName()).log(Level.SEVERE, null, ex);
        } catch (InterruptedException ex) {
            Logger.getLogger(TestDayPage.class.getName()).log(Level.SEVERE, null, ex);
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
