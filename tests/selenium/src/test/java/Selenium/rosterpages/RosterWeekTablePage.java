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
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
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
 * @author Mandelkow Page Object encapsulates the Home Page
 */
public class RosterWeekTablePage {

    protected static WebDriver driver;

    private final By userNameSpanBy = By.id("MenuListItemApplicationUsername");
    private final By dateChooserInputBy = By.id("date_chooser_input");
    private final By buttonWeekBackwardBy = By.id("button_week_backward");
    private final By buttonWeekForwardBy = By.id("button_week_forward");
    private final By branchFormSelectBy = By.id("branch_form_select");

    //private final By dutyRosterTableBy = By.id("duty_roster_table");
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
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.presenceOfElementLocated(userNameSpanBy));
        return driver.findElement(userNameSpanBy).getText();
    }

    public RosterWeekTablePage manageProfile() {
        // Page encapsulation to manage profile functionality
        return new RosterWeekTablePage(driver);
    }

    public RosterWeekTablePage goToDate(String date) {
        WebElement dateChooserInput = driver.findElement(dateChooserInputBy);
        dateChooserInput.sendKeys(date);
        dateChooserInput.sendKeys(Keys.ENTER);
        return new RosterWeekTablePage(driver);
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

    private By getRosterItemEmployeeIdXpathBy(int column, int row) {
        By rosterItemEmployeeIdXpathBy = By.xpath("/html/body/div[4]/div[4]/table/tbody/tr[" + row + "]/td[" + column + "]/span[1]/span[1]/b/a");
        return rosterItemEmployeeIdXpathBy;
    }

    private By getRosterItemDateXpathBy(int column) {
        By rosterItemEmployeeIdXpathBy = By.xpath("/html/body/div[4]/div[4]/table/thead/tr/td[" + column + "]/a");
        return rosterItemEmployeeIdXpathBy;
    }

    private By getRosterItemDutyStartXpathBy(int column, int row) {
        By rosterItemDutyStartXpathBy = By.xpath("//table[@id=\'duty_roster_table\']/tbody/tr[" + row + "]/td[" + column + "]/span[@class=\'employee_and_hours_and_duty_time\']/span[@class=\'duty_time\']/span[1]");
        return rosterItemDutyStartXpathBy;
    }

    private By getRosterItemDutyEndXpathBy(int column, int row) {
        By rosterItemDutyEndXpathBy = By.xpath("//table[@id=\'duty_roster_table\']/tbody/tr[" + row + "]/td[" + column + "]/span[@class=\'employee_and_hours_and_duty_time\']/span[@class=\'duty_time\']/span[2]");
        return rosterItemDutyEndXpathBy;
    }

    private By getRosterItemBreakStartXpathBy(int column, int row) {
        By rosterItemBreakStartXpathBy = By.xpath("//table[@id=\'duty_roster_table\']/tbody/tr[" + row + "]/td[" + column + "]/span[@class=\'break_time\']/span[1]");
        return rosterItemBreakStartXpathBy;
    }

    private By getRosterItemBreakEndXpathBy(int column, int row) {
        By rosterItemBreakEndXpathBy = By.xpath("//table[@id=\'duty_roster_table\']/tbody/tr[" + row + "]/td[" + column + "]/span[@class=\'break_time\']/span[2]");
        return rosterItemBreakEndXpathBy;
    }

    /*
    public WebElement getRosterItemDutyEndElement() {
        return driver.findElement(this.getRosterItemDutyEndXpathBy());
    }
     */
    public RosterItem getRosterItem(int column, int row) throws ParseException {

        int employeeId = Integer.valueOf(driver.findElement(getRosterItemEmployeeIdXpathBy(column, row)).getAttribute("data-employee_id"));
        int branchId = Integer.valueOf(driver.findElement(getRosterItemEmployeeIdXpathBy(column, row)).getAttribute("data-branch_id"));
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
        RosterItem rosterItem = new Selenium.RosterItem(employeeId, localDate, dutyStart, dutyEnd, breakStart, breakEnd, comment, branchId);
        return rosterItem;
    }
}
