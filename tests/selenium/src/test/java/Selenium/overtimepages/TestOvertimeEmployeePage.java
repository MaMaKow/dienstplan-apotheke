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
package Selenium.overtimepages;

import Selenium.ReadPropertyFile;
import Selenium.ScreenShot;
import Selenium.Overtime;
import Selenium.signinpage.SignInPage;
import java.util.Calendar;
import java.util.Locale;
import org.openqa.selenium.WebDriver;
import org.testng.Assert;
import org.testng.ITestResult;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;

/**
 *
 * @author Mandelkow
 */
/**
 *
 * @author Martin Mandelkow <netbeans@martin-mandelkow.de>
 */
public class TestOvertimeEmployeePage {

    WebDriver driver;

    @Test(enabled = true)/*new*/
    public void testDisplay() {
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
        OvertimeEmployeePage overtimeEmployeePage = new OvertimeEmployeePage(driver);
        Assert.assertEquals(overtimeEmployeePage.getUserNameText(), pdr_user_name);

        /**
         * Move to specific month:
         */
        Calendar calendar = Calendar.getInstance(Locale.GERMANY);
        calendar.set(2019, Calendar.DECEMBER, 24);
        overtimeEmployeePage.selectYear(calendar.get(Calendar.YEAR));
        overtimeEmployeePage.selectEmployee(5);
        /**
         * Create new overtime:
         */
        overtimeEmployeePage.addNewOvertime(calendar, 8, "Foo");
        /**
         * Find the newly created overtime:
         */
        Overtime overtime = overtimeEmployeePage.getOvertimeByCalendar(calendar);
        Assert.assertEquals(overtime.getBalance(), (float) 70);
        Assert.assertEquals(overtime.getHours(), (float) 8);
        Assert.assertEquals(overtime.getReason(), "Foo");
        /**
         * remove the created overtime:
         */
        overtimeEmployeePage.removeOvertimeByCalendar(calendar);
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
