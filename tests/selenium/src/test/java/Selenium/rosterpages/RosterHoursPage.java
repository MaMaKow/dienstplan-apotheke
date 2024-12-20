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

import Selenium.TestPage;
import Selenium.MenuFragment;
import Selenium.RosterItem;
import java.time.Duration;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.List;
import java.util.Locale;
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
public class RosterHoursPage {

    protected static WebDriver driver;
    By selectMonthSelectBy = By.xpath("//select[@name='month_or_part']");
    //By selectMonthSelectBy = By.xpath("/html/body/form/select[@name=month_or_part]");
    By user_name_spanBy = By.id("MenuListItemApplicationUsername");
    By selectEmployeeSelectBy = By.xpath("/html/body/form/select[@name='employee_key']");
    By selectYearSelectBy = By.xpath("/html/body/form/select[@name='year']");

    public RosterHoursPage(WebDriver driver) {
        this.driver = driver;

        if (getUserNameText().isEmpty()) {
            throw new IllegalStateException("This is not a logged in state,"
                    + " current page is: " + driver.getCurrentUrl());
        }
        MenuFragment.navigateTo(driver, MenuFragment.MenuLinkToRosterHoursList);

    }

    public void selectMonth(String monthString) {
        /*
        <p lang=de>Es gibt nicht nur die Möglichkeit, sin einzelne Monate anzusehen.
        Man kann auch Quartale oder das ganze Jahr auswählen.</p>
         */
        WebElement selectMonthSelectElement = driver.findElement(selectMonthSelectBy);
        Select selectMonthSelect = new Select(selectMonthSelectElement);
        selectMonthSelect.selectByVisibleText(monthString);
    }

    public void selectYear(String yearString) {
        WebElement selectYearSelectElement = driver.findElement(selectYearSelectBy);
        Select selectYearSelect = new Select(selectYearSelectElement);
        selectYearSelect.selectByVisibleText(yearString);
    }

    public void selectEmployee(String employeeString) {
        WebElement selectEmployeeSelectElement = driver.findElement(selectEmployeeSelectBy);
        Select selectEmployeeSelect = new Select(selectEmployeeSelectElement);
        selectEmployeeSelect.selectByVisibleText(employeeString);
    }

    public String getMonth() {
        /*
        <p lang=de>Es gibt nicht nur die Möglichkeit, sin einzelne Monate anzusehen.
        Man kann auch Quartale oder das ganze Jahr auswählen.</p>
         */
        WebElement selectMonthSelectElement = driver.findElement(selectMonthSelectBy);
        Select selectMonthSelect = new Select(selectMonthSelectElement);
        String selectedOption = selectMonthSelect.getFirstSelectedOption().getText();
        return selectedOption;
    }

    public String getYear() {
        WebElement selectYearSelectElement = driver.findElement(selectYearSelectBy);
        Select selectYearSelect = new Select(selectYearSelectElement);
        String selectedOption = selectYearSelect.getFirstSelectedOption().getText();
        return selectedOption;
    }

    public String getEmployeeName() {
        WebElement selectEmployeeSelectElement = driver.findElement(selectEmployeeSelectBy);
        Select selectEmployeeSelect = new Select(selectEmployeeSelectElement);
        String selectedOptionText = selectEmployeeSelect.getFirstSelectedOption().getText();
        return selectedOptionText;
    }

    public RosterItem getRosterOnDate(LocalDate targetLocalDate) {
        WebElement rowElement = getRowElement(targetLocalDate);

        By rosterItemStartBy = By.xpath(".//td[2]");
        WebElement rosterItemStartElement = rowElement.findElement(rosterItemStartBy);
        String dutyStart = rosterItemStartElement.getText();

        By rosterItemEndBy = By.xpath(".//td[3]");
        WebElement rosterItemEndElement = rowElement.findElement(rosterItemEndBy);
        String dutyEnd = rosterItemEndElement.getText();

        RosterItem rosterItem = new RosterItem("", targetLocalDate, dutyStart, dutyEnd, null, null, null, null);
        return rosterItem;
    }

    public RosterItem getRosterOnLocalDate(LocalDate targetLocalDate) {
        String employeeName = getEmployeeName();
        Workforce workforce = TestPage.workforce;
        workforce.getListOfEmployees();
        WebElement rowElement = getRowElement(targetLocalDate);

        By rosterItemStartBy = By.xpath(".//td[2]");
        WebElement rosterItemStartElement = rowElement.findElement(rosterItemStartBy);
        String dutyStart = rosterItemStartElement.getText();

        By rosterItemEndBy = By.xpath(".//td[3]");
        WebElement rosterItemEndElement = rowElement.findElement(rosterItemEndBy);
        String dutyEnd = rosterItemEndElement.getText();

        RosterItem rosterItem = new RosterItem("", targetLocalDate, dutyStart, dutyEnd, dutyStart, null, null, null);
        return rosterItem;
    }

    public WebElement getRowElement(LocalDate targetLocalDate) {

        By listOfRowsBy = By.xpath("//*[@id=\"marginalEmploymentHoursListTable\"]/tbody/tr");
        By rosterItemDateBy = By.xpath(".//td[1]");
        List<WebElement> listOfRowsElements = driver.findElements(listOfRowsBy);
        String dateString;
        WebElement rosterItemDateElement;
        DateTimeFormatter dateTimeFormatter = DateTimeFormatter.ofPattern("EEE dd.MM.yyyy", Locale.GERMANY);
        String targetDateCalendarString = targetLocalDate.format(dateTimeFormatter);
        for (WebElement rowElement : listOfRowsElements) {
            rosterItemDateElement = rowElement.findElement(rosterItemDateBy);
            dateString = rosterItemDateElement.getText();
            if (!dateString.equals(targetDateCalendarString)) {
                continue;
            }
            return rowElement;
        }
        return null;
    }

    public String getAbsenceStringOnLocalDate(LocalDate targetLocalDate) {
        WebElement rowElement = getRowElement(targetLocalDate);
        By absenceCellBy = By.xpath(".//td[last()]");
        WebElement absenceCellElement = rowElement.findElement(absenceCellBy);
        String absenceString = absenceCellElement.getText();
        return absenceString;
    }

    /**
     * Get user_name (span tag)
     *
     * @return String user_name text
     */
    public String getUserNameText() {
        // <h1>Hello userName</h1>
        WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(3));
        wait.until(ExpectedConditions.presenceOfElementLocated(user_name_spanBy));

        return driver.findElement(user_name_spanBy).getText();
    }

}
