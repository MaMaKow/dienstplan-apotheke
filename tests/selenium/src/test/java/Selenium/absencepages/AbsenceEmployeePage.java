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
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import org.openqa.selenium.Alert;
import org.openqa.selenium.By;
import org.openqa.selenium.NoSuchElementException;
import org.openqa.selenium.StaleElementReferenceException;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;
import static org.testng.Assert.assertEquals;

/**
 * Represents the Selenium page object for Absence Employee Page.
 * Handles navigation, creation, editing, and deletion of absences.
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

    /**
     * Constructor for the AbsenceEmployeePage class.
     */
    public AbsenceEmployeePage() {

        driver = Selenium.driver.Wrapper.getDriver();
        MenuFragment.navigateTo(driver, MenuFragment.MenuLinkToAbsenceEdit);

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
    }

    /**
     * Retrieves notification messages displayed in the user dialog container.
     *
     * This method searches for notification message elements within the user dialog container.
     * It then extracts the text content of each notification
     * message element and returns a list of strings containing the notification messages.
     *
     * @return A list of strings representing the notification messages displayed in the user dialog container.
     * If no notification messages are found, an empty list is returned.
     */
    public List<String> getUserDialogNotifications() {
        List<String> userDialogNotificationStrings = new ArrayList<>();
        By userDialogNotificationParagraphBy = By.xpath("/html/body/div[@id=\"main-area\"]/div[contains(@class, 'user_dialog_container')]/div[contains(@class, 'notification')]/span");
        List<WebElement> listOfNotificationParagraphs = driver.findElements(userDialogNotificationParagraphBy);
        for (WebElement paragraphElement : listOfNotificationParagraphs) {
            userDialogNotificationStrings.add(paragraphElement.getText());
        }
        return userDialogNotificationStrings;
    }

    /**
     * Retrieves error messages displayed in the user dialog container.
     *
     * This method searches for error message elements within the user dialog container.
     * It then extracts the text content of each error message element and returns
     * a list of strings containing the error messages.
     *
     * @return A list of strings representing the error messages displayed in the user dialog container.
     * If no error messages are found, an empty list is returned.
     *
     */
    public List<String> getUserDialogErrors() {
        List<String> userDialogErrorStrings = new ArrayList<>();
        By userDialogErrorParagraphBy = By.xpath("/html/body/div[@id='main-area']/div[contains(@class, 'user_dialog_container')]/div[contains(@class, 'error')]/span");
        List<WebElement> listOfErrorParagraphs = driver.findElements(userDialogErrorParagraphBy);
        for (WebElement paragraphElement : listOfErrorParagraphs) {
            userDialogErrorStrings.add(paragraphElement.getText());
        }
        return userDialogErrorStrings;
    }

    /**
     * Navigates to a specific year on the Absence Employee Page.
     *
     * @param year The year to navigate to.
     * @return A new instance of the AbsenceEmployeePage representing the selected year.
     */
    public AbsenceEmployeePage goToYear(int year) {
        // Select the desired year from the dropdown menu
        Select yearFormSelect = getYearFormSelect();
        yearFormSelect.selectByValue(String.valueOf(year));
        // Wait until the year dropdown's value attribute reflects the selected year
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.attributeToBe(goToYearSelectBy, "value", String.valueOf(year)));
        // Assert that the selected year matches the expected year
        assertEquals(this.getYear(), year);
        return new AbsenceEmployeePage();
    }

    /**
     * Retrieves the currently selected year from the year dropdown menu on the Absence Employee Page.
     *
     * @return The currently selected year as an integer.
     */
    public int getYear() {
        // Get the Select element for the year dropdown
        Select yearFormSelect = getYearFormSelect();
        // Get the WebElement representing the currently selected year
        WebElement yearElement = yearFormSelect.getFirstSelectedOption();
        // Extract the text of the selected year and parse it as an integer
        String year = yearElement.getText();
        return Integer.parseInt(year);
    }

    /**
     * Selects a specific employee from the employee dropdown menu on the Absence Employee Page.
     *
     * @param employeeKey The key or identifier of the employee to select.
     * @return A new instance of the AbsenceEmployeePage representing the selected employee.
     */
    public AbsenceEmployeePage goToEmployee(int employeeKey) {
        // Select the desired employee using their key or identifier
        employeeFormSelect.selectByValue(String.valueOf(employeeKey));
        // Return a new instance of the AbsenceEmployeePage for the selected employee
        return new AbsenceEmployeePage();
    }

    /**
     * Retrieves the key or identifier of the currently selected employee from the employee dropdown menu.
     *
     * @return The key or identifier of the currently selected employee as an integer.
     */
    public int getEmployeeKey() {
        // Get the WebElement representing the currently selected employee
        WebElement yearElement = employeeFormSelect.getFirstSelectedOption();
        // Get the employeeKey of the selected employee and parse it as an integer
        String employeeKey = yearElement.getAttribute("value");
        return Integer.parseInt(employeeKey);
    }

    /**
     * Retrieves and returns a Select element representing the year dropdown menu.
     *
     * This function finds the WebElement for the year dropdown menu using the provided
     * 'goToYearSelectBy' locator, creates a Select object for it, and returns it.
     *
     * @return A Select object representing the year dropdown menu.
     */
    private Select getYearFormSelect() {
        goToYearSelectElement = driver.findElement(goToYearSelectBy);
        Select yearFormSelect = new Select(goToYearSelectElement);
        return yearFormSelect;
    }

    /**
     * Creates a new absence record with the specified details on the Absence Employee Page.
     *
     * @param startDate The start date of the absence.
     * @param endDate The end date of the absence.
     * @param reasonId The reason code or identifier for the absence.
     * @param comment Any additional comments or notes for the absence.
     * @param approval The approval status for the absence.
     * @return A new instance of the AbsenceEmployeePage after creating the absence.
     */
    public AbsenceEmployeePage createNewAbsence(String startDate, String endDate, int reasonId, String comment, String approval) {
        // Locate and initialize the necessary input elements and buttons
        startDateInputElement = driver.findElement(startDateInputBy);
        endDateInputElement = driver.findElement(endDateInputBy);
        reasonIdInputSelectByElement = new Select(driver.findElement(reasonIdInputSelectBy));
        commentInputElement = driver.findElement(commentInputBy);
        durationOutputElement = driver.findElement(durationOutputBy);
        approvalInputSelectElement = new Select(driver.findElement(approvalInputSelectBy));
        createNewAbsenceSubmitButtonElement = driver.findElement(createNewAbsenceSubmitButtonBy);

        // Fill in the start and end date inputs
        Wrapper.fillDateInput(startDateInputElement, startDate);
        Wrapper.fillDateInput(endDateInputElement, endDate);

        // Select the reason from the dropdown menu
        reasonIdInputSelectByElement.selectByValue(String.valueOf(reasonId));

        // Clear and enter the comment
        commentInputElement.clear();
        commentInputElement.sendKeys(comment);

        // Select the approval status from the dropdown menu
        approvalInputSelectElement.selectByValue(approval);
        // Click the submit button to create the absence
        createNewAbsenceSubmitButtonElement.click();

        // Return a new instance of the AbsenceEmployeePage after creating the absence
        return new AbsenceEmployeePage();
        // String durationString = durationOutputElement.getText();
        //int duration = Integer.parseInt(durationString);

    }

    /**
     * Creates a new absence record with the specified start and end dates and reason ID,
     * with optional empty comment and "not_yet_approved" as the default approval status.
     *
     * @param startDate The start date of the absence.
     * @param endDate The end date of the absence.
     * @param reasonId The reason code or identifier for the absence.
     */
    public void createNewAbsence(String startDate, String endDate, int reasonId) {
        // Call the main createNewAbsence method with default empty comment and approval status
        this.createNewAbsence(startDate, endDate, reasonId, "", "not_yet_approved");
    }

    /**
     * Deletes an existing absence record with the specified start date on the Absence Employee Page.
     *
     * @param startDate The start date of the absence to be deleted.
     * @return A new instance of the AbsenceEmployeePage after deleting the absence (or if no matching absence was found).
     */
    public AbsenceEmployeePage deleteExistingAbsence(String startDate) {
        for (WebElement absenceRowElement : listOfAbsenceRowElements) {
            WebElement absenceOutDiv = absenceRowElement.findElement(By.xpath(".//td[1]/div"));
            String dateString = absenceOutDiv.getText();
            // Check if the start date of the absence matches the provided start date
            if (!startDate.equals(dateString)) {
                continue; // Skip to the next row if there is no match
            }
            WebElement deleteButton = absenceRowElement.findElement(deleteButtonBy);
            deleteButton.click();
            /**
             * Handle the confirmation alert
             * Alert will display: "Really delete this dataset?"
             */
            Alert alert = driver.switchTo().alert();
            /**
             * Press the OK button:
             */
            alert.accept(); // Confirm the deletion
            // Create a new instance of AbsenceEmployeePage and return it
            AbsenceEmployeePage newAbsenceEmployeePage;
            try {
                newAbsenceEmployeePage = new AbsenceEmployeePage();
            } catch (StaleElementReferenceException exception) {
                newAbsenceEmployeePage = new AbsenceEmployeePage();
            } catch (NoSuchElementException noSuchElementException) {
                newAbsenceEmployeePage = new AbsenceEmployeePage();
            }
            return newAbsenceEmployeePage;
        }
        // Return a new instance of AbsenceEmployeePage if no matching absence was found
        return new AbsenceEmployeePage();
    }

    /**
     * Edits an existing absence record with the specified start date, updating its details on the Absence Employee Page.
     *
     * @param startDateOld The old start date of the absence to be edited.
     * @param startDate The new start date for the edited absence.
     * @param endDate The new end date for the edited absence.
     * @param reasonId The new reason code or identifier for the edited absence.
     * @param comment The new comment or notes for the edited absence.
     * @param approval The new approval status for the edited absence.
     * @return A new instance of the AbsenceEmployeePage after editing the absence (or if no matching absence was found).
     */
    public AbsenceEmployeePage editExistingAbsence(String startDateOld, String startDate, String endDate, int reasonId, String comment, String approval) {
        for (WebElement absenceRowElement : listOfAbsenceRowElements) {
            WebElement absenceOutDiv = absenceRowElement.findElement(By.xpath(".//td[1]/div"));
            String dateString = absenceOutDiv.getText();
            // Check if the start date of the absence matches the provided old start date
            if (!startDateOld.equals(dateString)) {
                continue; // Skip to the next row if there is no match
            }
            WebElement editButton = absenceRowElement.findElement(editButtonBy);
            editButton.click();

            // Locate and update the input elements with new values
            WebElement startDateElement = absenceRowElement.findElement(By.xpath(".//td[1]/input[1]"));
            Wrapper.fillDateInput(startDateElement, startDate);
            WebElement endDateElement = absenceRowElement.findElement(By.xpath(".//td[2]/input[1]"));
            Wrapper.fillDateInput(endDateElement, endDate);
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

            // Return a new instance of the AbsenceEmployeePage after editing the absence
            return new AbsenceEmployeePage();
        }
        // Return a new instance of AbsenceEmployeePage if no matching absence was found
        return new AbsenceEmployeePage();
    }

    /**
     * Retrieves an existing absence record based on the start date and employee key on the Absence Employee Page.
     *
     * @param startDateString The start date of the absence to retrieve.
     * @param employeeKey The key or identifier of the employee associated with the absence.
     * @return An Absence object representing the retrieved absence record, or null if no matching absence was found.
     */
    public Absence getExistingAbsence(String startDateString, int employeeKey) {
        // Navigate to the employee and year related to the absence
        this.goToEmployee(employeeKey);
        LocalDate startDate = LocalDate.parse(startDateString, Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
        int year = startDate.getYear();
        this.goToYear(year);

        // Ensure that the selected employee and year match the provided values
        Assert.assertEquals(this.getEmployeeKey(), employeeKey);
        Assert.assertEquals(this.getYear(), year);

        // Iterate through absence rows to find the one with the matching start date
        WebElement absenceRowElement = getExistingAbsenceRowElement(startDateString, employeeKey);
        if (null == absenceRowElement) {
            return null;
        }
        WebElement startDateElement = absenceRowElement.findElement(By.xpath(".//td[1]/div"));
        String startDateStringFound = startDateElement.getText();

        // Retrieve information about the matching absence
        WebElement endDateElement = absenceRowElement.findElement(By.xpath(".//td[2]/div"));
        String endDateString = endDateElement.getText();
        LocalDate endDate = LocalDate.parse(endDateString, Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
        WebElement reasonElement = absenceRowElement.findElement(By.xpath(".//td[3]/div"));
        //String reasonString = reasonElement.getText();
        int reasonId = Integer.parseInt(reasonElement.getAttribute("data-reason_id"));
        WebElement commentElement = absenceRowElement.findElement(By.xpath(".//td[4]/div"));
        String commentString = commentElement.getText();
        WebElement durationElement = absenceRowElement.findElement(By.xpath(".//td[5]"));
        String durationString = durationElement.getText();
        WebElement approvalElement = absenceRowElement.findElement(By.xpath(".//td[6]/span"));
        String approvalString = approvalElement.getAttribute("data-absence_approval");
        //String approvalStringLocalized = approvalElement.getText();

        // Create and return an Absence object representing the retrieved absence record
        Absence absence = new Absence(employeeKey, startDate, endDate, reasonId, commentString, approvalString);
        return absence;

    }

    /**
     * Retrieves the WebElement representing an existing absence row based on the start date and employee key.
     *
     * This method navigates to the employee and year related to the absence, validates the selected employee
     * and year, and iterates through absence rows to find the one with the matching start date.
     *
     * @param startDate The start date of the absence in the format "dd.MM.yyyy".
     * @param employeeKey The unique key identifying the employee.
     * @return The WebElement representing the absence row with the matching start date and employee key,
     * or null if no matching absence row was found.
     */
    private WebElement getExistingAbsenceRowElement(String startDate, int employeeKey) {
        // Navigate to the employee and year related to the absence
        this.goToEmployee(employeeKey);
        LocalDate startDateLocalDate = LocalDate.parse(startDate, Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
        int year = startDateLocalDate.getYear();
        this.goToYear(year);

        // Ensure that the selected employee and year match the provided values
        Assert.assertEquals(this.getEmployeeKey(), employeeKey);
        Assert.assertEquals(this.getYear(), year);

        // Iterate through absence rows to find the one with the matching start date
        for (WebElement absenceRowElement : listOfAbsenceRowElements) {
            WebElement startDateElement = absenceRowElement.findElement(By.xpath(".//td[1]/div"));
            String startDateString = startDateElement.getText();

            // Check if the start date of the absence matches the provided start date
            if (!startDate.equals(startDateString)) {
                continue; // Skip to the next row if there is no match
            }

            // Retrieve information about the matching absence
            return absenceRowElement;
        }
        // Return null if no matching absence was found
        return null;
    }

    /**
     * Cancels the editing of an existing absence record without making any changes.
     * There is a cancel button for the edit function. Test if it works.
     *
     * @param startDateOld The start date of the absence to be edited (for identifying the absence to edit).
     * @param startDate The new start date for the edited absence (not used in this method).
     * @param endDate The new end date for the edited absence (not used in this method).
     * @param reasonId The new reason code or identifier for the edited absence (not used in this method).
     * @param comment The new comment or notes for the edited absence (not used in this method).
     * @param approval The new approval status for the edited absence (not used in this method).
     * @return A new instance of the AbsenceEmployeePage after canceling the editing (or if no matching absence was found).
     */
    public AbsenceEmployeePage editExistingAbsenceNot(String startDateOld, String startDate, String endDate, int reasonId, String comment, String approval) {
        for (WebElement absenceRowElement : listOfAbsenceRowElements) {
            WebElement absenceOutDiv = absenceRowElement.findElement(By.xpath(".//td[1]/div"));
            String dateString = absenceOutDiv.getText();
            // Check if the start date of the absence matches the provided start date
            if (!startDate.equals(dateString)) {
                continue; // Skip to the next row if there is no match
            }
            WebElement editButton = absenceRowElement.findElement(editButtonBy);
            editButton.click();
            WebElement startDateElement = absenceRowElement.findElement(By.xpath(".//td[1]/input[1]"));
            Wrapper.fillDateInput(startDateElement, startDate);
            WebElement endDateElement = absenceRowElement.findElement(By.xpath(".//td[2]/input[1]"));
            Wrapper.fillDateInput(endDateElement, endDate);
            Select reasonSelectElement = new Select(absenceRowElement.findElement(By.xpath(".//td[3]/select")));
            reasonSelectElement.selectByValue(String.valueOf(reasonId));
            WebElement commentElement = absenceRowElement.findElement(By.xpath(".//td[4]/input"));
            commentElement.clear();
            commentElement.sendKeys(comment);
            //WebElement durationElement = absenceRowElement.findElement(By.xpath("/td[5]"));
            //String durationString = durationElement.getText();
            Select approvalSelectElement = new Select(absenceRowElement.findElement(By.xpath(".//td[6]/select")));
            approvalSelectElement.selectByValue(approval);
            // Locate and cancel the editing by clicking the cancel button
            WebElement cancelButtonElement = absenceRowElement.findElement(cancelButtonBy);
            cancelButtonElement.click();
            // Return a new instance of the AbsenceEmployeePage after canceling the editing
            return new AbsenceEmployeePage();
        }
        // Return a new instance of AbsenceEmployeePage if no matching absence was found
        return new AbsenceEmployeePage();

    }

    /**
     * Checks if an absence identified by its start date and employee key has an overlap with other absences.
     *
     * This method retrieves the WebElement representing the existing absence row based on the start date
     * and employee key. It then looks for an overlap information element within the absence row.
     *
     * @param startDate The start date of the absence in the format "dd.MM.yyyy".
     * @param employeeKey The unique key identifying the employee.
     * @return true if an overlap information element is found within the absence row, indicating an overlap;
     * false otherwise.
     *
     */
    public boolean absenceHasAnOverlap(String startDate, int employeeKey) {
        WebElement absenceRowElement = getExistingAbsenceRowElement(startDate, employeeKey);
        By overlapInfoBy = By.xpath(".//p[@class=\'absenceCollisionParagraph\']");
        WebElement overlapInfoElement = absenceRowElement.findElement(overlapInfoBy);
        if (null == overlapInfoElement) {
            return false;
        }
        return true;
    }

    /**
     * Cuts the overlap on an absence identified by its start date and employee key.
     *
     * This method retrieves the WebElement representing the existing absence row based on the start date
     * and employee key. It then locates and clicks the "cut overlap" button within the absence row.
     * Upon clicking the button, it waits for the button to become stale, indicating that the overlap has been cut.
     *
     * @param startDate The start date of the absence in the format "dd.MM.yyyy".
     * @param employeeKey The unique key identifying the employee.
     * @return An instance of the AbsenceEmployeePage representing the page after cutting the overlap.
     *
     */
    public AbsenceEmployeePage cutOverlapOnAbsence(String startDate, int employeeKey) {
        WebElement absenceRowElement = getExistingAbsenceRowElement(startDate, employeeKey);
        By overlapCutButtonBy = By.xpath(".//button[contains(@class, 'overlapCutButton')]");
        WebElement overlapCutButtonElement = absenceRowElement.findElement(overlapCutButtonBy);
        overlapCutButtonElement.click();

        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.stalenessOf(overlapCutButtonElement));
        return new AbsenceEmployeePage();
    }
}
