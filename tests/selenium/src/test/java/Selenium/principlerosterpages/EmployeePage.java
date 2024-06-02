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
package Selenium.principlerosterpages;

import Selenium.MenuFragment;
import Selenium.RosterItem;
import java.text.ParseException;
import java.time.DayOfWeek;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.HashSet;
import java.util.List;
import java.util.Locale;
import java.util.Set;
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
public class EmployeePage {

    protected static WebDriver driver;
    private final By userNameSpanBy = By.id("MenuListItemApplicationUsername");
    //private final By employeeFormSelectBy = By.xpath("/html/body/div/form[@id='select_employee']/select");
    private final By employeeFormSelectBy = By.xpath("//form[@id='select_employee']/select");

    public EmployeePage(WebDriver driver) {
        this.driver = driver;

        if (this.getUserNameText().isEmpty()) {
            throw new IllegalStateException(
                    "This is not a logged in state," + " current page is: " + driver.getCurrentUrl());
        }
        MenuFragment.navigateTo(driver, MenuFragment.MenuLinkToPrincipleRosterEmployee);
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

    public EmployeePage manageProfile() {
        // Page encapsulation to manage profile functionality
        return new EmployeePage(driver);
    }

    public void selectEmployee(int employeeKey) {
        Select employeeFormSelect = new Select(driver.findElement(employeeFormSelectBy));
        employeeFormSelect.selectByValue(String.valueOf(employeeKey));
    }

    public void selectEmployeeByFullName(String fullName) {
        Select employeeFormSelect = new Select(driver.findElement(employeeFormSelectBy));
        employeeFormSelect.selectByVisibleText(fullName);
    }

    public int getEmployeeKey() {
        Select employeeFormSelect = new Select(driver.findElement(employeeFormSelectBy));
        int employeeKey = Integer.parseInt(employeeFormSelect.getFirstSelectedOption().getAttribute("value"));
        return employeeKey;
    }

    public String getEmployeeLastName() {
        WebElement employeeFormSelectElement = driver.findElement(employeeFormSelectBy);
        Select employeeFormSelect = new Select(employeeFormSelectElement);
        String[] employeeNameArray = employeeFormSelect.getFirstSelectedOption().getText().split(" ");
        String employeeName = employeeNameArray[1].trim();
        return employeeName;
    }

    public String getEmployeeFullName() {
        WebElement employeeFormSelectElement = driver.findElement(employeeFormSelectBy);
        Select employeeFormSelect = new Select(employeeFormSelectElement);
        String employeeFullName = employeeFormSelect.getFirstSelectedOption().getText();
        return employeeFullName;
    }

    public void submitForm(int alternationId) {
        String formPartString = "change_principle_roster_employee_form_" + String.valueOf(alternationId);
        By submitButtonBy = By.xpath("//*[contains(@form, '" + formPartString + "')]");
        WebElement submitButtonElement = driver.findElement(submitButtonBy);
        submitButtonElement.click();
    }

    private By getRosterItemColumnXpathBy(int alternationId, int column) {
        String alternationString = String.valueOf(alternationId + 1);
        By rosterItemColumnXpathBy = By.xpath("/html/body/div[2]/div[" + alternationString + "]/form/table/tbody/tr/td[" + column + "]");
        return rosterItemColumnXpathBy;
    }

    private By getRosterItemWeekdayXpathBy(int column) {
        By rosterItemEmployeeKeyXpathBy = By.xpath("/html/body/div/table/thead/tr/td[" + column + "]");
        return rosterItemEmployeeKeyXpathBy;
    }

    private By getRosterItemDutyStartXpathBy(int alternationId, int column, Integer row) {
        String alternationString = String.valueOf(alternationId + 1);
        By rosterItemDutyStartXpathBy = By.xpath("//html/body/div[2]/div[" + alternationString + "]/form/table/tbody/tr[" + row + "]/td[" + column + "]/input[contains(@name, 'duty_start_sql')]");
        return rosterItemDutyStartXpathBy;
    }

    private By getRosterItemDutyEndXpathBy(int alternationId, int column, int row) {
        String alternationString = String.valueOf(alternationId + 1);
        By rosterItemDutyEndXpathBy = By.xpath("//html/body/div[2]/div[" + alternationString + "]/form/table/tbody/tr[" + row + "]/td[" + column + "]/input[contains(@name, 'duty_end_sql')]");
        return rosterItemDutyEndXpathBy;
    }

    private By getRosterItemBreakStartXpathBy(int alternationId, int column, int row) {
        String alternationString = String.valueOf(alternationId + 1);
        By rosterItemBreakStartXpathBy = By.xpath("//html/body/div[2]/div[" + alternationString + "]/form/table/tbody/tr[" + row + "]/td[" + column + "]/input[contains(@name, 'break_start_sql')]");
        return rosterItemBreakStartXpathBy;
    }

    private By getRosterItemBreakEndXpathBy(int alternationId, int column, int row) {
        String alternationString = String.valueOf(alternationId + 1);
        By rosterItemBreakEndXpathBy = By.xpath("//html/body/div[2]/div[" + alternationString + "]/form/table/tbody/tr[" + row + "]/td[" + column + "]/input[contains(@name, 'break_end_sql')]");
        return rosterItemBreakEndXpathBy;
    }

    private By getRosterItemBranchNameXpathBy(int alternationId, int column, int row) {
        String alternationString = String.valueOf(alternationId + 1);
        By rosterItemBreakEndXpathBy = By.xpath("//html/body/div[2]/div[" + alternationString + "]/form/table/tbody/tr[" + row + "]/td[" + column + "]/select[contains(@name, 'branch_id')]");
        return rosterItemBreakEndXpathBy;
    }

    public RosterItem getRosterItem(int alternationId, int column, int row) throws ParseException {

        String employeeFullName = getEmployeeFullName();
        WebElement rosterItemColumn = driver.findElement(getRosterItemColumnXpathBy(alternationId, column));
        String dateString = rosterItemColumn.getAttribute("data-date_sql");
        String dutyStart = driver.findElement(getRosterItemDutyStartXpathBy(alternationId, column, row)).getAttribute("value");
        String dutyEnd = driver.findElement(getRosterItemDutyEndXpathBy(alternationId, column, row)).getAttribute("value");
        String breakStart = driver.findElement(getRosterItemBreakStartXpathBy(alternationId, column, row)).getAttribute("value");
        String breakEnd = driver.findElement(getRosterItemBreakEndXpathBy(alternationId, column, row)).getAttribute("value");
        Select branchNameSelect = new Select(driver.findElement(getRosterItemBranchNameXpathBy(alternationId, column, row)));
        //String branchName = branchNameSelect.getFirstSelectedOption().getText();
        int branchId = Integer.parseInt(branchNameSelect.getFirstSelectedOption().getAttribute("value"));
        String comment = null; //TODO: add comment
        LocalDate localDate = LocalDate.parse(dateString, DateTimeFormatter.ISO_LOCAL_DATE);
        RosterItem rosterItem = new Selenium.RosterItem(employeeFullName, localDate, dutyStart, dutyEnd, breakStart, breakEnd, comment, branchId);
        return rosterItem;
    }

    public HashSet<RosterItem> getSetOfRosterItems(int alternationId, DayOfWeek dayOfWeek) {
        HashSet<RosterItem> setOfRosterItems = new HashSet();
        dayOfWeek.getValue();
        String employeeFullName = getEmployeeFullName();
        WebElement rosterItemColumn = driver.findElement(getRosterItemColumnXpathBy(alternationId, dayOfWeek.getValue()));
        String dateString = rosterItemColumn.getAttribute("data-date_sql");

        List<WebElement> listOfTableCells = getTableCellElements(alternationId, dayOfWeek);
        listOfTableCells.forEach(tableCellElement -> {
            String dutyStart = tableCellElement.findElement(By.xpath(".//input[contains(@name, 'duty_start_sql')]")).getAttribute("value");
            String dutyEnd = tableCellElement.findElement(By.xpath(".//input[contains(@name, 'duty_end_sql')]")).getAttribute("value");
            String breakStart = tableCellElement.findElement(By.xpath(".//input[contains(@name, 'break_start_sql')]")).getAttribute("value");
            String breakEnd = tableCellElement.findElement(By.xpath(".//input[contains(@name, 'break_end_sql')]")).getAttribute("value");
            Select branchNameSelect = new Select(tableCellElement.findElement(By.xpath(".//select[contains(@name, 'branch_id')]")));
            int branchId = Integer.parseInt(branchNameSelect.getFirstSelectedOption().getAttribute("value"));
            LocalDate localDate = LocalDate.parse(dateString, DateTimeFormatter.ISO_LOCAL_DATE);
            String comment = null; //TODO: add comment
            RosterItem rosterItem = new Selenium.RosterItem(employeeFullName, localDate, dutyStart, dutyEnd, breakStart, breakEnd, comment, branchId);
            setOfRosterItems.add(rosterItem);
        });
        return setOfRosterItems;
    }

    private List<WebElement> getTableCellElements(int alternationId, DayOfWeek dayOfWeek) {
        /**
         * <p lang=de>Erst einmal suchen wir die richtige Tabelle: Für jede
         * alernationId gibt es eine eigene.</p>
         */
        String alternationString = String.valueOf(alternationId + 1);
        By rosterItemTableXpathBy = By.xpath("//html/body/div[2]/div[" + alternationString + "]/form/table/tbody");
        WebElement rosterItemTableElement = driver.findElement(rosterItemTableXpathBy);
        /**
         * <p lang=de>In der Tabelle wollen wir nur Zellen, die zum Wochentag
         * passen.</p>
         */
        List<WebElement> listOfTableCells = rosterItemTableElement.findElements(By.xpath(".//tr/td[" + dayOfWeek.getValue() + "]"));
        return listOfTableCells;
    }

    public void setRosterItem(int alternationId, int column, int row, RosterItem rosterItem) {
        driver.findElement(getRosterItemDutyStartXpathBy(alternationId, column, row)).sendKeys(rosterItem.getDutyStart());
        driver.findElement(getRosterItemDutyEndXpathBy(alternationId, column, row)).sendKeys(rosterItem.getDutyEnd());
        driver.findElement(getRosterItemBreakStartXpathBy(alternationId, column, row)).sendKeys(rosterItem.getBreakStart());
        driver.findElement(getRosterItemBreakEndXpathBy(alternationId, column, row)).sendKeys(rosterItem.getBreakEnd());
        Select branchNameSelect = new Select(driver.findElement(getRosterItemBranchNameXpathBy(alternationId, column, row)));
        branchNameSelect.selectByValue(String.valueOf(rosterItem.getBranchId()));
        submitForm(alternationId);
    }

}
