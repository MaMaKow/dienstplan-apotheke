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

import Selenium.MenuFragment;
import Selenium.RosterItem;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;
import java.util.Locale;
import java.util.logging.Level;
import java.util.logging.Logger;
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

    public RosterEmployeePage goToDate(String date) {
        WebElement dateChooserInput = driver.findElement(dateChooserInputBy);
        dateChooserInput.sendKeys(date);
        dateChooserInput.sendKeys(Keys.ENTER);
        return new RosterEmployeePage(driver);
    }

    public String getDate() {
        WebElement dateChooserInput = driver.findElement(dateChooserInputBy);
        String date_value = dateChooserInput.getAttribute("value");
        return date_value;
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

    public RosterEmployeePage selectEmployee(int employeeId) {
        Select employeeFormSelect = new Select(driver.findElement(employeeFormSelectBy));
        employeeFormSelect.selectByValue(String.valueOf(employeeId));
        return new RosterEmployeePage(driver);
    }

    public int getEmployeeId() {
        Select employeeFormSelect = new Select(driver.findElement(employeeFormSelectBy));
        int employeeId = Integer.parseInt(employeeFormSelect.getFirstSelectedOption().getAttribute("value"));
        return employeeId;
    }

    public String getEmployeeName() {
        Select employeeFormSelect = new Select(driver.findElement(employeeFormSelectBy));
        String[] employeeNameArray = employeeFormSelect.getFirstSelectedOption().getText().split("[0-9]*");
        String employeeName = employeeNameArray[1].trim();
        return employeeName;
    }

    private By getRosterItemDateXpathBy(int column) {
        By rosterItemEmployeeIdXpathBy = By.xpath("/html/body/div/table/thead/tr/td[" + column + "]/a");
        return rosterItemEmployeeIdXpathBy;
    }

    private By getRosterItemDutyStartXpathBy(int column, int row) {
        By rosterItemDutyStartXpathBy = By.xpath("//table/tbody/tr[" + row + "]/td[" + column + "]/span[@class=\'duty_time\']/span[1]");
        return rosterItemDutyStartXpathBy;
    }

    private By getRosterItemDutyEndXpathBy(int column, int row) {
        By rosterItemDutyEndXpathBy = By.xpath("//table/tbody/tr[" + row + "]/td[" + column + "]/span[contains(@class, \'duty_time\')]/span[2]");
        return rosterItemDutyEndXpathBy;
    }

    private By getRosterItemBreakStartXpathBy(int column, int row) {
        By rosterItemBreakStartXpathBy = By.xpath("//table/tbody/tr[" + row + "]/td[" + column + "]/span[contains(@class, \'break_time\')]/span[1]");
        return rosterItemBreakStartXpathBy;
    }

    private By getRosterItemBreakEndXpathBy(int column, int row) {
        By rosterItemBreakEndXpathBy = By.xpath("//table/tbody/tr[" + row + "]/td[" + column + "]/span[contains(@class, \'break_time\')]/span[2]");
        return rosterItemBreakEndXpathBy;
    }

    private By getRosterItemBranchNameXpathBy(int column, int row) {
        By rosterItemBreakEndXpathBy = By.xpath("//table/tbody/tr[" + row + "]/td[" + column + "]/span[contains(@class, 'branch_name')]");
        return rosterItemBreakEndXpathBy;
    }

    public RosterItem getRosterItem(int column, int row) throws ParseException {

        String employeeName = getEmployeeName();
        String dateString = driver.findElement(getRosterItemDateXpathBy(column)).getText();
        String dutyStart = driver.findElement(getRosterItemDutyStartXpathBy(column, row)).getText();
        String dutyEnd = driver.findElement(getRosterItemDutyEndXpathBy(column, row)).getText();
        String breakStart = driver.findElement(getRosterItemBreakStartXpathBy(column, row)).getText();
        String breakEnd = driver.findElement(getRosterItemBreakEndXpathBy(column, row)).getText();
        String branchName = driver.findElement(getRosterItemBranchNameXpathBy(column, row)).getText();
        //comment = "";
        /**
         * <p>
         * TODO: Die Locale könnte auch eine Konfigurationsvariable sein.
         * Locale.GERMANY <-> Locale.ENGLISH
         * </p>
         */
        Date dateParsed = new SimpleDateFormat("EE dd.MM.", Locale.GERMANY).parse(dateString);
        Calendar calendar = Calendar.getInstance();
        calendar.setTime(dateParsed);

        RosterItem rosterItem = new Selenium.RosterItem(employeeName, calendar, dutyStart, dutyEnd, breakStart, breakEnd, branchName);
        return rosterItem;
    }

    public void downloadICSFile() {
        //By downloadButtonBy = By.xpath("/html/body/div[2]/form[@id=download_ics_file_form]/button");
        By downloadButtonBy = By.xpath("//*[@id=\"download_ics_file_form\"]/button");
        WebElement downloadButtonElement = driver.findElement(downloadButtonBy);
        downloadButtonElement.click();
        try {
            Thread.sleep(5000);
        } catch (InterruptedException ex) {
            Logger.getLogger(RosterEmployeePage.class.getName()).log(Level.SEVERE, null, ex);
        }
    }

}
