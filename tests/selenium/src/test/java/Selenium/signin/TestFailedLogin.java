/*
 * Copyright (C) 2025 martin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Affero Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
package Selenium.signin;

/**
 *
 * @author martin
 */
import Selenium.LogoutPage;
import Selenium.PropertyFile;
import org.testng.Assert;
import Selenium.TestPage;
import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.testng.annotations.Test;
import org.testng.annotations.BeforeMethod;
import java.time.Duration;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

/**
 * Negative Login Tests
 *
 * Diese Testklasse prüft, dass Logins mit ungültigen Daten fehlschlagen
 *
 * @author Mandelkow
 */
public class TestFailedLogin extends TestPage {

    @BeforeMethod
    public void setupNegativeTest() throws Exception {
        driver = Selenium.driver.Wrapper.getDriver();
        propertyFile = new PropertyFile();
        String urlPageTest = propertyFile.getTestPageUrl();
        driver.get(urlPageTest);

        // Stelle sicher, dass wir ausgeloggt sind
        try {
            LogoutPage logoutPage = new LogoutPage();
            logoutPage.logout();
        } catch (Exception e) {
            // Ignorieren, wenn kein Logout möglich ist
        }
    }

    @Test(enabled = true, priority = 1)
    public void testLoginWithEmptyCredentials() {
        logger.info("Test: Login mit leeren Feldern");

        SignInPage signInPage = new SignInPage(driver);

        try {
            // Versuche Login mit leeren Feldern
            signInPage.loginInvalidUser("", "");

            // Prüfe, dass wir NICHT eingeloggt sind
            assertLoginFailed("Login sollte mit leeren Credentials fehlschlagen");

        } catch (Exception exception) {
            logger.error("Unerwarteter Fehler beim Test mit leeren Credentials", exception);
            Assert.fail("Test mit leeren Credentials ist fehlgeschlagen: " + exception.getMessage());
        }
    }

    @Test(enabled = true, priority = 2)
    public void testLoginWithEmptyUsername() {
        logger.info("Test: Login mit leerem Benutzernamen");

        SignInPage signInPage = new SignInPage(driver);
        String validPassword = propertyFile.getPdrUserPassword();

        try {
            // Versuche Login mit leerem Username
            signInPage.loginInvalidUser("", validPassword);

            // Prüfe, dass wir NICHT eingeloggt sind
            assertLoginFailed("Login sollte mit leerem Username fehlschlagen");

        } catch (Exception exception) {
            logger.error("Unerwarteter Fehler beim Test mit leerem Username", exception);
            Assert.fail("Test mit leerem Username ist fehlgeschlagen: " + exception.getMessage());
        }
    }

    @Test(enabled = true, priority = 3)
    public void testLoginWithEmptyPassword() {
        logger.info("Test: Login mit leerem Passwort");

        SignInPage signInPage = new SignInPage(driver);
        String validUsername = propertyFile.getPdrUserName();

        try {
            // Versuche Login mit leerem Passwort
            signInPage.loginInvalidUser(validUsername, "");

            // Prüfe, dass wir NICHT eingeloggt sind
            assertLoginFailed("Login sollte mit leerem Passwort fehlschlagen");

        } catch (Exception exception) {
            logger.error("Unerwarteter Fehler beim Test mit leerem Passwort", exception);
            Assert.fail("Test mit leerem Passwort ist fehlgeschlagen: " + exception.getMessage());
        }
    }

    @Test(enabled = true, priority = 4)
    public void testLoginWithWrongPassword() {
        logger.info("Test: Login mit falschem Passwort");

        SignInPage signInPage = new SignInPage(driver);
        String validUsername = propertyFile.getPdrUserName();
        String wrongPassword = "DiesIstEinFalschesPasswort123!";

        try {
            // Versuche Login mit falschem Passwort
            signInPage.loginInvalidUser(validUsername, wrongPassword);

            // Prüfe, dass wir NICHT eingeloggt sind
            assertLoginFailed("Login sollte mit falschem Passwort fehlschlagen");

            // Optional: Prüfe auf Fehlermeldung
            assertErrorMessageDisplayed();

        } catch (Exception exception) {
            logger.error("Unerwarteter Fehler beim Test mit falschem Passwort", exception);
            Assert.fail("Test mit falschem Passwort ist fehlgeschlagen: " + exception.getMessage());
        }
    }

    @Test(enabled = true, priority = 5)
    public void testLoginWithWrongUsername() {
        logger.info("Test: Login mit falschem Benutzernamen");

        SignInPage signInPage = new SignInPage(driver);
        String wrongUsername = "nichtexistierendernutzer123";
        String validPassword = propertyFile.getPdrUserPassword();

        try {
            // Versuche Login mit falschem Username
            signInPage.loginInvalidUser(wrongUsername, validPassword);

            // Prüfe, dass wir NICHT eingeloggt sind
            assertLoginFailed("Login sollte mit falschem Username fehlschlagen");

            // Optional: Prüfe auf Fehlermeldung
            assertErrorMessageDisplayed();

        } catch (Exception exception) {
            logger.error("Unerwarteter Fehler beim Test mit falschem Username", exception);
            Assert.fail("Test mit falschem Username ist fehlgeschlagen: " + exception.getMessage());
        }
    }

    @Test(enabled = true, priority = 6)
    public void testLoginWithBothWrong() {
        logger.info("Test: Login mit falschen Credentials");

        SignInPage signInPage = new SignInPage(driver);
        String wrongUsername = "falschernutzer";
        String wrongPassword = "falschespasswort";

        try {
            // Versuche Login mit falschen Credentials
            signInPage.loginInvalidUser(wrongUsername, wrongPassword);

            // Prüfe, dass wir NICHT eingeloggt sind
            assertLoginFailed("Login sollte mit falschen Credentials fehlschlagen");

            // Optional: Prüfe auf Fehlermeldung
            assertErrorMessageDisplayed();

        } catch (Exception exception) {
            logger.error("Unerwarteter Fehler beim Test mit falschen Credentials", exception);
            Assert.fail("Test mit falschen Credentials ist fehlgeschlagen: " + exception.getMessage());
        }
    }

    @Test(enabled = true, priority = 7)
    public void testLoginWithSQLInjection() {
        logger.info("Test: Login mit SQL Injection Versuch");

        SignInPage signInPage = new SignInPage(driver);
        String sqlInjection = "' OR '1'='1";

        try {
            // Versuche SQL Injection
            signInPage.loginInvalidUser(sqlInjection, sqlInjection);

            // Prüfe, dass wir NICHT eingeloggt sind
            assertLoginFailed("Login sollte bei SQL Injection Versuch fehlschlagen");

        } catch (Exception exception) {
            logger.error("Unerwarteter Fehler beim SQL Injection Test", exception);
            Assert.fail("SQL Injection Test ist fehlgeschlagen: " + exception.getMessage());
        }
    }

    @Test(enabled = true, priority = 8)
    public void testLoginWithSpecialCharacters() {
        logger.info("Test: Login mit Sonderzeichen");

        SignInPage signInPage = new SignInPage(driver);
        String specialChars = "!@#$%^&*()_+-=[]{}|;:',.<>?/~`";

        try {
            // Versuche Login mit Sonderzeichen
            signInPage.loginInvalidUser(specialChars, specialChars);

            // Prüfe, dass wir NICHT eingeloggt sind
            assertLoginFailed("Login sollte mit Sonderzeichen fehlschlagen");

        } catch (Exception exception) {
            logger.error("Unerwarteter Fehler beim Test mit Sonderzeichen", exception);
            Assert.fail("Test mit Sonderzeichen ist fehlgeschlagen: " + exception.getMessage());
        }
    }

    /**
     * Hilfsmethode: Prüft, dass der Login fehlgeschlagen ist
     */
    private void assertLoginFailed(String message) {
        try {
            // Prüfe, ob Login-Button noch vorhanden ist
            By signinBy = By.id("loginButtonSubmit");
            WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(2));
            wait.until(ExpectedConditions.presenceOfElementLocated(signinBy));

            // Login-Button ist noch da -> Login ist fehlgeschlagen (gut!)
            logger.info("Login ist erwartungsgemäß fehlgeschlagen");

        } catch (Exception e) {
            // Login-Button nicht gefunden -> möglicherweise eingeloggt (schlecht!)
            logger.error("Login-Button nicht gefunden - Login könnte erfolgreich gewesen sein!");
            Assert.fail(message);
        }

        // Zusätzlich prüfen, dass wir keinen eingeloggten User sehen
        SignInPage signInPage = new SignInPage(driver);
        String userName = signInPage.getUserNameText();
        Assert.assertNull(userName, "Es sollte kein eingeloggter User vorhanden sein, aber gefunden: " + userName);
    }

    /**
     * Hilfsmethode: Prüft, ob eine Fehlermeldung angezeigt wird
     */
    private void assertErrorMessageDisplayed() {
        try {
            // Passe den Selektor an deine Fehlermeldung an
            By errorMessageBy = By.className("error-message"); // Beispiel
            WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(2));
            WebElement errorMessage = wait.until(ExpectedConditions.presenceOfElementLocated(errorMessageBy));

            Assert.assertNotNull(errorMessage, "Fehlermeldung sollte angezeigt werden");
            String errorText = errorMessage.getText();
            Assert.assertFalse(errorText.isEmpty(), "Fehlermeldung sollte Text enthalten");
            logger.info("Fehlermeldung gefunden: " + errorText);

        } catch (Exception e) {
            logger.warn("Keine Fehlermeldung gefunden (optional)");
            // Nicht als Fehler werten, da dies optional ist
        }
    }
}
