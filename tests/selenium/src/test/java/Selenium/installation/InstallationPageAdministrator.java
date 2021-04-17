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

import Selenium.ReadPropertyFile;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

/**
 *
 * @author Mandelkow
 */
public class InstallationPageAdministrator {

    By InstallationPageAdminFormButtonBy;

    WebDriver driver;
    WebElement InstallationPageAdminFormButtonElement;

    public void fillForm() {
        driver = Selenium.driver.Wrapper.getDriver();
        WebElement administratorUserNameFormElement = driver.findElement(By.name("user_name"));
        WebElement administratorLastNameFormElement = driver.findElement(By.name("last_name"));
        WebElement administratorEmployeeIdFormElement = driver.findElement(By.name("employee_id"));
        WebElement administratorEmailFormElement = driver.findElement(By.name("email"));
        WebElement administratorPasswordFormElement = driver.findElement(By.name("password"));
        WebElement administratorPassword2FormElement = driver.findElement(By.name("password2"));

        ReadPropertyFile readPropertyFile = new ReadPropertyFile();
        String administratorUserName = readPropertyFile.getAdministratorUserName();
        String administratorLastName = readPropertyFile.getAdministratorLastName();
        String administratorEmployeeId = readPropertyFile.getAdministratorEmployeeId();
        String administratorEmail = readPropertyFile.getAdministratorEmail();
        String administratorPassword = readPropertyFile.getAdministratorPassword();

        administratorUserNameFormElement.clear();
        administratorUserNameFormElement.sendKeys(administratorUserName);
        administratorLastNameFormElement.clear();
        administratorLastNameFormElement.sendKeys(administratorLastName);
        administratorEmployeeIdFormElement.clear();
        administratorEmployeeIdFormElement.sendKeys(administratorEmployeeId);
        administratorEmailFormElement.clear();
        administratorEmailFormElement.sendKeys(administratorEmail);
        administratorPasswordFormElement.clear();
        administratorPasswordFormElement.sendKeys(administratorPassword);
        administratorPassword2FormElement.clear();
        administratorPassword2FormElement.sendKeys(administratorPassword);
    }

    public void moveFromAdminPage() {
        driver = Selenium.driver.Wrapper.getDriver();
        InstallationPageAdminFormButtonBy = By.id("InstallPageDatabaseFormButton");
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.presenceOfElementLocated(InstallationPageAdminFormButtonBy));
        InstallationPageAdminFormButtonElement = driver.findElement(InstallationPageAdminFormButtonBy);
        InstallationPageAdminFormButtonElement.click();
    }
}
