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

import Selenium.PropertyFile;
import Selenium.RosterItem;
import Selenium.ScreenShot;
import Selenium.signin.SignInPage;
import java.text.ParseException;
import java.time.LocalDate;
import java.time.Month;
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
public class TestEmployeePage {

    WebDriver driver;

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
         * Move to specific month:
         */
        employeePage.selectEmployee(5);
        Assert.assertEquals(5, employeePage.getEmployeeId());
        RosterItem rosterItem;
        try {
            rosterItem = employeePage.getRosterItem(1, 1, 1);
        } catch (ParseException ex) {
            Logger.getLogger(TestEmployeePage.class.getName()).log(Level.SEVERE, null, ex);
        }
        Assert.assertEquals(false, true);
    }

    @Test(dependsOnMethods = {"testEmployeePageRead"}, enabled = true)/*new*/
    public void testEmployeePageWrite() throws ParseException {
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
         * Move to specific month:
         */
        employeePage.selectEmployee(5);
        Assert.assertEquals(5, employeePage.getEmployeeId());
        /**
         * Set a new roster item:
         */

        LocalDate localDate = LocalDate.of(2021, Month.SEPTEMBER, 21);
        RosterItem rosterItemNew = new RosterItem(5, localDate, "10:30", "19:00", "12:30", "13:30", null, 2);
        RosterItem rosterItemOld = employeePage.getRosterItem(1, 1, 1);
        RosterItem rosterItemChanged;
        employeePage.setRosterItem(1, 1, 1, rosterItemNew);
        rosterItemChanged = employeePage.getRosterItem(1, 1, 1);
        Assert.assertEquals(rosterItemChanged.getEmployeeName(), rosterItemNew.getEmployeeName());
        Assert.assertEquals(rosterItemChanged.getDutyStart(), rosterItemNew.getDutyStart());
        Assert.assertEquals(rosterItemChanged.getDutyEnd(), rosterItemNew.getDutyEnd());
        Assert.assertEquals(rosterItemChanged.getBreakStart(), rosterItemNew.getBreakStart());
        Assert.assertEquals(rosterItemChanged.getBreakEnd(), rosterItemNew.getBreakEnd());
        Assert.assertEquals(rosterItemChanged.getBranchId(), rosterItemNew.getBranchId());
        /**
         * Reset everything to the old state:
         */
        employeePage.setRosterItem(1, 1, 1, rosterItemOld);
        //Assert.assertEquals(false, true);
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
