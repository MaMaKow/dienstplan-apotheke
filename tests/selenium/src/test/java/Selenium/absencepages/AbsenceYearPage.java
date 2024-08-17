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
package Selenium.absencepages;

import Selenium.Absence;
import Selenium.MenuFragment;
import Selenium.driver.Wrapper;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.List;
import java.util.Locale;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.Select;
import org.json.simple.JSONObject;
import org.json.simple.JSONValue;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

/**
 *
 * @author Mandelkow
 */
public class AbsenceYearPage {

    private final WebDriver driver;
    /**
     * navigation:
     */
    private final By yearSelectBy;
    private Select yearSelect;
    private By absenceFormEmployeeKeyBy = By.xpath("//select[@id=\"employeeKeySelect\"]");
    private By absenceFormStartIdBy = By.xpath("//*[@id=\"inputBoxFormStartDate\"]");
    private By absenceFormEndIdBy = By.xpath("//input[@id=\"inputBoxFormEndDate\"]");
    private By absenceFormReasonIdBy = By.xpath("//select[@id=\"absenceReasonInputSelect\"]");
    private By absenceFormCommentIdBy = By.xpath("//input[@id=\"inputBoxFormComment\"]");
    private By absenceFormSubmitButtonIdBy = By.xpath("//form[@id=\"inputBoxForm\"]/p/button");

    public AbsenceYearPage() {
        yearSelectBy = By.xpath("/html/body/div[3]/form/select");
        //yearSelectBy = By.xpath("/html/body/div/form[@id=\"select_year\"]/select");
        driver = Selenium.driver.Wrapper.getDriver();
        MenuFragment.navigateTo(driver, MenuFragment.MenuLinkToAbsencYear);

    }

    public AbsenceYearPage goToYear(int year) {
        try {
            WebElement yearSelectElement = driver.findElement(yearSelectBy);
            yearSelect = new Select(yearSelectElement);
            yearSelect.selectByValue(String.valueOf(year));
        } catch (Exception exception) {
            throw exception;
        }
        return new AbsenceYearPage();
    }

    public int getYear() {
        WebElement yearSelectElement = driver.findElement(yearSelectBy);
        yearSelect = new Select(yearSelectElement);
        String yearString = yearSelect.getFirstSelectedOption().getAttribute("value");
        return Integer.parseInt(yearString);
    }

    private WebElement getDayParagraphElement(String dateString) {
        if (dateString.length() != 6) {
            LocalDate dateStringParsed = LocalDate.parse(dateString, Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
            dateString = dateStringParsed.format(DateTimeFormatter.ofPattern("dd.MM."));
        }
        //By listOfDayParagraphsBy = By.xpath("/html/body/p");
        By listOfDayParagraphsBy = By.xpath("/html/body"
                + "/div[contains(@class, \"year-container\")]"
                + "/div[contains(@class, \"year-quarter-container\")]"
                + "/div[contains(@class, \"month-container\")]"
                + "/p[contains(@class, \"day-paragraph\")]");

        List<WebElement> listOfDayParagraphs = driver.findElements(listOfDayParagraphsBy);

        for (WebElement dayParagraphElement : listOfDayParagraphs) {
            WebElement dateStrongElement = dayParagraphElement.findElement(By.xpath(".//strong"));
            String paragraphDateString = dateStrongElement.getText();
            /**
             * The format of the date string is "dd.mm." (e.g. "17.05.")
             */
            if (!paragraphDateString.contains(dateString)) {
                continue;
            }
            return dayParagraphElement;
        }
        return null;
    }

    public Absence getAbsence(LocalDate startDate, int employeeKey) {
        String startDateString = startDate.format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
        WebElement dayParagraphElement = getDayParagraphElement(startDateString);
        List<WebElement> listOfAbsenceSpans = dayParagraphElement.findElements(By.xpath(".//span"));
        for (WebElement absenceSpan : listOfAbsenceSpans) {
            String absenceDataJson = absenceSpan.getAttribute("data-absence_details");
            Object object = JSONValue.parse(absenceDataJson);
            JSONObject jsonObject = (JSONObject) object;
            Long employeeKeyLong = (Long) jsonObject.get("employeeKey");
            int jsonEmployeeKeyInt = employeeKeyLong.intValue();

            if (employeeKey != jsonEmployeeKeyInt) {
                continue;
            }
            String endDateString = (String) jsonObject.get("end");
            LocalDate endDate = LocalDate.parse(endDateString, DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss"));
            Long reasonIdLong = (Long) jsonObject.get("reasonId");
            int reasonId = reasonIdLong.intValue();
            String comment = (String) jsonObject.get("comment");
            String approvalString = (String) jsonObject.get("approval");
            return new Absence(employeeKey, startDate, endDate, reasonId, comment, approvalString);
        }
        return null;
    }

    public AbsenceYearPage createNewAbsence(int employeeKey, LocalDate startDate, LocalDate endDate, String reasonString, String commentString, String approvalString) {
        String startDateString = startDate.format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
        String endDateString = endDate.format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
        WebElement dayParagraphElement = getDayParagraphElement(startDateString);
        dayParagraphElement.click();
        /**
         * Cicking on the dayParagraphElement will open a form. This form is
         * requested from the server via XMLHttpRequest()
         */
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.presenceOfElementLocated(absenceFormStartIdBy));
        WebElement absenceFormStartIdElement = driver.findElement(absenceFormStartIdBy);
        WebElement absenceFormEndIdElement = driver.findElement(absenceFormEndIdBy);
        WebElement absenceFormReasonIdElement = driver.findElement(absenceFormReasonIdBy);
        Select absenceFormReasonIdSelect = new Select(absenceFormReasonIdElement);
        WebElement absenceFormCommentIdElement = driver.findElement(absenceFormCommentIdBy);
        WebElement absenceFormSubmitButtonIdElement = driver.findElement(absenceFormSubmitButtonIdBy);
        WebElement absenceFormEmployeeKeyElement = driver.findElement(absenceFormEmployeeKeyBy);
        Select absenceFormEmployeeKeySelect = new Select(absenceFormEmployeeKeyElement);
        /**
         * Send the data:
         */
        absenceFormEmployeeKeySelect.selectByValue(String.valueOf(employeeKey));
        Wrapper.fillDateInput(absenceFormStartIdElement, startDateString);
        Wrapper.fillDateInput(absenceFormEndIdElement, endDateString);
        absenceFormReasonIdSelect.selectByVisibleText(reasonString);
        absenceFormCommentIdElement.clear();
        absenceFormCommentIdElement.sendKeys(commentString);
        /**
         * Submit form:
         */
        absenceFormSubmitButtonIdElement.click();
        return new AbsenceYearPage();
    }

    public AbsenceYearPage editExistingAbsence(int employeeKeyOld, LocalDate startDateOld, int employeeKey, LocalDate startDateNew, LocalDate endDateNew, String reasonStringNew, String commentStringNew, String approvalStringNew) {
        String startDateStringOld = startDateOld.format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
        WebElement dayParagraphElement = getDayParagraphElement(startDateStringOld);
        List<WebElement> listOfAbsenceSpans = dayParagraphElement.findElements(By.xpath("span"));
        for (WebElement absenceSpan : listOfAbsenceSpans) {
            String absenceDataJson = absenceSpan.getAttribute("data-absence_details");
            Object object = JSONValue.parse(absenceDataJson);
            JSONObject jsonObject = (JSONObject) object;
            Long jsonEmployeeKeyLong = (Long) jsonObject.get("employeeKey");
            int jsonEmployeeKeyInt = jsonEmployeeKeyLong.intValue();
            if (employeeKeyOld != jsonEmployeeKeyInt) {
                continue;
            }
            absenceSpan.click();
            WebDriverWait wait = new WebDriverWait(driver, 20);
            wait.until(ExpectedConditions.presenceOfElementLocated(absenceFormStartIdBy));

            /**
             * Cicking on the dayParagraphElement will open a form. This form is
             * requested from the server via XMLHttpRequest()
             */
            WebElement absenceFormEmployeeKeyElement = driver.findElement(absenceFormEmployeeKeyBy);
            Select absenceFormEmployeeKeySelect = new Select(absenceFormEmployeeKeyElement);
            WebElement absenceFormStartIdElement = driver.findElement(absenceFormStartIdBy);
            WebElement absenceFormEndIdElement = driver.findElement(absenceFormEndIdBy);
            WebElement absenceFormReasonIdElement = driver.findElement(absenceFormReasonIdBy);
            Select absenceFormReasonIdSelect = new Select(absenceFormReasonIdElement);
            WebElement absenceFormCommentIdElement = driver.findElement(absenceFormCommentIdBy);
            WebElement absenceFormSubmitButtonIdElement = driver.findElement(absenceFormSubmitButtonIdBy);
            /**
             * Send the data:
             */
            absenceFormEmployeeKeySelect.selectByValue(String.valueOf(employeeKey));
            Wrapper.fillDateInput(absenceFormStartIdElement, startDateNew);
            Wrapper.fillDateInput(absenceFormEndIdElement, endDateNew);
            absenceFormReasonIdSelect.selectByVisibleText(reasonStringNew);
            absenceFormCommentIdElement.clear();
            absenceFormCommentIdElement.sendKeys(commentStringNew);
            /**
             * Submit form:
             */
            absenceFormSubmitButtonIdElement.click();
            return new AbsenceYearPage();
        }
        return new AbsenceYearPage();
    }

    public AbsenceYearPage deleteExistingAbsence(int employeeKey, LocalDate startDate) {
        String startDateString = startDate.format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
        WebElement dayParagraphElement = getDayParagraphElement(startDateString);
        List<WebElement> listOfAbsenceSpans = dayParagraphElement.findElements(By.xpath("span"));
        for (WebElement absenceSpan : listOfAbsenceSpans) {
            String absenceDataJson = absenceSpan.getAttribute("data-absence_details");
            Object object = JSONValue.parse(absenceDataJson);
            JSONObject jsonObject = (JSONObject) object;
            Long jsonEmployeeKeyLong = (Long) jsonObject.get("employeeKey");
            int jsonEmployeeKeeInt = jsonEmployeeKeyLong.intValue();
            if (employeeKey != jsonEmployeeKeeInt) {
                continue;
            }
            absenceSpan.click();
            WebDriverWait wait = new WebDriverWait(driver, 20);
            wait.until(ExpectedConditions.presenceOfElementLocated(absenceFormStartIdBy));

            /**
             * Cicking on the dayParagraphElement will open a form. This form is
             * requested from the server via XMLHttpRequest()
             */
            By absenceFormDeleteButtonIdBy = By.xpath("//button[@id=\"inputBoxFormButtonDelete\"]");
            WebElement absenceFormDeleteButtonIdElement = driver.findElement(absenceFormDeleteButtonIdBy);
            /**
             * Submit form by delete:
             */
            absenceFormDeleteButtonIdElement.click();
            return new AbsenceYearPage();
        }
        return new AbsenceYearPage();
    }
}
