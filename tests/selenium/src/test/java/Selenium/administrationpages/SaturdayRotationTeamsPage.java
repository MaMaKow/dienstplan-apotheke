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
import java.util.List;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Iterator;
import org.openqa.selenium.Alert;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;

/**
 * @author Mandelkow
 */
public class SaturdayRotationTeamsPage {

    protected static WebDriver driver;
    By user_name_spanBy = By.id("MenuListItemApplicationUsername");
    By branchFormSelectBy = By.xpath("//*[@id=\"branch_form_select\"]");

    public HashMap<Integer, SaturdayRotationTeam> listOfTeams = new HashMap<>();

    public SaturdayRotationTeamsPage(WebDriver driver) {

        this.driver = driver;

        if (getUserNameText().isEmpty()) {
            throw new IllegalStateException("This is not a logged in state,"
                    + " current page is: " + driver.getCurrentUrl());
        }
        MenuFragment.navigateTo(driver, MenuFragment.MenuLinkToSaturdayRotationTeams);
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

    public HashMap<Integer, SaturdayRotationTeam> readListOfTeams() {
        By teamRowListBy = By.xpath("/html/body/table/tbody/tr");
        List<WebElement> teamRowListElements = driver.findElements(teamRowListBy);
        teamRowListElements.forEach(teamRowElement -> {
            /**
             * First find the id:
             */
            By teamIdSpanBy = By.xpath(".//td[2]/span");
            WebElement teamIdSpanElement = teamRowElement.findElement(teamIdSpanBy);
            int teamIdRead = Integer.valueOf(teamIdSpanElement.getText());
            /**
             * Then find the employees:
             */
            HashSet<Integer> listOfTeamMemberIdsRead = new HashSet();
            By teamEmployeeSelectBy = By.xpath(".//td[3]/form/span[1]/select");
            List<WebElement> teamEmployeeSelectElementList = teamRowElement.findElements(teamEmployeeSelectBy);
            teamEmployeeSelectElementList.forEach(teamEmployeeSelectElement
                    -> {
                Select teamEmployeeSelect = new Select(teamEmployeeSelectElement);
                listOfTeamMemberIdsRead.add(Integer.valueOf(teamEmployeeSelect.getFirstSelectedOption().getAttribute("value")));
            });
            SaturdayRotationTeam saturdayRotationTeam = new SaturdayRotationTeam(teamIdRead, listOfTeamMemberIdsRead);
            listOfTeams.put(teamIdRead, saturdayRotationTeam);
        });
        return listOfTeams;
    }

    public WebElement getTeamRowById(int teamIdShould) {
        WebElement teamRowElementFound = null;
        By teamRowListBy = By.xpath("/html/body/table/tbody/tr");
        List<WebElement> teamRowListElements = driver.findElements(teamRowListBy);
        for (Iterator<WebElement> teamRowListElementsIterator = teamRowListElements.iterator(); teamRowListElementsIterator.hasNext();) {
            WebElement teamRowElementCurrent = teamRowListElementsIterator.next();
            /**
             * First find the id:
             */
            By teamIdSpanBy = By.xpath(".//td[2]/span");
            WebElement teamIdSpanElement = teamRowElementCurrent.findElement(teamIdSpanBy);
            Integer teamIdRead = Integer.valueOf(teamIdSpanElement.getText());
            /**
             * Then compare the id to the target:
             */
            if (teamIdRead.equals(teamIdShould)) {
                teamRowElementFound = teamRowElementCurrent;
            }
        }
        return teamRowElementFound;
    }

    public HashMap<Integer, SaturdayRotationTeam> getListOfTeams() {
        return listOfTeams;
    }

    public SaturdayRotationTeam getTeamById(int teamId) {
        return listOfTeams.get(teamId);
    }

    public void addEmployeeToTeam(int teamId, int employeeId) {
        //TODO: Use WebElement teamRowElement = getTeamRowById(teamId);

        By teamRowListBy = By.xpath("/html/body/table/tbody/tr");
        List<WebElement> teamRowListElements = driver.findElements(teamRowListBy);
        teamRowListElements.forEach(teamRowElement -> {
            /**
             * First find the id:
             */
            By teamIdSpanBy = By.xpath(".//td[2]/span");
            WebElement teamIdSpanElement = teamRowElement.findElement(teamIdSpanBy);
            Integer teamIdRead = Integer.valueOf(teamIdSpanElement.getText());
            if (!teamIdRead.equals(teamId)) {
                return; // only skips this iteration.
            }
            /**
             * Now add another employee:
             */
            By addEmployeeLinkBy = By.xpath("/html/body/table/tbody/tr/td[3]/form/span/a");
            WebElement addEmployeeLinkElement = teamRowElement.findElement(addEmployeeLinkBy);
            addEmployeeLinkElement.click();
            /**
             * <p lang=de>Select auswählen und Mitarbeiter abschicken.</p>
             */
            By teamEmployeeSelectLastBy = By.xpath(".//td[3]/form/span[1]/select[last()]");
            WebElement teamEmployeeSelectLastElement = teamRowElement.findElement(teamEmployeeSelectLastBy);
            Select teamEmployeeSelectLastSelect = new Select(teamEmployeeSelectLastElement);
            teamEmployeeSelectLastSelect.selectByValue(String.valueOf(employeeId));
            /**
             * Get the last element: WebElement rosterTableRowElement =
             * rosterTableRowElementList.get(rosterTableRowElementList.size() -
             * 1);
             */
        });
    }

    public void addTeam(SaturdayRotationTeam saturdayRotationTeam) {
        By addTeamLinkBy = By.xpath("//*[@id=\"saturdayRotationTeamsAddTeamTd\"]");
        WebElement addTeamLinkElement = driver.findElement(addTeamLinkBy);
        addTeamLinkElement.click();
        By saturdayRotationTeamInputTableBy = By.xpath("//*[@id=\"saturday_rotation_team_input_table\"]");
        WebElement saturdayRotationTeamInputTableElement = driver.findElement(saturdayRotationTeamInputTableBy);
        int newTeamId = Integer.valueOf(saturdayRotationTeamInputTableElement.getAttribute("data-max_team_id"));
        saturdayRotationTeam.getListOfTeamMembers().forEach(employeeId -> {
            /**
             * TODO: <p lang=de>Funktioniert das auch wenn wir das bereits
             * vorhandene Select einfach ignorieren und uns mehrere neue
             * machen?</p>
             */
            addEmployeeToTeam(newTeamId, employeeId);
        });
    }

    public void removeTeamById(int teamId) {
        WebElement teamRowElement = getTeamRowById(teamId);
        By saturdayRotationTeamsRemoveTeamLinkBy = By.xpath("saturdayRotationTeamsRemoveTeamLink");
        WebElement saturdayRotationTeamsRemoveTeamLinkElement = teamRowElement.findElement(saturdayRotationTeamsRemoveTeamLinkBy);
        saturdayRotationTeamsRemoveTeamLinkElement.click();
        /**
         * Alert will display: "Really delete this dataset?"
         */
        Alert alert = driver.switchTo().alert();
        /**
         * Press the OK button:
         */
        alert.accept();

    }

    private boolean teamExists(int teamId) {
        WebElement teamRowElement = getTeamRowById(teamId);
        return teamRowElement != null;
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
