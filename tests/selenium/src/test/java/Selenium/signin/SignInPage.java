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
package Selenium.signin;

import Selenium.HomePage;
import Selenium.PropertyFile;
import org.openqa.selenium.By;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

/**
 *
 * @author Mandelkow
 *
 * Page Object encapsulates the Sign-in page.
 */
public class SignInPage {

    protected static WebDriver driver;
    By user_name_spanBy = By.id("MenuListItemApplicationUsername");

    // <input name="user_name" type="text" value="">
    private final By usernameBy = By.id("login_input_user_name");
    // <input name="password" type="password" value="">
    private final By passwordBy = By.id("login_input_user_password");
    // <input name="sign_in" type="submit" value="SignIn">
    private final By signinBy = By.id("login_button_submit");

    public SignInPage(WebDriver driver) {
        this.driver = driver;
    }

    /**
     * Login as valid user
     *
     * @param userName
     * @param password
     * @return HomePage object
     */
    public HomePage loginValidUser(String userName, String password) {
        WebDriverWait waitLong = new WebDriverWait(driver, 20);
        WebDriverWait waitShort = new WebDriverWait(driver, 1);
        try {
            waitShort.until(ExpectedConditions.presenceOfElementLocated(By.id("login_button_submit")));
        }
        catch (TimeoutException exception) {
            /**
             * <p lang=de>Wenn wir keinen Login submit button finden, dann
             * könnte es ja sein, dass wir bereits eingeloggt sind?</p>
             *
             * @todo
             * <p>
             * What do we do in that case? Just cotinue without logging in or
             * stop the whole program? What might go wrong if we just accept the
             * existing login?</p>
             *
             */
            if (!getUserNameText().isEmpty()) {
                /*

                throw new IllegalStateException("This is already a logged in state,"
                        + " current page is: " + driver.getCurrentUrl());
                 */
                System.out.println("We have already been logged in. Nothing to do here.");
                return new HomePage(driver);
            } else {
                /**
                 * Oder haben wir vielleicht nur nicht lang genug gewartet?
                 */
                waitLong.until(ExpectedConditions.presenceOfElementLocated(By.id("login_button_submit")));
            }
        }
        driver.findElement(usernameBy).sendKeys(userName);
        driver.findElement(passwordBy).sendKeys(password);
        driver.findElement(signinBy).click();
        return new HomePage(driver);
    }

    public HomePage loginValidUser() {
        PropertyFile propertyFile = new PropertyFile();
        String password = propertyFile.getPdrUserPassword();
        String userName = propertyFile.getPdrUserName();
        HomePage homePage = this.loginValidUser(userName, password);
        return homePage;
    }

    /**
     * Get user_name (span tag)
     * <p lang=de>
     * Die Loginseite hat keinen user_name text. Allerdings kann es passieren,
     * dass die Seite bereits eingeloggt ist. Dann finden wir einen bereits
     * eingeloggten Nutzer.</p>
     *
     * @return String user_name text
     */
    public String getUserNameText() {
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.presenceOfElementLocated(user_name_spanBy));

        return driver.findElement(user_name_spanBy).getText();
    }

}
