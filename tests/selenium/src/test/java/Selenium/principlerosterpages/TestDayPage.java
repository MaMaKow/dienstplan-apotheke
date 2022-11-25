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
package Selenium.principlerosterpages;

import Selenium.PrincipleRoster;
import Selenium.PrincipleRosterDay;
import Selenium.PrincipleRosterItem;
import Selenium.PropertyFile;
import Selenium.ScreenShot;
import Selenium.signin.SignInPage;
import java.text.ParseException;
import java.time.DayOfWeek;
import java.time.LocalTime;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;
import org.testng.ITestResult;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.BeforeSuite;
import org.testng.annotations.Test;
import org.testng.asserts.SoftAssert;

/**
 *
 * @author Martin Mandelkow <netbeans@martin-mandelkow.de>
 */
public class TestDayPage {

    WebDriver driver;
    SoftAssert softAssert = new SoftAssert();
    private PropertyFile propertyFile;

    @Test(enabled = true, dependsOnMethods = {"testRosterCreate", "testRosterCopyAlternation"})/*passed*/
    public void testDateNavigation() throws Exception {
        driver = Selenium.driver.Wrapper.getDriver();
        propertyFile = new PropertyFile();
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

    @Test(enabled = true, dependsOnMethods = {"testRosterCreate", "testRosterCopyAlternation", "testRosterChangeDragAndDrop"})/*passed*/
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
        PrincipleRosterItem principleRosterItem = principleRosterMonday.getPrincipleRosterItemByEmployeeId(employeeId);

        dayPage.goToBranch(branchId);
        dayPage.goToAlternation(alternationId);
        dayPage.goToWeekday(dayOfWeek);
        PrincipleRosterItem rosterItemRead = dayPage.getRosterItemByEmployeeId(employeeId);
        softAssert.assertEquals(rosterItemRead.getEmployeeName(), principleRosterItem.getEmployeeName());
        softAssert.assertEquals(rosterItemRead.getBranchId(), principleRosterItem.getBranchId());
        softAssert.assertEquals(rosterItemRead.getDutyStart(), principleRosterItem.getDutyStart());
        softAssert.assertEquals(rosterItemRead.getDutyEnd(), principleRosterItem.getDutyEnd());
        softAssert.assertEquals(rosterItemRead.getBreakStart(), principleRosterItem.getBreakStart());
        softAssert.assertEquals(rosterItemRead.getBreakEnd(), principleRosterItem.getBreakEnd());
        //Assert.assertEquals("", rosterItem.getComment(),); //TODO: Kommentare
        softAssert.assertAll();
    }

    @Test(enabled = true, dependsOnMethods = {"testRosterCreate", "testRosterCopyAlternation"})/*new*/
    public void testRosterChange() throws Exception {
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
        int employeeId = 2;

        dayPage.goToBranch(branchId);
        dayPage.goToAlternation(alternationId);
        dayPage.goToWeekday(dayOfWeek);

        PrincipleRoster principleRoster = new PrincipleRoster(branchId, alternationId);
        PrincipleRosterDay principleRosterMonday = principleRoster.getPrincipleRosterDay(dayOfWeek);
        PrincipleRosterItem principleRosterItem = principleRosterMonday.getPrincipleRosterItemByEmployeeId(employeeId);
        /**
         * principleRosterItemChanged holds the values, into which the
         * principleRosterItem will be changed.
         */
        int employeeIdChanged = 11;
        PrincipleRosterItem principleRosterItemChanged = new PrincipleRosterItem(employeeIdChanged, dayOfWeek, LocalTime.of(11, 05), LocalTime.of(16, 10), LocalTime.of(12, 35), LocalTime.of(13, 10), null, branchId);

        dayPage.changeRosterInputDutyStart(employeeId, principleRosterItemChanged.getDutyStart());
        dayPage.changeRosterInputDutyEnd(employeeId, principleRosterItemChanged.getDutyEnd());
        dayPage.changeRosterInputBreakStart(employeeId, principleRosterItemChanged.getBreakStart());
        dayPage.changeRosterInputBreakEnd(employeeId, principleRosterItemChanged.getBreakEnd());
        dayPage.changeRosterInputEmployee(employeeId, principleRosterItemChanged.getEmployeeId());
        dayPage.rosterFormSubmit();
        PrincipleRosterItem rosterItemRead = dayPage.getRosterItemByEmployeeId(employeeIdChanged);
        softAssert.assertEquals(rosterItemRead.getDutyStart(), principleRosterItemChanged.getDutyStart());
        softAssert.assertEquals(rosterItemRead.getDutyEnd(), principleRosterItemChanged.getDutyEnd());
        softAssert.assertEquals(rosterItemRead.getBreakStart(), principleRosterItemChanged.getBreakStart());
        softAssert.assertEquals(rosterItemRead.getBreakEnd(), principleRosterItemChanged.getBreakEnd());
        softAssert.assertEquals(rosterItemRead.getEmployeeId(), principleRosterItemChanged.getEmployeeId());

        /**
         * Revert the changes for the next test:
         */
        dayPage.changeRosterInputDutyStart(employeeIdChanged, principleRosterItem.getDutyStart());
        dayPage.changeRosterInputDutyEnd(employeeIdChanged, principleRosterItem.getDutyEnd());
        dayPage.changeRosterInputBreakStart(employeeIdChanged, principleRosterItem.getBreakStart());
        dayPage.changeRosterInputBreakEnd(employeeIdChanged, principleRosterItem.getBreakEnd());
        dayPage.changeRosterInputEmployee(employeeIdChanged, employeeId);
        dayPage.rosterFormSubmit();
        softAssert.assertAll();
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
    public void testRosterCreate() throws Exception {
        driver = Selenium.driver.Wrapper.getDriver();
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
         * Move to specific branch:
         */
        int branchId = 1;
        DayOfWeek dayOfWeek = DayOfWeek.MONDAY;
        dayPage.goToBranch(branchId);
        /**
         * alternationId MUST be 0! There is no other alternation yet. Therefore
         * there is no SELECT element for it yet.
         *
         */
        int alternationId = 0;
        dayPage.goToAlternation(0);
        dayPage.goToWeekday(dayOfWeek);
        PrincipleRoster principleRoster = new PrincipleRoster(branchId, alternationId);
        PrincipleRosterDay principleRosterDay = principleRoster.getPrincipleRosterDay(dayOfWeek);

        dayPage.addRosterRow();
        for (PrincipleRosterItem principleRosterItem : principleRosterDay.getlistOfPrincipleRosterItems().values()) {
            dayPage.createNewRosterItem(principleRosterItem);
        }
        for (PrincipleRosterItem principleRosterItem : principleRosterDay.getlistOfPrincipleRosterItems().values()) {
            PrincipleRosterItem principleRosterItemRead = dayPage.getRosterItemByEmployeeId(principleRosterItem.getEmployeeId());
            softAssert.assertEquals(principleRosterItemRead.getDutyStart(), principleRosterItem.getDutyStart());
            softAssert.assertEquals(principleRosterItemRead.getEmployeeName(), principleRosterItem.getEmployeeName());
            softAssert.assertEquals(principleRosterItemRead.getDutyEnd(), principleRosterItem.getDutyEnd());
            softAssert.assertEquals(principleRosterItemRead.getBreakStart(), principleRosterItem.getBreakStart());
            softAssert.assertEquals(principleRosterItemRead.getBreakEnd(), principleRosterItem.getBreakEnd());
            softAssert.assertAll();
        }
    }

    @Test(enabled = true, dependsOnMethods = {"testRosterCreate", "testRosterCopyAlternation", "testRosterChangeDragAndDrop"})/*new*/
    public void testRosterChangePlotErrors() throws ParseException, Exception {
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
        PrincipleRoster principleRoster = new PrincipleRoster(branchId, alternationId);
        PrincipleRosterDay principleRosterDay = principleRoster.getPrincipleRosterDay(dayOfWeek);
        PrincipleRosterItem principleRosterItem = principleRosterDay.getPrincipleRosterItemByEmployeeId(employeeId);

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
        softAssert.assertEquals(rosterItemRead.getDutyStart(), principleRosterItem.getDutyStart());
        softAssert.assertEquals(rosterItemRead.getEmployeeName(), principleRosterItem.getEmployeeName());
        softAssert.assertAll();
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
        PrincipleRoster principleRoster = new PrincipleRoster(branchId, alternationId);
        PrincipleRosterDay principleRosterDay = principleRoster.getPrincipleRosterDay(dayOfWeek);
        PrincipleRosterItem principleRosterItem = principleRosterDay.getPrincipleRosterItemByEmployeeId(employeeId);
        try {
            int dutyOffset = 90;
            int breakOffset = 120;
            dayPage.changeRosterByDragAndDrop(dayPage.getUnixTime(), employeeId, dutyOffset, "duty");
            dayPage.changeRosterByDragAndDrop(dayPage.getUnixTime(), employeeId, breakOffset, "break");
            dayPage.rosterFormSubmit();
            PrincipleRosterItem rosterItemRead = dayPage.getRosterItemByEmployeeId(employeeId);
            /**
             * <p lang=en>CAVE: rosterItem is read after the first dragAndDrop.
             * Those changes are reverted afterwards. The Assertions work on the
             * firstly changed values.</p>
             */
            softAssert.assertEquals(rosterItemRead.getEmployeeName(), principleRosterItem.getEmployeeName());
            softAssert.assertEquals(rosterItemRead.getDutyStart(), principleRosterItem.getDutyStart().plusMinutes(dutyOffset));
            softAssert.assertEquals(rosterItemRead.getDutyEnd(), principleRosterItem.getDutyEnd().plusMinutes(dutyOffset));
            softAssert.assertEquals(rosterItemRead.getBreakStart(), principleRosterItem.getBreakStart().plusMinutes(breakOffset));
            softAssert.assertEquals(rosterItemRead.getBreakEnd(), principleRosterItem.getBreakEnd().plusMinutes(breakOffset));
            /**
             * Revert the changes for the next test:
             */
            dayPage.changeRosterByDragAndDrop(dayPage.getUnixTime(), employeeId, -dutyOffset, "duty");
            dayPage.changeRosterByDragAndDrop(dayPage.getUnixTime(), employeeId, -breakOffset, "break");
            dayPage.rosterFormSubmit();
            softAssert.assertAll();
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

    @BeforeSuite
    public void setUpSuite() {
        driver = Selenium.driver.Wrapper.getDriver();
        /**
         * Refresh the page contents from the nextcloud data:
         */
        propertyFile = new PropertyFile();
        String testPageFolderPath = propertyFile.getUrlInstallTest();
        driver.get(testPageFolderPath + "selenium-refresh.php");
        By seleniumCopyDoneBy = By.xpath("//*[@id=\"span_done\"]");
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.presenceOfElementLocated(seleniumCopyDoneBy));
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
