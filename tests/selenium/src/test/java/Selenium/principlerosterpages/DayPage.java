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
import Selenium.PrincipleRosterItem;
import Selenium.driver.Wrapper;
import java.time.DayOfWeek;
import java.time.LocalDate;
import java.time.LocalTime;
import java.time.format.DateTimeFormatter;
import java.util.List;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.openqa.selenium.By;
import org.openqa.selenium.Keys;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.interactions.Actions;
import org.openqa.selenium.logging.LogEntries;
import org.openqa.selenium.logging.LogEntry;
import org.openqa.selenium.logging.LogType;
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
    private final By addRosterRowButtonBy = By.xpath("//*[contains(@id, \'roster_input_row_add_row_target_\')]");
    private final By copyAltenationButton = By.xpath("//form[@id=\"principle_roster_copy_form\"]/button");

    /**
     * TODO: Change the PHP to make the button more specific via class or id.
     *
     */
    private final By buttonSubmitBy = By.id("submit_button");

    public DayPage(WebDriver driver) {
        this.driver = driver;

        if (this.getUserNameText().isEmpty()) {
            throw new IllegalStateException(
                    "This is not a logged in state," + " current page is: " + driver.getCurrentUrl());
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

    public DayPage manageProfile() {
        // Page encapsulation to manage profile functionality
        return new DayPage(driver);
    }

    public void goToWeekday(int weekdayIndex) {
        WebElement weekdayChooserInput = driver.findElement(weekdayChooserInputBy);
        Select weekdayChooserSelect = new Select(weekdayChooserInput);
        weekdayChooserSelect.selectByValue(String.valueOf(weekdayIndex));
    }

    public void goToWeekday(DayOfWeek dayOfWeek) {
        WebElement weekdayChooserInput = driver.findElement(weekdayChooserInputBy);
        Select weekdayChooserSelect = new Select(weekdayChooserInput);
        weekdayChooserSelect.selectByValue(String.valueOf(dayOfWeek.getValue()));
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
        By listOfRosterRowsBy = By.xpath("/html/body/div[3]/form[@id=\"principle_roster_form\"]/table/tbody/tr/td");
        List<WebElement> listOfRosterRowElements = driver.findElements(listOfRosterRowsBy);
        int numberOfRosterRowElements = listOfRosterRowElements.size();
        WebElement addRosterRowButtonElement = driver.findElement(addRosterRowButtonBy);
        addRosterRowButtonElement.click();

        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.numberOfElementsToBe(listOfRosterRowsBy, (numberOfRosterRowElements + 1)));
    }

    public int getUnixTime() {
        /**
         * <p lang=de>
         * Es wird nur ein Tag dargestellt. Es ist daher egal, welcher
         * <td>ausgewählt wird. Das data-date_unix ist immer identisch.
         * </p>
         */
        By rosterTableColumnBy = By.xpath("//*[@id=\"principle_roster_form\"]/table/tbody/tr/td");
        WebElement rosterTableColumnElement = driver.findElement(rosterTableColumnBy);
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.presenceOfElementLocated(rosterTableColumnBy));

        String unixTimeString = rosterTableColumnElement.getAttribute("data-date_unix");
        return Integer.valueOf(unixTimeString);
    }

    private int getRosterValueBranchId() {
        WebElement rosterTableRow = this.findRosterTableFirstRow();
        int rosterValue = Integer.parseInt(rosterTableRow.getAttribute("data-branch_id"));
        return rosterValue;
    }

    private String getRosterValueDateString() {
        WebElement rosterTableRow = this.findRosterTableFirstRow();
        String rosterValue = rosterTableRow.getAttribute("data-date_sql");
        return rosterValue;
    }

    private String getRosterValueDutyStartInTableRow(WebElement rosterTableRow) {

        WebElement rosterInputElement = findRosterInputDutyStartInTableRow(rosterTableRow);
        String rosterValue = rosterInputElement.getAttribute("value");
        return rosterValue;
    }

    private String getRosterValueDutyEndInTabeRow(WebElement rosterTableRow) {
        WebElement rosterInputElement = findRosterInputDutyEndInTableRow(rosterTableRow);
        String rosterValue = rosterInputElement.getAttribute("value");
        return rosterValue;
    }

    private String getRosterValueBreakStartInTableRow(WebElement rosterTableRow) {
        WebElement rosterInputElement = findRosterInputBreakStartInTableRow(rosterTableRow);
        String rosterValue = rosterInputElement.getAttribute("value");
        return rosterValue;
    }

    private String getRosterValueBreakEndByEmployeeId(WebElement rosterTableRow) {
        WebElement rosterInputElement = findRosterInputBreakEndInTableRow(rosterTableRow);
        String rosterValue = rosterInputElement.getAttribute("value");
        return rosterValue;
    }

    public PrincipleRosterItem getRosterItemByEmployeeId(int employeeId) {
        DateTimeFormatter dateTimeFormatterSql = DateTimeFormatter.ISO_LOCAL_DATE;
        WebElement rosterTableRow = findRosterTableRowByEmloyeeId(employeeId);
        String dateSql = this.getRosterValueDateString();
        LocalDate localDate = LocalDate.parse(dateSql, dateTimeFormatterSql);

        String dutyStart = getRosterValueDutyStartInTableRow(rosterTableRow);
        String dutyEnd = getRosterValueDutyEndInTabeRow(rosterTableRow);
        String breakStart = getRosterValueBreakStartInTableRow(rosterTableRow);
        String breakEnd = getRosterValueBreakEndByEmployeeId(rosterTableRow);
        int branchId = getRosterValueBranchId();
        String comment = null; //TODO; add comment
        PrincipleRosterItem principleRosterItem = new PrincipleRosterItem(employeeId, localDate.getDayOfWeek(),
                LocalTime.parse(dutyStart, DateTimeFormatter.ISO_TIME),
                LocalTime.parse(dutyEnd, DateTimeFormatter.ISO_TIME),
                LocalTime.parse(breakStart, DateTimeFormatter.ISO_TIME),
                LocalTime.parse(breakEnd, DateTimeFormatter.ISO_TIME),
                comment, branchId);
        return principleRosterItem;
    }

    private WebElement findRosterTableFirstRow() {
        String rowXPath = "//*[@id=\"principle_roster_form\"]/table/tbody/tr/td";
        By rowBy = By.xpath(rowXPath);
        WebElement rosterTableRowElement = driver.findElement(rowBy);
        return rosterTableRowElement;
    }

    private WebElement findRosterTableRowByEmloyeeId(int employeeId) {
        /**
         * Wir brauchen zwei By Variablen. CSS kann tatsächlich gerade markierte
         * options finden. XPath kann parent elements finden.
         */
        By rowCssBy = By.cssSelector("#principle_roster_form > table > tbody > tr > td > span > select > option:checked[value=\"" + employeeId + "\"]");
        By rowXpathBy = By.xpath("parent::select/parent::span/parent::td");
        WebElement rosterTableRowOptionElement = driver.findElement(rowCssBy);
        WebElement rosterTableRowElement = rosterTableRowOptionElement.findElement(rowXpathBy);
        return rosterTableRowElement;
    }

    private WebElement findRosterInputEmployeeByEmployeeId(int employeeId) {
        WebElement tableRow = findRosterTableRowByEmloyeeId(employeeId);
        By inputBy = By.xpath(".//*[contains(@name, \"employee_id\")]");
        WebElement rosterInputElement = tableRow.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputDutyStartByEmployeeId(int employeeId) {
        WebElement rosterTableRow = findRosterTableRowByEmloyeeId(employeeId);
        By inputBy = By.xpath(".//*[contains(@name, \"duty_start_sql\")]");
        WebElement rosterInputElement = rosterTableRow.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputDutyStartInTableRow(WebElement rosterTableRow) {
        By inputBy = By.xpath(".//*[contains(@name, \"duty_start_sql\")]");
        WebElement rosterInputElement = rosterTableRow.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputDutyEndInTableRow(WebElement rosterTableRow) {
        By inputBy = By.xpath(".//*[contains(@name, \"duty_end_sql\")]");
        WebElement rosterInputElement = rosterTableRow.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputBreakStartInTableRow(WebElement rosterTableRow) {
        By inputBy = By.xpath(".//*[contains(@name, \"break_start_sql\")]");
        WebElement rosterInputElement = rosterTableRow.findElement(inputBy);
        return rosterInputElement;
    }

    private WebElement findRosterInputBreakEndInTableRow(WebElement rosterTableRow) {
        By inputBy = By.xpath(".//*[contains(@name, \"break_end_sql\")]");
        WebElement rosterInputElement = rosterTableRow.findElement(inputBy);
        return rosterInputElement;
    }

    public void changeRosterInputEmployee(int employeeIdOld, int employeeIdNew) {
        WebElement rosterInputEmployeeElement = findRosterInputEmployeeByEmployeeId(employeeIdOld);
        Select inputElementSelect = new Select(rosterInputEmployeeElement);
        inputElementSelect.selectByValue(String.valueOf(employeeIdNew));
    }

    public void changeRosterInputDutyStart(int employeeId, LocalTime time) {
        WebElement rosterInputElement = findRosterInputDutyStartByEmployeeId(employeeId);
        if (null == time) {
            /**
             * Delete the time from the form field:
             */
            rosterInputElement.sendKeys(Keys.DELETE);
            rosterInputElement.sendKeys(Keys.TAB);
            rosterInputElement.sendKeys(Keys.DELETE);
            return;
        }
        rosterInputElement.sendKeys(time.format(DateTimeFormatter.ofPattern("HH:mm")));
    }

    public void changeRosterInputDutyEnd(int employeeId, LocalTime time) {
        WebElement rosterTableRow = findRosterTableRowByEmloyeeId(employeeId);

        WebElement rosterInputElement = findRosterInputDutyEndInTableRow(rosterTableRow);
        if (null == time) {
            /**
             * Delete the time from the form field:
             */
            rosterInputElement.sendKeys(Keys.DELETE);
            rosterInputElement.sendKeys(Keys.TAB);
            rosterInputElement.sendKeys(Keys.DELETE);
            return;
        }
        rosterInputElement.sendKeys(time.format(DateTimeFormatter.ofPattern("HH:mm")));
    }

    public void changeRosterInputBreakStart(int employeeId, LocalTime time) {
        WebElement rosterTableRow = findRosterTableRowByEmloyeeId(employeeId);
        WebElement rosterInputElement = findRosterInputBreakStartInTableRow(rosterTableRow);
        rosterInputElement.sendKeys(time.format(DateTimeFormatter.ofPattern("HH:mm")));
    }

    public void changeRosterInputBreakEnd(int employeeId, LocalTime time) {
        WebElement rosterTableRow = findRosterTableRowByEmloyeeId(employeeId);
        WebElement rosterInputElement = findRosterInputBreakEndInTableRow(rosterTableRow);
        rosterInputElement.sendKeys(time.format(DateTimeFormatter.ofPattern("HH:mm")));
    }

    public void createNewRosterItem(PrincipleRosterItem rosterItem) {
        addRosterRow();
        By insertedRowBy = By.xpath("//*[@id=\"principle_roster_form\"]/table/tbody/tr[(last()-1)]/td");
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.presenceOfElementLocated(insertedRowBy));

        WebElement insertedRowElement = driver.findElement(insertedRowBy);
        /**
         * duty start:
         */
        By dutyStartInputBy = By.xpath(".//*[contains(@name, \"duty_start_sql\")]");
        wait.until(ExpectedConditions.presenceOfElementLocated(dutyStartInputBy));

        WebElement dutyStartRosterInputElement = insertedRowElement.findElement(dutyStartInputBy);
        dutyStartRosterInputElement.sendKeys(rosterItem.getDutyStart().format(DateTimeFormatter.ofPattern("HH:mm")));
        /**
         * duty end:
         */
        By dutyEndInputBy = By.xpath(".//*[contains(@name, \"duty_end_sql\")]");
        WebElement dutyEndRosterInputElement = insertedRowElement.findElement(dutyEndInputBy);
        dutyEndRosterInputElement.sendKeys(rosterItem.getDutyEnd().format(DateTimeFormatter.ofPattern("HH:mm")));
        /**
         * break start:
         */
        By breakStartInputBy = By.xpath(".//*[contains(@name, \"break_start_sql\")]");
        WebElement breakStartRosterInputElement = insertedRowElement.findElement(breakStartInputBy);
        breakStartRosterInputElement.sendKeys(rosterItem.getBreakStart().format(DateTimeFormatter.ofPattern("HH:mm")));
        /**
         * break end:
         */
        By breakEndInputBy = By.xpath(".//*[contains(@name, \"break_end_sql\")]");
        WebElement breakEndRosterInputElement = insertedRowElement.findElement(breakEndInputBy);
        breakEndRosterInputElement.sendKeys(rosterItem.getBreakEnd().format(DateTimeFormatter.ofPattern("HH:mm")));
        /**
         * employee:
         */
        By employeeInputBy = By.xpath(".//*[contains(@name, \"employee_id\")]");
        WebElement employeeRosterInputElement = insertedRowElement.findElement(employeeInputBy);
        Select empoyeeInputElementSelect = new Select(employeeRosterInputElement);
        empoyeeInputElementSelect.selectByValue(String.valueOf(rosterItem.getEmployeeId()));
        /**
         * Get log from console.log() inside the browser:
         */
        //Wrapper.printBrowserConsole();
        rosterFormSubmit();
    }

    public void rosterFormSubmit() {
        WebElement buttonSubmitElement = driver.findElement(buttonSubmitBy);
        buttonSubmitElement.click();
    }

    private WebElement getRosterPlotDutyElementByEmployeeId(int employeeId) {
        By elementBy = By.xpath("//*[contains(@id, \"work_box_\") and @data-employee_id=\"" + employeeId + "\"]/p");
        WebElement element = driver.findElement(elementBy);
        return element;
    }

    private WebElement getRosterPlotBreakElementByEmployeeId(int employeeId) {
        By elementBy = By.xpath("//*[contains(@id, \"break_box_\") and @data-employee_id=\"" + employeeId + "\"]");
        WebElement element = driver.findElement(elementBy);
        return element;
    }

    public void changeRosterByDragAndDrop(int unixTime, int employeeId, double offsetMinutes, String dutyOrBreak) throws Exception {
        PrincipleRosterItem rosterItemReadBefore = getRosterItemByEmployeeId(employeeId);
        /**
         * @todo
         * <p lang=de>
         * Ich bin nicht sicher, wie man am besten den Zusammenhang zwischen
         * Pixel und Minuten regeln sollte.
         * </p>
         */
        WebElement rosterPlotElement;
        double barWidthFactor = getPlotDataBarWidthFactor();
        double offsetPixelsDouble = ((offsetMinutes / 60) * barWidthFactor * 1.30);
        int offsetPixels = (int) Math.round(offsetPixelsDouble);
        if ("duty".equals(dutyOrBreak)) {
            rosterPlotElement = getRosterPlotDutyElementByEmployeeId(employeeId);
        } else if ("break".equals(dutyOrBreak)) {
            rosterPlotElement = getRosterPlotBreakElementByEmployeeId(employeeId);
        } else {
            String message = "dutyOrBreak must be duty or break" + dutyOrBreak + "given.";
            System.err.println(message);
            throw new Exception(message);
        }
        Actions actions = new Actions(driver);
        /**
         * The factor 0.9 is experimental. An element of width 400 was only
         * 377,91 px.
         */
        double elementOffsetDouble = -1 * ((rosterPlotElement.getSize().getWidth() - 5) / 2) * 0.9;
        int elementOffset = (int) Math.round(elementOffsetDouble);
        actions.moveToElement(rosterPlotElement, elementOffset, 0).build().perform();
        actions.clickAndHold().build().perform();
        actions.moveByOffset(offsetPixels, 0).build().perform();
        actions.release().build().perform();
        /**
         * The release does not seem to work properly. Move the mouse once more:
         */
        actions.moveToElement(rosterPlotElement, elementOffset, 0).build().perform();
        Thread.sleep(5000);
        WebDriverWait wait = new WebDriverWait(driver, 20);
        /**
         * CAVE! Wir bräuchten eigentlich zwei By Variablen. CSS kann
         * tatsächlich gerade markierte options finden. XPath kann parent
         * elements finden. Wenn wir nur XPath verwenden, ist nicht zwingend die
         * AKTUELL SELEKTIERTE option ausgewählt.
         */
        if ("duty".equals(dutyOrBreak)) {
            By dutyStartInputBy = By.xpath("//*[@id=\"principle_roster_form\"]/table/tbody/tr/td/span/select/option[@value=\"" + employeeId + "\" and @selected]/parent::select/parent::span/parent::td/input[contains(@name, \"duty_start_sql\")]");
            By dutyEndInputBy = By.xpath("//*[@id=\"principle_roster_form\"]/table/tbody/tr/td/span/select/option[@value=\"" + employeeId + "\" and @selected]/parent::select/parent::span/parent::td/input[contains(@name, \"duty_end_sql\")]");
            wait.until(ExpectedConditions.attributeToBe(dutyStartInputBy, "value", rosterItemReadBefore.getDutyStart().plusMinutes((long) offsetMinutes).format(DateTimeFormatter.ofPattern("HH:mm"))));
            wait.until(ExpectedConditions.attributeToBe(dutyEndInputBy, "value", rosterItemReadBefore.getDutyEnd().plusMinutes((long) offsetMinutes).format(DateTimeFormatter.ofPattern("HH:mm"))));
        } else if ("break".equals(dutyOrBreak)) {
            By breakStartInputBy = By.xpath("//*[@id=\"principle_roster_form\"]/table/tbody/tr/td/span/select/option[@value=\"" + employeeId + "\" and @selected]/parent::select/parent::span/parent::td/input[contains(@name, \"break_start_sql\")]");
            By breakEndInputBy = By.xpath("//*[@id=\"principle_roster_form\"]/table/tbody/tr/td/span/select/option[@value=\"" + employeeId + "\" and @selected]/parent::select/parent::span/parent::td/input[contains(@name, \"break_end_sql\")]");
            wait.until(ExpectedConditions.attributeToBe(breakStartInputBy, "value", rosterItemReadBefore.getBreakStart().plusMinutes((long) offsetMinutes).format(DateTimeFormatter.ofPattern("HH:mm"))));
            wait.until(ExpectedConditions.attributeToBe(breakEndInputBy, "value", rosterItemReadBefore.getBreakEnd().plusMinutes((long) offsetMinutes).format(DateTimeFormatter.ofPattern("HH:mm"))));
        } else {
            System.err.println("fail");
            String message = "dutyOrBreak must be duty or break" + dutyOrBreak + "given.";
            throw new Exception(message);
        }
    }

    private int getPlotDataBarWidthFactor() {
        By svgImageBy = By.xpath("//*[@id=\"main-area\"]/div/div/*[name()=\"svg\"]");
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.presenceOfElementLocated(svgImageBy));
        WebElement svgImageElement = driver.findElement(svgImageBy);
        String barWidthFactorString = svgImageElement.getAttribute("data-bar_width_factor");
        return Integer.valueOf(barWidthFactorString);
    }

    public void copyAlternation() {
        WebElement copyAlternationButtonElement = driver.findElement(copyAltenationButton);
        copyAlternationButtonElement.click();
    }
}
