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

import Selenium.MenuFragment;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.List;
import java.util.Date;
import java.util.Locale;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;

/**
 * <p lang=de>
 * TODO: Es muss auf Feiertage geachtet werden. Am Sa 01.05.2021 war Tag der
 * Arbeit. Das hat den Samstagsplaner damals durcheinander gebracht. Das sollte
 * nun gefixt sein.
 * </p>
 *
 *
 * @author Mandelkow
 */
public class SaturdayListPage {

    protected static WebDriver driver;
    By user_name_spanBy = By.id("MenuListItemApplicationUsername");
    By branchFormSelectBy = By.xpath("//*[@id=\"branch_form_select\"]");
    By selectYearSelectBy = By.xpath("/html/body/form/select[@name='year']");
    By saturdayRowListBy = By.xpath("/html/body/table/tbody/tr");
    By saturdayRowDateBy = By.xpath(".//td[1]");
    By saturdayRowTeamIdBy = By.xpath(".//td[2]");
    By saturdayRowTeamMembersBy = By.xpath(".//td[3]");//The single members are in <span>s.
    By saturdayRowScheduledEmployeesBy = By.xpath(".//td[4]");//The single members are in <span>s.

    public SaturdayListPage(WebDriver driver) {
        this.driver = driver;

        if (getUserNameText().isEmpty()) {
            throw new IllegalStateException("This is not a logged in state,"
                    + " current page is: " + driver.getCurrentUrl());
        }
        MenuFragment.navigateTo(driver, MenuFragment.MenuLinkToSaturdayList);

    }

    public void selectYear(String yearString) {
        WebElement selectYearSelectElement = driver.findElement(selectYearSelectBy);
        Select selectYearSelect = new Select(selectYearSelectElement);
        selectYearSelect.selectByVisibleText(yearString);
    }

    public void selectBranch(int branchId) {
        Select branchFormSelect = new Select(driver.findElement(branchFormSelectBy));
        branchFormSelect.selectByValue(String.valueOf(branchId));
    }

    public int getBranchId() {
        Select branchFormSelect = new Select(driver.findElement(branchFormSelectBy));
        int branchId = Integer.parseInt(branchFormSelect.getFirstSelectedOption().getAttribute("value"));
        return branchId;
    }

    private WebElement getSaturdayRowElementByDate(Date targetDate) {
        List<WebElement> saturdayRowListElements = driver.findElements(saturdayRowListBy);

        for (WebElement saturdayRowElement : saturdayRowListElements) {
            WebElement saturdayRowDateElement = saturdayRowElement.findElement(saturdayRowDateBy);
            String saturdayRowDateString = saturdayRowDateElement.getText().substring(3, 13);
            SimpleDateFormat simpleDateFormat = new SimpleDateFormat("dd.MM.yyyy", Locale.GERMAN);
            if (simpleDateFormat.format(targetDate).equals(saturdayRowDateString)) {
                return saturdayRowElement;
            }
        }
        System.err.println("Wir haben nichts gefunden: return null;");
        return null;
    }

    public int getTeamIdOnDate(Date targetDate) {
        WebElement saturdayRowElement = getSaturdayRowElementByDate(targetDate);
        WebElement teamIdWebElement = saturdayRowElement.findElement(saturdayRowTeamIdBy);
        return Integer.valueOf(teamIdWebElement.getText());
    }

    public boolean teamIdOnDateIsMissing(Date targetDate) {
        WebElement saturdayRowElement = getSaturdayRowElementByDate(targetDate);
        try {
            WebElement teamIdWebElement = saturdayRowElement.findElement(saturdayRowTeamIdBy);
            return false;
        } catch (Exception exception) {
            /*
             * <p lang=de>
             * Es wurde kein WebElement gefunden.
             * Dies ist vermutlich ein Feiertag.
             * </p>
             */
            return true;
        }
    }

    public ArrayList getTeamMembersOnDate(Date targetDate) {
        ArrayList<String> teamMembers = new ArrayList<>();
        WebElement saturdayRowElement = getSaturdayRowElementByDate(targetDate);
        WebElement teamMembersWebElement = saturdayRowElement.findElement(saturdayRowTeamMembersBy);
        //System.out.println(teamMembersWebElement.getAttribute("innerHTML"));
        By listOfMembersBy = By.xpath(".//span");
        List<WebElement> listOfMemberElements = teamMembersWebElement.findElements(listOfMembersBy);
        listOfMemberElements.forEach(memberElement -> {
            teamMembers.add(memberElement.getText());
        });
        return teamMembers;
    }

    public ArrayList getScheduledEmployeesOnDate(Date targetDate) {
        ArrayList<String> scheduledEmployees = new ArrayList<>();
        WebElement saturdayRowElement = getSaturdayRowElementByDate(targetDate);
        WebElement scheduledEmployeesWebElement = saturdayRowElement.findElement(saturdayRowScheduledEmployeesBy);
        //System.out.println(scheduledEmployeesWebElement.getAttribute("innerHTML"));
        By listOfMembersBy = By.xpath(".//span");
        List<WebElement> listOfMemberElements = scheduledEmployeesWebElement.findElements(listOfMembersBy);
        listOfMemberElements.forEach(memberElement -> {
            scheduledEmployees.add(memberElement.getText());
        });
        return scheduledEmployees;
    }

    /**
     * Get user_name (span tag)
     *
     * @return String user_name text
     */
    public String getUserNameText() {
        // <h1>Hello userName</h1>
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.presenceOfElementLocated(user_name_spanBy));

        return driver.findElement(user_name_spanBy).getText();
    }

}
