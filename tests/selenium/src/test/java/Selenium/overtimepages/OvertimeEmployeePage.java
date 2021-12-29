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
package Selenium.overtimepages;

import Selenium.MenuFragment;
import Selenium.Overtime;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.List;
import java.util.logging.Level;
import java.util.logging.Logger;
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
public class OvertimeEmployeePage {

    protected static WebDriver driver;

    private final By selectYearBy = By.xpath("/html/body/div/form[@id='select_year']/select");
    private final By employeeFormSelectBy = By.xpath("//form[@id='select_employee']/select");
    private final By dateInputBy = By.xpath("//*[@id=\"date_chooser_input\"]");
    private final By hoursInputBy = By.xpath("//*[@id=\"stunden\"]");
    private final By balanceNewSpanBy = By.xpath("//*[@id=\"balance_new\"]");
    private final By reasonInputBy = By.xpath("//*[@id=\"grund\"]");
    private final By submitButtonBy = By.xpath("/html/body/div/table/tbody/tr/td/input[@name=\"submitStunden\"]");
    private final SimpleDateFormat simpleDateFormat;

    public OvertimeEmployeePage(WebDriver driver) {
        this.simpleDateFormat = new SimpleDateFormat("dd.MM.yyyy");
        this.driver = driver;

        if (this.getUserNameText().isEmpty()) {
            throw new IllegalStateException(
                    "This is not a logged in state," + " current page is: " + driver.getCurrentUrl());
        }
        MenuFragment.navigateTo(driver, MenuFragment.MenuLinkToOvertimeEdit);
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
        final By userNameSpanBy = By.id("MenuListItemApplicationUsername");
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.presenceOfElementLocated(userNameSpanBy));

        return driver.findElement(userNameSpanBy).getText();
    }

    public OvertimeEmployeePage selectYear(int year) {
        WebElement selectYearElement = driver.findElement(selectYearBy);
        Select selectYearSelect = new Select(selectYearElement);
        selectYearSelect.selectByValue(String.valueOf(year));
        return new OvertimeEmployeePage(driver);
    }

    public OvertimeEmployeePage selectEmployee(int employeeId) {
        WebElement selectEmployeeElement = driver.findElement(employeeFormSelectBy);
        Select selectEmployeeSelect = new Select(selectEmployeeElement);
        selectEmployeeSelect.selectByValue(String.valueOf(employeeId));
        return new OvertimeEmployeePage(driver);
    }

    private List<WebElement> getListOfOvertimeRows() {
        By listOfOverTimeRowsBy = By.xpath("/html/body/div[2]/table/tbody[2]/tr");
        List<WebElement> listOfOvertimeRowElements = driver.findElements(listOfOverTimeRowsBy);
        return listOfOvertimeRowElements;
    }

    private WebElement findOvertimeRowByIndex(int index) {
        List<WebElement> listOfOvertimeRows = getListOfOvertimeRows();
        return listOfOvertimeRows.get(index);
    }

    private WebElement findOvertimeRowByDate(Calendar calendar) {
        By dateBy = By.xpath(".//td/form");
        List<WebElement> listOfOvertimeRows = getListOfOvertimeRows();
        for (WebElement overtimeRowElement : listOfOvertimeRows) {
            WebElement dateElement = overtimeRowElement.findElement(dateBy);
            if (simpleDateFormat.format(calendar.getTime()).equals(dateElement.getText())) {
                return overtimeRowElement;
            }
        }
        return null;
    }

    public Overtime getOvertimeByCalendar(Calendar calendar) {
        WebElement overtimeRow = findOvertimeRowByDate(calendar);
        //String dateString = overtimeRow.findElement(By.xpath(".//td[1]/form")).getText();
        //simpleDateFormat.format(dateString);
        float hours = Float.valueOf(overtimeRow.findElement(By.xpath(".//td[2]")).getText());
        float balance = Float.valueOf(overtimeRow.findElement(By.xpath(".//td[3]")).getText());
        String reason = overtimeRow.findElement(By.xpath(".//td[4]")).getText();
        return new Overtime(calendar, hours, balance, reason);
    }

    public OvertimeEmployeePage addNewOvertime(Calendar calendar, float hours, String reason) {
        /**
         * date:
         */
        String dateString = simpleDateFormat.format(calendar.getTime());
        WebElement dateInputElement = driver.findElement(dateInputBy);
        dateInputElement.clear();
        dateInputElement.sendKeys(dateString);
        /**
         * hours:
         */
        WebElement hoursInputElement = driver.findElement(hoursInputBy);
        hoursInputElement.sendKeys(String.valueOf(hours));
        /**
         * reason:
         */
        WebElement reasonInputElement = driver.findElement(reasonInputBy);
        reasonInputElement.sendKeys(reason);
        /**
         * submit:
         */
        WebElement submitButtonElement = driver.findElement(submitButtonBy);
        submitButtonElement.click();
        /**
         * <p lang=de>
         * Mitunter werden Warnmeldungen angezeigt z.B. "Das Datum des Eintrages
         * liegt vor dem letzten vorhandenen Datum. Sind Sie sicher, dass die
         * Daten korrekt sind?" Wir akzeptieren hier einfach die Abfrage.
         * </p>
         */
        driver.switchTo().alert().accept();

        return new OvertimeEmployeePage(driver);

    }

    public OvertimeEmployeePage removeOvertimeByCalendar(Calendar calendar) {
        WebElement overtimeRow = findOvertimeRowByDate(calendar);
        By removeButtonBy = By.xpath(".//input");
        WebElement removeButtonElement = overtimeRow.findElement(removeButtonBy);
        removeButtonElement.click();
        /**
         * <p lang=de>
         * Vor dem Löschen erscheint ein alert mit dem Text: "Really delete this
         * data set?" Wir bestätigen diese Anfrage mit OK.
         * </p>
         *
         */
        driver.switchTo().alert().accept();
        return new OvertimeEmployeePage(driver);
    }

}
