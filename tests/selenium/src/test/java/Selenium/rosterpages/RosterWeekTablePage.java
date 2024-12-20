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
package Selenium.rosterpages;

import Selenium.TestPage;
import Selenium.Employee;
import Selenium.MenuFragment;
import Selenium.RosterItem;
import Selenium.driver.Wrapper;
import java.text.ParseException;
import java.time.DayOfWeek;
import java.time.Duration;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.Locale;
import org.openqa.selenium.By;
import org.openqa.selenium.ElementClickInterceptedException;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.interactions.Actions;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

/**
 *
 * @author Mandelkow Page Object encapsulates the Home Page
 */
public class RosterWeekTablePage {

    protected static WebDriver driver;

    private final By userNameSpanBy = By.id("MenuListItemApplicationUsername");
    private final By dateChooserInputBy = By.id("date_chooser_input");
    private final By buttonWeekBackwardBy = By.id("button_week_backward");
    private final By buttonWeekForwardBy = By.id("button_week_forward");
    private final By branchFormSelectBy = By.id("branch_form_select");

    private int recursionCounter = 0;

    //private final By dutyRosterTableBy = By.id("dutyRosterTable");
    public RosterWeekTablePage(WebDriver driver) {
        this.driver = driver;

        if (this.getUserNameText().isEmpty()) {
            throw new IllegalStateException("This is not a logged in state,"
                    + " current page is: " + driver.getCurrentUrl());
        }
        MenuFragment.navigateTo(driver, MenuFragment.MenuLinkToRosterWeekTable);
    }

    /**
     * Get user_name (span tag)
     *
     * @return String user_name text
     */
    public String getUserNameText() {
        WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(3));
        wait.until(ExpectedConditions.presenceOfElementLocated(userNameSpanBy));
        return driver.findElement(userNameSpanBy).getText();
    }

    public RosterWeekTablePage manageProfile() {
        // Page encapsulation to manage profile functionality
        return new RosterWeekTablePage(driver);
    }

    public RosterWeekTablePage goToDate(String date) {

        WebElement dateChooserInput = driver.findElement(dateChooserInputBy);
        By dateChooserSubmitBy = By.xpath("//*[@name=\"tagesAuswahl\"]");
        WebElement dateChooserSubmit = driver.findElement(dateChooserSubmitBy);
        try {
            Wrapper.fillDateInput(dateChooserInput, date);
            dateChooserSubmit.click();
        } catch (ElementClickInterceptedException e) {
            // Maybe the mouse is on the menu, obsuring the element.
            // Handle the exception by moving the mouse away from the menu

            // Use Actions class to move to the element and then away from it
            handleElementClickIntercepted(dateChooserSubmit);

            // Retry the click on your problematic element
            if (recursionCounter < 3) {
                recursionCounter++;
                return goToDate(date);
            }
        }

        return new RosterWeekTablePage(driver);
    }

    private void handleElementClickIntercepted(WebElement element) {
        // Use Actions class to move to the element and then away from it
        Actions actions = new Actions(driver);
        actions.moveToElement(element).perform();
        actions.moveByOffset(0, 200).perform();  // Move away, adjust the offset as needed
    }

    public RosterWeekTablePage goToDate(LocalDate localDate) {
        String dateString = localDate.format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
        RosterWeekTablePage rosterWeekTablePage = goToDate(dateString);
        return rosterWeekTablePage;
    }

    public String getDate() {
        WebElement dateChooserInput = driver.findElement(dateChooserInputBy);
        String date_value = dateChooserInput.getAttribute("value");
        return date_value;
    }

    public RosterWeekTablePage moveWeekBackward() {
        WebElement button_week_backward = driver.findElement(buttonWeekBackwardBy);
        button_week_backward.click();
        return new RosterWeekTablePage(driver);
    }

    public RosterWeekTablePage moveWeekForward() {
        WebElement button_week_forward = driver.findElement(buttonWeekForwardBy);
        button_week_forward.click();
        return new RosterWeekTablePage(driver);
    }

    public RosterWeekTablePage selectBranch(int branchId) {
        Select branchFormSelect = new Select(driver.findElement(branchFormSelectBy));
        branchFormSelect.selectByValue(String.valueOf(branchId));
        return new RosterWeekTablePage(driver);
    }

    public int getBranch() {
        Select branchFormSelect = new Select(driver.findElement(branchFormSelectBy));
        int branchId = Integer.parseInt(branchFormSelect.getFirstSelectedOption().getAttribute("value"));
        return branchId;
    }

    private By getRosterItemEmployeeKeyXpathBy(int column, int row) {
        By rosterItemEmployeeKeyXpathBy = By.xpath("/html/body/div[4]/div[4]/table/tbody/tr[" + row + "]/td[" + column + "]/span[1]/span[1]/b/a");
        return rosterItemEmployeeKeyXpathBy;
    }

    private By getRosterItemDateXpathBy(int column) {
        By rosterItemEmployeeKeyXpathBy = By.xpath("/html/body/div[4]/div[4]/table/thead/tr/td[" + column + "]/a");
        return rosterItemEmployeeKeyXpathBy;
    }

    private By getRosterItemDutyStartXpathBy(int column, int row) {
        By rosterItemDutyStartXpathBy = By.xpath("//table[@id=\'dutyRosterTable\']/tbody/tr[" + row + "]/td[" + column + "]/span[@class=\'employee-and-hours-and-duty-time\']/span[@class=\'duty-time\']/span[1]");
        return rosterItemDutyStartXpathBy;
    }

    private By getRosterItemDutyEndXpathBy(int column, int row) {
        By rosterItemDutyEndXpathBy = By.xpath("//table[@id=\'dutyRosterTable\']/tbody/tr[" + row + "]/td[" + column + "]/span[@class=\'employee-and-hours-and-duty-time\']/span[@class=\'duty-time\']/span[2]");
        return rosterItemDutyEndXpathBy;
    }

    private By getRosterItemBreakStartXpathBy(int column, int row) {
        By rosterItemBreakStartXpathBy = By.xpath("//table[@id=\'dutyRosterTable\']/tbody/tr[" + row + "]/td[" + column + "]/span[@class=\'break-time\']/span[1]");
        return rosterItemBreakStartXpathBy;
    }

    private By getRosterItemBreakEndXpathBy(int column, int row) {
        By rosterItemBreakEndXpathBy = By.xpath("//table[@id=\'dutyRosterTable\']/tbody/tr[" + row + "]/td[" + column + "]/span[@class=\'break-time\']/span[2]");
        return rosterItemBreakEndXpathBy;
    }

    /**
     * @param dayOfWeek Monday is 1 Sunday is 7
     * @param employeeFullName full name of the employee
     * @return RosterItem the first roster item, that is found for this employee.
     * CAVE: There could be more items for the same employee!
     */
    public RosterItem getRosterItemByEmployeeKey(DayOfWeek dayOfWeek, String employeeFullName) {

        WebElement rosterTableDataElement = getRosterTableDataElementByEmployeeName(dayOfWeek, employeeFullName);

        Select branchFormSelect = new Select(driver.findElement(branchFormSelectBy));
        int branchId = Integer.parseInt(branchFormSelect.getFirstSelectedOption().getAttribute("value"));
        By rosterItemEmployeeXpathBy = By.xpath(".//span[1]/span[1]/b/a");
        By rosterItemDutyStartXpathBy = By.xpath(".//span[@class=\'employee-and-hours-and-duty-time\']/span[@class=\'duty-time\']/span[1]");
        By rosterItemDutyEndXpathBy = By.xpath(".//span[@class=\'employee-and-hours-and-duty-time\']/span[@class=\'duty-time\']/span[2]");
        By rosterItemBreakStartXpathBy = By.xpath(".//span[@class=\'break-time\']/span[1]");
        By rosterItemBreakEndXpathBy = By.xpath(".//span[@class=\'break-time\']/span[2]");
        String dateSql = rosterTableDataElement.findElement(rosterItemEmployeeXpathBy).getAttribute("data-date_sql");

        LocalDate localDateParsed = LocalDate.parse(dateSql, DateTimeFormatter.ISO_LOCAL_DATE);
        Workforce workforce = TestPage.workforce;
        Employee employeeShould = workforce.getEmployeeByFullName(employeeFullName);
        String employeeNameStringFound = rosterTableDataElement.findElement(rosterItemEmployeeXpathBy).getText();
        /**
         * Make sure, that we found the right TD TableData:
         */
        Assert.assertEquals(employeeShould.getLastName(), employeeNameStringFound);
        /**
         * Produce the RosterItem object
         */
        String dutyStart = rosterTableDataElement.findElement(rosterItemDutyStartXpathBy).getText();
        String dutyEnd = rosterTableDataElement.findElement(rosterItemDutyEndXpathBy).getText();
        String breakStart = rosterTableDataElement.findElement(rosterItemBreakStartXpathBy).getText();
        String breakEnd = rosterTableDataElement.findElement(rosterItemBreakEndXpathBy).getText();
        String comment = null;//TODO; add comment
        RosterItem rosterItem = new RosterItem(employeeFullName, localDateParsed, dutyStart, dutyEnd, breakStart, breakEnd, comment, branchId);
        return rosterItem;

    }

    private WebElement getRosterTableDataElementByEmployeeName(DayOfWeek dayOfWeek, String employeeFullName) {
        int indexOfDay = dayOfWeek.getValue();
        By rowXpathBy = By.xpath("//table[@id=\"dutyRosterTable\"]/tbody/tr/td[" + indexOfDay + "]/span[1]/span[1]/b/a[@data-employeeFullName=\"" + employeeFullName + "\"]/parent::b/parent::span/parent::span/parent::td");
        WebElement rosterTableDataElement = driver.findElement(rowXpathBy);
        return rosterTableDataElement;
    }

    public RosterItem getRosterItem(int column, int row) throws ParseException {

        int employeeKey = Integer.parseInt(driver.findElement(getRosterItemEmployeeKeyXpathBy(column, row)).getAttribute("data-employee_key"));
        int branchId = Integer.parseInt(driver.findElement(getRosterItemEmployeeKeyXpathBy(column, row)).getAttribute("data-branch_id"));
        String dateString = driver.findElement(getRosterItemDateXpathBy(column)).getText();
        String dutyStart = driver.findElement(getRosterItemDutyStartXpathBy(column, row)).getText();
        String dutyEnd = driver.findElement(getRosterItemDutyEndXpathBy(column, row)).getText();
        String breakStart = driver.findElement(getRosterItemBreakStartXpathBy(column, row)).getText();
        String breakEnd = driver.findElement(getRosterItemBreakEndXpathBy(column, row)).getText();
        String comment = null;//TODO: add comment
        /**
         * <p>
         * TODO: Die Locale könnte auch eine Konfigurationsvariable sein.
         * Locale.GERMANY <-> Locale.ENGLISH
         * </p>
         */
        DateTimeFormatter dateTimeFormatter = DateTimeFormatter.ofPattern("EE dd.MM.", Locale.GERMANY);
        LocalDate localDate = LocalDate.parse(dateString, dateTimeFormatter);
        RosterItem rosterItem = new Selenium.RosterItem(employeeKey, localDate, dutyStart, dutyEnd, breakStart, breakEnd, comment, branchId);
        return rosterItem;
    }
}
