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
import java.time.Duration;
import org.openqa.selenium.By;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

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

    public SignInPage(WebDriver driver) {
        logWithDetails("get driver");
        this.driver = driver;
        logger.debug("getUserNameText");
        if (null != getUserNameText()) {
            /**
             * This user is already logged in.
             */
            logger.debug("found username, user is already logged in.");
            return;
        }
        try {
            logger.debug("found username, user is already logged in.");
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
            driver.get(propertyFile.getTestPageUrl());
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
        logWithDetails("login valid user");
        logger.debug("test if logged in:");
        String userNameText = getUserNameText();
        if (userNameText != null && userName.equals(userNameText)) {
            logger.debug("The exact user is logged in. Returning new Homepage");
            /**
             * This user is already logged in.
             */
            return new HomePage(driver);
        }
        if (userNameText != null && !userName.equals(userNameText)) {
            logger.error("Some other user is logged in. You have to logout first!");
            /**
             * Some other user is still logged in.
             */
            throw new Exception("Some other user is logged in. You have to logout first!");
        }

        try {
            logger.debug("Wait for signIn button");
            waitShort.until(ExpectedConditions.presenceOfElementLocated(signinBy));
            logger.debug("waited for button and found it");
        } catch (TimeoutException exception) {
            logger.debug("Did not find a login button with wait: " + waitShort.toString());
            throw exception;
        }
        logger.debug("input userName");
        driver.findElement(usernameBy).clear();
        driver.findElement(usernameBy).sendKeys(userName);
        logger.debug("input passphrase");
        driver.findElement(passwordBy).clear();
        driver.findElement(passwordBy).sendKeys(passphrase);
        logger.debug("click signin");
        driver.findElement(signinBy).click();
        logger.debug("return new HomePage MenuTiles");
        return new HomePage(driver);
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
        logWithDetails("Search for logged in username");
        WebDriverWait waitShort = new WebDriverWait(driver, Duration.ofMillis(100));
        logger.debug("before try");
        try {
            logger.debug("before try");
            waitShort.until(ExpectedConditions.presenceOfElementLocated(userNameSpanBy));
            logger.debug("return the found username");
            return driver.findElement(userNameSpanBy).getText();
        } catch (Exception exception) {
            logger.debug("catch");
            logger.debug("Cannot find 'userNameSpan'. We might not be logged in.");
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
}
