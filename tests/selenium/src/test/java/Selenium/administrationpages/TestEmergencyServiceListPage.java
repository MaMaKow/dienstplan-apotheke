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
package Selenium.administrationpages;

import Selenium.PropertyFile;
import Selenium.ScreenShot;
import Selenium.rosterpages.Workforce;
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
public class TestEmergencyServiceListPage {

    WebDriver driver;

    @Test(enabled = true)/*new*/
    public void testEmergencyService() {
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
        EmergencyServiceListPage emergencyServiceListPage = new EmergencyServiceListPage(driver);
        Assert.assertEquals(emergencyServiceListPage.getUserNameText(), pdr_user_name);
        emergencyServiceListPage.selectYear("2021");
        emergencyServiceListPage.selectBranch(2);
        emergencyServiceListPage.selectYear("2019");
        emergencyServiceListPage.selectBranch(1);
        Assert.assertEquals(emergencyServiceListPage.getYear(), 2019);
        Assert.assertEquals(emergencyServiceListPage.getBranchId(), 1);
        /**
         * <p lang=de>Daten einfügen:</p>
         */
        Integer employeeIdInsert = 2;
        Integer employeeIdChange = 4;
        Workforce workforce = new Workforce();
        /**
         * <p lang=de>Nur Apotheker (und PI) können allein im Notdienst
         * einhesetzt werden.</p>
         */
        Assert.assertEquals("Apotheker", workforce.getEmployeeById(employeeIdInsert).getProfession());
        Assert.assertEquals("Apotheker", workforce.getEmployeeById(employeeIdChange).getProfession());
        LocalDate localDate = LocalDate.of(2019, Month.AUGUST, 8);
        emergencyServiceListPage = emergencyServiceListPage.addLineForDate(localDate);
        emergencyServiceListPage = emergencyServiceListPage.setEmployeeIdOnDate(localDate, employeeIdInsert);
        /**
         * <p lang=de>Daten abfragen:</p>
         */
        Assert.assertEquals(emergencyServiceListPage.getEmployeeIdOnDate(localDate), employeeIdInsert);
        emergencyServiceListPage = emergencyServiceListPage.setEmployeeIdOnDate(localDate, employeeIdChange);
        Assert.assertEquals(emergencyServiceListPage.getEmployeeIdOnDate(localDate), employeeIdChange);
        /**
         * <p lang=de>Zeilen wieder entfernen</p>
         */
        emergencyServiceListPage.doNotRemoveLineByDate(localDate);
        Assert.assertNotNull(emergencyServiceListPage.getEmployeeIdOnDate(localDate));
        emergencyServiceListPage = emergencyServiceListPage.removeLineByDate(localDate);
        Assert.assertNull(emergencyServiceListPage.getEmployeeIdOnDate(localDate));
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
