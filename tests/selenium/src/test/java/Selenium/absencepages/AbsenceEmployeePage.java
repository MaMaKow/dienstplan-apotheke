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
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import org.openqa.selenium.Alert;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.Select;

/**
 *
 * @author Mandelkow
 */
public class AbsenceEmployeePage {

    private final WebDriver driver;
    Map<Integer, String> listOfAbsenceReasons;
    /**
     * Basic navigation:
     */
    private final By goToYearSelectBy;
    private WebElement goToYearSelectElement;
    private Select yearFormSelect;
    private final By goToEmployeeSelectBy;
    private WebElement goToEmployeeSelectElement;
    private Select employeeFormSelect;
    /**
     * Create new absence:
     */
    private final By startDateInputBy;
    private WebElement startDateInputElement;
    private final By endDateInputBy;
    private WebElement endDateInputElement;
    private final By commentInputBy;
    private WebElement commentInputElement;
    private final By reasonIdInputSelectBy;
    private Select reasonIdInputSelectByElement;
    private final By durationOutputBy;
    private WebElement durationOutputElement;
    private final By approvalInputSelectBy;
    private Select approvalInputSelectElement;
    private final By createNewAbsenceSubmitButtonBy;
    private WebElement createNewAbsenceSubmitButtonElement;
    /**
     * Edit or delete existing absence:
     */
    private By absenceRowsBy;
    private List<WebElement> listOfAbsenceRowElements;

    private final By deleteButtonBy = By.xpath(".//td[7]/button[1]");
    private final By cancelButtonBy = By.xpath(".//td[7]/button[2]");
    private final By editButtonBy = By.xpath(".//td[7]/button[3]");
    private final By saveButtonBy = By.xpath(".//td[7]/button[4]");

    public AbsenceEmployeePage() {

        driver = Selenium.driver.Wrapper.getDriver();

        listOfAbsenceReasons = new HashMap<>();
        listOfAbsenceReasons.put(1, "Urlaub");
        listOfAbsenceReasons.put(2, "Resturlaub");
        listOfAbsenceReasons.put(3, "Krankheit");
        listOfAbsenceReasons.put(4, "Krankheit des Kindes");
        listOfAbsenceReasons.put(5, "Überstunden genommen");
        listOfAbsenceReasons.put(6, "bezahlte Freistellung");
        listOfAbsenceReasons.put(7, "Mutterschutz");
        listOfAbsenceReasons.put(8, "Elternzeit");
        /**
         * Choose year:
         */
        goToYearSelectBy = By.xpath("/html/body/div[2]/form[@id=\"select_year\"]/select");
        goToYearSelectElement = driver.findElement(goToYearSelectBy);
        yearFormSelect = new Select(goToYearSelectElement);
        /**
         * Choose employee:
         */
        goToEmployeeSelectBy = By.xpath("/html/body/div[2]/form[@id=\"select_employee\"]/select");
        goToEmployeeSelectElement = driver.findElement(goToEmployeeSelectBy);
        employeeFormSelect = new Select(goToEmployeeSelectElement);

        /**
         * Create new absence:
         */
        startDateInputBy = By.xpath("//*[@id=\"beginn\"]");
        endDateInputBy = By.xpath("//*[@id=\"ende\"]");
        reasonIdInputSelectBy = By.xpath("//*[@id=\"input_line_new\"]/td[3]/select[@id=\"new_absence_reason_id_select\"]");
        approvalInputSelectBy = By.xpath("//*[@id=\"new_absence_approval_select\"]");
        commentInputBy = By.xpath("//*[@id=\"input_line_new\"]/td[4]/input[@id=\"new_absence_input_comment\"]");
        durationOutputBy = By.xpath("/html/body/div[2]/table/thead/tr[2]/td[@id=\"tage\"]");
        createNewAbsenceSubmitButtonBy = By.xpath("//*[@id=\"save_new\"]");

        /**
         * Edit or delete old entries:
         */
        absenceRowsBy = By.xpath("/html/body/div[2]/table/tbody/tr");
        listOfAbsenceRowElements = driver.findElements(absenceRowsBy);
        /*
         * <p lang=de>
         * TODO: Wir können auch die Fehleroptionen noch mit testen.
         * </p>
         */
        By userDialogErrorParagraphBy = By.xpath("/html/body/div[@id=\"main-area\"]/div[contains(@class, 'user_dialog_container')]/div[contains(@class, 'error')]/p/");
        By userDialogNotificationParagraphBy = By.xpath("/html/body/div[@id=\"main-area\"]/div[contains(@class, 'user_dialog_container')]/div[contains(@class, 'notification')]/p/");
    }

    public AbsenceEmployeePage goToYear(int year) {
        yearFormSelect.selectByValue(String.valueOf(year));
        return new AbsenceEmployeePage();
    }

    public int getYear() {
        WebElement yearElement = yearFormSelect.getFirstSelectedOption();
        String year = yearElement.getText();
        return Integer.parseInt(year);
    }

    public AbsenceEmployeePage goToEmployee(int employeeId) {
        employeeFormSelect.selectByValue(String.valueOf(employeeId));
        return new AbsenceEmployeePage();
    }

    public int getEmployeeId() {
        WebElement yearElement = employeeFormSelect.getFirstSelectedOption();
        String employeeId = yearElement.getAttribute("value");
        return Integer.parseInt(employeeId);
    }

    public AbsenceEmployeePage createNewAbsence(String startDate, String endDate, int reasonId, String comment, String approval) {
        startDateInputElement = driver.findElement(startDateInputBy);
        endDateInputElement = driver.findElement(endDateInputBy);
        reasonIdInputSelectByElement = new Select(driver.findElement(reasonIdInputSelectBy));
        commentInputElement = driver.findElement(commentInputBy);
        durationOutputElement = driver.findElement(durationOutputBy);
        approvalInputSelectElement = new Select(driver.findElement(approvalInputSelectBy));
        createNewAbsenceSubmitButtonElement = driver.findElement(createNewAbsenceSubmitButtonBy);

        startDateInputElement.clear();
        startDateInputElement.sendKeys(startDate);
        endDateInputElement.clear();
        endDateInputElement.sendKeys(endDate);
        reasonIdInputSelectByElement.selectByValue(String.valueOf(reasonId));
        commentInputElement.clear();
        commentInputElement.sendKeys(comment);
        approvalInputSelectElement.selectByValue(approval);
        createNewAbsenceSubmitButtonElement.click();
        return new AbsenceEmployeePage();
        // String durationString = durationOutputElement.getText();
        //int duration = Integer.parseInt(durationString);

    }

    public void createNewAbsence(String startDate, String endDate, int reasonId) {
        this.createNewAbsence(startDate, endDate, reasonId, "", "not_yet_approved");
    }

    public AbsenceEmployeePage deleteExistingAbsence(String startDate) {
        for (WebElement absenceRowElement : listOfAbsenceRowElements) {
            WebElement absenceOutDiv = absenceRowElement.findElement(By.xpath(".//td[1]/div"));
            String dateString = absenceOutDiv.getText();
            if (!startDate.equals(dateString)) {
                continue;
            }
            WebElement deleteButton = absenceRowElement.findElement(deleteButtonBy);
            deleteButton.click();
            /**
             * Alert will display: "Really delete this dataset?"
             */
            Alert alert = driver.switchTo().alert();
            /**
             * Press the OK button:
             */
            alert.accept();
            return new AbsenceEmployeePage();
        }
        return new AbsenceEmployeePage();

    }

    public AbsenceEmployeePage editExistingAbsence(String startDateOld, String startDate, String endDate, int reasonId, String comment, String approval) {
        for (WebElement absenceRowElement : listOfAbsenceRowElements) {
            WebElement absenceOutDiv = absenceRowElement.findElement(By.xpath(".//td[1]/div"));
            String dateString = absenceOutDiv.getText();
            if (!startDateOld.equals(dateString)) {
                continue;
            }
            WebElement editButton = absenceRowElement.findElement(editButtonBy);
            editButton.click();
            WebElement startDateElement = absenceRowElement.findElement(By.xpath(".//td[1]/input[1]"));
            startDateElement.clear();
            startDateElement.sendKeys(startDate);
            WebElement endDateElement = absenceRowElement.findElement(By.xpath(".//td[2]/input[1]"));
            endDateElement.clear();
            endDateElement.sendKeys(endDate);
            Select reasonSelectElement = new Select(absenceRowElement.findElement(By.xpath(".//td[3]/select")));
            reasonSelectElement.selectByValue(String.valueOf(reasonId));
            WebElement commentElement = absenceRowElement.findElement(By.xpath(".//td[4]/input"));
            commentElement.clear();
            commentElement.sendKeys(comment);
            //WebElement durationElement = absenceRowElement.findElement(By.xpath("/td[5]"));
            //String durationString = durationElement.getText();
            Select approvalSelectElement = new Select(absenceRowElement.findElement(By.xpath(".//td[6]/select")));
            approvalSelectElement.selectByValue(approval);
            WebElement submitButtonElement = absenceRowElement.findElement(saveButtonBy);
            submitButtonElement.click();
            return new AbsenceEmployeePage();

        }
        return new AbsenceEmployeePage();

    }

    public Absence getExistingAbsence(String startDate, int employeeId) {
        for (WebElement absenceRowElement : listOfAbsenceRowElements) {
            WebElement startDateElement = absenceRowElement.findElement(By.xpath(".//td[1]/div"));
            String startDateString = startDateElement.getText();
            if (!startDate.equals(startDateString)) {
                continue;
            }
            WebElement endDateElement = absenceRowElement.findElement(By.xpath(".//td[2]/div"));
            String endDateString = endDateElement.getText();
            WebElement reasonElement = absenceRowElement.findElement(By.xpath(".//td[3]/div"));
            //String reasonString = reasonElement.getText();
            int reasonId = Integer.valueOf(reasonElement.getAttribute("data-reason_id"));
            WebElement commentElement = absenceRowElement.findElement(By.xpath(".//td[4]/div"));
            String commentString = commentElement.getText();
            WebElement durationElement = absenceRowElement.findElement(By.xpath(".//td[5]"));
            String durationString = durationElement.getText();
            WebElement approvalElement = absenceRowElement.findElement(By.xpath(".//td[6]/span"));
            String approvalString = approvalElement.getAttribute("data-absence_approval");
            //String approvalStringLocalized = approvalElement.getText();
            Absence absence = new Absence(employeeId, startDateString, endDateString, reasonId, commentString, durationString, approvalString);
            return absence;
        }
        return null;
    }

    /**
     * There is a cancel button for the edit function.Does it work?
     *
     * @param startDateOld
     * @param startDate
     * @param endDate
     * @param reasonId
     * @param comment
     * @param approval
     * @return true if element with matching date was found.
     *
     * TODO: Should the date be a dateObject in order to make sure that there is
     * no problem with formatting?
     */
    public AbsenceEmployeePage editExistingAbsenceNot(String startDateOld, String startDate, String endDate, int reasonId, String comment, String approval) {
        for (WebElement absenceRowElement : listOfAbsenceRowElements) {
            WebElement absenceOutDiv = absenceRowElement.findElement(By.xpath(".//td[1]/div"));
            String dateString = absenceOutDiv.getText();
            if (!startDate.equals(dateString)) {
                continue;
            }
            WebElement editButton = absenceRowElement.findElement(editButtonBy);
            editButton.click();
            WebElement startDateElement = absenceRowElement.findElement(By.xpath(".//td[1]/input[1]"));
            startDateElement.clear();
            startDateElement.sendKeys(startDate);
            WebElement endDateElement = absenceRowElement.findElement(By.xpath(".//td[2]/input[1]"));
            endDateElement.clear();
            endDateElement.sendKeys(endDate);
            Select reasonSelectElement = new Select(absenceRowElement.findElement(By.xpath(".//td[3]/select")));
            reasonSelectElement.selectByValue(String.valueOf(reasonId));
            WebElement commentElement = absenceRowElement.findElement(By.xpath(".//td[4]/input"));
            commentElement.clear();
            commentElement.sendKeys(comment);
            //WebElement durationElement = absenceRowElement.findElement(By.xpath("/td[5]"));
            //String durationString = durationElement.getText();
            Select approvalSelectElement = new Select(absenceRowElement.findElement(By.xpath(".//td[6]/select")));
            approvalSelectElement.selectByValue(approval);
            WebElement cancelButtonElement = absenceRowElement.findElement(cancelButtonBy);
            cancelButtonElement.click();
            return new AbsenceEmployeePage();

        }
        return new AbsenceEmployeePage();

    }

}
