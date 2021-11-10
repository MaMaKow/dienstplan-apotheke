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
import Selenium.signin.SignInPage;
import java.util.Date;
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
        workforceManagementPage.selectEmployee(5);

        Employee employeeObject = workforceManagementPage.getEmployeeObject();
        Assert.assertEquals(employeeObject.getEmployeeId(), 5);
        Assert.assertEquals(employeeObject.getLastName(), "Mandelkow");
        Assert.assertEquals(employeeObject.getFirstName(), "Martin");
        Assert.assertEquals(employeeObject.getProfession(), "Apotheker");
        Assert.assertEquals(employeeObject.getWorkingHours(), (float) 40);
        Assert.assertEquals(employeeObject.getLunchBreakMinutes(), 30);
        Assert.assertEquals(employeeObject.getHolidays(), 28);
        Assert.assertEquals(employeeObject.getBranchString(), String.valueOf(1));//TODO: probably wrong
        Assert.assertEquals(employeeObject.getAbilitiesGoodsReceipt(), true);
        Assert.assertEquals(employeeObject.getAbilitiesCompounding(), false);
        Assert.assertEquals(employeeObject.getStartOfEmployment(), new Date(2015, 1, 1));
        Assert.assertEquals(employeeObject.getEndOfEmployment(), null);
    }

    @Test(enabled = true)/*new*/
    public void testCreateEmployee() {
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

        Employee employeeObject0 = new Employee("0", "Arnold", "Valentina", "Zugehfrau", "40", "30", "28", "Hauptapotheke", "false", "false", "01.01.2001", "30.06.2021");
        Employee employeeObject1 = new Employee("1", "Becker", "Alexandra", "PI", "40", "30", "28", "Hauptapotheke", "false", "false", "01.01.2002", "");
        Employee employeeObject2 = new Employee("2", "Bauer", "Anabell", "Apotheker", "40", "30", "28", "Hauptapotheke", "false", "false", "01.01.2003", "");
        Employee employeeObject3 = new Employee("3", "Busch", "Elisabeth", "Apotheker", "40", "30", "28", "Hauptapotheke", "true", "true", "01.01.2004", "");
        Employee employeeObject4 = new Employee("4", "Baumann", "Albert", "Apotheker", "40", "30", "28", "Filiale", "false", "false", "01.01.2005", "");
        Employee employeeObject5 = new Employee("5", "Kremer", "Albert", "PTA", "40", "30", "28", "Hauptapotheke", "false", "true", "01.01.2006", "");
        Employee employeeObject6 = new Employee("6", "Clemens", "Albert", "PTA", "40", "30", "28", "Hauptapotheke", "false", "true", "01.01.2007", "");
        Employee employeeObject7 = new Employee("7", "Christ", "Albert", "PTA", "40", "30", "28", "Hauptapotheke", "true", "false", "01.01.2008", "");
        Employee employeeObject8 = new Employee("8", "Conrad", "Franzuska", "PTA", "40", "30", "28", "Hauptapotheke", "true", "true", "01.01.2009", "");
        Employee employeeObject9 = new Employee("9", "Müller", "Luisa", "PTA", "40", "30", "28", "Hauptapotheke", "true", "true", "01.01.2010", "");
        Employee employeeObject10 = new Employee("10", "Daniel", "Emma", "PI", "40", "30", "28", "Filiale", "false", "true", "01.01.2011", "");
        Employee employeeObject11 = new Employee("11", "Dahmen", "Marie", "PTA", "40", "30", "28", "Filiale", "false", "true", "01.01.2012", "");
        Employee employeeObject12 = new Employee("12", "Dietrich", "Lea", "PTA", "40", "30", "28", "Filiale", "false", "true", "01.01.2013", "");
        Employee employeeObject13 = new Employee("13", "Dambach", "Jule", "PKA", "40", "30", "28", "Hauptapotheke", "true", "false", "01.01.2014", "");
        Employee employeeObject14 = new Employee("14", "Decker", "Hannah", "PKA", "40", "30", "28", "Hauptapotheke", "true", "false", "01.01.2015", "31.12.2050");
        /**
         * TODO: CAVE! Old employees seem to be overwritten.
         */
        workforceManagementPage.createEmployee(employeeObject0);
        workforceManagementPage.createEmployee(employeeObject1);
        workforceManagementPage.createEmployee(employeeObject2);
        workforceManagementPage.createEmployee(employeeObject3);
        workforceManagementPage.createEmployee(employeeObject4);
        workforceManagementPage.createEmployee(employeeObject5);
        workforceManagementPage.createEmployee(employeeObject6);
        workforceManagementPage.createEmployee(employeeObject7);
        workforceManagementPage.createEmployee(employeeObject8);
        workforceManagementPage.createEmployee(employeeObject9);
        workforceManagementPage.createEmployee(employeeObject10);
        workforceManagementPage.createEmployee(employeeObject11);
        workforceManagementPage.createEmployee(employeeObject12);
        workforceManagementPage.createEmployee(employeeObject13);
        workforceManagementPage.createEmployee(employeeObject14);

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
