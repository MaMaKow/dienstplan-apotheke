/*
 * Copyright (C) 2021 Mandelkow
 *
 * Dienstplan Apotheke
 *
 * This program is free software: you can redistribute iterator and/or modify
 * iterator under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that iterator will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
package Selenium.administrationpages;

import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
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
    private final By formElementOpeningTimesMondayFromBy = By.xpath("//*[@id=\"branch_input_opening_times_fieldset_table\"]/tbody/tr[1]/td[3]/input");
    private final By formElementOpeningTimesMondayToBy = By.xpath("//*[@id=\"branch_input_opening_times_fieldset_table\"]/tbody/tr[1]/td[5]/input");
    private final By formElementOpeningTimesSundayFromBy = By.xpath("//*[@id=\"branch_input_opening_times_fieldset_table\"]/tbody/tr[7]/td[3]/input");
    private final By formElementOpeningTimesSundayToBy = By.xpath("//*[@id=\"branch_input_opening_times_fieldset_table\"]/tbody/tr[7]/td[3]/input");

    private WebElement formElementBranchId;
    private WebElement formElementBranchPepId;
    private WebElement formElementBranchName;
    private WebElement formElementBranchShortName;
    private WebElement formElementBranchAddress;
    private WebElement formElementBranchManager;

    /**
     * Buttons:
     */
    private final By formElementSubmitBy = By.xpath("//*[@id=\"submit_branch_data\"]");
    private final By formElementRemoveBranchBy = By.xpath("//*[@id=\"form_buttons_container\"]/button[@id=\"branch_form_button_remove\"]");
    private WebElement formElementSubmit;
    private WebElement formElementRemoveBranch;
    /**
     * opening times stored in input elements of type time
     */
    HashMap<Integer, WebElement[]> openingTimeElements;

    public BranchAdministrationPage() {
        driver = Selenium.driver.Wrapper.getDriver();
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.presenceOfElementLocated(formElementSubmitBy));

        /**
         * Form elements:
         */
        formElementBranchId = driver.findElement(formElementBranchIdBy);
        formElementBranchPepId = driver.findElement(formElementBranchPepIdBy);
        formElementBranchName = driver.findElement(formElementBranchNameBy);
        formElementBranchShortName = driver.findElement(formElementBranchShortNameBy);
        formElementBranchAddress = driver.findElement(formElementBranchAddressBy);
        formElementBranchManager = driver.findElement(formElementBranchManagerBy);
        /**
         * Form elements for the opening times:
         */

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

        /**
         * Buttons:
         */
        formElementSubmit = driver.findElement(formElementSubmitBy);
        formElementRemoveBranch = driver.findElement(formElementRemoveBranchBy);

    }

    /**
     * <p lang=de>
     * TODO: Bitte hier noch parametriesieren! Die Funktion muss auch mit
     * Parametern von außen funktionieren.
     * </p>
     *
     * @param branchId
     * @param branchPepId
     * @param branchName
     * @param branchShortName
     * @param branchAddress
     * @param branchManager
     * @param openingTimesMap
     */
    public void createNewBranch(int branchId,
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
        branchFormSelect.selectByVisibleText("create new branch");
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
        /**
         * String[] openingTimesMonday = openingTimesMap.get(0); String
         * openingTimesFrom = openingTimesMonday[0]; String openingTimesTo =
         * openingTimesMonday[1];
         * formElementOpeningTimesMondayFrom.sendKeys(openingTimesFrom);
         * formElementOpeningTimesMondayTo.sendKeys(openingTimesTo);
         * formElementOpeningTimesSundayFrom.sendKeys("08:00");
         * formElementOpeningTimesSundayTo.sendKeys("16:00");
         */

        System.out.println("Es gibt eine openingTimesMap.");
        System.out.println(openingTimesMap);
        System.out.println("Es gibt eine openingTimeElements.");
        System.out.println(openingTimeElements);
        int openingTimesMapSize = openingTimesMap.size();
        System.out.println("Jetzt schauen wir uns die Zeilen an.");
        System.out.println("Öffnungszeiten von Montag=1");
        for (int row = 1; row <= openingTimesMapSize; row++) {
            System.out.println(row);
            /**
             * Get the time input elements:
             */
            WebElement[] timeElementsFromTo = openingTimeElements.get(row - 1);
            WebElement timeElementFrom = timeElementsFromTo[0];
            WebElement timeElementTo = timeElementsFromTo[1];
            System.out.println(timeElementFrom.getAttribute("name"));
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
        formElementSubmit.click();
    }

    /**
     * TODO: This function is not part of any test yet. Please write a full test
     * for the
     *
     * @param branchId
     */
    void removeBranch(int branchId) {
        /**
         * Select the to remove branch:
         */
        Select branchFormSelect = new Select(driver.findElement(formElementBranchSelectBy));
        branchFormSelect.selectByValue(String.valueOf(branchId));
        /**
         * submit removal:
         */
        formElementRemoveBranch.click();
    }
}
