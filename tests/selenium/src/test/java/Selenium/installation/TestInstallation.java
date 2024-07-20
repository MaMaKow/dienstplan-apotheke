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
package Selenium.installation;

import Selenium.Branch;
import Selenium.HomePage;
import Selenium.NetworkOfBranchOffices;
import Selenium.PropertyFile;
import Selenium.ScreenShot;
import Selenium.administrationpages.BranchAdministrationPage;
import Selenium.driver.Wrapper;
import Selenium.signin.SignInPage;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import static org.testng.Assert.assertEquals;
import org.testng.annotations.Test;
import java.util.Map;
import org.testng.Assert;
import org.testng.ITestResult;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;

/**
 *
 * @author Mandelkow
 */
public class TestInstallation extends Selenium.TestPage {

    private WebDriver driver;
    private PropertyFile propertyFile;
    /**
     * <p lang=de>
     * Diese URL wird aus der apache Seite für Ordner ohne index.html
     * ausgelesen. z.B.
     * https://your-host.com/development/testing/dienstplan-test-0_14_0_899_gba26b727b1e29aede593fa5066b003982bc19c16/
     * </p>
     */
    private String testPageUrl;
    public String packageName;
    public String className;
    public String methodName;

    @Test(enabled = true)/*passed*/
    public void testInstallation() throws Exception {
        driver = Wrapper.getDriver();
        propertyFile = new PropertyFile();
        String testPageFolderPath = propertyFile.getUrlInstallTest();
        /**
         * Visit the page script selenium-copy.php. This script will copy a
         * fresh pdr instance into testPageFolderPath. The state will be exactly
         * like in the nextcloud.
         */
        driver.get(testPageFolderPath + "selenium-copy.php");
        By seleniumCopyDoneBy = By.xpath("//*[@id=\"span_done\"]");
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.presenceOfElementLocated(seleniumCopyDoneBy));

        driver.get(testPageFolderPath);
        String testPageUrlPath = "dienstplan-test/";
        testPageUrl = testPageFolderPath + testPageUrlPath;
        propertyFile.setTestPageUrl(testPageUrl);
        driver.get(testPageUrl);
        /**
         * Start the actual installation process:
         */
        try {
            InstallationPageIntro installationPageIntro = new InstallationPageIntro();
            InstallationPageWelcome installationPageWelcome = installationPageIntro.moveToWelcomePage();
            InstallationPageRequirements installationPageRequirements = installationPageWelcome.moveToRequirementsPage();
            InstallationPageDatabase installationPageDatabase = installationPageRequirements.moveToDatabasePage();
            installationPageDatabase.fillForm();
            InstallationPageAdministrator installationPageAdministrator = installationPageDatabase.moveToAdminPage();
            installationPageAdministrator.fillForm();
            installationPageAdministrator.moveFromAdminPage();
        } catch (Exception exception) {
            System.out.println("driver.getCurrentUrl()");
            System.out.println(driver.getCurrentUrl());
            System.out.println("driver.getPageSource()");
            System.out.println(driver.getPageSource());
            Assert.fail();
            throw exception;
        }

        /**
         * <p lang=de>
         * Die Anwendung ist installiert. Jetzt ist es Zeit, sie zu
         * konfigurieren:
         * </p>
         */
        SignInPage signInPage = new SignInPage(driver);
        String pdr_user_password = propertyFile.getPdrUserPassword();
        String pdr_user_name = propertyFile.getPdrUserName();
        HomePage homePage = signInPage.loginValidUser(pdr_user_name, pdr_user_password);
        assertEquals(pdr_user_name, homePage.getUserNameText());
        /**
         * <p lang=de>
         * Jetzt ist es Zeit, die Filialen zu konfigurieren. Es braucht
         * mindestens eine Hauptapotheke.
         * </p>
         */
        driver.get(this.testPageUrl + "src/php/pages/branch-management.php");
        BranchAdministrationPage branchAdministrationPage = new BranchAdministrationPage();

        NetworkOfBranchOffices networkOfBranchOffices = new NetworkOfBranchOffices();
        Map<Integer, Branch> listOfBranches = networkOfBranchOffices.getListOfBranches();
        final BranchAdministrationPage branchAdministrationPageForLambda = branchAdministrationPage;
        listOfBranches.forEach((branchId, branchObject) -> {
            branchAdministrationPageForLambda.createNewBranch(branchObject);
        });

    }

    @BeforeMethod
    public void setUpMethod(ITestResult result) {
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
        if (testResult.getStatus() != ITestResult.FAILURE) {
            driver.quit();
        }
    }
}
