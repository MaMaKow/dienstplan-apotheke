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
import Selenium.Utilities.LoggingTest;
import Selenium.administrationpages.BranchAdministrationPage;
import Selenium.driver.Wrapper;
import Selenium.signin.SignInPage;
import java.time.Duration;
import java.util.Map;
import org.openqa.selenium.By;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;
import static org.testng.Assert.assertEquals;
import org.testng.annotations.Test;

/**
 *
 * @author Mandelkow
 */
public class TestInstallation extends Selenium.TestPage {

    private String testPageUrl;

    @Test(dependsOnMethods = {"testInstallation"})
    @Override
    public void signIn() {
        try {
            super.signIn();
        } catch (Exception exception) {
            logger.error("Sign in failed.");
            Assert.fail();
        }
    }

    @Test(enabled = true)/*passed*/
    public void testInstallation() throws Exception {
        LoggingTest loggingTest = new LoggingTest();
        driver = Wrapper.getDriver();
        propertyFile = new PropertyFile();
        String testPageFolderPath = propertyFile.getUrlInstallTest();
        /**
         * Visit the page script selenium-copy.php. This script will copy a
         * fresh pdr instance into testPageFolderPath. The state will be exactly
         * like in the nextcloud.
         */
        try {
            driver.get(testPageFolderPath + "selenium-copy.php");
            By seleniumCopyDoneBy = By.xpath("//*[@id=\"span_done\"]");
            WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(3));
            wait.until(ExpectedConditions.presenceOfElementLocated(seleniumCopyDoneBy));

            String testPageUrlPath = "dienstplan-test/";
            testPageUrl = testPageFolderPath + testPageUrlPath;
            propertyFile.setTestPageUrl(testPageUrl);
            driver.get(testPageUrl);
            /**
             * Start the actual installation process:
             */
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
        try {
            HomePage homePage = signInPage.loginValidUser(pdr_user_name, pdr_user_password);
            Assert.assertEquals(pdr_user_name, homePage.getUserNameText());
        } catch (Exception exception) {
            logger.error("Sign in failed due to an exception: " + exception.getMessage(), exception);
            Assert.fail("Sign in failed. Exception: " + exception.getMessage());
        }

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
}
