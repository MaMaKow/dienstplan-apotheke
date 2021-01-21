package Selenium;

import static Selenium.SignInPage.driver;
import java.io.IOException;
import java.net.MalformedURLException;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.util.logging.Level;
import java.util.logging.Logger;
import static org.junit.Assert.assertEquals;
import org.junit.Test;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.chrome.ChromeOptions;

/**
 *
 * @author Mandelkow
 */
public class TestRosterWeekTablePage {

    @Test
    public void testDateNavigation() {
        try {
            System.setProperty("webdriver.chrome.driver", "C:\\Program Files\\chromedriver_87_win32\\chromedriver.exe");
            ChromeOptions options = new ChromeOptions();
            options.addArguments("ignore-certificate-errors");
            driver = new ChromeDriver(options);
            //driver.get("https://martin-mandelkow.de/apotheke/dienstplan-test/");
            driver.get("https://localhost/dienstplan/");

            /**
             * Sign in:
             */
            SignInPage signInPage = new SignInPage(driver);
            String pdr_user_password = Files.readAllLines(Paths.get("C:\\Users\\Mandelkow\\Nextcloud\\Dokumente\\Freizeit\\Verschlüsselung\\pdr_user_password_selenium")).get(0);
            String pdr_user_name = "selenium_test_user";
            HomePage homePage = signInPage.loginValidUser(pdr_user_name, pdr_user_password);
            RosterWeekTablePage rosterWeekTablePage = new RosterWeekTablePage(driver);
            assertEquals(rosterWeekTablePage.getUserNameText(), pdr_user_name);
            rosterWeekTablePage.goToDate("01.07.2020"); //This date is a wednesday.
            assertEquals(rosterWeekTablePage.getDate(), "2020-06-29"); //This is the corresponding monday.
            rosterWeekTablePage.moveWeekBackward();
            assertEquals(rosterWeekTablePage.getDate(), "2020-06-22"); //This is the corresponding monday.
            rosterWeekTablePage.moveWeekForward();
            assertEquals(rosterWeekTablePage.getDate(), "2020-06-29"); //This is the corresponding monday.
            driver.quit();
        } catch (MalformedURLException exception) {
            Logger.getLogger(TestRosterWeekTablePage.class.getName()).log(Level.SEVERE, null, exception);
        } catch (IOException exception) {
            Logger.getLogger(TestRosterWeekTablePage.class.getName()).log(Level.SEVERE, null, exception);
        } finally {
            driver.quit();
        }
    }
}
//
