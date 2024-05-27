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

import Selenium.TestPage;
import java.util.HashMap;
import java.util.Map;
import org.testng.Assert;
import org.testng.annotations.Test;

public class TestUserManagementPage extends TestPage {

    @Test(enabled = true)
    public void testReadUserDetails() {
        // Sign in
        super.signIn();

        // Go to the User Management page
        UserManagementPage userManagementPage = new UserManagementPage(driver);

        // Choose a user by key
        int userKey = 1;
        userManagementPage.goToUser(userKey);

        // Assertions to verify that the user is correctly selected and displayed
        // Check if the user's email, employee key, and status are as expected.
        String administratorUserName = propertyFile.getAdministratorUserName();
        String administratorEmail = propertyFile.getAdministratorEmail();

        Assert.assertEquals(userManagementPage.getUserKey(), userKey);
        Assert.assertEquals(userManagementPage.getUserName(), administratorUserName);
        Assert.assertEquals(userManagementPage.getUserEmail(), administratorEmail);
        Assert.assertEquals(userManagementPage.getEmployeeKey(), null);
        Assert.assertEquals(userManagementPage.getUserStatus(), "active");

        // You can also test privileges if needed.
        Map<String, Boolean> privileges = userManagementPage.getPrivileges();
        Assert.assertEquals(privileges.get("administration"), true);
        Assert.assertEquals(privileges.get("create_roster"), true);
        Assert.assertEquals(privileges.get("create_employee"), true);
        Assert.assertEquals(privileges.get("approve_roster"), true);
        Assert.assertEquals(privileges.get("create_overtime"), true);
        Assert.assertEquals(privileges.get("create_absence"), true);
        Assert.assertEquals(privileges.get("request_own_absence"), true);
    }

    @Test(enabled = true, dependsOnMethods = {"testReadUserDetails"})
    public void testEditUserDetails() {
        // Sign in
        super.signIn();

        // Go to the User Management page
        UserManagementPage userManagementPage = new UserManagementPage(driver);
        Integer oldEmployeeKey = userManagementPage.getEmployeeKey();
        int newEmployeeKey = 3;
        userManagementPage.setEmployeeKey(newEmployeeKey);
        userManagementPage.submitForm();
        int newEmployeeKeyFound = userManagementPage.getEmployeeKey();
        /**
         * Change back to old value:
         */
        userManagementPage.setEmployeeKey(oldEmployeeKey);
        userManagementPage.submitForm();
        /**
         * Check if everything worked:
         */
        Assert.assertEquals(newEmployeeKeyFound, newEmployeeKey);

        String newStatus = "deleted";
        String oldStatus = userManagementPage.getUserStatus();
        userManagementPage.setUserStatus(newStatus);
        String newStatusFound = userManagementPage.getUserStatus();
        userManagementPage.setUserStatus(oldStatus);
        Assert.assertEquals(newStatusFound, newStatus);

        Map<String, Boolean> oldPrivileges = userManagementPage.getPrivileges();
        Map<String, Boolean> newPrivileges = new HashMap<>();
        newPrivileges.put("administration", true);
        newPrivileges.put("create_roster", true);
        newPrivileges.put("create_employee", false);
        newPrivileges.put("approve_roster", false);
        newPrivileges.put("create_overtime", true);
        newPrivileges.put("create_absence", false);
        newPrivileges.put("request_own_absence", true);

        userManagementPage.setPrivileges(newPrivileges);
        userManagementPage.submitForm();
        Map<String, Boolean> newPrivilegesFound = userManagementPage.getPrivileges();
        Assert.assertEquals(newPrivilegesFound, newPrivileges);
        userManagementPage.setPrivileges(oldPrivileges);
        userManagementPage.submitForm();
        Assert.assertEquals(newPrivilegesFound, newPrivileges);
    }
}
