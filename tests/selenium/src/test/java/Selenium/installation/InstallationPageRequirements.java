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

import java.time.Duration;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

/**
 *
 * @author Mandelkow
 */
public class InstallationPageRequirements {

    WebDriver driver;
    By InstallPageRequirementsFormButtonBy = By.id("InstallPageCheckRequirementsFormButton");
    WebElement InstallPageRequirementsFormButtonElement;

    public InstallationPageRequirements() {
        driver = Selenium.driver.Wrapper.getDriver();
        WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(3));
        wait.until(ExpectedConditions.presenceOfElementLocated(InstallPageRequirementsFormButtonBy));
    }

    public InstallationPageDatabase moveToDatabasePage() {
        driver = Selenium.driver.Wrapper.getDriver();
        WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(3));
        wait.until(ExpectedConditions.presenceOfElementLocated(InstallPageRequirementsFormButtonBy));
        InstallPageRequirementsFormButtonElement = driver.findElement(InstallPageRequirementsFormButtonBy);
        wait.until(ExpectedConditions.elementToBeClickable(InstallPageRequirementsFormButtonElement));
        try {
            Thread.sleep(500);
        } catch (InterruptedException ex) {
            Logger.getLogger(InstallationPageRequirements.class.getName()).log(Level.SEVERE, null, ex);
        }
        InstallPageRequirementsFormButtonElement.click();
        return new InstallationPageDatabase();
    }
}
