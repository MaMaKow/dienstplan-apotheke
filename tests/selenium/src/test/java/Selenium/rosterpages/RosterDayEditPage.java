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
import Selenium.driver.Wrapper;
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
 * kann man per "name" raussuchen. Roster[1612998000][3][employee_key] - Im
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
    private final By tableRowListXpathBy = By.xpath("//*[@id=\"roster_form\"]/table/tbody/tr[@data-roster_row_iterator]");

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
     * We only need this in order to check, if we are logged in.
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
        if (localDate.format(Wrapper.DATE_TIME_FORMATTER_YEAR_MONTH_DAY).equals(getDateString())) {
            /**
             * We are on that date already. There is nothing to do:
             */
            return;
        }
        WebElement dateChooserInput;
        dateChooserInput = driver.findElement(dateChooserInputBy);
        Wrapper.fillDateInput(dateChooserInput, localDate);
        By dateChooserInputSendBy = By.xpath("/html/body/div[2]/div[3]/form/input[@name=\"tagesAuswahl\"]");
        WebElement dateChooserInputSendEement = driver.findElement(dateChooserInputSendBy);
        dateChooserInputSendEement.click();
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.presenceOfElementLocated(dateChooserInputBy));
        assertEquals(localDate.format(Wrapper.DATE_TIME_FORMATTER_YEAR_MONTH_DAY), getDateString());
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

    /**
     * @todo This method is called seven times from different other functions. Maybe call this once and inject the result into those methods.
     * @param employeeKey
     * @return
     */
    private WebElement findRosterTableRowByEmployee(String employeeFullName) {
        /**
         * Wir nutzen zwei By Variablen. Erst die tatsächlich gerade markierte
         * options finden. Dann parent elements finden.
         */
        By rowXpathOptionBy = By.xpath("/html/body/div/form[@id=\"roster_form\"]/table/tbody/tr/td[@class=\"roster_input_row\"]/span/select/option[@selected and text()=\"" + employeeFullName + "\"]");

        WebElement rosterTableRowOptionElement = driver.findElement(rowXpathOptionBy);
        By rowXpathBy = By.xpath("parent::select/parent::span/parent::td/parent::tr");
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
        By inputBy = By.xpath(".//td/span/select[contains(@name, \"employee_key\")]");
        WebElement rosterInputElement = rosterTableRow.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputDutyStart(String employeeFullName) {
        WebElement rosterTableRow = findRosterTableRowByEmployee(employeeFullName);
        By inputBy = By.xpath(".//td/input[contains(@name, \"duty_start_sql\")]");
        WebElement rosterInputElement = rosterTableRow.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputDutyStart(WebElement rosterTableRow) {
        By inputBy = By.xpath(".//td/input[contains(@name, \"duty_start_sql\")]");
        WebElement rosterInputElement = rosterTableRow.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputDutyEnd(String employeeFullName) {
        WebElement rosterTableRow = findRosterTableRowByEmployee(employeeFullName);
        By inputBy = By.xpath(".//td/input[contains(@name, \"duty_end_sql\")]");
        WebElement rosterInputElement = rosterTableRow.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputDutyEnd(WebElement rosterTableRow) {
        By inputBy = By.xpath(".//td/input[contains(@name, \"duty_end_sql\")]");
        WebElement rosterInputElement = rosterTableRow.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputBreakStart(String employeeFullName) {
        WebElement rosterTableRow = findRosterTableRowByEmployee(employeeFullName);
        By inputBy = By.xpath(".//td/input[contains(@name, \"break_start_sql\")]");
        WebElement rosterInputElement = rosterTableRow.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputBreakStart(WebElement rosterTableRow) {
        By inputBy = By.xpath(".//td/input[contains(@name, \"break_start_sql\")]");
        WebElement rosterInputElement = rosterTableRow.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputBreakEnd(String employeeFullName) {
        WebElement rosterTableRow = findRosterTableRowByEmployee(employeeFullName);
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
     * @param employeeKeyNew The id of the employee, who will be used as
     * substitute
     */
    private void changeRosterInputEmployee(WebElement rosterTableRow, Integer employeeKeyNew) {
        WebElement rosterInputEmployeeElement = findRosterInputEmployee(rosterTableRow);
        Select inputElementSelect = new Select(rosterInputEmployeeElement);
        String employeeKeyNewString = "";
        if (null != employeeKeyNew) {
            employeeKeyNewString = String.valueOf(employeeKeyNew);
        }
        inputElementSelect.selectByValue(employeeKeyNewString);
    }

    private void changeRosterInputDutyStart(WebElement rosterTableRow, String time) {
        WebElement rosterInputElement = findRosterInputDutyStart(rosterTableRow);
        rosterInputElement.clear();
        rosterInputElement.sendKeys(time);
    }

    private void changeRosterInputDutyEnd(WebElement rosterTableRow, String time) {
        WebElement rosterInputElement = findRosterInputDutyEnd(rosterTableRow);
        rosterInputElement.clear();
        rosterInputElement.sendKeys(time);
    }

    private void changeRosterInputBreakStart(WebElement rosterTableRow, String time) {
        WebElement rosterInputElement = findRosterInputBreakStart(rosterTableRow);
        rosterInputElement.clear();
        rosterInputElement.sendKeys(time);
    }

    private void changeRosterInputBreakEnd(WebElement rosterTableRow, String time) {
        WebElement rosterInputElement = findRosterInputBreakEnd(rosterTableRow);
        rosterInputElement.clear();
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
            return;
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
        this.changeRosterInputEmployee(rosterTableRow, rosterItem.getEmployeeKey());
        this.changeRosterInputDutyStart(rosterTableRow, rosterItem.getDutyStart());
        this.changeRosterInputDutyEnd(rosterTableRow, rosterItem.getDutyEnd());
        this.changeRosterInputBreakStart(rosterTableRow, rosterItem.getBreakStart());
        this.changeRosterInputBreakEnd(rosterTableRow, rosterItem.getBreakEnd());
        this.changeRosterInputComment(rosterTableRow, rosterItem.getComment());
        /**
         * @TODO: Wait until the element is successfully filled.
         */
    }

    public void rosterInputEditRow(RosterItem rosterItemOld, RosterItem rosterItemNew) {
        WebElement rosterTableRow = findRosterTableRowByEmployee(rosterItemOld.getEmployeeFullName());
        this.changeRosterInputEmployee(rosterTableRow, rosterItemNew.getEmployeeKey());
        this.changeRosterInputDutyStart(rosterTableRow, rosterItemNew.getDutyStart());
        this.changeRosterInputDutyEnd(rosterTableRow, rosterItemNew.getDutyEnd());
        this.changeRosterInputBreakStart(rosterTableRow, rosterItemNew.getBreakStart());
        this.changeRosterInputBreakEnd(rosterTableRow, rosterItemNew.getBreakEnd());
        this.changeRosterInputComment(rosterTableRow, rosterItemNew.getComment());
    }

    private Integer getRosterValueBranchId(String employeeFullName) {
        WebElement rosterTableRow = this.findRosterTableRowByEmployee(employeeFullName);
        int rosterValue = Integer.parseInt(rosterTableRow.getAttribute("data-branch_id"));
        return rosterValue;
    }

    private String getRosterValueDateString(String employeeFullName) {
        WebElement rosterTableRow = this.findRosterTableRowByEmployee(employeeFullName);
        String rosterValue = rosterTableRow.getAttribute("data-date_sql");
        return rosterValue;
    }

    private String getRosterValueDutyStart(String employeeFullName) {
        WebElement rosterInputElement = findRosterInputDutyStart(employeeFullName);
        String rosterValue = rosterInputElement.getAttribute("value");
        return rosterValue;
    }

    private String getRosterValueDutyEnd(String employeeFullName) {
        WebElement rosterInputElement = findRosterInputDutyEnd(employeeFullName);
        String rosterValue = rosterInputElement.getAttribute("value");
        return rosterValue;
    }

    private String getRosterValueBreakStart(String employeeFullName) {
        WebElement rosterInputElement = findRosterInputBreakStart(employeeFullName);
        String rosterValue = rosterInputElement.getAttribute("value");
        return rosterValue;
    }

    private String getRosterValueBreakEnd(String employeeFullName) {
        WebElement rosterInputElement = findRosterInputBreakEnd(employeeFullName);
        String rosterValue = rosterInputElement.getAttribute("value");
        return rosterValue;
    }

    public RosterItem getRosterItem(String employeeFullName) throws ParseException {
        RosterItem rosterItem;
        DateTimeFormatter dateTimeFormatterSql = DateTimeFormatter.ISO_LOCAL_DATE;
        String dateSql = this.getRosterValueDateString(employeeFullName);
        LocalDate localDateParsed = LocalDate.parse(dateSql, dateTimeFormatterSql);
        String dutyStart = getRosterValueDutyStart(employeeFullName);
        String dutyEnd = getRosterValueDutyEnd(employeeFullName);
        String breakStart = getRosterValueBreakStart(employeeFullName);
        String breakEnd = getRosterValueBreakEnd(employeeFullName);
        int branchId = getRosterValueBranchId(employeeFullName);
        String comment = null;//TODO; add comment
        rosterItem = new RosterItem(employeeFullName, localDateParsed, dutyStart, dutyEnd, breakStart, breakEnd, comment, branchId);
        return rosterItem;
    }

}
