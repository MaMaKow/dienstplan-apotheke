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

import Selenium.driver.Wrapper;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.testng.annotations.Test;

/**
 *
 * @author Mandelkow
 */
public class TestInstallation {

    WebDriver driver;

    @Test(enabled = true)
    public void testInstallation() {
        String testPageFolderPath = "https://martin-mandelkow.de/development/testing/";
        String testPageUrlXPath = "/html/body/table/tbody/tr[5]/td[2]/a";
        By testPageUrlBy = By.xpath(testPageUrlXPath);
        driver = Wrapper.getDriver();
        driver.get(testPageFolderPath);
        WebElement testPageLink = driver.findElement(testPageUrlBy);
        testPageLink.click();
        InstallationPageIntro installationPageIntro = new InstallationPageIntro();
        InstallationPageWelcome installationPageWelcome = installationPageIntro.moveToWelcomePage();
        InstallationPageRequirements installationPageRequirements = installationPageWelcome.moveToRequirementsPage();
        InstallationPageDatabase installationPageDatabase = installationPageRequirements.moveToDatabasePage();
        installationPageDatabase.fillForm();
        InstallationPageAdministrator installationPageAdministrator = installationPageDatabase.moveToAdminPage();
        installationPageAdministrator.fillForm();
        installationPageAdministrator.moveFromAdminPage();
    }
}
