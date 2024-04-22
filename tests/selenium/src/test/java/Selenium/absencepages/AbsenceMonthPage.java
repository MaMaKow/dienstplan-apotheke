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
import Selenium.driver.Wrapper;
import java.util.List;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.Select;
import org.json.simple.JSONObject;
import org.json.simple.JSONValue;

/**
 *
 * @author Mandelkow
 */
public class AbsenceMonthPage {

    private final WebDriver driver;
    /**
     * navigation:
     */
    private final By yearSelectBy;
    private final By monthSelectBy;
    private Select yearSelectElement;
    private Select monthSelectElement;

    public AbsenceMonthPage() {
        yearSelectBy = By.xpath("/html/body/form[@id=\"select_year\"]/select");
        monthSelectBy = By.xpath("/html/body/form[@id=\"select_month\"]/select");
        driver = Selenium.driver.Wrapper.getDriver();

    }

    public void goToYear(int year) {
        yearSelectElement = (Select) driver.findElement(yearSelectBy);
        yearSelectElement.selectByValue(String.valueOf(year));
    }

    public void goToMonth(int month) {
        monthSelectElement = (Select) driver.findElement(monthSelectBy);
        monthSelectElement.selectByValue(String.valueOf(month));
    }

    private WebElement getDayParagraphElement(String dateString) {
        By listOfDayParagraphsBy = By.xpath("/html/body/p");
        List<WebElement> listOfDayParagraphs = driver.findElements(listOfDayParagraphsBy);

        for (WebElement dayParagraphElement : listOfDayParagraphs) {
            WebElement dateStrongElement = dayParagraphElement.findElement(By.xpath("/strong"));
            String paragraphDateString = dateStrongElement.getText();
            /**
             * The format of the date string is "DDD dd.mm." (e.g. "Mon 17.05.")
             * There is a whitespace " " between the weekday abbreviation and
             * the date string.
             */
            if (!paragraphDateString.contains(" " + dateString)) {
                continue;
            }
            return dayParagraphElement;
        }
        return null;
    }

    public Absence getAbsence(String startDateString, int employeeKey) {
        WebElement dayParagraphElement = getDayParagraphElement(startDateString);
        List<WebElement> listOfAbsenceSpans = dayParagraphElement.findElements(By.xpath("span"));
        for (WebElement absenceSpan : listOfAbsenceSpans) {
            String absenceDataJson = absenceSpan.getAttribute("data-absence_details");
            Object object = JSONValue.parse(absenceDataJson);
            JSONObject jsonObject = (JSONObject) object;
            if (employeeKey != (int) jsonObject.get("employeeKey")) {
                continue;
            }
            String endDateString = (String) jsonObject.get("end");
            int reasonId = (int) jsonObject.get("reasonId");
            String comment = (String) jsonObject.get("comment");
            String duration = (String) jsonObject.get("days");
            String approvalString = (String) jsonObject.get("approval");
            //return new Absence(employeeKey, startDateString, endDateString, reasonId, comment, duration, approvalString);
        }
        return null;
    }

    public void createNewAbsence(int employeeKey, String startDateString, String endDateString, String reasonString, String commentString, String approvalString) {

        WebElement dayParagraphElement = getDayParagraphElement(startDateString);
        dayParagraphElement.click();
        /**
         * Cicking on the dayParagraphElement will open a form. This form is
         * requested from the server via XMLHttpRequest()
         */
        By absenceFormEmployeeKeyBy = By.xpath("/html/body/div[3]/form[@id=\"input_box_form\"]/p[1]/select[@id=\"employee_key_select\"]");
        By absenceFormStartIdBy = By.xpath("/html/body/div[3]/form[@id=\"input_box_form\"]/p[2]/input[@id=\"input_box_form_start_date\"]");
        By absenceFormEndIdBy = By.xpath("/html/body/div[3]/form[@id=\"input_box_form\"]/p[3]/input[@id=\"input_box_form_end_date\"]");
        By absenceFormReasonIdBy = By.xpath("/html/body/div[3]/form[@id=\"input_box_form\"]/p[4]/select[@id=\"absence_reason_input_select\"]");
        By absenceFormCommentIdBy = By.xpath("/html/body/div[3]/form[@id=\"input_box_form\"]/p[5]/input[@id=\"input_box_form_comment\"]");
        By absenceFormSubmitButtonIdBy = By.xpath("/html/body/div[3]/form[@id=\"input_box_form\"]/p[6]/button");
        Select absenceFormEmployeeKeyElement = (Select) driver.findElement(absenceFormEmployeeKeyBy);
        WebElement absenceFormStartIdElement = driver.findElement(absenceFormStartIdBy);
        WebElement absenceFormEndIdElement = driver.findElement(absenceFormEndIdBy);
        Select absenceFormReasonIdElement = (Select) driver.findElement(absenceFormReasonIdBy);
        WebElement absenceFormCommentIdElement = driver.findElement(absenceFormCommentIdBy);
        WebElement absenceFormSubmitButtonIdElement = driver.findElement(absenceFormSubmitButtonIdBy);
        /**
         * Send the data:
         */
        absenceFormEmployeeKeyElement.selectByValue(String.valueOf(employeeKey));
        Wrapper.fillDateInput(absenceFormStartIdElement, startDateString);
        Wrapper.fillDateInput(absenceFormEndIdElement, endDateString);
        absenceFormReasonIdElement.selectByVisibleText(reasonString);
        absenceFormCommentIdElement.clear();
        absenceFormCommentIdElement.sendKeys(commentString);
        /**
         * Submit form:
         */
        absenceFormSubmitButtonIdElement.click();
    }

    public void editExistingAbsence(int employeeKeyOld, String startDateStringOld, int employeeKey, String startDateString, String endDateString, String reasonString, String commentString, String approvalString) {
        WebElement dayParagraphElement = getDayParagraphElement(startDateString);
        List<WebElement> listOfAbsenceSpans = dayParagraphElement.findElements(By.xpath("span"));
        for (WebElement absenceSpan : listOfAbsenceSpans) {
            String absenceDataJson = absenceSpan.getAttribute("data-absence_details");
            Object object = JSONValue.parse(absenceDataJson);
            JSONObject jsonObject = (JSONObject) object;
            if (employeeKeyOld != (int) jsonObject.get("employeeKey")) {
                continue;
            }
            /**
             * Cicking on the dayParagraphElement will open a form. This form is
             * requested from the server via XMLHttpRequest()
             */
            By absenceFormEmployeeKeyBy = By.xpath("/html/body/div[3]/form[@id=\"input_box_form\"]/p[1]/select[@id=\"employee_key_select\"]");
            By absenceFormStartIdBy = By.xpath("/html/body/div[3]/form[@id=\"input_box_form\"]/p[2]/input[@id=\"input_box_form_start_date\"]");
            By absenceFormEndIdBy = By.xpath("/html/body/div[3]/form[@id=\"input_box_form\"]/p[3]/input[@id=\"input_box_form_end_date\"]");
            By absenceFormReasonIdBy = By.xpath("/html/body/div[3]/form[@id=\"input_box_form\"]/p[4]/select[@id=\"absence_reason_input_select\"]");
            By absenceFormCommentIdBy = By.xpath("/html/body/div[3]/form[@id=\"input_box_form\"]/p[5]/input[@id=\"input_box_form_comment\"]");
            By absenceFormSubmitButtonIdBy = By.xpath("/html/body/div[3]/form[@id=\"input_box_form\"]/p[6]/button");
            Select absenceFormEmployeeKeyElement = (Select) driver.findElement(absenceFormEmployeeKeyBy);
            WebElement absenceFormStartIdElement = driver.findElement(absenceFormStartIdBy);
            WebElement absenceFormEndIdElement = driver.findElement(absenceFormEndIdBy);
            Select absenceFormReasonIdElement = (Select) driver.findElement(absenceFormReasonIdBy);
            WebElement absenceFormCommentIdElement = driver.findElement(absenceFormCommentIdBy);
            WebElement absenceFormSubmitButtonIdElement = driver.findElement(absenceFormSubmitButtonIdBy);
            /**
             * Send the data:
             */
            absenceFormEmployeeKeyElement.selectByValue(String.valueOf(employeeKey));
            Wrapper.fillDateInput(absenceFormStartIdElement, startDateString);
            Wrapper.fillDateInput(absenceFormEndIdElement, endDateString);
            absenceFormReasonIdElement.selectByVisibleText(reasonString);
            absenceFormCommentIdElement.clear();
            absenceFormCommentIdElement.sendKeys(commentString);
            /**
             * Submit form:
             */
            absenceFormSubmitButtonIdElement.click();

        }
    }
}
