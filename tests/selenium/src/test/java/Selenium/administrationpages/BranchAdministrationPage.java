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

import Selenium.Branch;
import Selenium.RealData.RealNetworkOfBranchOffices;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;

/*
 * BranchAdministrationPage class handles the interaction with the web page
 * for administering branches in the Dienstplan Apotheke application.
 * It provides methods for creating new branches, removing existing branches,
 * and retrieving the real data for network of branch offices.
 *
 * @author Mandelkow
 */
public class BranchAdministrationPage {

    WebDriver driver;
    /**
     * branch_form_select element:
     */
    private final By formElementBranchSelectBy = By.xpath("//*[@id=\"branch_form_select\"]");
    /**
     * Form input elements:
     */
    private final By formElementBranchIdBy = By.xpath("//*[@id=\"branch_id\"]");
    private final By formElementBranchPepIdBy = By.xpath("//*[@id=\"branch_pep_id\"]");
    private final By formElementBranchNameBy = By.xpath("//*[@id=\"branch_name\"]");
    private final By formElementBranchShortNameBy = By.xpath("//*[@id=\"branch_short_name\"]");
    private final By formElementBranchAddressBy = By.xpath("//*[@id=\"branch_address\"]");
    private final By formElementBranchManagerBy = By.xpath("//*[@id=\"branch_manager\"]");

    /**
     * Buttons:
     */
    private final By formElementSubmitBy = By.xpath("//*[@id=\"submit_branch_data\"]");
    private final By formElementRemoveBranchBy = By.xpath("//*[@id=\"form_buttons_container\"]/button[@id=\"branch_form_button_remove\"]");

    /**
     * opening times stored in input elements of type time
     */
    public BranchAdministrationPage() {
        driver = Selenium.driver.Wrapper.getDriver();
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.presenceOfElementLocated(formElementSubmitBy));

    }

    /**
     * Retrieves the opening time input elements from the web page.
     * The method locates the table containing opening time inputs and extracts
     * the time input elements for each day.
     *
     * @return A HashMap with row indices as keys and arrays of time input elements
     * (from and to) as values.
     */
    private HashMap getOpeningTimeElements() {
        HashMap<Integer, WebElement[]> openingTimeElements;
        By openingTimesTableBy = By.xpath("//*[@id=\"branch_input_opening_times_fieldset_table\"]");
        By timeInputsBy = By.xpath("//input[@type='time']");

        WebElement openingTimesTable = driver.findElement(openingTimesTableBy);
        List listOfTimes = openingTimesTable.findElements(timeInputsBy);
        int row = 0;
        openingTimeElements = new HashMap<>();

        for (Iterator iterator = listOfTimes.iterator(); iterator.hasNext();) {
            WebElement timeElementFrom = (WebElement) iterator.next();
            WebElement timeElementTo = (WebElement) iterator.next();
            WebElement[] timeElementsFromTo = {timeElementFrom, timeElementTo};
            openingTimeElements.put(row, timeElementsFromTo);
            row++;
        }
        return openingTimeElements;
    }

    /**
     * Creates a new branch on the web page using the details provided in the
     * Branch object. This method serves as a convenient wrapper to create a
     * new branch by extracting information from an existing Branch object.
     *
     * @param branch The Branch object containing details for the new branch.
     * @return A new instance of BranchAdministrationPage after the form submission.
     */
    public BranchAdministrationPage createNewBranch(Branch branch) {
        int branchId = branch.getBranchId();
        int branchPepId = branch.getBranchPepId();
        String branchName = branch.getBranchName();
        String branchShortName = branch.getBranchShortName();
        String branchAddress = branch.getBranchAddress();
        String branchManager = branch.getBranchManager();
        HashMap<Integer, String[]> openingTimesMap = branch.getOpeningTimesMap();
        return createNewBranch(branchId, branchPepId, branchName, branchShortName, branchAddress, branchManager, openingTimesMap);
    }

    /**
     * Creates a new branch on the web page with the provided branch details.
     * The method selects the option to create a new branch, fills the form with
     * the given parameters, including opening times, and submits the form.
     *
     * @param branchId The unique identifier for the new branch.
     * @param branchPepId The PEP (PersonalEinsatzPlanung) identifier for the new branch.
     * @param branchName The name of the new branch.
     * @param branchShortName The short name or abbreviation for the new branch.
     * @param branchAddress The address of the new branch.
     * @param branchManager The manager's name for the new branch.
     * @param openingTimesMap A HashMap containing the opening times for each day.
     * @return A new instance of BranchAdministrationPage after the form submission.
     */
    private BranchAdministrationPage createNewBranch(int branchId,
            int branchPepId,
            String branchName,
            String branchShortName,
            String branchAddress,
            String branchManager,
            HashMap<Integer, String[]> openingTimesMap
    ) {
        /**
         * Select the new branch:
         */
        Select branchFormSelect = new Select(driver.findElement(formElementBranchSelectBy));
        branchFormSelect.selectByVisibleText("neue Filiale anlegen");
        /**
         * Form elements:
         */
        WebElement formElementBranchId = driver.findElement(formElementBranchIdBy);
        WebElement formElementBranchPepId = driver.findElement(formElementBranchPepIdBy);
        WebElement formElementBranchName = driver.findElement(formElementBranchNameBy);
        WebElement formElementBranchShortName = driver.findElement(formElementBranchShortNameBy);
        WebElement formElementBranchAddress = driver.findElement(formElementBranchAddressBy);
        WebElement formElementBranchManager = driver.findElement(formElementBranchManagerBy);
        /**
         * Fill the form:
         */
        formElementBranchId.clear();
        formElementBranchId.sendKeys(String.valueOf(branchId));
        formElementBranchPepId.clear();
        formElementBranchPepId.sendKeys(String.valueOf(branchPepId));
        formElementBranchName.clear();
        formElementBranchName.sendKeys(branchName);
        formElementBranchShortName.clear();
        formElementBranchShortName.sendKeys(branchShortName);
        formElementBranchAddress.clear();
        formElementBranchAddress.sendKeys(branchAddress);
        formElementBranchManager.clear();
        formElementBranchManager.sendKeys(branchManager);

        int openingTimesMapSize = openingTimesMap.size();
        for (int row = 1; row <= openingTimesMapSize; row++) {
            /**
             * Get the time input elements:
             */
            HashMap<Integer, WebElement[]> openingTimeElements = getOpeningTimeElements();
            WebElement[] timeElementsFromTo = openingTimeElements.get(row - 1);
            WebElement timeElementFrom = timeElementsFromTo[0];
            WebElement timeElementTo = timeElementsFromTo[1];
            /**
             * Fill them with time data:
             */
            String[] openingTimesDay = openingTimesMap.get(row);
            timeElementFrom.sendKeys(openingTimesDay[0]);
            timeElementTo.sendKeys(openingTimesDay[1]);
        }
        /**
         * submit:
         */
        WebElement formElementSubmit;

        formElementSubmit = driver.findElement(formElementSubmitBy);
        formElementSubmit.click();
        return new BranchAdministrationPage();
    }

    /**
     * Removes a branch from the web page with the specified branch identifier.
     * The method selects the branch to remove, submits the removal action, and
     * effectively deletes the branch from the application.
     *
     * @TODO: This method is currently not covered by any test. It is recommended
     * to write a comprehensive test suite to ensure the correct functionality
     * of the branch removal process.
     *
     * @param branchId The unique identifier of the branch to be removed.
     *
     */
    public void removeBranch(int branchId) {
        /**
         * Select the to remove branch:
         */
        Select branchFormSelect = new Select(driver.findElement(formElementBranchSelectBy));
        branchFormSelect.selectByValue(String.valueOf(branchId));
        /**
         * submit removal:
         */
        WebElement formElementRemoveBranch;
        formElementRemoveBranch = driver.findElement(formElementRemoveBranchBy);
        formElementRemoveBranch.click();
    }

    public boolean exists(int branchId) {
        /**
         * Select to find branch:
         */
        Select branchFormSelect = new Select(driver.findElement(formElementBranchSelectBy));
        try {
            branchFormSelect.selectByValue(String.valueOf(branchId));
        } catch (Exception e) {
            return false;
        }
        return true;
    }

    /**
     * Retrieves the real network of branch offices from the web page.
     * The method iterates through the available branch options, gathers
     * information about each branch, including opening times, and constructs
     * a RealNetworkOfBranchOffices object containing the collected data.
     *
     * Note: This method assumes the existence of branches on the page.
     *
     * @return A RealNetworkOfBranchOffices object representing the network
     * of branches with detailed information.
     */
    public RealNetworkOfBranchOffices getRealNetworkOfBranchOffices() {
        /**
         * Select the new branch:
         */
        RealNetworkOfBranchOffices realNetworkOfBranchOffices = new RealNetworkOfBranchOffices();
        Select branchFormSelect = new Select(driver.findElement(formElementBranchSelectBy));
        List<WebElement> selectBranchOptions = branchFormSelect.getOptions();
        List<String> optionValueStrings = new ArrayList<>();

        for (WebElement option : selectBranchOptions) {
            String optionValueString = option.getAttribute("value");
            optionValueStrings.add(optionValueString);
        }

        for (String optionValueStringRead : optionValueStrings) {
            //Move to branch
            if (optionValueStringRead.equals("")) {
                // This is the option to create a new branch.
                continue;
            }
            branchFormSelect = new Select(driver.findElement(formElementBranchSelectBy));
            branchFormSelect.selectByValue(optionValueStringRead); // At this point the page is reloaded. Every old WebElement becomes stale.

            /**
             * Form elements:
             */
            WebElement formElementBranchId = driver.findElement(formElementBranchIdBy);
            WebElement formElementBranchPepId = driver.findElement(formElementBranchPepIdBy);
            WebElement formElementBranchName = driver.findElement(formElementBranchNameBy);
            WebElement formElementBranchShortName = driver.findElement(formElementBranchShortNameBy);
            WebElement formElementBranchAddress = driver.findElement(formElementBranchAddressBy);
            WebElement formElementBranchManager = driver.findElement(formElementBranchManagerBy);
            /**
             * Fill the form:
             */
            int branchId = Integer.parseInt(formElementBranchId.getAttribute("value"));
            int branchPepId = Integer.parseInt(formElementBranchPepId.getAttribute("value"));
            String branchName = formElementBranchName.getAttribute("value");
            String branchShortName = formElementBranchShortName.getAttribute("value");
            String branchAddress = formElementBranchAddress.getAttribute("value");
            String branchManager = formElementBranchManager.getAttribute("value");
            HashMap<Integer, WebElement[]> openingTimeElements = getOpeningTimeElements();
            HashMap<Integer, String[]> openingTimes = new HashMap<>();

            int openingTimesMapSize = openingTimeElements.size();
            for (int row = 1; row <= openingTimesMapSize; row++) {
                /**
                 * Get the time input elements:
                 */
                WebElement[] timeElementsFromTo = openingTimeElements.get(row - 1);
                WebElement timeElementFrom = timeElementsFromTo[0];
                WebElement timeElementTo = timeElementsFromTo[1];
                /**
                 * Fill them with time data:
                 */
                String[] openingTimesDay = new String[2];
                openingTimesDay[0] = timeElementFrom.getAttribute("value");
                openingTimesDay[1] = timeElementTo.getAttribute("value");
                openingTimes.put(row, openingTimesDay);
            }
            Branch branch = new Branch(branchId, branchPepId, branchName, branchShortName, branchAddress, branchManager, openingTimes);
            realNetworkOfBranchOffices.add(branch);
        }
        return realNetworkOfBranchOffices;
    }
}
