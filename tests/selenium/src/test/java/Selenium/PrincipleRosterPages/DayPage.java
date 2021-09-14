/*
 * Copyright (C) 2021 Martin Mandelkow <netbeans@martin-mandelkow.de>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
package Selenium.PrincipleRosterPages;

import Selenium.MenuFragment;
import Selenium.RosterItem;
import Selenium.RosterPages.RosterDayEditPage;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;
import java.util.Locale;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;

/**
 *
 * @author Martin Mandelkow <netbeans@martin-mandelkow.de>
 */
public class DayPage {

    protected static WebDriver driver;

    private final By weekdayChooserInputBy = By.xpath("//*[@id=\"week_day_form\"]/select");
    private final By alternationChooserInputBy = By.xpath("//*[@id=\"alternating_week_form\"]/select");
    private final By branchChooserInputBy = By.xpath("//*[@id=\"branch_form_select\"]");
    private final By userNameSpanBy = By.id("MenuListItemApplicationUsername");
    private final By addRosterRowButtonBy = By.xpath("//*[@id=\"principle_roster_form\"]/table/tbody/tr[]/td/button/"); //TODO: Change the PHP to make the button more specific via class or id.
    private final By buttonSubmitBy = By.id("submit_button");

    public DayPage(WebDriver driver) {
        this.driver = driver;

        if (this.getUserNameText().isEmpty()) {
            throw new IllegalStateException("This is not a logged in state,"
                    + " current page is: " + driver.getCurrentUrl());
        }
        MenuFragment.navigateTo(driver, MenuFragment.MenuLinkToPrincipleRosterDay);
    }

    /**
     * Get user_name (span tag)
     *
     * We only need this in order to check, if we are logged in. TODO: Could
     * this be a static method in the signInPage class?
     *
     * @return String user_name text
     */
    public String getUserNameText() {
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.presenceOfElementLocated(userNameSpanBy));

        return driver.findElement(userNameSpanBy).getText();
    }

    public RosterDayEditPage manageProfile() {
        // Page encapsulation to manage profile functionality
        return new RosterDayEditPage(driver);
    }

    public void goToWeekday(int weekdayIndex) {
        WebElement weekdayChooserInput = driver.findElement(weekdayChooserInputBy);
        Select weekdayChooserSelect = new Select(weekdayChooserInput);
        weekdayChooserSelect.selectByValue(String.valueOf(weekdayIndex));
    }

    public void goToAlternation(int alternatingWeekId) {
        WebElement alternationChooserInput = driver.findElement(alternationChooserInputBy);
        Select alternationChooserSelect = new Select(alternationChooserInput);
        alternationChooserSelect.selectByValue(String.valueOf(alternatingWeekId));

    }

    public void goToBranch(int branchId) {
        WebElement branchChooserInput = driver.findElement(branchChooserInputBy);
        Select branchChooserSelect = new Select(branchChooserInput);
        branchChooserSelect.selectByValue(String.valueOf(branchId));

    }

    public int getBranchId() {
        WebElement branchChooserInput = driver.findElement(branchChooserInputBy);
        String value = branchChooserInput.getAttribute("value");
        return Integer.valueOf(value);
    }

    public int getAlternationId() {
        WebElement alternationChooserInput = driver.findElement(alternationChooserInputBy);
        String value = alternationChooserInput.getAttribute("value");
        return Integer.valueOf(value);
    }

    public int getWeekdayIndex() {
        WebElement weekdayChooserInput = driver.findElement(weekdayChooserInputBy);
        String value = weekdayChooserInput.getAttribute("value");
        return Integer.valueOf(value);
    }

    public void addRosterRow() {
        WebElement addRosterRowButtonElement = driver.findElement(addRosterRowButtonBy);
        addRosterRowButtonElement.click();
    }

    public int getUnixTime() {
        /**
         * <p lang=de>
         * Es wird nur ein Tag dargestellt. Es ist daher egal, welcher <td>
         * ausgewählt wird. Das data-date_unix ist immer identisch.
         * </p>
         */
        By rosterTableColumnBy = By.xpath("//*[@id=\"principle_roster_form\"]/table/tbody/tr/td");
        WebElement rosterTableColumnElement = driver.findElement(rosterTableColumnBy);
        String unixTimeString = rosterTableColumnElement.getAttribute("data-date_unix");
        return Integer.valueOf(unixTimeString);
    }

    /*
    public RosterItem getRosterItem(int rowIndex) {
        int unixTime = getUnixTime();
        ////*[@id="principle_roster_form"]/table/tbody/tr[3]/td/span/select
        return new RosterItem(employeeName, calendar, dutyStart, dutyEnd, breakStart, breakEnd, branchString, comment);
    }
     */
    public int getRosterValueUnixDate(int iterator) {
        WebElement rosterTableRow = this.findRosterTableRow(iterator);
        int rosterValue = Integer.parseInt(rosterTableRow.getAttribute("data-date_unix"));
        return rosterValue;
    }

    public String getRosterValueDateString(int iterator) {
        WebElement rosterTableRow = this.findRosterTableRow(iterator);
        String rosterValue = rosterTableRow.getAttribute("data-date_sql");
        return rosterValue;
    }

    public int getRosterValueEmployeeId(int unixDate, int iterator) {
        WebElement rosterInputElement = findRosterInputEmployee(unixDate, iterator);
        //Select inputElementSelect = new Select(rosterInputElement);
        int rosterValue = Integer.parseInt(rosterInputElement.getAttribute("value"));
        return rosterValue;
    }

    public String getRosterValueEmployeeName(int unixDate, int iterator) {
        WebElement rosterInputElement = findRosterInputEmployee(unixDate, iterator);
        Select inputElementSelect = new Select(rosterInputElement);
        WebElement selectedOption = inputElementSelect.getFirstSelectedOption();
        String selectedoption = selectedOption.getText();
        //String rosterValue = rosterInputElement.getAttribute("value");
        return selectedoption;
    }

    public String getRosterValueDutyStart(int unixDate, int iterator) {
        WebElement rosterInputElement = findRosterInputDutyStart(unixDate, iterator);
        String rosterValue = rosterInputElement.getAttribute("value");
        return rosterValue;
    }

    public String getRosterValueDutyEnd(int unixDate, int iterator) {
        WebElement rosterInputElement = findRosterInputDutyEnd(unixDate, iterator);
        String rosterValue = rosterInputElement.getAttribute("value");
        return rosterValue;
    }

    public String getRosterValueBreakStart(int unixDate, int iterator) {
        WebElement rosterInputElement = findRosterInputBreakStart(unixDate, iterator);
        String rosterValue = rosterInputElement.getAttribute("value");
        return rosterValue;
    }

    public String getRosterValueBreakEnd(int unixDate, int iterator) {
        WebElement rosterInputElement = findRosterInputBreakEnd(unixDate, iterator);
        String rosterValue = rosterInputElement.getAttribute("value");
        return rosterValue;
    }

    public RosterItem getRosterItem(int iterator) throws ParseException {
        String dateSql = this.getRosterValueDateString(iterator);
        Date dateParsed = new SimpleDateFormat("yyyy-MM-dd", Locale.ENGLISH).parse(dateSql);
        Calendar calendar = Calendar.getInstance();
        calendar.setTime(dateParsed);

        int unixDate = getRosterValueUnixDate(iterator);
        String employeeName = this.getRosterValueEmployeeName(unixDate, iterator);
        String dutyStart = getRosterValueDutyStart(unixDate, iterator);
        String dutyEnd = getRosterValueDutyEnd(unixDate, iterator);
        String breakStart = getRosterValueBreakStart(unixDate, iterator);
        String breakEnd = getRosterValueBreakEnd(unixDate, iterator);
        RosterItem rosterItem = new RosterItem(employeeName, calendar, dutyStart, dutyEnd, breakStart, breakEnd);
        return rosterItem;
    }

    private WebElement findRosterTableRow(int iterator) {
        int rowInTable = iterator + 2;
        String rowXPath = "//*[@id=\"principle_roster_form\"]/table/tbody/tr[" + rowInTable + "]/td";
        By rowBy = By.xpath(rowXPath);
        WebElement rosterTableRowElement = driver.findElement(rowBy);
        return rosterTableRowElement;
    }

    private WebElement findRosterInputEmployee(int unixDate, int iterator) {
        String inputName = "Roster[" + unixDate + "][" + iterator + "][employee_id]";
        By inputBy = By.name(inputName);
        WebElement rosterInputElement = driver.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputDutyStart(int unixDate, int iterator) {
        String inputName = "Roster[" + unixDate + "][" + iterator + "][duty_start_sql]";
        By inputBy = By.name(inputName);
        WebElement rosterInputElement = driver.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputDutyEnd(int unixDate, int iterator) {
        String inputName = "Roster[" + unixDate + "][" + iterator + "][duty_end_sql]";
        By inputBy = By.name(inputName);
        WebElement rosterInputElement = driver.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputBreakStart(int unixDate, int iterator) {
        String inputName = "Roster[" + unixDate + "][" + iterator + "][break_start_sql]";
        By inputBy = By.name(inputName);
        WebElement rosterInputElement = driver.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputBreakEnd(int unixDate, int iterator) {
        String inputName = "Roster[" + unixDate + "][" + iterator + "][break_end_sql]";
        By inputBy = By.name(inputName);
        WebElement rosterInputElement = driver.findElement(inputBy);
        return rosterInputElement;
    }

    public void changeRosterInputEmployee(int unixDate, int iterator, int employeeId) {
        WebElement rosterInputEmployeeElement = findRosterInputEmployee(unixDate, iterator);
        Select inputElementSelect = new Select(rosterInputEmployeeElement);
        inputElementSelect.selectByValue(String.valueOf(employeeId));
    }

    public void changeRosterInputDutyStart(int unixDate, int iterator, String time) {
        WebElement rosterInputElement = findRosterInputDutyStart(unixDate, iterator);
        rosterInputElement.sendKeys(time);
    }

    public void changeRosterInputDutyEnd(int unixDate, int iterator, String time) {
        WebElement rosterInputElement = findRosterInputDutyEnd(unixDate, iterator);
        rosterInputElement.sendKeys(time);
    }

    public void changeRosterInputBreakStart(int unixDate, int iterator, String time) {
        WebElement rosterInputElement = findRosterInputBreakStart(unixDate, iterator);
        rosterInputElement.sendKeys(time);
    }

    public void changeRosterInputBreakEnd(int unixDate, int iterator, String time) {
        WebElement rosterInputElement = findRosterInputBreakEnd(unixDate, iterator);
        rosterInputElement.sendKeys(time);
    }

    public void rosterFormSubmit() {
        WebElement buttonSubmitElement = driver.findElement(buttonSubmitBy);
        buttonSubmitElement.click();
    }

}
