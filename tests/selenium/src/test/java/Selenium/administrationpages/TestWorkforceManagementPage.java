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

import Selenium.Employee;
import Selenium.PropertyFile;
import Selenium.ScreenShot;
import Selenium.rosterpages.Workforce;
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

    @Test(enabled = true, dependsOnMethods = {"testCreateEmployee"})/*new*/
    public void testReadEmployee() {
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
        WorkforceManagementPage workforceManagementPage = new WorkforceManagementPage(driver);
        Assert.assertEquals(workforceManagementPage.getUserNameText(), pdr_user_name);
        workforceManagementPage.selectEmployee(1);

        Workforce workforce = new Workforce();
        Map<Integer, Employee> listOfEmployeesMap = workforce.readFromFile();
        Employee employeeObjectShould = listOfEmployeesMap.get(5);

        workforceManagementPage.selectEmployee(employeeObjectShould.getEmployeeId());

        Employee employeeObject = workforceManagementPage.getEmployeeObject();
        Assert.assertEquals(employeeObject.getEmployeeId(), employeeObjectShould.getEmployeeId());
        Assert.assertEquals(employeeObject.getLastName(), employeeObjectShould.getLastName());
        Assert.assertEquals(employeeObject.getFirstName(), employeeObjectShould.getFirstName());
        Assert.assertEquals(employeeObject.getProfession(), employeeObjectShould.getProfession());
        Assert.assertEquals(employeeObject.getWorkingHours(), employeeObjectShould.getWorkingHours());
        Assert.assertEquals(employeeObject.getLunchBreakMinutes(), employeeObjectShould.getLunchBreakMinutes());
        Assert.assertEquals(employeeObject.getHolidays(), employeeObjectShould.getHolidays());
        Assert.assertEquals(employeeObject.getBranchString(), employeeObjectShould.getBranchString());
        Assert.assertEquals(employeeObject.getAbilitiesGoodsReceipt(), employeeObjectShould.getAbilitiesGoodsReceipt());
        Assert.assertEquals(employeeObject.getAbilitiesCompounding(), employeeObjectShould.getAbilitiesCompounding());
        Assert.assertEquals(employeeObject.getStartOfEmployment(), employeeObjectShould.getStartOfEmployment());
        Assert.assertEquals(employeeObject.getEndOfEmployment(), employeeObjectShould.getEndOfEmployment());
    }

    @Test(enabled = true)/*new*/
    public void testCreateEmployee() {

        Workforce workforce = new Workforce();
        //workforce.writeToFile(listOfEmployees);
        Map<Integer, Employee> listOfEmployeesMap = workforce.readFromFile();
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
        WorkforceManagementPage workforceManagementPage = new WorkforceManagementPage(driver);
        Assert.assertEquals(workforceManagementPage.getUserNameText(), pdr_user_name);

        /**
         * TODO: CAVE! Old employees seem to be overwritten.
         */
        listOfEmployeesMap.forEach((employeeId, employee) -> {
            workforceManagementPage.createEmployee(employee);
        });

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
