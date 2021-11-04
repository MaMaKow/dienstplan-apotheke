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

import Selenium.ReadPropertyFile;
import Selenium.ScreenShot;
import Selenium.signin.SignInPage;
import java.util.Map;
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
public class TestWorkforceManagementPage {

    WebDriver driver;

    @Test(enabled = true)/*new*/
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
        WorkforceManagementPage workforceManagementPage = new WorkforceManagementPage(driver);
        Assert.assertEquals(workforceManagementPage.getUserNameText(), pdr_user_name);
        workforceManagementPage.selectEmployee(1);
        workforceManagementPage.selectEmployee(5);
        Map employeeData = workforceManagementPage.getEmployeeData();
        Assert.assertEquals(employeeData.get("employeeId"), String.valueOf(5));
        Assert.assertEquals(employeeData.get("employeeLastName"), "Mandelkow");
        Assert.assertEquals(employeeData.get("employeeFirstName"), "Martin");
        Assert.assertEquals(employeeData.get("employeeProfession"), "Apotheker");
        Assert.assertEquals(employeeData.get("employeeWorkingHours"), String.valueOf(40));
        Assert.assertEquals(employeeData.get("employeeLunchBreakMinutes"), String.valueOf(30));
        Assert.assertEquals(employeeData.get("employeeHolidays"), String.valueOf(28));
        Assert.assertEquals(employeeData.get("employeeBranch"), String.valueOf(1));
        Assert.assertEquals(employeeData.get("employeeAbilitiesGoodsReceipt"), "true");
        Assert.assertEquals(employeeData.get("employeeAbilitiesCompounding"), null);
        Assert.assertEquals(employeeData.get("employeeStartOfEmployment"), "2015-01-01");
        Assert.assertEquals(employeeData.get("employeeEndOfEmployment"), "");
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
