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
import org.openqa.selenium.support.ui.Select;
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
        By applicationNameBy = By.xpath("/html/body/div[3]/form/div/fieldset[1]/input[@name=\"application_name\"]");
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

    public String getDatabasePassword() {
        /**
         * The password MUST NOT be visible!
         */
        By databaseUserBy = By.xpath("/html/body/div[3]/form/div/fieldset[1]/input[@name='database_password']");
        WebElement databaseUserElement = driver.findElement(databaseUserBy);
        return databaseUserElement.getAttribute("value");
    }

    public String getContactEmail() {
        By elementBy = By.xpath("/html/body/div[3]/form/div/fieldset[2]/input[@name='contact_email']");
        WebElement webElement = driver.findElement(elementBy);
        return webElement.getAttribute("value");
    }

    public String getLanguage() {
        By elementBy = By.xpath("/html/body/div[3]/form/div/fieldset[3]/select[@name='language']");
        WebElement webElement = driver.findElement(elementBy);
        Select selectElement = new Select(webElement);
        return selectElement.getFirstSelectedOption().getText();

    }

    public void setLocales(String localeString) {
        By elementBy = By.xpath("/html/body/div[3]/form/div/fieldset[3]/input[@name='LC_TIME']");
        WebElement webElement = driver.findElement(elementBy);
        webElement.clear();
        webElement.sendKeys(localeString);
    }

    public void setApplicationName(String applicationName) {
        By elementBy = By.xpath("/html/body/div/form/div/fieldset/input[@name=\"application_name\"]");
        WebElement webElement = driver.findElement(elementBy);
        webElement.clear();
        webElement.sendKeys(applicationName);
    }

    public String getLocales() {
        By elementBy = By.xpath("/html/body/div/form/div/fieldset/input[@name='LC_TIME']");
        WebElement webElement = driver.findElement(elementBy);
        return webElement.getAttribute("value");
    }

    public String getEncoding() {
        By elementBy = By.xpath("/html/body/div[3]/form/div/fieldset[3]/input[@name='mb_internal_encoding']");
        WebElement webElement = driver.findElement(elementBy);
        return webElement.getAttribute("value");
    }

    public String getErrorLogPath() {
        By elementBy = By.xpath("/html/body/div[3]/form/div/fieldset[4]/input[@name='error_log']");
        WebElement webElement = driver.findElement(elementBy);
        return webElement.getAttribute("value");
    }

    public Integer getErrorReporting() {
        Integer errorReportingValue = null;
        /**
         * By xpaths:
         */
        By errorReportingErrorBy = By.xpath("//*[@id=\"errorReportingError\"]");
        By errorReportingWarningBy = By.xpath("//*[@id=\"errorReportingWarning\"]");
        By errorReportingNoticeBy = By.xpath("//*[@id=\"error_reporting_notice\"]");
        By errorReportingAllBy = By.xpath("//*[@id=\"error_reporting_all\"]");
        /**
         * WebElements:
         */
        WebElement errorReportingErrorElement = driver.findElement(errorReportingErrorBy);
        WebElement errorReportingWarningElement = driver.findElement(errorReportingWarningBy);
        WebElement errorReportingNoticeElement = driver.findElement(errorReportingNoticeBy);
        WebElement errorReportingAllElement = driver.findElement(errorReportingAllBy);
        /**
         * Only one radio can be selected:
         */
        if (errorReportingErrorElement.isSelected()) {
            errorReportingValue = Integer.valueOf(errorReportingErrorElement.getAttribute("value")); //4437
            return errorReportingValue;
        }
        if (errorReportingWarningElement.isSelected()) {
            errorReportingValue = Integer.valueOf(errorReportingWarningElement.getAttribute("value")); //5111
            return errorReportingValue;
        }
        if (errorReportingNoticeElement.isSelected()) {
            errorReportingValue = Integer.valueOf(errorReportingNoticeElement.getAttribute("value")); //30719
            return errorReportingValue;
        }
        if (errorReportingAllElement.isSelected()) {
            errorReportingValue = Integer.valueOf(errorReportingAllElement.getAttribute("value")); //32767
            return errorReportingValue;
        }
        /**
         * found none:
         */
        return errorReportingValue;
    }

    public boolean getHideDisapproved() {
        Boolean hideDisapprovedValue = null;
        /**
         * By xpaths:
         */
        By showDisapprovedBy = By.xpath("//*[@id=\"configurationInputDiv\"]/fieldset[5]/input[@value=\"0\"]");
        By hideDisapprovedBy = By.xpath("//*[@id=\"configurationInputDiv\"]/fieldset[5]/input[@value=\"1\"]");
        /**
         * WebElements:
         */
        WebElement showDisapprovedElement = driver.findElement(showDisapprovedBy);
        WebElement hideDisapprovedElement = driver.findElement(hideDisapprovedBy);
        /**
         * Only one radio can be selected:
         */
        if (showDisapprovedElement.isSelected()) {
            hideDisapprovedValue = false;
            return hideDisapprovedValue;
        }
        if (hideDisapprovedElement.isSelected()) {
            hideDisapprovedValue = true;
            return hideDisapprovedValue;
        }
        /**
         * found none:
         */
        return hideDisapprovedValue;
    }

    public String getEmailMethod() {
        String hideDisapprovedValue = null;
        /**
         * By selector: The selected element is :checked
         */
        //By showDisapprovedBy = By.cssSelector("#configurationInputDiv > fieldset:nth-child(7) > input[type=radio]:nth-child(3)");
        By emailMethodBy = By.cssSelector("#configurationInputDiv > fieldset:nth-child(7) > input[type=radio]:checked");
        /**
         * WebElements:
         */
        WebElement emailMethodElement = driver.findElement(emailMethodBy);
        /**
         * Only one radio can be selected:
         */
        if (emailMethodElement.isSelected()) {
            hideDisapprovedValue = emailMethodElement.getAttribute("value");
            return hideDisapprovedValue;
        }
        /**
         * found none:
         */
        return hideDisapprovedValue;
    }

    public void submitForm() {
        By submitButtonBy = By.xpath("//*[@id=\"configurationInputDiv\"]/input[@type=\"submit\"]");
        WebElement submitButtonElement = driver.findElement(submitButtonBy);
        submitButtonElement.click();
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
