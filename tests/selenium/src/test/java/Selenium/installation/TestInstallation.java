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

import Selenium.HomePage;
import Selenium.ReadPropertyFile;
import Selenium.ScreenShot;
import Selenium.administrationpages.BranchAdministrationPage;
import Selenium.driver.Wrapper;
import Selenium.SignInPage.SignInPage;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import static org.testng.Assert.assertEquals;
import org.testng.annotations.Test;
import java.util.HashMap;
import org.testng.ITestResult;
import org.testng.annotations.AfterMethod;

/**
 *
 * @author Mandelkow
 */
public class TestInstallation {

    WebDriver driver;
    ReadPropertyFile readPropertyFile;
    /**
     * <p lang=de>
     * Diese URL wird aus der apache Seite für Ordner ohne index.html
     * ausgelesen. z.B.
     * https://your-host.com/development/testing/dienstplan-test-0_14_0_899_gba26b727b1e29aede593fa5066b003982bc19c16/
     * </p>
     */
    String testPageUrl;

    @Test(enabled = false)/*passed*/
    public void testInstallation() {
        driver = Wrapper.getDriver();
        readPropertyFile = new ReadPropertyFile();
        String testPageFolderPath = readPropertyFile.getUrlInstallTest();
        /**
         * Visit the page script selenium-copy.php. This script will copy a
         * fresh pdr instance into testPageFolderPath. The state will be exactly
         * like in the nextcloud.
         */
        driver.get(testPageFolderPath + "selenium-copy.php");
        String seleniumCopyDoneXPath = "//*[@id=\"span_done\"]";
        By seleniumCopyDoneBy = By.xpath(seleniumCopyDoneXPath);
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.presenceOfElementLocated(seleniumCopyDoneBy));

        driver.get(testPageFolderPath);
        String testPageUrlXPath = "/html/body/table/tbody/tr[5]/td[2]/a";
        By testPageUrlBy = By.xpath(testPageUrlXPath);
        WebElement testPageLink = driver.findElement(testPageUrlBy);
        testPageUrl = testPageLink.getAttribute("href");
        testPageLink.click();
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

        /**
         * <p lang=de>
         * Die Anwendung ist installiert. Jetzt ist es Zeit, sie zu
         * konfigurieren:
         * </p>
         */
        SignInPage signInPage = new SignInPage(driver);
        String pdr_user_password = readPropertyFile.getPdrUserPassword();
        String pdr_user_name = readPropertyFile.getPdrUserName();
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
        HashMap<Integer, String[]> openingTimes = new HashMap<>();
        String[] openingTimeWeekdays = {"08:00", "18:00"};
        String[] openingTimeSaturday = {"10:00", "16:00"};
        openingTimes.put(1, openingTimeWeekdays);
        openingTimes.put(2, openingTimeWeekdays);
        openingTimes.put(3, openingTimeWeekdays);
        openingTimes.put(4, openingTimeWeekdays);
        openingTimes.put(5, openingTimeWeekdays);
        openingTimes.put(6, openingTimeSaturday);
        branchAdministrationPage.createNewBranch(5, 5, "Neue Filiale", "Filiale", "Nebenstraße 5\n12345 Berlin", pdr_user_name, openingTimes);
    }

    @AfterMethod
    public void tearDown(ITestResult testResult) {
        driver = Selenium.driver.Wrapper.getDriver();
        new ScreenShot(testResult);
        driver.quit();

    }
}
