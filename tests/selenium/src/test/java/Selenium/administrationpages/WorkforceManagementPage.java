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
import java.util.HashMap;
import java.util.Map;
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
public class WorkforceManagementPage {

    protected static WebDriver driver;

    By user_name_spanBy = By.id("MenuListItemApplicationUsername");

    public WorkforceManagementPage(WebDriver driver) {

        this.driver = driver;

        if (getUserNameText().isEmpty()) {
            throw new IllegalStateException("This is not a logged in state,"
                    + " current page is: " + driver.getCurrentUrl());
        }
        MenuFragment.navigateTo(driver, MenuFragment.MenuLinkToManageEmployee);

    }

    public WorkforceManagementPage selectEmployee(int employeeId) {
        By selectEmployeeBy = By.xpath("/html/body/div[2]/form[@id=\"select_employee\"]/select[@name=\"employee_id\"]");
        WebElement selectEmployeeElement = driver.findElement(selectEmployeeBy);
        Select selectEmployeeSelect = new Select(selectEmployeeElement);
        selectEmployeeSelect.selectByValue(String.valueOf(employeeId));
        return new WorkforceManagementPage(driver);
    }

    public Map getEmployeeData() {
        Map employeeData = new HashMap();
        /**
         * employeeId:
         */
        By employeeIdInputBy = By.xpath("//*[@id=\"employee_id\"]");
        WebElement employeeIdInputElement = driver.findElement(employeeIdInputBy);
        employeeData.put("employeeId", employeeIdInputElement.getAttribute("value"));
        /**
         * last name:
         */
        By employeeLastNameInputBy = By.xpath("//*[@id=\"last_name\"]");
        WebElement employeeLastNameInputElement = driver.findElement(employeeLastNameInputBy);
        employeeData.put("employeeLastName", employeeLastNameInputElement.getAttribute("value"));
        /**
         * first name:
         */
        By employeeFirstNameInputBy = By.xpath("//*[@id=\"first_name\"]");
        WebElement employeeFirstNameInputElement = driver.findElement(employeeFirstNameInputBy);
        employeeData.put("employeeFirstName", employeeFirstNameInputElement.getAttribute("value"));
        /**
         * profession: One of the radio buttons is checked.
         */
        By employeeProfessionBy = By.xpath("/html/body/div[2]/form[2]/fieldset[2]/input[@name=\"profession\" and @checked]");
        WebElement employeeProfessionElement = driver.findElement(employeeProfessionBy);
        employeeData.put("employeeProfession", employeeProfessionElement.getAttribute("value"));
        /**
         * hours:
         */
        By employeeWeeklyWorkingHoursBy = By.xpath("//*[@id=\"working_hours\"]");
        WebElement employeeWeeklyWorkingHoursElement = driver.findElement(employeeWeeklyWorkingHoursBy);
        employeeData.put("employeeWorkingHours", employeeWeeklyWorkingHoursElement.getAttribute("value"));

        By employeeLunchBreakMinutesBy = By.xpath("//*[@id=\"lunch_break_minutes\"]");
        WebElement employeeLunchBreakMinutesElement = driver.findElement(employeeLunchBreakMinutesBy);
        employeeData.put("employeeLunchBreakMinutes", employeeLunchBreakMinutesElement.getAttribute("value"));

        By employeeHolidaysBy = By.xpath("//*[@id=\"holidays\"]");
        WebElement employeeHolidaysElement = driver.findElement(employeeHolidaysBy);
        employeeData.put("employeeHolidays", employeeHolidaysElement.getAttribute("value"));

        /**
         * main branch:
         */
        By employeeBranchBy = By.xpath("/html/body/div[2]/form[2]/fieldset[4]/input[@name=\"branch\" and @checked]");
        WebElement employeeBranchElement = driver.findElement(employeeBranchBy);
        employeeData.put("employeeBranch", employeeBranchElement.getAttribute("value"));

        /**
         * abilities:
         */
        By employeeAbilitiesGoodsReceiptBy = By.xpath("//*[@id=\"goods_receipt\"]");
        WebElement employeeAbilitiesGoodsReceiptElement = driver.findElement(employeeAbilitiesGoodsReceiptBy);
        employeeData.put("employeeAbilitiesGoodsReceipt", employeeAbilitiesGoodsReceiptElement.getAttribute("checked"));

        By employeeAbilitiesCompoundingBy = By.xpath("//*[@id=\"compounding\"]");
        WebElement employeeAbilitiesCompoundingElement = driver.findElement(employeeAbilitiesCompoundingBy);
        employeeData.put("employeeAbilitiesCompounding", employeeAbilitiesCompoundingElement.getAttribute("checked"));

        /**
         * employment:
         */
        By employeeStartOfEmploymentBy = By.xpath("//*[@id=\"start_of_employment\"]");
        WebElement employeeStartOfEmploymentElement = driver.findElement(employeeStartOfEmploymentBy);
        employeeData.put("employeeStartOfEmployment", employeeStartOfEmploymentElement.getAttribute("value"));

        By employeeEndOfEmploymentBy = By.xpath("//*[@id=\"end_of_employment\"]");
        WebElement employeeEndOfEmploymentElement = driver.findElement(employeeEndOfEmploymentBy);
        employeeData.put("employeeEndOfEmployment", employeeEndOfEmploymentElement.getAttribute("value"));

        /**
         * return map:
         */
        return employeeData;
    }

    public WorkforceManagementPage submitForm() {
        By submitButtonBy = By.xpath("//*[@id=\"save_new\"]");
        WebElement submitButtonElement = driver.findElement(submitButtonBy);
        submitButtonElement.click();

        return new WorkforceManagementPage(driver);
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
