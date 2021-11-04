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
import Selenium.signin.SignInPage;
import java.util.Calendar;
import java.util.Locale;
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
        Calendar targetDate = Calendar.getInstance(Locale.GERMANY);
        targetDate.set(2019, Calendar.AUGUST, 8);
        emergencyServiceListPage = emergencyServiceListPage.addLineForDate(targetDate);
        emergencyServiceListPage = emergencyServiceListPage.setEmployeeIdOnDate(targetDate.getTime(), 9);
        /**
         * <p lang=de>Daten abfragen:</p>
         */
        Assert.assertEquals(emergencyServiceListPage.getEmployeeIdOnDate(targetDate.getTime()), (Integer) 9);
        emergencyServiceListPage = emergencyServiceListPage.setEmployeeIdOnDate(targetDate.getTime(), 5);
        Assert.assertEquals(emergencyServiceListPage.getEmployeeIdOnDate(targetDate.getTime()), (Integer) 5);
        /**
         * <p lang=de>Zeilen wieder entfernen</p>
         */
        emergencyServiceListPage.doNotRemoveLineByDate(targetDate);
        emergencyServiceListPage = emergencyServiceListPage.removeLineByDate(targetDate);
        Assert.assertNull(emergencyServiceListPage.getEmployeeIdOnDate(targetDate.getTime()));
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
