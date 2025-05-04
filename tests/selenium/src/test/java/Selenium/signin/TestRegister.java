/*
 * Copyright (C) 2024 Mandelkow
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
package Selenium.signin;

import Selenium.LogoutPage;
import Selenium.User;
import Selenium.UserRegistry;
import Selenium.administrationpages.UserManagementPage;
import java.util.Map;
import org.testng.Assert;
import org.testng.annotations.Test;

/**
 *
 * @author Mandelkow
 */
public class TestRegister extends Selenium.TestPage {

    @Test()
    public void testRegisterUsers() throws Exception {
        LogoutPage logoutPage = new LogoutPage();
        logoutPage.logout();
        UserRegistry.initializeUsers();
        Map<String, User> allUsers = UserRegistry.getAllUsers();
        for (Map.Entry<String, User> userEntry : allUsers.entrySet()) {
            try {
                driver.get(propertyFile.getTestPageUrl());
                SignInPage signInPage = new SignInPage(driver);
                signInPage.moveToRegisterNewUser();
                RegisterPage registerPage = new RegisterPage(driver);
                User user = userEntry.getValue();
                registerPage.registerUser(user);
                /**
                 * Newly registered users have to be approved either by using the UserManagementPage or by the register_approve.php
                 */

            } catch (Exception exception) {
                logger.error(driver.getCurrentUrl());
                logger.error(driver.getPageSource());
                throw exception;
            }
        }
        SignInPage signInPage = new SignInPage(driver);
        signInPage.loginValidUser();
        for (Map.Entry<String, User> userEntry : allUsers.entrySet()) {
            UserManagementPage userManagementPage = new UserManagementPage(driver);
            userManagementPage.goToUserByName(userEntry.getValue().getUserName());
            userManagementPage.setUserStatus("active");
            userManagementPage.submitForm();
            Assert.assertEquals(userManagementPage.getUserName(), userEntry.getValue().getUserName());
        }
        /**
         * After registration the privileges will be handled by TestUserManagementPage
         */
    }

}
