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
import Selenium.PropertyFile;
import Selenium.RosterItem;
import Selenium.ScreenShot;
import Selenium.signin.SignInPage;
import java.text.ParseException;
import java.time.DayOfWeek;
import java.time.LocalDate;
import java.time.Month;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Optional;
import java.util.Set;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.openqa.selenium.WebDriver;
import org.testng.Assert;
import org.testng.ITestResult;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import org.testng.asserts.SoftAssert;

/**
 *
 * @author Martin Mandelkow <netbeans@martin-mandelkow.de>
 */
public class TestEmployeePage {

    WebDriver driver;
    SoftAssert softAssert = new SoftAssert();

    @Test(enabled = true)/*new*/
    public void testEmployeePageRead() {
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
        EmployeePage employeePage = new EmployeePage(driver);
        Assert.assertEquals(employeePage.getUserNameText(), pdr_user_name);

        /**
         * Move to specific employee:
         */
        int employeeId = 1;
        employeePage.selectEmployee(employeeId);
        Assert.assertEquals(employeeId, employeePage.getEmployeeId());

        int alternationId = 0;
        int branchId = 1;
        PrincipleRoster principleRoster = new PrincipleRoster(branchId, alternationId);
        HashMap<DayOfWeek, PrincipleRosterDay> principleEmployeeRoster = principleRoster.getPrincipleRosterByEmployee(employeeId);
        principleEmployeeRoster.entrySet().forEach(principleRosterDayEntry -> {
            principleRosterDayEntry.getValue().getlistOfPrincipleRosterItems().values().forEach((principleRosterItemShould) -> {
                /**
                 * Dies ist das PrincipleRosterItem
                 */
                Set<RosterItem> setOfRosterItemsFound = employeePage.getSetOfRosterItems(alternationId, principleRosterDayEntry.getKey());
                HashSet<RosterItem> setOfEqualItems = new HashSet();
                setOfRosterItemsFound.stream().map(rosterItemFound -> {
                    if (rosterItemFound.getEmployeeId() == principleRosterItemShould.getEmployeeId()
                            && rosterItemFound.getBranchId() == principleRosterItemShould.getBranchId()
                            && rosterItemFound.getLocalDate().getDayOfWeek().equals(principleRosterItemShould.getWeekday())
                            && rosterItemFound.getDutyStartLocalDateTime().toLocalTime().equals(principleRosterItemShould.getDutyStart())
                            && rosterItemFound.getDutyEndLocalDateTime().toLocalTime().equals(principleRosterItemShould.getDutyEnd())
                            && rosterItemFound.getBreakStartLocalDateTime().toLocalTime().equals(principleRosterItemShould.getBreakStart())
                            && rosterItemFound.getBreakEndLocalDateTime().toLocalTime().equals(principleRosterItemShould.getBreakEnd())) {
                        setOfEqualItems.add(rosterItemFound);
                    }
                    return rosterItemFound;
                }).forEachOrdered(_item -> {
                    softAssert.assertEquals(1, setOfEqualItems.size());
                });
            }
            );
        });
        softAssert.assertAll();
    }

    @Test(dependsOnMethods = {"testEmployeePageRead"}, enabled = true)/*new*/
    public void testEmployeePageWrite() throws ParseException, Exception {
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
        EmployeePage employeePage = new EmployeePage(driver);
        Assert.assertEquals(employeePage.getUserNameText(), pdr_user_name);

        /**
         * Move to specific employee:
         */
        employeePage.selectEmployee(1);
        Assert.assertEquals(1, employeePage.getEmployeeId());
        /**
         * Set a new roster item:
         */

        LocalDate localDate = LocalDate.of(2021, Month.SEPTEMBER, 21);
        RosterItem rosterItemNew = new RosterItem(1, localDate, "10:30", "19:00", "12:30", "13:30", null, 1);
        //RosterItem rosterItemOld = employeePage.getRosterItem(1, 1, 1);
        HashSet<RosterItem> rosterItemsOldSet = employeePage.getSetOfRosterItems(0, DayOfWeek.MONDAY);
        Optional<RosterItem> rosterItemOldOptional = rosterItemsOldSet.stream().findFirst();
        if (rosterItemOldOptional.isEmpty()) {
            throw new Exception("No rosterItem was found in the principle roster. There has to be at least one item!");
        }
        RosterItem rosterItemOld = rosterItemOldOptional.get();

        Assert.assertEquals(rosterItemsOldSet.size(), 1); // This method will only work if there is only one item in the set.

        RosterItem rosterItemChanged;
        employeePage.setRosterItem(1, 1, 1, rosterItemNew);
        rosterItemChanged = employeePage.getRosterItem(1, 1, 1);

        softAssert.assertEquals(rosterItemChanged.getEmployeeName(), rosterItemNew.getEmployeeName());
        softAssert.assertEquals(rosterItemChanged.getDutyStart(), rosterItemNew.getDutyStart());
        softAssert.assertEquals(rosterItemChanged.getDutyEnd(), rosterItemNew.getDutyEnd());
        softAssert.assertEquals(rosterItemChanged.getBreakStart(), rosterItemNew.getBreakStart());
        softAssert.assertEquals(rosterItemChanged.getBreakEnd(), rosterItemNew.getBreakEnd());
        softAssert.assertEquals(rosterItemChanged.getBranchId(), rosterItemNew.getBranchId());
        /**
         * Reset everything to the old state:
         */
        employeePage.setRosterItem(1, 1, 1, rosterItemOld);
        softAssert.assertAll();
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
        //driver.quit();
    }

}
