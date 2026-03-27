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

import Selenium.BasePage;
import Selenium.MenuFragment;
import Selenium.Utilities.LogCollector;
import Selenium.driver.Wrapper;
import java.time.Duration;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.List;
import org.openqa.selenium.Alert;
import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.NoSuchElementException;
import org.openqa.selenium.StaleElementReferenceException;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.interactions.Actions;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;

/**
 * @author Mandelkow
 */
public class EmergencyServiceListPage extends BasePage {

    By user_name_spanBy = By.id("MenuListItemApplicationUsername");
    By branchFormSelectBy = By.xpath("//*[@id=\"branch_form_select\"]");
    By selectYearSelectBy = By.xpath("//*[@id=\"select_year\"]/select");
    By emergencyRowListBy = By.xpath("//*[@id=\"emergencyServiceTable\"]/tbody/tr");
    By emergencyRowEmployeeSelectBy = By.xpath(".//td/select[@name=\"emergency_service_employee\"]");

    public EmergencyServiceListPage(WebDriver driver) {
        super(driver);

        if (getUserNameText().isEmpty()) {
            throw new IllegalStateException("This is not a logged in state,"
                    + " current page is: " + driver.getCurrentUrl());
        }
        WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(3));
        try {
            wait.until(ExpectedConditions.presenceOfElementLocated(MenuFragment.MenuLinkToEmergencyServiceList));
            MenuFragment.navigateTo(driver, MenuFragment.MenuLinkToEmergencyServiceList);
        } catch (NoSuchElementException noSuchElementException) {
            wait.until(ExpectedConditions.presenceOfElementLocated(MenuFragment.MenuLinkToEmergencyServiceList));
            MenuFragment.navigateTo(driver, MenuFragment.MenuLinkToEmergencyServiceList);
        }
    }

    public void selectYear(String yearString) {
        LogCollector.debug("method selectYear() with year = " + yearString);
        LogCollector.debug("find element selectYearSelectElement:");
        WebElement selectYearSelectElement = driver.findElement(selectYearSelectBy);
        LogCollector.debug("javascript scroll into view selectYearSelectElement");
        ((JavascriptExecutor) driver).executeScript("arguments[0].scrollIntoView(true);", selectYearSelectElement);
        LogCollector.debug("actions.moveToElement(dateInputElement).perform();:");
        Actions actions = new Actions(driver);
        actions.moveToElement(selectYearSelectElement).perform();
        LogCollector.debug("Select selectYearSelect = new Select(selectYearSelectElement);:");
        Select selectYearSelect = new Select(selectYearSelectElement);
        LogCollector.debug("selectYearSelect.selectByVisibleText(yearString);:");
        selectYearSelect.selectByVisibleText(yearString);
    }

    public void selectYear(int year) {
        String yearString = String.valueOf(year);
        selectYear(yearString);
    }

    public int getYear() {
        WebElement selectYearSelectElement = driver.findElement(selectYearSelectBy);
        Select selectYearSelect = new Select(selectYearSelectElement);
        String selectedYearString = selectYearSelect.getFirstSelectedOption().getAttribute("value");
        return Integer.parseInt(selectedYearString);
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

    private WebElement getEmergencyRowElementByDate(LocalDate localDate) {
        LogCollector.debug("method getEmergencyRowElementByDate()");

        LogCollector.debug("find emergencyRowListElements:");
        List<WebElement> emergencyRowListElements = driver.findElements(emergencyRowListBy);
        By emergencyRowDateBy = By.xpath(".//td[1]/input[@name=\"emergency_service_date\"]");

        LogCollector.debug("remove header line:");
        emergencyRowListElements.remove(0); //The first element is the heading <th></th>
        LogCollector.debug("start for loop with emergencyRowListElements:");
        for (WebElement emergencyRowElement : emergencyRowListElements) {
            LogCollector.debug("extract emergencyRowDateElementList:");
            List<WebElement> emergencyRowDateElementList = emergencyRowElement.findElements(emergencyRowDateBy);
            if (emergencyRowDateElementList.isEmpty()) {
                LogCollector.debug("emergencyRowDateElementList is empty");
                continue;
            }
            LogCollector.debug("find emergencyRowDateElement:");
            WebElement emergencyRowDateElement = emergencyRowElement.findElement(emergencyRowDateBy);
            LogCollector.debug("extract string emergencyRowDateString:");
            String emergencyRowDateString = emergencyRowDateElement.getAttribute("value");
            if (localDate.format(Wrapper.DATE_TIME_FORMATTER_YEAR_MONTH_DAY).equals(emergencyRowDateString)) {
                LogCollector.debug("return emergencyRowElement:");
                return emergencyRowElement;
            }
        }
        //Wir haben nichts gefunden: return null
        LogCollector.debug("did not find any element return null:");
        return null;
    }

    public Integer getEmployeeKeyOnDate(LocalDate localDate) {
        WebElement emergencyRowElement = getEmergencyRowElementByDate(localDate);
        if (null == emergencyRowElement) {
            //There is no such element at all
            return null;
        }
        WebElement employeeKeyWebElement = emergencyRowElement.findElement(emergencyRowEmployeeSelectBy);
        Select employeeKeySelect = new Select(employeeKeyWebElement);
        WebElement selectedOption = employeeKeySelect.getFirstSelectedOption();
        String selectedOptionText = selectedOption.getAttribute("value");
        if (selectedOptionText.equals("")) {
            //There is such a date. But there is no employee selected.
            return null;
        }
        int selectedOptionInt = Integer.parseInt(selectedOptionText);
        return selectedOptionInt;
    }

    public String getEmployeeFullNameOnDate(LocalDate localDate) {
        WebElement emergencyRowElement = getEmergencyRowElementByDate(localDate);
        if (null == emergencyRowElement) {
            //There is no such element at all
            return null;
        }
        WebElement employeeSelectWebElement = emergencyRowElement.findElement(emergencyRowEmployeeSelectBy);
        Select employeeSelect = new Select(employeeSelectWebElement);
        WebElement selectedOption = employeeSelect.getFirstSelectedOption();
        String selectedOptionText = selectedOption.getText();
        if (selectedOptionText.equals("")) {
            //There is such a date. But there is no employee selected.
            return null;
        }
        return selectedOptionText;
    }

    public boolean rowExistsOnDate(LocalDate localDate) {
        LogCollector.debug("Method rowExistsOnDate()");
        LogCollector.debug("find emergencyRowElement:");
        WebElement emergencyRowElement = getEmergencyRowElementByDate(localDate);
        LogCollector.debug("localDate: " + localDate.format(DateTimeFormatter.ISO_DATE));
        if (null == emergencyRowElement) {
            LogCollector.debug("There is no such element at all");
            //There is no such element at all
            return false;
        }
        LogCollector.debug("There is an element:");
        LogCollector.debug(emergencyRowElement.getAttribute("outerHTML"));
        return true;
    }

    public EmergencyServiceListPage setEmployeeNameOnDate(LocalDate localDate, String employeeFullName) {
        WebElement emergencyRowElement = getEmergencyRowElementByDate(localDate);
        WebElement employeeKeyWebElement = emergencyRowElement.findElement(emergencyRowEmployeeSelectBy);
        Select employeeKeySelect = new Select(employeeKeyWebElement);
        employeeKeySelect.selectByVisibleText(employeeFullName);
        return new EmergencyServiceListPage(driver);
    }

    public EmergencyServiceListPage setEmployeeKeyOnDate(LocalDate localDate, int employeeKey) {
        WebElement emergencyRowElement = getEmergencyRowElementByDate(localDate);
        WebElement employeeKeyWebElement = emergencyRowElement.findElement(emergencyRowEmployeeSelectBy);
        Select employeeKeySelect = new Select(employeeKeyWebElement);
        employeeKeySelect.selectByValue(String.valueOf(employeeKey));
        return new EmergencyServiceListPage(driver);
    }

    public EmergencyServiceListPage addLineForDate(LocalDate localDate) {
        LogCollector.debug("method addLineForDate");
        LogCollector.debug("define actions");
        Actions actions = new Actions(driver);
        LogCollector.debug("find element dateInputElement");
        WebElement dateInputElement = driver.findElement(By.xpath("//*[@id=\"add_new_line_date\"]"));
        LogCollector.debug("move to element dateInputElement");
        LogCollector.debug(".getLocation().getX()" + dateInputElement.getLocation().getX());
        LogCollector.debug(".getLocation().getY()" + dateInputElement.getLocation().getY());
        LogCollector.debug("dateInputElement.getRect().getX()" + dateInputElement.getRect().getX());
        LogCollector.debug("dateInputElement.getRect().getY()" + dateInputElement.getRect().getY());
        LogCollector.debug("dateInputElement.getRect().getHeight()" + dateInputElement.getRect().getHeight());
        LogCollector.debug("dateInputElement.getRect().getWidth()" + dateInputElement.getRect().getWidth());
        ((JavascriptExecutor) driver).executeScript("arguments[0].scrollIntoView(true);", dateInputElement);

        actions.moveToElement(dateInputElement).perform();
        LogCollector.debug("fill dateInputElement");
        Wrapper.fillDateInput(dateInputElement, localDate);
        LogCollector.debug("find element submitButton");
        WebElement submitButton = driver.findElement(By.xpath("//*[@id=\"add_new_line_submit\"]"));
        LogCollector.debug("move to element submitButton");
        ((JavascriptExecutor) driver).executeScript("arguments[0].scrollIntoView(true);", submitButton);
        actions.moveToElement(submitButton).perform();
        LogCollector.debug("Wait for submit button");
        //submitButton.click();
        wait.until(ExpectedConditions.elementToBeClickable(submitButton));
        LogCollector.debug("Scroll ino view with javascript submit button");
        ((JavascriptExecutor) driver).executeScript("arguments[0].scrollIntoView(true);", submitButton);
        LogCollector.debug("Click with javascript submit button");
        ((JavascriptExecutor) driver).executeScript("arguments[0].click();", submitButton);
        LogCollector.debug("Return new EmergencyServiceListPage");
        return new EmergencyServiceListPage(driver);
    }

    public EmergencyServiceListPage removeLineByDate(LocalDate localDate) {
        WebElement emergencyRowElement = getEmergencyRowElementByDate(localDate);
        WebElement deleteButton = emergencyRowElement.findElement(By.xpath(".//button[contains(@id, \'delete\')]"));
        deleteButton.click();
        /**
         * Alert will display: "Really delete this dataset?" Press the OK
         * button:
         */
        Alert alert = driver.switchTo().alert();
        alert.accept();
        EmergencyServiceListPage emergencyServiceListPageAfterDeletion;
        try {
            /**
             * This step often results in a stale element state. In that case we
             * will just repeat the creation of the new page:
             */
            emergencyServiceListPageAfterDeletion = new EmergencyServiceListPage(driver);
        } catch (StaleElementReferenceException staleElementReferenceException) {
            emergencyServiceListPageAfterDeletion = new EmergencyServiceListPage(driver);
        } catch (Exception exception) {
            exception.printStackTrace();
            System.out.println(exception.getMessage());
            emergencyServiceListPageAfterDeletion = new EmergencyServiceListPage(driver);
        }
        return emergencyServiceListPageAfterDeletion;
    }

    public EmergencyServiceListPage doNotRemoveLineByDate(LocalDate localDate) {
        WebElement emergencyRowElement = getEmergencyRowElementByDate(localDate);
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
        WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(3));
        wait.until(ExpectedConditions.presenceOfElementLocated(user_name_spanBy));
        WebElement userNameSpanElement;
        try {
            userNameSpanElement = driver.findElement(user_name_spanBy);
        } catch (NoSuchElementException noSuchElementException) {
            wait.until(ExpectedConditions.presenceOfElementLocated(user_name_spanBy));
            userNameSpanElement = driver.findElement(user_name_spanBy);
        }
        String userName = userNameSpanElement.getText();
        return userName;
    }

}
