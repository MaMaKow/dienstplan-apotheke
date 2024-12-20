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

import Selenium.rosterpages.Workforce;
import Selenium.signin.SignInPage;
import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.time.Duration;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.ITestResult;
import org.testng.Assert;
import org.testng.ITestContext;
import org.testng.Reporter;
import org.testng.SkipException;
import org.testng.asserts.SoftAssert;
import org.testng.annotations.BeforeSuite;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.AfterSuite;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;

/**
 *
 * @author Mandelkow
 */
public class TestPage {

    protected WebDriver driver;
    protected SoftAssert softAssert = new SoftAssert();
    protected PropertyFile propertyFile;
    public static Boolean someTestHasFailed = false;
    public String packageName;
    public String className;
    public String methodName;
    public static Workforce workforce;
    public final Logger logger;

    public TestPage() {
        this.logger = LogManager.getLogger(this.getClass());
    }

    @Test
    public void signIn() throws Exception {

        driver = Selenium.driver.Wrapper.getDriver();
        propertyFile = new PropertyFile();
        String urlPageTest = propertyFile.getTestPageUrl();
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

    public void realSignIn() throws Exception {
        driver = Selenium.driver.Wrapper.getDriver();
        propertyFile = new PropertyFile();
        String urlPageTest = propertyFile.getRealTestPageUrl();
        driver.get(urlPageTest);

        /**
         * Sign in:
         */
        SignInPage signInPage = new SignInPage(driver);
        String real_user_password = propertyFile.getRealPassword();
        String real_user_name = propertyFile.getRealUsername();
        HomePage homePage = signInPage.loginValidUser(real_user_name, real_user_password);
        Assert.assertEquals(homePage.getUserNameText(), real_user_name);

    }

    @BeforeSuite
    public void setUpSuite() {
        driver = Selenium.driver.Wrapper.getDriver();
        propertyFile = new PropertyFile();
        workforce = new Workforce();
        // Get the context manually
        ITestContext context = Reporter.getCurrentTestResult().getTestContext();
        if (isRealWorldTest(context)) {
            return;
        }
        /**
         * Refresh the page contents from the nextcloud data:
         */
        try {
            String testPageFolderPath = propertyFile.getUrlInstallTest();
            driver.get(testPageFolderPath + "selenium-refresh.php");
            By seleniumCopyDoneBy = By.xpath("//*[@id=\"span_done\"]");
            WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(3));
            wait.until(ExpectedConditions.presenceOfElementLocated(seleniumCopyDoneBy));
        } catch (Exception exception) {
            logger.error("driver.getCurrentUrl()");
            logger.error(driver.getCurrentUrl());
            logger.error("driver.getPageSource()");
            logger.error(driver.getPageSource());
            throw exception;
        }
        //driver.quit();
    }

    @BeforeMethod
    public void setUpMethod(ITestResult result) {
        if (true == someTestHasFailed) {
            throw new SkipException("Some Test has failed. Skipping all the other methods.");
        }
        // Print the name of the class and the currently executing test method to the log file
        packageName = this.getClass().getPackageName();
        className = this.getClass().getSimpleName();
        methodName = result.getMethod().getMethodName();
        System.err.println("Package: " + packageName + ", Class: " + className + ", Method: " + methodName);
    }

    @AfterMethod
    public void tearDown(ITestResult testResult) {
        driver = Selenium.driver.Wrapper.getDriver();
        ScreenShot screenshot = new ScreenShot();
        screenshot.takeScreenShot(packageName, className, methodName);
        if (testResult.getStatus() == ITestResult.SUCCESS) {
            //driver.quit();
        } else {
            /**
             * Mark this whole test suite as failed:
             */
            someTestHasFailed = true;
        }
    }

    @AfterSuite
    public void tearDownSuite() {
        if (!someTestHasFailed) {
            driver.quit();
            // Write "SUCCESS" to the test-result file
            writeTestResult("SUCCESS");
        } else {
            try {
                // Capture the page source
                String pageSource = driver.getPageSource();

                // Specify the file where the page source will be written
                File pageSourceFile = new File("failed_test_page.html");

                // Write the page source to the file
                try (FileWriter writer = new FileWriter(pageSourceFile)) {
                    writer.write(pageSource);
                }

            } catch (IOException exception) {
                exception.printStackTrace();
            }
            ScreenShot screenShot = new ScreenShot();
            screenShot.takeScreenShot(packageName, className, methodName);
            // Write "FAILED" to the test-result file
            writeTestResult("FAILED");
        }
    }

    private boolean isRealWorldTest(ITestContext context) {
        // Check if the suite name contains "testng_realworld.xml"
        logger.debug("Suite: " + context.getSuite().getName());
        return context.getSuite().getName().contains("testng_realworld.xml");
    }

    private void writeTestResult(String result) {
        File resultFile = new File("test-result");
        try (FileWriter writer = new FileWriter(resultFile)) {
            writer.write(result);
        } catch (IOException e) {
            e.printStackTrace();
        }
    }
}
