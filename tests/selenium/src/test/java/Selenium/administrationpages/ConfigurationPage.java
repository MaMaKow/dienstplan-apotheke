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
package Selenium.administrationpages;

import Selenium.MenuFragment;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

/**
 *
 * @author Mandelkow
 */
public class ConfigurationPage {

    protected static WebDriver driver;
    By user_name_spanBy = By.id("MenuListItemApplicationUsername");

    public ConfigurationPage(WebDriver driver) {
        this.driver = driver;

        if (getUserNameText().isEmpty()) {
            throw new IllegalStateException("This is not a logged in state,"
                    + " current page is: " + driver.getCurrentUrl());
        }
        MenuFragment.navigateTo(driver, MenuFragment.MenuLinkToConfiguration);

    }

    public String getApplicationName() {
        By applicationNameBy = By.xpath("/html/body/div[3]/form/div/fieldset[1]/input[@name='application_name']");
        WebElement applicationNameElement = driver.findElement(applicationNameBy);
        return applicationNameElement.getAttribute("value");
    }

    public String getDatabaseName() {
        By databaseNameBy = By.xpath("/html/body/div[3]/form/div/fieldset[1]/input[@name='database_name']");
        WebElement databaseNameElement = driver.findElement(databaseNameBy);
        return databaseNameElement.getAttribute("value");
    }

    public String getDatabaseUser() {
        By databaseUserBy = By.xpath("/html/body/div[3]/form/div/fieldset[1]/input[@name='database_user']");
        WebElement databaseUserElement = driver.findElement(databaseUserBy);
        return databaseUserElement.getAttribute("value");
    }

    public String getContactEmail() {
        By databasePasswordBy = By.xpath("/html/body/div[3]/form/div/fieldset[2]/input[@name='contact_email']");
        WebElement databasePasswordElement = driver.findElement(databasePasswordBy);
        return databasePasswordElement.getAttribute("value");
    }

    /**
     * Get user_name (span tag)
     *
     * @return String user_name text
     */
    public String getUserNameText() {
        // <h1>Hello userName</h1>
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.presenceOfElementLocated(user_name_spanBy));

        return driver.findElement(user_name_spanBy).getText();
    }

}
