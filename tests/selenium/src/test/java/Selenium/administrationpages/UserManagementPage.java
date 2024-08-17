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
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.Select;

import java.util.HashMap;
import java.util.List;
import java.util.Map;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

public class UserManagementPage {

    private final WebDriver driver;
    private final By userSelectBy = By.xpath("/html/body/form[1]/select[@name=\"user_key\"]");
    private Select userSelectElement;
    private final By user_name_spanBy = By.id("MenuListItemApplicationUsername");

    public UserManagementPage(WebDriver driver) {
        this.driver = driver;
        if (getUserNameText().isEmpty()) {
            throw new IllegalStateException("This is not a logged in state,"
                    + " current page is: " + driver.getCurrentUrl());
        }
        MenuFragment.navigateTo(driver, MenuFragment.MenuLinkToManageUser);
    }

    public void goToUser(int user_key) {
        userSelectElement = new Select(driver.findElement(userSelectBy));
        userSelectElement.selectByValue(String.valueOf(user_key));
    }

    private Select getUserKeySelect() {
        WebElement userKeyElement = driver.findElement(userSelectBy);
        Select userKeySelect = new Select(userKeyElement);
        return userKeySelect;
    }

    public int getUserKey() {
        Select userKeySelect = getUserKeySelect();
        return Integer.parseInt(userKeySelect.getFirstSelectedOption().getAttribute("value"));
    }

    public String getUserName() {
        Select userKeySelect = getUserKeySelect();
        return userKeySelect.getFirstSelectedOption().getText();
    }

    public String getUserEmail() {
        WebElement emailElement = driver.findElement(By.id("userEmail"));
        return emailElement.getAttribute("value");
    }

    public Integer getEmployeeKey() {
        Select employeeKeySelect = getEmployeeKeySelect();
        String employeeKeyFoundString = employeeKeySelect.getFirstSelectedOption().getAttribute("value");
        if (employeeKeyFoundString.equals("")) {
            return null;
        }
        return Integer.parseInt(employeeKeyFoundString);
    }

    private Select getEmployeeKeySelect() {
        WebElement employeeKeyElement = driver.findElement(By.id("employee_key"));
        Select employeeKeySelect = new Select(employeeKeyElement);
        return employeeKeySelect;
    }

    private Select getUserStatusSelect() {
        WebElement statusElement = driver.findElement(By.id("userStatus"));
        Select statusSelect = new Select(statusElement);
        return statusSelect;
    }

    public String getUserStatus() {
        Select statusSelect = getUserStatusSelect();
        return statusSelect.getFirstSelectedOption().getAttribute("value");
    }

    public Map<String, Boolean> getPrivileges() {
        Map<String, Boolean> privileges = new HashMap<>();
        List<WebElement> privilegeElements = driver.findElements(By.xpath("//form[@id='user_management']/fieldset[@id='privilegeGroup']/input[@type='checkbox']"));
        for (WebElement privilegeElement : privilegeElements) {
            String privilegeName = privilegeElement.getAttribute("value");
            boolean hasPrivilege = privilegeElement.isSelected();
            privileges.put(privilegeName, hasPrivilege);
        }
        return privileges;
    }

    // Method to set the employee key
    public void setEmployeeKey(Integer newEmployeeKey) {
        Select employeeKeySelect = getEmployeeKeySelect();
        if (null == newEmployeeKey) {
            employeeKeySelect.selectByValue("");
            return;
        }
        employeeKeySelect.selectByValue(String.valueOf(newEmployeeKey));
        return;
    }

    // Method to change user status
    public void setUserStatus(String newStatus) {
        Select statusSelect = getUserStatusSelect();
        statusSelect.selectByValue(newStatus);
    }

    // Method to change privileges
    public void setPrivileges(Map<String, Boolean> newPrivileges) {
        for (Map.Entry<String, Boolean> entry : newPrivileges.entrySet()) {
            String privilegeName = entry.getKey();
            boolean hasPrivilege = entry.getValue();

            WebElement privilegeElement = driver.findElement(By.xpath("//input[@value='" + privilegeName + "']"));

            if (hasPrivilege && !privilegeElement.isSelected()) {
                privilegeElement.click(); // Select the privilege
            } else if (!hasPrivilege && privilegeElement.isSelected()) {
                privilegeElement.click(); // Deselect the privilege
            }
        }
    }
    // Method to submit the form

    public void submitForm() {
        WebElement submitButton = driver.findElement(By.id("user_management_form_submit"));
        submitButton.click(); // Click the submit button to save the changes
    }

    /**
     * Get user_name (span tag)
     *
     * @return String user_name text
     */
    public String getUserNameText() {
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.presenceOfElementLocated(user_name_spanBy));
        return driver.findElement(user_name_spanBy).getText();
    }
}
