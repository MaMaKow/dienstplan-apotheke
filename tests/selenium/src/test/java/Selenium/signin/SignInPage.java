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
import Selenium.Utilities.LogCollector;
import java.time.Duration;
import org.openqa.selenium.By;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

/**
 *
 * @author Mandelkow
 *
 * Page Object encapsulates the Sign-in page.
 */
public class SignInPage extends Selenium.BasePage {

    private final By usernameBy = By.id("loginInputUserName");
    private final By passwordBy = By.id("loginInputUserPassphrase");
    private final By signinBy = By.id("loginButtonSubmit");

    /**
     *
     * @param driver
     */
    public SignInPage(WebDriver driver) {
        super(driver);  // Call to BasePage constructor
        this.driver = driver;
        try {
            WebDriverWait waitShort = new WebDriverWait(driver, Duration.ofSeconds(1));
            waitShort.until(ExpectedConditions.presenceOfElementLocated(signinBy));
        } catch (Exception e) {
            /**
             * Wenn wir keinen Login-button finden, gehen wir zur
             * wahrscheinlichen Position der Login-Seite Eigentlich ist dies nur
             * der Link zur index.php Wenn wir nicht eingelogt sind, werden wir
             * von dort aus weiter zum login geleitet. Wenn wir eingeloggt sind,
             * landen wir im Menü.
             */
            PropertyFile propertyFile = new PropertyFile();
            /**
             * @todo: Wenn die fogende Zeile den Wechsel zur testRealPageUrl
             * erzwingt, gelingt der Wechsel zum Login in der realPage nicht.
             */
            //driver.get(propertyFile.getTestPageUrl());
        }
    }

    /**
     * Login as valid user
     *
     * @param userName
     * @param passphrase
     * @return HomePage object
     * @throws java.lang.Exception
     */
    public HomePage loginValidUser(String userName, String passphrase) throws Exception {
        LogCollector.debug("method signInPage.loginValidUser()");
        try {
            LogCollector.debug("wait for signinBy");
            waitShort.until(ExpectedConditions.presenceOfElementLocated(signinBy));
        } catch (TimeoutException exception) {
            String userNameText = getUserNameText();
            if (userNameText != null && userName.equals(userNameText)) {
                /**
                 * This user is already logged in.
                 */
                return new HomePage(driver);
            }
            if (userNameText != null && !userName.equals(userNameText)) {
                LogCollector.error("Some other user is logged in. You have to logout first!");
                /**
                 * Some other user is still logged in.
                 */
                throw new Exception("Some other user is logged in. You have to logout first!");
            }
        }
        LogCollector.debug("enter sign in form data:");
        driver.findElement(usernameBy).clear();
        driver.findElement(usernameBy).sendKeys(userName);
        driver.findElement(passwordBy).clear();
        driver.findElement(passwordBy).sendKeys(passphrase);
        LogCollector.debug("click sign in button:");
        driver.findElement(signinBy).click();
        LogCollector.debug("create new HomePage:");
        HomePage newHomePage = new HomePage(driver);
        LogCollector.debug("return new HomePage:");
        return newHomePage;
    }

    public HomePage loginValidUser() throws Exception {
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
    @Override
    public String getUserNameText() {
        WebDriverWait waitShort = new WebDriverWait(driver, Duration.ofMillis(100));
        try {
            waitShort.until(ExpectedConditions.presenceOfElementLocated(userNameSpanBy));
            return driver.findElement(userNameSpanBy).getText();
        } catch (Exception exception) {
            LogCollector.error("Cannot find 'userNameSpan'. We might not be logged in.");
            return null;
        }
    }

    public void moveToRegisterNewUser() {
        By moveBy = By.xpath("/html/body/div/p[1]/a");
        WebElement moveToRegisterNewUserLink = driver.findElement(moveBy);
        moveToRegisterNewUserLink.click();
    }

    public void moveToResetLostPassword() {
        By moveBy = By.xpath("/html/body/div/p[2]/a");
        WebElement moveToResetLostPasswordLink = driver.findElement(moveBy);
        moveToResetLostPasswordLink.click();

    }

    /**
     * Login mit ungültigen Credentials (für negative Tests) Diese Methode
     * erwartet KEINEN erfolgreichen Login
     *
     * @param userName
     * @param passphrase
     * @throws java.lang.Exception
     */
    public void loginInvalidUser(String userName, String passphrase) throws Exception {
        LogCollector.debug("method signInPage.loginInvalidUser()");

        // Warte auf Login-Button
        try {
            waitShort.until(ExpectedConditions.presenceOfElementLocated(signinBy));
        } catch (TimeoutException exception) {
            LogCollector.warn("Login button not found - might already be logged in");
            throw new Exception("Cannot perform invalid login test - not on login page");
        }

        LogCollector.debug("enter invalid sign in form data:");

        // Felder leeren und Daten eingeben
        WebElement usernameField = driver.findElement(usernameBy);
        usernameField.clear();
        if (userName != null && !userName.isEmpty()) {
            usernameField.sendKeys(userName);
        }

        WebElement passwordField = driver.findElement(passwordBy);
        passwordField.clear();
        if (passphrase != null && !passphrase.isEmpty()) {
            passwordField.sendKeys(passphrase);
        }

        LogCollector.debug("click sign in button:");
        driver.findElement(signinBy).click();

        // Kurz warten, um der Anwendung Zeit zu geben, zu reagieren
        Thread.sleep(500);

        LogCollector.debug("Invalid login attempt completed");

        /**
         * Prüfe, dass der Login tatsächlich fehlgeschlagen ist Wenn wir eine
         * HomePage erstellen können und einen User finden, ist der Login
         * fälschlicherweise erfolgreich gewesen.
         */
        try {
            LogCollector.debug("Verify that login failed - try creating HomePage:");
            HomePage newHomePage = new HomePage(driver);
            String loggedInUser = newHomePage.getUserNameText();

            // Wenn wir hier ankommen, war der Login erfolgreich - das ist FALSCH!
            Assert.fail("Login sollte fehlschlagen, war aber erfolgreich! Eingeloggter User: " + loggedInUser);

        } catch (IllegalStateException expected) {
            // Das ist der erwartete Fall - HomePage wirft IllegalStateException bei nicht eingeloggtem User
            LogCollector.debug("Login ist erwartungsgemäß fehlgeschlagen (IllegalStateException): " + expected.getMessage());
        } catch (TimeoutException expected) {
            // Alternative: TimeoutException von getUserNameText() bedeutet auch, dass kein User eingeloggt ist
            LogCollector.debug("Login ist erwartungsgemäß fehlgeschlagen (TimeoutException): " + expected.getMessage());
        } catch (Exception unexpected) {
            // Andere Exceptions sollten nicht auftreten
            LogCollector.error("Unerwartete Exception beim Prüfen des fehlgeschlagenen Logins" + unexpected.getMessage());
            throw unexpected;
        }
    }

}
