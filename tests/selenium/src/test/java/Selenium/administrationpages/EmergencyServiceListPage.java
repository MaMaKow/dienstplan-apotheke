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
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.List;
import java.util.Date;
import java.util.Locale;
import org.openqa.selenium.Alert;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;

/**
 * @author Mandelkow
 */
public class EmergencyServiceListPage {

    protected static WebDriver driver;
    By user_name_spanBy = By.id("MenuListItemApplicationUsername");
    By branchFormSelectBy = By.xpath("//*[@id=\"branch_form_select\"]");
    By selectYearSelectBy = By.xpath("//*[@id=\"select_year\"]/select");
    //By selectYearSelectBy = By.xpath("/html/body/form/select[@name='year']");
    By emergencyRowListBy = By.xpath("//*[@id=\"emergency_service_table\"]/tbody/tr");
    //By emergencyRowListBy = By.xpath("/html/body/table/tbody/tr");
    By emergencyRowDateBy = By.xpath(".//td[1]");
    By emergencyRowEmployeeIdBy = By.xpath(".//td[2]/form/select");

    public EmergencyServiceListPage(WebDriver driver) {
        this.driver = driver;

        if (getUserNameText().isEmpty()) {
            throw new IllegalStateException("This is not a logged in state,"
                    + " current page is: " + driver.getCurrentUrl());
        }
        MenuFragment.navigateTo(driver, MenuFragment.MenuLinkToEmergencyServiceList);

    }

    public void selectYear(String yearString) {
        WebElement selectYearSelectElement = driver.findElement(selectYearSelectBy);
        Select selectYearSelect = new Select(selectYearSelectElement);
        selectYearSelect.selectByVisibleText(yearString);
    }

    public int getYear() {
        WebElement selectYearSelectElement = driver.findElement(selectYearSelectBy);
        Select selectYearSelect = new Select(selectYearSelectElement);
        String selectedYearString = selectYearSelect.getFirstSelectedOption().getAttribute("value");
        return Integer.valueOf(selectedYearString);
    }

    public void selectBranch(int branchId) {
        Select branchFormSelect = new Select(driver.findElement(branchFormSelectBy));
        branchFormSelect.selectByValue(String.valueOf(branchId));
    }

    public int getBranchId() {
        Select branchFormSelect = new Select(driver.findElement(branchFormSelectBy));
        int branchId = Integer.parseInt(branchFormSelect.getFirstSelectedOption().getAttribute("value"));
        return branchId;
    }

    private WebElement getEmergencyRowElementByDate(Date targetDate) {
        List<WebElement> emergencyRowListElements = driver.findElements(emergencyRowListBy);
        emergencyRowListElements.remove(0); //The first element is the heading <th></th>
        for (WebElement emergencyRowElement : emergencyRowListElements) {
            WebElement emergencyRowDateElement = emergencyRowElement.findElement(emergencyRowDateBy);
            String emergencyRowDateString = emergencyRowDateElement.getText().substring(3, 13);
            SimpleDateFormat simpleDateFormat = new SimpleDateFormat("dd.MM.yyyy", Locale.GERMAN);
            if (simpleDateFormat.format(targetDate).equals(emergencyRowDateString)) {
                return emergencyRowElement;
            }
        }
        System.err.println("Wir haben nichts gefunden: return null;");
        return null;
    }

    public Integer getEmployeeIdOnDate(Date targetDate) {
        WebElement emergencyRowElement = getEmergencyRowElementByDate(targetDate);
        if (null == emergencyRowElement) {
            return null;
        }
        WebElement employeeIdWebElement = emergencyRowElement.findElement(emergencyRowEmployeeIdBy);
        Select employeeIdSelect = new Select(employeeIdWebElement);
        WebElement selectedOption = employeeIdSelect.getFirstSelectedOption();
        return Integer.valueOf(selectedOption.getAttribute("value"));
    }

    public EmergencyServiceListPage setEmployeeIdOnDate(Date targetDate, int employeeId) {
        WebElement emergencyRowElement = getEmergencyRowElementByDate(targetDate);
        WebElement employeeIdWebElement = emergencyRowElement.findElement(emergencyRowEmployeeIdBy);
        Select employeeIdSelect = new Select(employeeIdWebElement);
        employeeIdSelect.selectByValue(String.valueOf(employeeId));
        return new EmergencyServiceListPage(driver);
    }

    public EmergencyServiceListPage addLineForDate(Calendar calendar) {
        SimpleDateFormat simpleDateFormat = new SimpleDateFormat("dd.MM.", Locale.GERMANY);
        WebElement dateInputElement = driver.findElement(By.xpath("//*[@id=\"add_new_line_date\"]"));
        dateInputElement.sendKeys(simpleDateFormat.format(calendar));
        WebElement submitButton = driver.findElement(By.xpath("//*[@id=\"add_new_line_submit\"]"));
        submitButton.click();
        return new EmergencyServiceListPage(driver);
    }

    public EmergencyServiceListPage removeLineByDate(Calendar calendar) {
        WebElement emergencyRowElement = getEmergencyRowElementByDate(calendar.getTime());
        WebElement deleteButton = emergencyRowElement.findElement(By.xpath(".//button[contains(@id, \'delete\')]"));
        deleteButton.click();
        /**
         * Alert will display: "Really delete this dataset?" Press the OK
         * button:
         */
        Alert alert = driver.switchTo().alert();
        alert.accept();
        return new EmergencyServiceListPage(driver);
    }

    public EmergencyServiceListPage doNotRemoveLineByDate(Calendar calendar) {
        WebElement emergencyRowElement = getEmergencyRowElementByDate(calendar.getTime());
        WebElement deleteButton = emergencyRowElement.findElement(By.xpath(".//button[contains(@id, \'delete\')]"));
        deleteButton.click();
        /**
         * Alert will display: "Really delete this dataset?" Press the Cancel
         * button:
         */
        Alert alert = driver.switchTo().alert();
        alert.dismiss();
        return new EmergencyServiceListPage(driver);
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
