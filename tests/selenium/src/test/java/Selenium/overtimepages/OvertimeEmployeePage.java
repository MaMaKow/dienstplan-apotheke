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
import Selenium.driver.Wrapper;
import java.time.LocalDate;
import java.util.List;
import org.openqa.selenium.By;
import org.openqa.selenium.NoSuchElementException;
import org.openqa.selenium.StaleElementReferenceException;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;

/**
 *
 * @author Mandelkow
 */
public class OvertimeEmployeePage extends Selenium.BasePage {

    protected static WebDriver driver;

    private final By selectYearBy = By.xpath("/html/body/div/form[@id='select_year']/select");
    private final By employeeFormSelectBy = By.xpath("//form[@id='select_employee']/select");
    private final By dateInputBy = By.xpath("//*[@id=\"date_chooser_input\"]");
    private final By hoursInputBy = By.xpath("//*[@id=\"stunden\"]");
    //private final By balanceNewSpanBy = By.xpath("//*[@id=\"balance_new\"]");
    private final By reasonInputBy = By.xpath("//*[@id=\"grund\"]");
    private final By submitButtonBy = By.xpath("/html/body/div/table/tbody/tr/td/input[@name=\"submitStunden\"]");

    public OvertimeEmployeePage(WebDriver driver) {
        super(driver);  // Call to BasePage constructor
        this.driver = driver;
        if (this.getUserNameText().isEmpty()) {
            throw new IllegalStateException(
                    "This is not a logged in state," + " current page is: " + driver.getCurrentUrl());
        }
        MenuFragment.navigateTo(driver, MenuFragment.MenuLinkToOvertimeEdit);
    }

    public OvertimeEmployeePage selectYear(int year) {
        WebElement selectYearElement = driver.findElement(selectYearBy);
        Select selectYearSelect = new Select(selectYearElement);
        selectYearSelect.selectByValue(String.valueOf(year));
        return new OvertimeEmployeePage(driver);
    }

    public OvertimeEmployeePage selectEmployee(int employeeKey) {
        WebElement selectEmployeeElement = driver.findElement(employeeFormSelectBy);
        Select selectEmployeeSelect = new Select(selectEmployeeElement);
        selectEmployeeSelect.selectByValue(String.valueOf(employeeKey));
        return new OvertimeEmployeePage(driver);
    }

    private List<WebElement> getListOfOvertimeRows() {
        By listOfOverTimeRowsBy = By.xpath("/html/body/div[2]/table/tbody[2]/tr");
        List<WebElement> listOfOvertimeRowElements = driver.findElements(listOfOverTimeRowsBy);
        return listOfOvertimeRowElements;
    }

    private WebElement findOvertimeRowByDate(LocalDate localDate) {
        By dateBy = By.xpath(".//td/input[@name=\"editDateNew\"]");
        List<WebElement> listOfOvertimeRows = getListOfOvertimeRows();
        for (WebElement overtimeRowElement : listOfOvertimeRows) {
            WebElement dateElement = overtimeRowElement.findElement(dateBy);
            if (dateElement.getAttribute("value").equals(localDate.format(Wrapper.DATE_TIME_FORMATTER_YEAR_MONTH_DAY))) {
                return overtimeRowElement;
            }
        }
        return null;
    }

    public Overtime getOvertimeByLocalDate(LocalDate localDate) throws Exception {
        WebElement overtimeRow = findOvertimeRowByDate(localDate);
        if (null == overtimeRow) {
            logger.error("No overtime found for given date.");
            throw new Exception("No overtime found for given date.");
        }
        float hours = Float.parseFloat(overtimeRow.findElement(By.xpath(".//input[@name=\"editHoursNew\"]")).getAttribute("value"));
        float balance = Float.parseFloat(overtimeRow.findElement(By.xpath(".//td[3]")).getText());
        String reason = overtimeRow.findElement(By.xpath(".//input[@name=\"editReasonNew\"]")).getAttribute("value");
        return new Overtime(localDate, hours, balance, reason);
    }

    public OvertimeEmployeePage addNewOvertimeForEmployee(int employeeKey, LocalDate localDate, float hours, String reason) {
        this.selectEmployee(employeeKey);
        return addNewOvertime(localDate, hours, reason);
    }

    public OvertimeEmployeePage addNewOvertime(LocalDate localDate, float hours, String reason) {
        /**
         * date:
         */
        WebElement dateInputElement = driver.findElement(dateInputBy);
        Wrapper.fillDateInput(dateInputElement, localDate);
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
        try {
            driver.switchTo().alert().accept();
        } catch (Exception exception) {
            System.err.println(exception.getLocalizedMessage());
        }

        OvertimeEmployeePage newOvertimeEmployeePage;
        try {
            newOvertimeEmployeePage = new OvertimeEmployeePage(driver);
        } catch (StaleElementReferenceException staleElementReferenceException) {
            newOvertimeEmployeePage = new OvertimeEmployeePage(driver);
        }
        return newOvertimeEmployeePage;

    }

    public OvertimeEmployeePage removeOvertimeByLocalDate(LocalDate localDate) {
        this.selectYear(localDate.getYear());
        WebElement overtimeRow = findOvertimeRowByDate(localDate);
        By removeButtonBy = By.xpath(".//button[@name=\"deleteRow\"]");
        WebElement removeButtonElement = overtimeRow.findElement(removeButtonBy);
        removeButtonElement.click();
        /**
         * <p lang=de>
         * Vor dem Löschen erscheint ein alert mit dem Text: "Really delete this
         * data set?" Wir bestätigen diese Anfrage mit OK.
         * </p>
         */
        wait.until(ExpectedConditions.alertIsPresent());
        driver.switchTo().alert().accept();
        OvertimeEmployeePage newOvertimeEmployeePage;
        try {
            newOvertimeEmployeePage = new OvertimeEmployeePage(driver);
        } catch (StaleElementReferenceException | NoSuchElementException staleElementReferenceException) {
            newOvertimeEmployeePage = new OvertimeEmployeePage(driver);
        }
        return newOvertimeEmployeePage;
    }

    public OvertimeEmployeePage editOvertimeByLocalDate(LocalDate localDate, LocalDate dateNew, float hoursNew, String reasonNew) {
        this.selectYear(localDate.getYear());
        WebElement overtimeRow = findOvertimeRowByDate(localDate);
        String localDateString = localDate.format(Wrapper.DATE_TIME_FORMATTER_YEAR_MONTH_DAY);
        By editButtonBy = By.xpath(".//button[@id=\"editButton_" + localDateString + "\"]");
        By submitEditButtonBy = By.xpath(".//button[@id=\"save_" + localDateString + "\"]");
        WebElement editButtonElement = overtimeRow.findElement(editButtonBy);
        editButtonElement.click();
        waitShort.until(ExpectedConditions.invisibilityOf(editButtonElement));

        By dateNewInputBy = By.xpath(".//input[@name=\"editDateNew\"]");
        By hoursNewInputBy = By.xpath(".//input[@name=\"editHoursNew\"]");
        By reasonNewInputBy = By.xpath(".//input[@name=\"editReasonNew\"]");

        /**
         * date:
         */
        WebElement dateInputElement = overtimeRow.findElement(dateNewInputBy);
        Wrapper.fillDateInput(dateInputElement, dateNew);
        /**
         * hours:
         */
        WebElement hoursInputElement = overtimeRow.findElement(hoursNewInputBy);
        hoursInputElement.clear();
        hoursInputElement.sendKeys(String.valueOf(hoursNew));
        /**
         * reason:
         */
        WebElement reasonInputElement = overtimeRow.findElement(reasonNewInputBy);
        reasonInputElement.clear();
        reasonInputElement.sendKeys(reasonNew);
        /**
         * submit:
         */
        WebElement submitButtonElement = overtimeRow.findElement(submitEditButtonBy);
        submitButtonElement.click();

        OvertimeEmployeePage newOvertimeEmployeePage;
        try {
            newOvertimeEmployeePage = new OvertimeEmployeePage(driver);
        } catch (StaleElementReferenceException | NoSuchElementException staleElementReferenceException) {
            newOvertimeEmployeePage = new OvertimeEmployeePage(driver);
        }
        return newOvertimeEmployeePage;
    }

}
