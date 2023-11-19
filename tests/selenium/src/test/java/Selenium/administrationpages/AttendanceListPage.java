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
package Selenium.administrationpages;

import java.util.HashMap;
import java.util.List;
import java.util.Map;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.Select;

/**
 *
 * @author Mandelkow
 */
public class AttendanceListPage {

    private final WebDriver driver;
    /**
     * navigation:
     */
    private final By yearSelectBy;
    private final By monthSelectBy;
    private Select yearSelectElement;
    private Select monthSelectElement;

    public AttendanceListPage() {
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

    private WebElement getAttendanceRowElement(String dateString) {
        By attendanceRowBy = By.xpath("/html/body/table/tbody/tr");
        List<WebElement> listOfAttendanceRowElements = driver.findElements(attendanceRowBy);
        By firstRowBy = By.xpath("/td[1]");
        for (WebElement attendanceRowElement : listOfAttendanceRowElements) {
            WebElement firstColumnElement = attendanceRowElement.findElement(firstRowBy);
            if (!firstColumnElement.getText().equals(dateString)) {
                continue;
            }
            return attendanceRowElement;
        }
        return null;
    }

    public String getAbsenceString(String dateString, int employeeKey) {
        WebElement attendanceRowElement = getAttendanceRowElement(dateString);
        Map<Integer, Integer> employeeMap = getEmployeeMap();
        int columnInt = employeeMap.get(employeeKey);
        By listOfAttendanceColumnsBy = By.xpath("/td[" + columnInt + "]");
        WebElement attendanceColumnElement = attendanceRowElement.findElement(listOfAttendanceColumnsBy);
        String absenceString = attendanceColumnElement.getText();
        return absenceString;
    }

    private Map<Integer, Integer> getEmployeeMap() {
        Map<Integer, Integer> columnMap = new HashMap<>();
        /**
         * following::text() will make sure, that only the employeId part is
         * matched.
         */
        By listOfEmployeeColumnsBy = By.xpath("/html/body/table/tbody/tr[1]/td[]/br/following::text()");
        List<WebElement> listOfEmployeeColumnElements = driver.findElements(listOfEmployeeColumnsBy);
        /**
         * We start the loop at one, because the first column (0) is not an
         * employee.
         */
        for (int i = 1; i <= listOfEmployeeColumnElements.size(); i++) {
            int someEmployeeKey = Integer.parseInt(listOfEmployeeColumnElements.get(i).getText());
            columnMap.put(someEmployeeKey, i);
        }
        return columnMap;
    }
}
