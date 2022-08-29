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
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedCondition;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;

/**
 * @author Mandelkow
 */
public class SaturdayRotationTeamsPage {

    protected static WebDriver driver;
    private final By user_name_spanBy = By.id("MenuListItemApplicationUsername");
    private final By branchFormSelectBy = By.xpath("//*[@id=\"branch_form_select\"]");
    private final By teamRowListBy = By.xpath("/html/body/table/tbody/tr/td/parent::tr"); //Select a tr, which is parent of td, not of th, thereby skipping the heading

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
        List<WebElement> teamRowListElements = driver.findElements(teamRowListBy);
        teamRowListElements.remove(teamRowListElements.size() - 1); // Remove the last row. It only contains the link to add another row.
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
            By teamEmployeeSelectBy = By.xpath(".//td[3]/form/span/select");
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
        WebElement teamRowElementFound;
        By teamRowListBy = By.xpath("/html/body/table/tbody/tr/td/parent::tr"); //Select a tr, which is parent of td, not of th, thereby skipping the heading
        List<WebElement> teamRowListElements = driver.findElements(teamRowListBy);
        teamRowListElements.remove(teamRowListElements.size() - 1); // Remove the last row. It only contains the link to add another row.
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
                return teamRowElementFound;
            }
        }
        return null;
    }

    private int getNumberOfTeamRows() {
        List<WebElement> teamRowListElements = driver.findElements(teamRowListBy);
        teamRowListElements.remove(teamRowListElements.size() - 1); // Remove the last row. It only contains the link to add another row.
        return teamRowListElements.size();
    }

    public HashMap<Integer, SaturdayRotationTeam> getListOfTeams() {
        return listOfTeams;
    }

    public SaturdayRotationTeam getTeamById(int teamId) {
        this.readListOfTeams();
        return listOfTeams.get(teamId);
    }

    public void addEmployeeToTeam(int teamId, int employeeId) {
        WebDriverWait wait = new WebDriverWait(driver, 10);
        WebElement teamRowElement = getTeamRowById(teamId);
        /**
         * Add another employee:
         */
        By addEmployeeLinkBy = By.xpath(".//td[3]/form/span/a");
        WebElement addEmployeeLinkElement = teamRowElement.findElement(addEmployeeLinkBy);
        By teamEmployeeSelectCountBy = By.xpath(".//td[3]/form/span/select");

        int numberOfSelectElementsBeforeClick = driver.findElements(teamEmployeeSelectCountBy).size();
        addEmployeeLinkElement.click();
        /**
         * Wait until JavaScript has added the new select element:
         */
        wait.until(ExpectedConditions.numberOfElementsToBe(teamEmployeeSelectCountBy, numberOfSelectElementsBeforeClick + 1));
        /**
         * <p lang=de>Select auswählen und Mitarbeiter abschicken.</p>
         */
        By teamEmployeeSelectLastBy = By.xpath(".//td[3]/form/span[(last()-1)]/select");
        WebElement teamEmployeeSelectLastElement = teamRowElement.findElement(teamEmployeeSelectLastBy);
        Select teamEmployeeSelectLastSelect = new Select(teamEmployeeSelectLastElement);
        teamEmployeeSelectLastSelect.selectByValue(String.valueOf(employeeId));

        /**
         * The page will reload. When the page has reloaded, there will be new
         * elements. The old Select will be stale.
         */
        wait.until(ExpectedConditions.stalenessOf(teamEmployeeSelectLastElement));
        ExpectedCondition<Boolean> pageLoad = new ExpectedCondition<Boolean>() {
            public Boolean apply(WebDriver driver) {
                return ((JavascriptExecutor) driver).executeScript("return document.readyState").equals("complete");
            }
        };
        wait.until(pageLoad);
    }

    public int addTeam(SaturdayRotationTeam saturdayRotationTeam) {
        By addTeamLinkBy = By.xpath("//*[@id=\"saturdayRotationTeamsAddTeamTd\"]/a");
        WebElement addTeamLinkElement = driver.findElement(addTeamLinkBy);
        int numberOfTeamRowsBeforeClick = getNumberOfTeamRows(); // CAVE: Be aware, that this is one less, than the number of teamRowListBy
        addTeamLinkElement.click();
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.numberOfElementsToBe(teamRowListBy, numberOfTeamRowsBeforeClick + 2));

        By saturdayRotationTeamInputTableBy = By.xpath("//*[@id=\"saturday_rotation_team_input_table\"]");
        WebElement saturdayRotationTeamInputTableElement = driver.findElement(saturdayRotationTeamInputTableBy);
        int newTeamId = Integer.valueOf(saturdayRotationTeamInputTableElement.getAttribute("data-max_team_id"));
        /**
         * <p lang=de>Erst ann diesem Punkt wissen wir, welche teamId das neue
         * Team bekommt. Deshalb setzen wir hier die teamId. Als nächstes muss
         * der Rest des Programmes über die neue Id in Kenntnis gesetzt werden.
         * Die Information steckt im Objekt. Damit wird sie nach oben getragen,
         * denn der Rest des Programmes hat eine Referenz zu diesem Objekt.</p>
         */
        saturdayRotationTeam.setTeamId(newTeamId);
        saturdayRotationTeam.getListOfTeamMembers().forEach(employeeId -> {
            addEmployeeToTeam(newTeamId, employeeId);
        });
        this.readListOfTeams();
        return newTeamId;
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
