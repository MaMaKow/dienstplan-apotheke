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
import Selenium.NetworkOfBranchOffices;
import Selenium.RosterItem;
import Selenium.driver.FileAvailabilityChecker;
import Selenium.driver.Wrapper;
import static Selenium.rosterpages.RosterWeekTablePage.driver;
import java.io.File;
import java.text.ParseException;
import java.time.DayOfWeek;
import java.time.LocalDate;
import java.time.Year;
import java.time.format.DateTimeFormatter;
import java.util.Locale;
import java.util.logging.Level;
import java.util.logging.Logger;
import junit.framework.Assert;
import org.openqa.selenium.By;
import org.openqa.selenium.Keys;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;

/**
 *
 * @author Mandelkow
 */
public class RosterEmployeePage {

    protected static WebDriver driver;

    private final By userNameSpanBy = By.id("MenuListItemApplicationUsername");
    private final By dateChooserInputBy = By.id("date_chooser_input");
    private final By buttonWeekBackwardBy = By.id("button_week_backward");
    private final By buttonWeekForwardBy = By.id("button_week_forward");
    private final By employeeFormSelectBy = By.xpath("/html/body/div/form[@id='select_employee']/select");

    private final File downloadedICalendarFile = new File("/tmp/selenium/shared_downloads/Calendar.ics");

    public RosterEmployeePage(WebDriver driver) {
        this.driver = driver;

        if (this.getUserNameText().isEmpty()) {
            throw new IllegalStateException("This is not a logged in state,"
                    + " current page is: " + driver.getCurrentUrl());
        }
        MenuFragment.navigateTo(driver, MenuFragment.MenuLinkToRosterEmployee);
    }

    /**
     * Get user_name (span tag)
     *
     * @return String user_name text
     */
    public String getUserNameText() {
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.presenceOfElementLocated(userNameSpanBy));
        return driver.findElement(userNameSpanBy).getText();
    }

    public RosterEmployeePage manageProfile() {
        // Page encapsulation to manage profile functionality
        return new RosterEmployeePage(driver);
    }

    public RosterEmployeePage goToDate(LocalDate localDate) {
        String dateString = localDate.format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
        return goToDate(dateString);
    }

    public RosterEmployeePage goToDate(String date) {
        if (date.equals(getDate())) {
            return this;
        }
        WebElement dateChooserInput = driver.findElement(dateChooserInputBy);
        Wrapper.fillDateInput(dateChooserInput, date);
        By dateChooserSubmitBy = By.xpath("//*[@name=\"tagesAuswahl\"]");
        WebElement dateChooserSubmit = driver.findElement(dateChooserSubmitBy);
        dateChooserSubmit.click();
        return new RosterEmployeePage(driver);
    }

    public String getDate() {
        WebElement dateChooserInput = driver.findElement(dateChooserInputBy);
        String date_value = dateChooserInput.getAttribute("value");
        return date_value;
    }

    /**
     *
     * @return localDate The localDate of the Monday in the selected week.
     */
    public LocalDate getLocalDate() {
        String dateString = getDate();
        LocalDate localDate = LocalDate.parse(dateString, Wrapper.DATE_TIME_FORMATTER_YEAR_MONTH_DAY);
        return localDate;
    }

    public RosterEmployeePage moveWeekBackward() {
        WebElement button_week_backward = driver.findElement(buttonWeekBackwardBy);
        button_week_backward.click();
        return new RosterEmployeePage(driver);
    }

    public RosterEmployeePage moveWeekForward() {
        WebElement button_week_forward = driver.findElement(buttonWeekForwardBy);
        button_week_forward.click();
        return new RosterEmployeePage(driver);
    }

    public RosterEmployeePage selectEmployee(int employeeKey) {
        Select employeeFormSelect = new Select(driver.findElement(employeeFormSelectBy));
        employeeFormSelect.selectByValue(String.valueOf(employeeKey));
        return new RosterEmployeePage(driver);
    }

    public int getEmployeeKey() {
        Select employeeFormSelect = new Select(driver.findElement(employeeFormSelectBy));
        int employeeKey = Integer.parseInt(employeeFormSelect.getFirstSelectedOption().getAttribute("value"));
        return employeeKey;
    }

    public String getEmployeeLastName() {
        Select employeeFormSelect = new Select(driver.findElement(employeeFormSelectBy));
        String[] employeeNameArray = employeeFormSelect.getFirstSelectedOption().getText().split("[0-9]*");
        String employeeName = employeeNameArray[1].trim();
        return employeeName;
    }

    public String getEmployeeFullName() {
        Select employeeFormSelect = new Select(driver.findElement(employeeFormSelectBy));
        String employeeFullName = employeeFormSelect.getFirstSelectedOption().getText();
        return employeeFullName;
    }

    private By getRosterItemDateXpathBy(int column) {
        By rosterItemEmployeeKeyXpathBy = By.xpath("/html/body/div/table/thead/tr/td[" + column + "]/a");
        return rosterItemEmployeeKeyXpathBy;
    }

    private By getRosterItemDutyStartXpathBy(int column, int row) {
        By rosterItemDutyStartXpathBy = By.xpath("//table/tbody/tr[" + row + "]/td[" + column + "]/span[@class=\'duty-time\']/span[1]");
        return rosterItemDutyStartXpathBy;
    }

    private By getRosterItemDutyEndXpathBy(int column, int row) {
        By rosterItemDutyEndXpathBy = By.xpath("//table/tbody/tr[" + row + "]/td[" + column + "]/span[contains(@class, \'duty-time\')]/span[2]");
        return rosterItemDutyEndXpathBy;
    }

    private By getRosterItemBreakStartXpathBy(int column, int row) {
        By rosterItemBreakStartXpathBy = By.xpath("//table/tbody/tr[" + row + "]/td[" + column + "]/span[contains(@class, \'break-time\')]/span[1]");
        return rosterItemBreakStartXpathBy;
    }

    private By getRosterItemBreakEndXpathBy(int column, int row) {
        By rosterItemBreakEndXpathBy = By.xpath("//table/tbody/tr[" + row + "]/td[" + column + "]/span[contains(@class, \'break-time\')]/span[2]");
        return rosterItemBreakEndXpathBy;
    }

    private By getRosterItemBranchNameXpathBy(int column, int row) {
        By rosterItemBreakEndXpathBy = By.xpath("//table/tbody/tr[" + row + "]/td[" + column + "]/span[contains(@class, 'branch_name')]");
        return rosterItemBreakEndXpathBy;
    }

    public RosterItem getRosterItem(DayOfWeek dayOfWeek) throws ParseException {
        return getRosterItem(dayOfWeek.getValue(), 1);
    }

    public RosterItem getRosterItem(int column, int row) throws ParseException {
        String employeeFullName = getEmployeeFullName();
        /**
         * We will need a year to correctly parse the date:
         */
        LocalDate localMondayDate = getLocalDate();
        LocalDate actualLocalDate = localMondayDate.plusDays(column - 1);
        int currentYear = actualLocalDate.getYear();
        /**
         * Find the roster values in the table data:
         */
        String dateString = driver.findElement(getRosterItemDateXpathBy(column)).getText();
        String dutyStart = driver.findElement(getRosterItemDutyStartXpathBy(column, row)).getText();
        String dutyEnd = driver.findElement(getRosterItemDutyEndXpathBy(column, row)).getText();
        String breakStart = driver.findElement(getRosterItemBreakStartXpathBy(column, row)).getText();
        String breakEnd = driver.findElement(getRosterItemBreakEndXpathBy(column, row)).getText();
        String branchName = driver.findElement(getRosterItemBranchNameXpathBy(column, row)).getText();

        NetworkOfBranchOffices networkOfBranchOffices = new NetworkOfBranchOffices();
        int branchId = networkOfBranchOffices.getBranchByName(branchName).getBranchId();
        String comment = null;

        DateTimeFormatter dateTimeFormatter = DateTimeFormatter.ofPattern("eeee dd.MM.yyyy", Locale.GERMANY);
        LocalDate localDate = LocalDate.parse(dateString + currentYear, dateTimeFormatter);
        RosterItem rosterItem = new RosterItem(employeeFullName, localDate, dutyStart, dutyEnd, breakStart, breakEnd, comment, branchId);
        return rosterItem;
    }

    public File downloadICSFile() {
        downloadedICalendarFile.delete();
        By downloadButtonBy = By.xpath("//*[@id=\"download_ics_file_form\"]/button");
        WebElement downloadButtonElement = driver.findElement(downloadButtonBy);
        downloadButtonElement.click();
        try {
            FileAvailabilityChecker.waitForFileAvailability(downloadedICalendarFile);
        } catch (Exception exception) {
            Logger.getLogger(RosterEmployeePage.class.getName()).log(Level.SEVERE, null, exception);
            Assert.fail(exception.getMessage());
        }
        return downloadedICalendarFile;
    }

    public void deleteICSFile() {
        downloadedICalendarFile.delete();
    }

}
