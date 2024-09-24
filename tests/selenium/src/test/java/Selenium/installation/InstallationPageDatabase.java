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

import Selenium.PropertyFile;
import java.util.logging.Level;
import java.util.logging.Logger;
import java.time.Duration;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

/**
 *
 * @author Mandelkow
 */
public class InstallationPageDatabase {

    By InstallationPageDatabaseFormButtonBy = By.id("InstallPageDatabaseFormButton");
    WebDriver driver;

    WebElement InstallationPageDatabaseFormButtonElement;

    public InstallationPageDatabase() {
        this.driver = Selenium.driver.Wrapper.getDriver();
        InstallationPageDatabaseFormButtonElement = driver.findElement(InstallationPageDatabaseFormButtonBy);
        if (!InstallationPageDatabaseFormButtonElement.isDisplayed()) {
            throw new IllegalStateException("This is not InstallPageDatabase,"
                    + " current page is: " + driver.getCurrentUrl());

        }
    }

    public void fillForm() throws Exception {
        WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(3));
        wait.until(ExpectedConditions.presenceOfElementLocated(By.id("database_host")));
        wait.until(ExpectedConditions.presenceOfElementLocated(InstallationPageDatabaseFormButtonBy));

        //WebElement databaseManagementSystemFormElement = driver.findElement(By.id("database_management_system"));
        WebElement databaseHostFormElement = driver.findElement(By.id("database_host"));
        WebElement databasePortFormElement = driver.findElement(By.id("database_port"));
        WebElement databaseUserFormElement = driver.findElement(By.id("database_user"));
        WebElement databasePasswordFormElement = driver.findElement(By.id("database_password"));
        WebElement databaseNameFormElement = driver.findElement(By.id("database_name"));

        PropertyFile propertyFile = new PropertyFile();
        String databaseUserName = propertyFile.getDatabaseUserName();
        String databasePassword = propertyFile.getDatabasePassword();
        String databaseHostname = propertyFile.getDatabaseHostname();
        String databasePort = propertyFile.getDatabasePort();
        String databaseName = propertyFile.getDatabaseName();

        databaseHostFormElement.clear();
        //databaseHostFormElement.sendKeys("localhost");
        Selenium.driver.Wrapper.CustomSendKeysIE(databaseHostFormElement, databaseHostname);
        databasePortFormElement.clear();
        databasePortFormElement.sendKeys(databasePort);
        databaseUserFormElement.clear();
        databaseUserFormElement.sendKeys(databaseUserName);
        databasePasswordFormElement.clear();
        databasePasswordFormElement.sendKeys(databasePassword);
        databaseNameFormElement.clear();
        databaseNameFormElement.sendKeys(databaseName);
    }

    public InstallationPageAdministrator moveToAdminPage() {
        WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(3));
        wait.until(ExpectedConditions.presenceOfElementLocated(InstallationPageDatabaseFormButtonBy));
        InstallationPageDatabaseFormButtonElement = driver.findElement(InstallationPageDatabaseFormButtonBy);
        try {
            Thread.sleep(500);
        } catch (InterruptedException ex) {
            Logger.getLogger(InstallationPageDatabase.class.getName()).log(Level.SEVERE, null, ex);
        }
        InstallationPageDatabaseFormButtonElement.click();
        return new InstallationPageAdministrator();
    }
}
