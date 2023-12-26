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

import Selenium.Employee;
import Selenium.MenuFragment;
import Selenium.driver.Wrapper;
import java.time.LocalDate;
import java.util.HashMap;
import java.util.List;
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

    private final By user_name_spanBy = By.id("MenuListItemApplicationUsername");
    private final By selectEmployeeBy = By.xpath("/html/body/div[2]/form[@id=\"select_employee\"]/select[@name=\"employee_key\"]");

    private final By employeeLastNameInputBy = By.xpath("//*[@id=\"last_name\"]");
    private final By employeeFirstNameInputBy = By.xpath("//*[@id=\"first_name\"]");
    private final By employeeWeeklyWorkingHoursBy = By.xpath("//*[@id=\"working_week_hours\"]");
    private final By employeeLunchBreakMinutesBy = By.xpath("//*[@id=\"lunch_break_minutes\"]");
    private final By employeeHolidaysBy = By.xpath("//*[@id=\"holidays\"]");
    private final By employeeAbilitiesGoodsReceiptBy = By.xpath("//*[@id=\"goods_receipt\"]");
    private final By employeeAbilitiesCompoundingBy = By.xpath("//*[@id=\"compounding\"]");
    private final By employeeKeyInputBy = By.xpath("//*[@id=\"employee_key\"]");

    public WorkforceManagementPage(WebDriver driver) {
        this.driver = driver;
        if (getUserNameText().isEmpty()) {
            throw new IllegalStateException("This is not a logged in state,"
                    + " current page is: " + driver.getCurrentUrl());
        }
        MenuFragment.navigateTo(driver, MenuFragment.MenuLinkToManageEmployee);
    }

    public WorkforceManagementPage selectEmployee(Employee employee) throws Exception {
        WebElement selectEmployeeElement = driver.findElement(selectEmployeeBy);
        Select selectEmployeeSelect = new Select(selectEmployeeElement);
        String optionText = employee.getFirstName() + " " + employee.getLastName();
        if (!Wrapper.isOptionTextPresent(selectEmployeeSelect, optionText)) {
            throw new Exception("Employee not found");
        }
        selectEmployeeSelect.selectByVisibleText(optionText);
        return new WorkforceManagementPage(driver);
    }

    public void selectEmployee(String employeeKeyValueString) {
        WebElement selectEmployeeElement = driver.findElement(selectEmployeeBy);
        Select selectEmployeeSelect = new Select(selectEmployeeElement);
        selectEmployeeSelect.selectByValue(employeeKeyValueString);
        /**
         * <p lang=de>Hier darf keine neue Page returned werden. Es ist wichtig,
         * dass die Seite nicht nach dem select ein zweites Mal neu geladen
         * wird.</p>
         */
    }

    public WorkforceManagementPage setEmployeeData(Employee employeeObject) {
        /**
         * last name:
         */
        WebElement employeeLastNameInputElement = driver.findElement(employeeLastNameInputBy);
        employeeLastNameInputElement.clear();
        employeeLastNameInputElement.sendKeys(employeeObject.getLastName());
        /**
         * first name:
         */
        WebElement employeeFirstNameInputElement = driver.findElement(employeeFirstNameInputBy);
        employeeFirstNameInputElement.clear();
        employeeFirstNameInputElement.sendKeys(employeeObject.getFirstName());
        /**
         * profession: One of the radio buttons should be checked.
         */
        By employeeProfessionBy = By.xpath("/html/body/div[2]/form[2]/fieldset[2]/label/input[@name=\"profession\" and @value=\"" + employeeObject.getProfession() + "\"]");
        WebElement employeeProfessionElement = driver.findElement(employeeProfessionBy);
        employeeProfessionElement.click();
        /**
         * hours:
         */
        WebElement employeeWeeklyWorkingHoursElement = driver.findElement(employeeWeeklyWorkingHoursBy);
        employeeWeeklyWorkingHoursElement.clear();
        employeeWeeklyWorkingHoursElement.sendKeys(String.valueOf(employeeObject.getWorkingHours()));

        WebElement employeeLunchBreakMinutesElement = driver.findElement(employeeLunchBreakMinutesBy);
        employeeLunchBreakMinutesElement.clear();
        employeeLunchBreakMinutesElement.sendKeys(String.valueOf(employeeObject.getLunchBreakMinutes()));

        WebElement employeeHolidaysElement = driver.findElement(employeeHolidaysBy);
        employeeHolidaysElement.clear();
        employeeHolidaysElement.sendKeys(String.valueOf(employeeObject.getHolidays()));

        /**
         * main branch:
         */
        By employeeBranchBy = By.xpath("//*[@id=\"human_resource_management\"]/fieldset[4]/label/span[contains(text(), '" + employeeObject.getBranchString() + "')]");
        WebElement employeeBranchElement = driver.findElement(employeeBranchBy);
        employeeBranchElement.click();

        /**
         * abilities:
         */
        WebElement employeeAbilitiesGoodsReceiptElement = driver.findElement(employeeAbilitiesGoodsReceiptBy);
        if (null == employeeAbilitiesGoodsReceiptElement.getAttribute("checked")) {
            if (true == employeeObject.getAbilitiesGoodsReceipt()) {
                employeeAbilitiesGoodsReceiptElement.click();
            }
        } else {
            if (false == employeeObject.getAbilitiesGoodsReceipt()) {
                employeeAbilitiesGoodsReceiptElement.click();
            }
        }

        WebElement employeeAbilitiesCompoundingElement = driver.findElement(employeeAbilitiesCompoundingBy);
        if (null == employeeAbilitiesCompoundingElement.getAttribute("checked")) {
            if (true == employeeObject.getAbilitiesCompounding()) {
                employeeAbilitiesCompoundingElement.click();
            }
        } else {
            if (false == employeeObject.getAbilitiesCompounding()) {
                employeeAbilitiesCompoundingElement.click();
            }
        }
        /**
         * employment:
         */
        By employeeStartOfEmploymentBy = By.xpath("//*[@id=\"start_of_employment\"]");
        WebElement employeeStartOfEmploymentElement = driver.findElement(employeeStartOfEmploymentBy);
        LocalDate dateStartThing = employeeObject.getStartOfEmployment();
        if (null != dateStartThing) {
            Wrapper.fillDateInput(employeeStartOfEmploymentElement, dateStartThing);
        }
        By employeeEndOfEmploymentBy = By.xpath("//*[@id=\"end_of_employment\"]");
        WebElement employeeEndOfEmploymentElement = driver.findElement(employeeEndOfEmploymentBy);
        LocalDate dateEndThing = employeeObject.getEndOfEmployment();
        if (null != dateEndThing) {
            Wrapper.fillDateInput(employeeEndOfEmploymentElement, dateEndThing);
        }
        /**
         * Finally submit
         */
        return submitForm();
    }

    public WorkforceManagementPage createEmployee(Employee employeeObject) {
        try {
            selectEmployee(employeeObject);

            /**
             * If this employee exists, it will not be created again. Instead we
             * will adapt the values:
             */
            return setEmployeeData(employeeObject);
        } catch (Exception e) {
            /**
             * The employee does not exist yet. It will be created.
             */
        }
        selectEmployee("");//Select the empty new employee
        return setEmployeeData(employeeObject);
    }

    public Employee getEmployeeObject() {
        HashMap<String, String> employeeData = new HashMap<>();
        /**
         * employeeKey:
         */
        WebElement employeeKeyInputElement = driver.findElement(employeeKeyInputBy);
        employeeData.put("employeeKey", employeeKeyInputElement.getAttribute("value"));
        /**
         * last name:
         */
        WebElement employeeLastNameInputElement = driver.findElement(employeeLastNameInputBy);
        employeeData.put("employeeLastName", employeeLastNameInputElement.getAttribute("value"));
        /**
         * first name:
         */
        WebElement employeeFirstNameInputElement = driver.findElement(employeeFirstNameInputBy);
        employeeData.put("employeeFirstName", employeeFirstNameInputElement.getAttribute("value"));
        /**
         * profession: One of the radio buttons is checked.
         */
        By employeeProfessionBy = By.xpath("/html/body/div[2]/form[2]/fieldset[2]/label/input[@name=\"profession\" and @checked]");
        WebElement employeeProfessionElement = driver.findElement(employeeProfessionBy);
        employeeData.put("employeeProfession", employeeProfessionElement.getAttribute("value"));
        /**
         * hours:
         */
        WebElement employeeWeeklyWorkingHoursElement = driver.findElement(employeeWeeklyWorkingHoursBy);
        employeeData.put("employeeWorkingHours", employeeWeeklyWorkingHoursElement.getAttribute("value"));

        WebElement employeeLunchBreakMinutesElement = driver.findElement(employeeLunchBreakMinutesBy);
        employeeData.put("employeeLunchBreakMinutes", employeeLunchBreakMinutesElement.getAttribute("value"));

        WebElement employeeHolidaysElement = driver.findElement(employeeHolidaysBy);
        employeeData.put("employeeHolidays", employeeHolidaysElement.getAttribute("value"));

        /**
         * main branch:
         */
        By employeeBranchBy = By.xpath("/html/body/div[2]/form[2]/fieldset[4]/label/input[@name=\"branch\" and @checked]");
        WebElement employeeBranchElement = driver.findElement(employeeBranchBy);
        employeeData.put("employeeBranchId", employeeBranchElement.getAttribute("value"));
        By employeeBranchLabelBy = By.xpath("/html/body/div[2]/form[2]/fieldset[4]/label/input[@name=\"branch\" and @checked]/parent::label");
        WebElement employeeBranchLabelElement = driver.findElement(employeeBranchLabelBy);
        employeeData.put("employeeBranchName", employeeBranchLabelElement.getText());

        /**
         * abilities:
         */
        WebElement employeeAbilitiesGoodsReceiptElement = driver.findElement(employeeAbilitiesGoodsReceiptBy);
        employeeData.put("employeeAbilitiesGoodsReceipt", employeeAbilitiesGoodsReceiptElement.getAttribute("checked"));

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
        return new Employee(employeeData);
    }

    public WorkforceManagementPage submitForm() {
        By submitButtonBy = By.xpath("//*[@id=\"save_new\"]");
        WebElement submitButtonElement = driver.findElement(submitButtonBy);
        submitButtonElement.click();
        WorkforceManagementPage newWorkforceManagementPage = new WorkforceManagementPage(driver);
        return newWorkforceManagementPage;
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
