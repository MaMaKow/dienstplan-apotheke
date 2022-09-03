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
import java.util.logging.Level;
import java.util.logging.Logger;
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
public class TestSaturdayListPage {

    WebDriver driver;

    @Test(enabled = true)
    public void testSaturdayListPage() {
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
        SaturdayListPage saturdayListPage = new SaturdayListPage(driver);
        Assert.assertEquals(saturdayListPage.getUserNameText(), pdr_user_name);
        saturdayListPage.selectYear("2021");
        saturdayListPage.selectBranch(1);
        Assert.assertEquals(saturdayListPage.getBranchId(), 1);
        /**
         * <p lang=de>
         * Der 03. October 2026 ist ein Feiertag. Weil an diesem Feiertag kein
         * Samstagsteam arbeitet, muss nach dem Feiertag das Team arbeiten, dass
         * sonst am 03.10. dran gewesen wäre.
         * </p>
         */
        Calendar saturdayCalendar = Calendar.getInstance(Locale.GERMANY);
        saturdayCalendar.set(2026, Calendar.SEPTEMBER, 26);
        Assert.assertEquals(saturdayListPage.getTeamIdOnDate(saturdayCalendar.getTime()), 2);
        saturdayCalendar.set(2026, Calendar.OCTOBER, 3);
        Assert.assertEquals(saturdayListPage.teamIdOnDateIsMissing(saturdayCalendar.getTime()), true);
        saturdayCalendar.set(2026, Calendar.OCTOBER, 10);
        Assert.assertEquals(saturdayListPage.getTeamIdOnDate(saturdayCalendar.getTime()), 3);
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
