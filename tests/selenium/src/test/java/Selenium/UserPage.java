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
package Selenium;

import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Map;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.testng.Assert;

/**
 *
 * @author Mandelkow
 */
public class UserPage {

    private final WebDriver driver;
    private final PropertyFile propertyFile;
    /**
     * Form to give consent into receiving emails on roster change:
     */
    private final By receiveEmailsOptInBy;
    private final WebElement receiveEmailsOptInElement;
    /**
     * Form to change the user password:
     */
    private final By oldPasswordInputBy;
    private final WebElement oldPasswordInputElement;
    private final By newPasswordInput1By;
    private final WebElement newPasswordInput1Element;
    private final By newPasswordInput2By;
    private final WebElement newPasswordInput2Element;
    /**
     * List of privileges:
     */
    private final By privilegeCheckboxAdministrationBy;
    private final WebElement privilegeCheckboxAdministrationElement;
    private final By newPasswordSubmitButtonBy;
    private final WebElement newPasswordSubmitButtonElement;
    private final By privilegeCheckboxCreateEmployeeBy;
    private final WebElement privilegeCheckboxCreateEmployeeElement;
    private By privilegeCheckboxCreateRosterBy;
    private final WebElement privilegeCheckboxCreateRosterElement;
    private By privilegeListOfCheckboxesBy;
    private final List<WebElement> privilegeListOfCheckboxesElements;
    private By privilegeCheckboxApproveRosterBy;
    private final WebElement privilegeCheckboxApproveRosterElement;
    private By privilegeCheckboxCreateOvertimeBy;
    private final WebElement privilegeCheckboxCreateOvertimeElement;
    private By privilegeCheckboxRequestOwnAbsenceBy;
    private final WebElement privilegeCheckboxRequestOwnAbsenceElement;
    private By privilegeCheckboxCreateAbsenceBy;
    private final WebElement privilegeCheckboxCreateAbsenceElement;

    public UserPage() {
        this.driver = Selenium.driver.Wrapper.getDriver();
        this.propertyFile = new PropertyFile();
        /**
         * Form to give consent into receiving emails on roster change:
         */
        this.receiveEmailsOptInBy = By.xpath("/html/body/main/fieldset[@id=\"email_consent\"]/label/input");
        this.receiveEmailsOptInElement = driver.findElement(receiveEmailsOptInBy);

        /**
         * Form to change the user password:
         */
        this.oldPasswordInputBy = By.xpath("//*[@id=\"change_password\"]/label[1]/input");
        this.oldPasswordInputElement = this.driver.findElement(oldPasswordInputBy);
        this.newPasswordInput1By = By.xpath("//*[@id=\"change_password\"]/label[2]/input");
        this.newPasswordInput1Element = this.driver.findElement(this.newPasswordInput1By);
        this.newPasswordInput2By = By.xpath("//*[@id=\"change_password\"]/label[3]/input");
        this.newPasswordInput2Element = this.driver.findElement(this.newPasswordInput2By);
        this.newPasswordSubmitButtonBy = By.xpath("/html/body/main/fieldset[@id=\"change_password\"]/input[2]");
        this.newPasswordSubmitButtonElement = this.driver.findElement(newPasswordSubmitButtonBy);
        /**
         * List of privileges:
         */
        this.privilegeCheckboxAdministrationBy = By.xpath("/html/body/main/fieldset[3]/input[@id=\"administration\"]");
        this.privilegeCheckboxAdministrationElement = this.driver.findElement(this.privilegeCheckboxAdministrationBy);
        this.privilegeCheckboxCreateEmployeeBy = By.xpath("/html/body/main/fieldset[3]/input[@id=\"create_employee\"]");
        this.privilegeCheckboxCreateEmployeeElement = this.driver.findElement(this.privilegeCheckboxCreateEmployeeBy);
        this.privilegeCheckboxCreateRosterBy = By.xpath("/html/body/main/fieldset[3]/input[@id=\"create_roster\"]");
        this.privilegeCheckboxCreateRosterElement = this.driver.findElement(this.privilegeCheckboxCreateRosterBy);
        this.privilegeCheckboxApproveRosterBy = By.xpath("/html/body/main/fieldset[3]/input[@id=\"approve_roster\"]");
        this.privilegeCheckboxApproveRosterElement = this.driver.findElement(this.privilegeCheckboxApproveRosterBy);
        this.privilegeCheckboxCreateOvertimeBy = By.xpath("/html/body/main/fieldset[3]/input[@id=\"create_overtime\"]");
        this.privilegeCheckboxCreateOvertimeElement = this.driver.findElement(this.privilegeCheckboxCreateOvertimeBy);
        this.privilegeCheckboxCreateAbsenceBy = By.xpath("/html/body/main/fieldset[3]/input[@id=\"create_absence\"]");
        this.privilegeCheckboxCreateAbsenceElement = this.driver.findElement(this.privilegeCheckboxCreateAbsenceBy);
        this.privilegeCheckboxRequestOwnAbsenceBy = By.xpath("/html/body/main/fieldset[3]/input[@id=\"request_own_absence\"]");
        this.privilegeCheckboxRequestOwnAbsenceElement = this.driver.findElement(this.privilegeCheckboxRequestOwnAbsenceBy);

        this.privilegeListOfCheckboxesBy = By.xpath("/html/body/main/fieldset[3]/input");
        this.privilegeListOfCheckboxesElements = this.driver.findElements(this.privilegeListOfCheckboxesBy);
        int numberOfExistingPrivileges = this.privilegeListOfCheckboxesElements.size();
        Assert.assertEquals(7, numberOfExistingPrivileges, "The number of existing privileges has changed. Please add tests for them!");
    }

    public boolean getEmailConsent() {
        boolean userWantsEmail = this.receiveEmailsOptInElement.isSelected();
        return userWantsEmail;
    }

    public void setNewPassword(String newPassword) {
        this.oldPasswordInputElement.clear();
        this.oldPasswordInputElement.sendKeys(this.propertyFile.getPdrUserPassword());
        this.newPasswordInput1Element.clear();
        this.newPasswordInput1Element.sendKeys(newPassword);
        this.newPasswordInput2Element.clear();
        this.newPasswordInput2Element.sendKeys(newPassword);
        this.newPasswordSubmitButtonElement.click();
    }

    public Map getPrivileges() {
        /**
         * <p lang=de>
         * Statt Map könnte man auch Set<String> nutzen. Es wäre dann TRUE für
         * jedes Element im Set und FALSE für jedes Element, das nicht im Set
         * ist.
         * </p>
         */
        Map<String, Boolean> listOfUserPrivileges;
        listOfUserPrivileges = new HashMap<>();
        for (Iterator<WebElement> iterator = this.privilegeListOfCheckboxesElements.iterator(); iterator.hasNext();) {
            WebElement privilegeCheckboxElement = iterator.next();
            String privilege = privilegeCheckboxElement.getAttribute("name");
            boolean userHasPrivilege = privilegeCheckboxElement.isSelected();
            listOfUserPrivileges.put(privilege, userHasPrivilege);
        }
        return listOfUserPrivileges;
    }

}
