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
package Selenium.RosterPages;

import Selenium.MenuFragment;
import Selenium.RosterItem;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;
import java.util.Locale;
import org.openqa.selenium.By;
import org.openqa.selenium.Keys;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;

/**
 *
 * @author Mandelkow Page Object encapsulates the Roster day page with edit
 * privilege
 *
 * TODO: <p lang=de>
 * - Im php code muss noch der Lesen button mit einer id belegt werden. Dann
 * kann man den Wechsel zwischen den Seiten auch testen. - Die Input Elemente
 * kann man per "name" raussuchen. Roster[1612998000][3][employee_id] - Im
 * user_dialog_container kann man die Fehler auslesen. - Der Button zum
 * roster_input_row_add_row_image hat noch keine id. roster_input_add_row_button
 * wäre gut.
 * </p>
 */
public class RosterDayEditPage {

    protected static WebDriver driver;

    private final By dateChooserInputBy = By.id("date_chooser_input");
    private final By buttonSubmitBy = By.id("submit_button");
    private final By buttonDayBackwardBy = By.id("button_day_backward");
    private final By buttonDayForwardBy = By.id("button_day_forward");
    private final By branchFormSelectBy = By.id("branch_form_select");
    private final By userNameSpanBy = By.id("MenuListItemApplicationUsername");
    private final By buttonRosterInputAddRowBy = By.id("roster_input_add_row_button");

    public RosterDayEditPage(WebDriver driver) {
        this.driver = driver;

        if (this.getUserNameText().isEmpty()) {
            throw new IllegalStateException("This is not a logged in state,"
                    + " current page is: " + driver.getCurrentUrl());
        }
        MenuFragment.navigateTo(driver, MenuFragment.MenuLinkToRosterDayEdit);
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

    public void goToDate(String date) {
        WebElement dateChooserInput = driver.findElement(dateChooserInputBy);
        dateChooserInput.sendKeys(date);
        dateChooserInput.sendKeys(Keys.ENTER);
    }

    public String getDate() {
        WebElement dateChooserInput = driver.findElement(dateChooserInputBy);
        String date_value = dateChooserInput.getAttribute("value");
        return date_value;
    }

    public void moveDayBackward() {
        WebElement button_day_backward = driver.findElement(buttonDayBackwardBy);
        button_day_backward.click();
    }

    public void moveDayForward() {
        WebElement button_day_forward = driver.findElement(buttonDayForwardBy);
        button_day_forward.click();
    }

    public void selectBranch(int branchId) {
        Select branchFormSelect = new Select(driver.findElement(branchFormSelectBy));
        branchFormSelect.selectByValue(String.valueOf(branchId));
    }

    public int getBranch() {
        Select branchFormSelect = new Select(driver.findElement(branchFormSelectBy));
        int branchId = Integer.parseInt(branchFormSelect.getFirstSelectedOption().getAttribute("value"));
        return branchId;
    }

    public int getApproval() throws Exception {
        /*
		 * TODO: <p lang=de>
		 * Ablehnen und Genehmigen kann nicht ordentlich geprüft werden.
		 * Der Status muss maschinenlesbar auf der Seite sichtbar sein.
		 * Anschließend können wir die Funktionen Genehmigen und Ablehnen testen.
		 * </p>
         */
        throw new Exception("Not implemented yet");
        //return branchId;
    }

    private WebElement findRosterTableRow(int iterator) {
        int rowInTable = iterator + 2;
        String rowXPath = "/html/body/div[2]/form/table/tbody/tr[" + rowInTable + "]/td";
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

    /**
     * <p lang=de>
     * Diese Funktion sendet kein submit. Das Formular muss anschließend noch
     * abgesendet werden!
     * </p>
     */
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

    public void rosterInputAddRow() {
        WebElement buttonRosterInputAddRowElement = driver.findElement(buttonRosterInputAddRowBy);
        buttonRosterInputAddRowElement.click();
    }

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

}
