/*
 * Copyright (C) 2022 Mandelkow
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
package Selenium;

import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;
import org.testng.ITestResult;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeSuite;
import org.testng.asserts.SoftAssert;
import Selenium.signin.SignInPage;
import org.testng.annotations.Test;

/**
 *
 * @author Mandelkow
 */
public class TestPage {

    protected WebDriver driver;
    protected SoftAssert softAssert = new SoftAssert();
    protected PropertyFile propertyFile;

    @Test
    public void signIn() {
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
        HomePage homePage = signInPage.loginValidUser(pdr_user_name, pdr_user_password);
        Assert.assertEquals(homePage.getUserNameText(), pdr_user_name);

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
        driver.quit();
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
