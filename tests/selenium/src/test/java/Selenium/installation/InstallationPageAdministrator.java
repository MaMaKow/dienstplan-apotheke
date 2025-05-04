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
public class InstallationPageAdministrator extends Selenium.BasePage {

    By InstallationPageAdminFormButtonBy;

    WebElement InstallationPageAdminFormButtonElement;

    public InstallationPageAdministrator(WebDriver driver) {
        super(driver);  // Call to BasePage constructor
        InstallationPageAdminFormButtonBy = By.id("InstallPageAdministratorFormButton");
        wait.until(ExpectedConditions.presenceOfElementLocated(InstallationPageAdminFormButtonBy));
    }

    public void fillForm() throws Exception {
        WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(3));
        wait.until(ExpectedConditions.presenceOfElementLocated(By.name("user_name")));
        wait.until(ExpectedConditions.presenceOfElementLocated(InstallationPageAdminFormButtonBy));

        WebElement administratorUserNameFormElement = driver.findElement(By.name("user_name"));
        WebElement administratorEmailFormElement = driver.findElement(By.name("email"));
        WebElement administratorPasswordFormElement = driver.findElement(By.name("password"));
        WebElement administratorPassword2FormElement = driver.findElement(By.name("password2"));

        PropertyFile propertyFile = new PropertyFile();
        String administratorUserName = propertyFile.getAdministratorUserName();
        String administratorEmail = propertyFile.getAdministratorEmail();
        String administratorPassword = propertyFile.getAdministratorPassword();

        administratorUserNameFormElement.clear();
        //administratorUserNameFormElement.sendKeys(administratorUserName);
        //Wrapper.CustomSendKeysIE(administratorUserNameFormElement, administratorUserName);
        Selenium.driver.Wrapper.CustomSendKeysIE(administratorUserNameFormElement, administratorUserName);

        administratorEmailFormElement.clear();
        administratorEmailFormElement.sendKeys(administratorEmail);
        administratorPasswordFormElement.clear();
        administratorPasswordFormElement.sendKeys(administratorPassword);
        administratorPassword2FormElement.clear();
        administratorPassword2FormElement.sendKeys(administratorPassword);
    }

    public void moveFromAdminPage() {
        wait.until(ExpectedConditions.presenceOfElementLocated(InstallationPageAdminFormButtonBy));
        InstallationPageAdminFormButtonElement = driver.findElement(InstallationPageAdminFormButtonBy);
        InstallationPageAdminFormButtonElement.click();
    }
}
