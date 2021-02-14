package Selenium;

//import Selenium.SignInPage.SignInPage;
//import static Selenium.SignInPage.SignInPage.driver;
import java.io.IOException;
import java.net.MalformedURLException;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.util.logging.Level;
import java.util.logging.Logger;
import static org.junit.Assert.assertEquals;
import org.junit.Test;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.chrome.ChromeDriver;

/**
 *
 * @author Mandelkow
 */
public class TestLogin {

    static WebDriver driver;

    @Test
    public void testLogin() {
        System.setProperty("webdriver.chrome.driver", "C:\\Program Files\\chromedriver_87_win32\\chromedriver.exe");
        ChromeDriver driver = new ChromeDriver();

        WebDriver driver2 = Selenium.driver.Wrapper.getDriver();

        driver.get("https://martin-mandelkow.de/apotheke/dienstplan-test/");

        try {
            Selenium.signinpage.SignInPage signInPage = new Selenium.signinpage.SignInPage(driver);
            String pdr_user_password = Files.readAllLines(Paths.get("C:\\Users\\Mandelkow\\Nextcloud\\Dokumente\\Freizeit\\Verschlüsselung\\pdr_user_password_selenium")).get(0);
            String pdr_user_name = "selenium_test_user";
            HomePage homePage = signInPage.loginValidUser(pdr_user_name, pdr_user_password);
            assertEquals(homePage.getUserNameText(), pdr_user_name);
            driver.quit();
        }
        catch (MalformedURLException exception) {
            Logger.getLogger(TestLogin.class.getName()).log(Level.SEVERE, null, exception);
        }
        catch (IOException exception) {
            Logger.getLogger(TestLogin.class.getName()).log(Level.SEVERE, null, exception);
        }
        finally {
            driver.quit();
        }
    }
}
//
