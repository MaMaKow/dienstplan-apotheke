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

import Selenium.Employee;
import Selenium.MenuFragment;
import Selenium.RosterItem;
import java.text.ParseException;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.List;
import org.openqa.selenium.By;
import org.openqa.selenium.Keys;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;
import static org.testng.Assert.assertEquals;

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
    //private final By buttonRosterInputAddRowBy = By.id("roster_input_add_row_button");
    private final By buttonRosterInputAddRowBy = By.xpath("//*[contains(@id, \'roster_input_row_add_row_target_\')]");
    By tableRowListXpathBy = By.xpath("//*[@id=\"roster_form\"]/table/tbody/tr[@data-roster_row_iterator]");

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

    public void goToDate(LocalDate localDate) {
        WebElement dateChooserInput;
        dateChooserInput = driver.findElement(dateChooserInputBy);
        dateChooserInput.sendKeys(localDate.format(Employee.DATE_TIME_FORMATTER_DAY_MONTH_YEAR));
        dateChooserInput.sendKeys(Keys.ENTER);
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.presenceOfElementLocated(dateChooserInputBy));
        dateChooserInput = driver.findElement(dateChooserInputBy);
        assertEquals(dateChooserInput.getAttribute("value"), localDate.format(Employee.DATE_TIME_FORMATTER_YEAR_MONTH_DAY));
    }

    public String getDateString() {
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

//    private WebElement findRosterTableRow(int iterator) {
//        int rowInTable = iterator + 2;
//        String rowXPath = "/html/body/div[2]/form/table/tbody/tr[" + rowInTable + "]/td";
//        By rowBy = By.xpath(rowXPath);
//        WebElement rosterTableRowElement = driver.findElement(rowBy);
//        return rosterTableRowElement;
//    }
    private WebElement findRosterTableRowByEmployee(Integer employeeId) {
        /**
         * Wir brauchen zwei By Variablen. CSS kann tatsächlich gerade markierte
         * options finden. XPath kann parent elements finden.
         */
        By rowCssBy = By.cssSelector("#roster_form > table > tbody > tr > td > span > select > option:checked[value=\"" + employeeId + "\"]");
        By rowXpathBy = By.xpath("parent::select/parent::span/parent::td/parent::tr");
        WebElement rosterTableRowOptionElement = driver.findElement(rowCssBy);
        WebElement rosterTableRowElement = rosterTableRowOptionElement.findElement(rowXpathBy);
        return rosterTableRowElement;
    }

    private WebElement findLastRosterTableRow() {
        By rowXpathBy = By.xpath("//*[@id=\"roster_form\"]/table/tbody/tr[@data-roster_row_iterator]");
        List<WebElement> rosterTableRowElementList = driver.findElements(rowXpathBy);
        /**
         * Get the last element:
         */
        WebElement rosterTableRowElement = rosterTableRowElementList.get(rosterTableRowElementList.size() - 1);
        return rosterTableRowElement;
    }

    private WebElement findRosterInputEmployee(WebElement rosterTableRow) {
        By inputBy = By.xpath(".//td/span/select[contains(@name, \"employee_id\")]");
        WebElement rosterInputElement = rosterTableRow.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputDutyStart(int employeeId) {
        WebElement rosterTableRow = findRosterTableRowByEmployee(employeeId);
        By inputBy = By.xpath(".//td/input[contains(@name, \"duty_start_sql\")]");
        WebElement rosterInputElement = rosterTableRow.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputDutyStart(WebElement rosterTableRow) {
        By inputBy = By.xpath(".//td/input[contains(@name, \"duty_start_sql\")]");
        WebElement rosterInputElement = rosterTableRow.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputDutyEnd(int employeeId) {
        WebElement rosterTableRow = findRosterTableRowByEmployee(employeeId);
        By inputBy = By.xpath(".//td/input[contains(@name, \"duty_end_sql\")]");
        WebElement rosterInputElement = rosterTableRow.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputDutyEnd(WebElement rosterTableRow) {
        By inputBy = By.xpath(".//td/input[contains(@name, \"duty_end_sql\")]");
        WebElement rosterInputElement = rosterTableRow.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputBreakStart(int employeeId) {
        WebElement rosterTableRow = findRosterTableRowByEmployee(employeeId);
        By inputBy = By.xpath(".//td/input[contains(@name, \"break_start_sql\")]");
        WebElement rosterInputElement = rosterTableRow.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputBreakStart(WebElement rosterTableRow) {
        By inputBy = By.xpath(".//td/input[contains(@name, \"break_start_sql\")]");
        WebElement rosterInputElement = rosterTableRow.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputBreakEnd(int employeeId) {
        WebElement rosterTableRow = findRosterTableRowByEmployee(employeeId);
        By inputBy = By.xpath(".//td/input[contains(@name, \"break_end_sql\")]");
        WebElement rosterInputElement = rosterTableRow.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputBreakEnd(WebElement rosterTableRow) {
        By inputBy = By.xpath(".//td/input[contains(@name, \"break_end_sql\")]");
        WebElement rosterInputElement = rosterTableRow.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputComment(WebElement rosterTableRow) {
        By inputBy = By.xpath(".//td/div/input[contains(@name, \"comment\")]");
        WebElement rosterInputElement = rosterTableRow.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputCommentActivator(WebElement rosterTableRow) {
        By inputBy = By.xpath(".//td/div[contains(@id, \"roster_input_row_comment_input_\") and contains(@id, \"_link_div_show\")]");
        WebElement rosterInputElement = rosterTableRow.findElement(inputBy);
        return rosterInputElement;
    }

    /**
     * <p lang=de>
     * Diese Funktion sendet kein submit. Das Formular muss anschließend noch
     * abgesendet werden!</p>
     *
     * @param employeeIdNew The id of the employee, who will be used as
     * substitute
     */
    private void changeRosterInputEmployee(WebElement rosterTableRow, int employeeIdNew) {
        WebElement rosterInputEmployeeElement = findRosterInputEmployee(rosterTableRow);
        Select inputElementSelect = new Select(rosterInputEmployeeElement);
        inputElementSelect.selectByValue(String.valueOf(employeeIdNew));
    }

    private void changeRosterInputDutyStart(WebElement rosterTableRow, String time) {
        WebElement rosterInputElement = findRosterInputDutyStart(rosterTableRow);
        rosterInputElement.sendKeys(time);
    }

    private void changeRosterInputDutyEnd(WebElement rosterTableRow, String time) {
        WebElement rosterInputElement = findRosterInputDutyEnd(rosterTableRow);
        rosterInputElement.sendKeys(time);
    }

    private void changeRosterInputBreakStart(WebElement rosterTableRow, String time) {
        WebElement rosterInputElement = findRosterInputBreakStart(rosterTableRow);
        rosterInputElement.sendKeys(time);
    }

    private void changeRosterInputBreakEnd(WebElement rosterTableRow, String time) {
        WebElement rosterInputElement = findRosterInputBreakEnd(rosterTableRow);
        rosterInputElement.sendKeys(time);
    }

    private void changeRosterInputComment(WebElement rosterTableRow, String comment) {
        if (null == comment || comment.isBlank()) {
            return;
        }
        try {
            /**
             * <p lang=de>Wenn kein Kommentar vorhanden ist, dann muss das
             * Kommentarfeld erst durch klick auf "K+" sichtbar gemacht
             * werden.</p>
             */
            WebElement rosterInputCommentActivatorElement = findRosterInputCommentActivator(rosterTableRow);
            rosterInputCommentActivatorElement.click();
        } catch (Exception e) {
            /**
             * <p lang=de>Dann war das Kommentarfeld wohl schon sichtbar. Es ist
             * nichts zu tun.</p>
             */
        }
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.presenceOfElementLocated(userNameSpanBy));

        WebElement rosterInputElement = findRosterInputComment(rosterTableRow);
        if (rosterInputElement.isEnabled()) {
            rosterInputElement.clear();
            rosterInputElement.sendKeys(comment);
        }
        System.err.println("Das Setzen des Kommentars hat nicht funktioniert.");
    }

    public void rosterFormSubmit() {
        WebElement buttonSubmitElement = driver.findElement(buttonSubmitBy);
        buttonSubmitElement.click();
    }

    public void rosterInputAddRow(RosterItem rosterItem) {
        WebElement buttonRosterInputAddRowElement = driver.findElement(buttonRosterInputAddRowBy);
        int numberOfRosterTableRowsBeforeClick = driver.findElements(tableRowListXpathBy).size();
        buttonRosterInputAddRowElement.click();
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.numberOfElementsToBe(tableRowListXpathBy, numberOfRosterTableRowsBeforeClick + 1));

        WebElement rosterTableRow = findLastRosterTableRow();
        this.changeRosterInputEmployee(rosterTableRow, rosterItem.getEmployeeId());
        this.changeRosterInputDutyStart(rosterTableRow, rosterItem.getDutyStart());
        this.changeRosterInputDutyEnd(rosterTableRow, rosterItem.getDutyEnd());
        this.changeRosterInputBreakStart(rosterTableRow, rosterItem.getBreakStart());
        this.changeRosterInputBreakEnd(rosterTableRow, rosterItem.getBreakEnd());
        this.changeRosterInputComment(rosterTableRow, rosterItem.getComment());
        /**
         * @TODO: Comment is not implemented yet.
         * @TODO: Wait until the element is successfully filled.
         */
    }

    private Integer getRosterValueBranchId(int employeeId) {
        WebElement rosterTableRow = this.findRosterTableRowByEmployee(employeeId);
        int rosterValue = Integer.parseInt(rosterTableRow.getAttribute("data-branch_id"));
        return rosterValue;
    }

    private String getRosterValueDateString(int employeeId) {
        WebElement rosterTableRow = this.findRosterTableRowByEmployee(employeeId);
        String rosterValue = rosterTableRow.getAttribute("data-date_sql");
        return rosterValue;
    }

    private String getRosterValueDutyStart(int employeeId) {
        WebElement rosterInputElement = findRosterInputDutyStart(employeeId);
        String rosterValue = rosterInputElement.getAttribute("value");
        return rosterValue;
    }

    private String getRosterValueDutyEnd(int employeeId) {
        WebElement rosterInputElement = findRosterInputDutyEnd(employeeId);
        String rosterValue = rosterInputElement.getAttribute("value");
        return rosterValue;
    }

    private String getRosterValueBreakStart(int employeeId) {
        WebElement rosterInputElement = findRosterInputBreakStart(employeeId);
        String rosterValue = rosterInputElement.getAttribute("value");
        return rosterValue;
    }

    private String getRosterValueBreakEnd(int employeeId) {
        WebElement rosterInputElement = findRosterInputBreakEnd(employeeId);
        String rosterValue = rosterInputElement.getAttribute("value");
        return rosterValue;
    }

    public RosterItem getRosterItem(int employeeId) throws ParseException {
        DateTimeFormatter dateTimeFormatterSql = DateTimeFormatter.ISO_LOCAL_DATE;
        String dateSql = this.getRosterValueDateString(employeeId);
        LocalDate localDateParsed = LocalDate.parse(dateSql, dateTimeFormatterSql);
        String dutyStart = getRosterValueDutyStart(employeeId);
        String dutyEnd = getRosterValueDutyEnd(employeeId);
        String breakStart = getRosterValueBreakStart(employeeId);
        String breakEnd = getRosterValueBreakEnd(employeeId);
        int branchId = getRosterValueBranchId(employeeId);
        String comment = null;//TODO; add comment
        RosterItem rosterItem = new RosterItem(employeeId, localDateParsed, dutyStart, dutyEnd, breakStart, breakEnd, comment, branchId);
        return rosterItem;
    }

}
