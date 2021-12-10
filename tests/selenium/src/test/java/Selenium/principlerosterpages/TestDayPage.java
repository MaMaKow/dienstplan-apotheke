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

import Selenium.Employee;
import Selenium.PrincipleRoster;
import Selenium.PrincipleRosterDay;
import Selenium.PrincipleRosterItem;
import Selenium.PropertyFile;
import Selenium.RosterItem;
import Selenium.ScreenShot;
import Selenium.rosterpages.Workforce;
import Selenium.signin.SignInPage;
import java.text.ParseException;
import java.time.DayOfWeek;
import java.time.Instant;
import java.time.LocalDate;
import java.time.LocalTime;
import java.time.Month;
import java.time.ZoneId;
import java.util.HashMap;
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

    @Test(enabled = true, dependsOnMethods = {"testRosterCreate", "testRosterCopyAlternation"})/*passed*/
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

    @Test(enabled = true, dependsOnMethods = {"testRosterCreate", "testRosterCopyAlternation"})/*passed*/
    public void testRosterDisplay() {
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
        DayPage dayPage = new DayPage(driver);
        Assert.assertEquals(dayPage.getUserNameText(), pdr_user_name);

        /**
         * Move to specific principle roster:
         */
        int branchId = 1;
        int alternationId = 0;
        DayOfWeek dayOfWeek = DayOfWeek.MONDAY;
        int employeeId = 2; //TODO: Hier könnte eine for-Schleife stehen. Die geht dann alle employees in principleRosterMonday durch.

        PrincipleRoster principleRoster = new PrincipleRoster(branchId, alternationId);
        PrincipleRosterDay principleRosterMonday = principleRoster.getPrincipleRosterDay(dayOfWeek);
        dayPage.goToBranch(branchId);
        dayPage.goToAlternation(alternationId);
        dayPage.goToWeekday(dayOfWeek);
        PrincipleRosterItem rosterItemRead = dayPage.getRosterItemByEmployeeId(employeeId);
        PrincipleRosterItem principleRosterItem = principleRosterMonday.getPrincipleRosterItemByEmployeeId(employeeId);
        Assert.assertEquals(rosterItemRead.getBranchId(), principleRosterItem.getBranchId());
        Assert.assertEquals(rosterItemRead.getDutyStart(), principleRosterItem.getDutyStart());
        Assert.assertEquals(rosterItemRead.getDutyEnd(), principleRosterItem.getDutyEnd());
        Assert.assertEquals(rosterItemRead.getBreakStart(), principleRosterItem.getBreakStart());
        Assert.assertEquals(rosterItemRead.getBreakEnd(), principleRosterItem.getBreakEnd());
        //Assert.assertEquals("", rosterItem.getComment(),); //TODO: Kommentare
        Assert.assertEquals(rosterItemRead.getEmployeeName(), principleRosterItem.getEmployeeName());
    }

    @Test(enabled = true, dependsOnMethods = {"testRosterCreate", "testRosterCopyAlternation"})/*new*/
    public void testRosterChange() {
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
        DayPage dayPage = new DayPage(driver);
        Assert.assertEquals(dayPage.getUserNameText(), pdr_user_name);

        /**
         * Move to specific month:
         */
        int branchId = 1;
        int alternationId = 0;
        DayOfWeek dayOfWeek = DayOfWeek.MONDAY;
        int employeeId = 2; //TODO: Hier könnte eine for-Schleife stehen. Die geht dann alle employees in principleRosterMonday durch.

        dayPage.goToBranch(branchId);
        dayPage.goToAlternation(alternationId);
        dayPage.goToWeekday(dayOfWeek);

        dayPage.changeRosterInputDutyStart(employeeId, LocalTime.of(11, 0));
        dayPage.changeRosterInputDutyEnd(employeeId, LocalTime.of(16, 0));
        dayPage.changeRosterInputBreakStart(employeeId, LocalTime.of(12, 30));
        dayPage.changeRosterInputBreakEnd(employeeId, LocalTime.of(13, 0));
        dayPage.changeRosterInputEmployee(employeeId, 11);
        dayPage.rosterFormSubmit();
        PrincipleRosterItem rosterItemRead = dayPage.getRosterItemByEmployeeId(employeeId);
        Assert.assertEquals(rosterItemRead.getDutyStart(), LocalTime.of(11, 0));
        Assert.assertEquals(rosterItemRead.getDutyEnd(), LocalTime.of(16, 0));
        Assert.assertEquals(rosterItemRead.getBreakStart(), LocalTime.of(12, 30));
        Assert.assertEquals(rosterItemRead.getBreakEnd(), LocalTime.of(13, 0));
        Assert.assertEquals(rosterItemRead.getEmployeeId(), 11);

        /**
         * Revert the changes for the next test:
         */
        dayPage.changeRosterInputDutyStart(employeeId, LocalTime.of(8, 0));
        dayPage.changeRosterInputDutyEnd(employeeId, LocalTime.of(18, 0));
        dayPage.changeRosterInputBreakStart(employeeId, LocalTime.of(11, 30));
        dayPage.changeRosterInputBreakEnd(employeeId, LocalTime.of(12, 0));
        dayPage.changeRosterInputEmployee(employeeId, 12);
        dayPage.rosterFormSubmit();
    }

    @Test(enabled = true, dependsOnMethods = {"testRosterCreate"})/*new*/
    public void testRosterCopyAlternation() {
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
        DayPage dayPage = new DayPage(driver);
        Assert.assertEquals(dayPage.getUserNameText(), pdr_user_name);

        /**
         * Copy the alternation:
         */
        dayPage.copyAlternation();
    }

    @Test(enabled = true)/*new*/
    public void testRosterCreate() {
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
        DayPage dayPage = new DayPage(driver);
        Assert.assertEquals(dayPage.getUserNameText(), pdr_user_name);

        /**
         * Move to specific month:
         */
        int branchId = 1;
        DayOfWeek dayOfWeek = DayOfWeek.MONDAY;
        int employeeId = 2; //TODO: Hier könnte eine for-Schleife stehen. Die geht dann alle employees in principleRosterMonday durch.
        dayPage.goToBranch(branchId);
        /**
         * alternationId MUST be 0! There is no other alternation yet. Therefore
         * there is no SELECT element for it yet. TODO: Implement a failsafe
         * dayPage.goToAlternation();
         *
         */
        int alternationId = 0;
        //dayPage.goToAlternation(0);
        dayPage.goToWeekday(dayOfWeek);
        PrincipleRoster principleRoster = new PrincipleRoster(branchId, alternationId);

        dayPage.addRosterRow();
        PrincipleRosterDay principleRosterDay = principleRoster.getPrincipleRosterDay(dayOfWeek);
        for (PrincipleRosterItem principleRosterItem : principleRosterDay.getlistOfPrincipleRosterItems().values()) {
            dayPage.createNewRosterItem(principleRosterItem);
            PrincipleRosterItem principleRosterItemRead = dayPage.getRosterItemByEmployeeId(employeeId);
            Assert.assertEquals(principleRosterItemRead.getDutyStart(), principleRosterItem.getDutyStart());
            Assert.assertEquals(principleRosterItemRead.getDutyEnd(), principleRosterItem.getDutyEnd());
            Assert.assertEquals(principleRosterItemRead.getBreakStart(), principleRosterItem.getBreakStart());
            Assert.assertEquals(principleRosterItemRead.getBreakEnd(), principleRosterItem.getBreakEnd());
            Assert.assertEquals(principleRosterItemRead.getEmployeeName(), principleRosterItem.getEmployeeName());
        }
    }

    @Test(enabled = true, dependsOnMethods = {"testRosterCreate", "testRosterCopyAlternation"})/*new*/
    public void testRosterChangePlotErrors() throws ParseException {
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
        DayPage dayPage = new DayPage(driver);
        Assert.assertEquals(dayPage.getUserNameText(), pdr_user_name);

        /**
         * Move to specific month:
         */
        int branchId = 1;
        DayOfWeek dayOfWeek = DayOfWeek.MONDAY;
        int employeeId = 2; //TODO: Hier könnte eine for-Schleife stehen. Die geht dann alle employees in principleRosterMonday durch.
        int alternationId = 0;

        dayPage.goToBranch(branchId);
        dayPage.goToAlternation(alternationId);
        dayPage.goToWeekday(dayOfWeek);

        /**
         * <p lang=de>
         * Teste die Reaktion auf fehlenden Dienststart
         * </p>
         */
        dayPage.changeRosterInputDutyStart(employeeId, null);
        dayPage.rosterFormSubmit();

        PrincipleRosterItem rosterItemRead = dayPage.getRosterItemByEmployeeId(employeeId);
        Assert.assertEquals(rosterItemRead.getDutyStart(), "11:00");
        Assert.assertEquals(rosterItemRead.getEmployeeName().length(), 9);

    }

    @Test(enabled = true, dependsOnMethods = {"testRosterCreate", "testRosterCopyAlternation"})/*new*/
    public void testRosterChangeDragAndDrop() throws Exception {
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
        DayPage dayPage = new DayPage(driver);
        Assert.assertEquals(dayPage.getUserNameText(), pdr_user_name);

        /**
         * Move to specific month:
         */
        int branchId = 1;
        DayOfWeek dayOfWeek = DayOfWeek.MONDAY;
        int employeeId = 2; //TODO: Hier könnte eine for-Schleife stehen. Die geht dann alle employees in principleRosterMonday durch.
        int alternationId = 0;

        dayPage.goToBranch(branchId);
        dayPage.goToAlternation(alternationId);
        dayPage.goToWeekday(dayOfWeek);

        try {
            dayPage.changeRosterByDragAndDrop(dayPage.getUnixTime(), 1, 30, "duty");
            dayPage.changeRosterByDragAndDrop(dayPage.getUnixTime(), 1, -30, "duty");
            dayPage.changeRosterByDragAndDrop(dayPage.getUnixTime(), 1, 360, "duty");
            dayPage.changeRosterByDragAndDrop(dayPage.getUnixTime(), 1, 90, "break");
            dayPage.rosterFormSubmit();
            PrincipleRosterItem rosterItemRead = dayPage.getRosterItemByEmployeeId(employeeId);
            /**
             * Revert the changes for the next test:
             */
            dayPage.changeRosterByDragAndDrop(dayPage.getUnixTime(), 1, -360, "duty");
            dayPage.changeRosterByDragAndDrop(dayPage.getUnixTime(), 1, -90, "break");
            dayPage.rosterFormSubmit();
            /**
             * <p lang=en>CAVE: rosterItem is read after the first dragAndDrop.
             * Those changes are reverted afterwards. The Assertions work on the
             * firstly changed values.</p>
             */
            Assert.assertEquals(rosterItemRead.getDutyStart(), "09:00");
            Assert.assertEquals(rosterItemRead.getDutyEnd(), "19:00");
            Assert.assertEquals(rosterItemRead.getBreakStart(), "13:00");
            Assert.assertEquals(rosterItemRead.getBreakEnd(), "13:30");
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
