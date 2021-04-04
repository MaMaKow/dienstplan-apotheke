package Selenium.signinpage;

import Selenium.HomePage;
import Selenium.ScreenShot;
import java.io.IOException;
import java.net.MalformedURLException;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.openqa.selenium.WebDriver;
import static org.testng.Assert.assertEquals;
import org.testng.ITestResult;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.Test;

/**
 *
 * @author Mandelkow
 */
public class TestLogin {

    // static WebDriver driver;
    @Test(enabled = true)
    public void testLogin() {
        WebDriver driver = Selenium.driver.Wrapper.getDriver();

        driver.get("https://martin-mandelkow.de/apotheke/dienstplan-test/");
        //driver.get("https://localhost/dienstplan/");

        try {
            Selenium.signinpage.SignInPage signInPage = new Selenium.signinpage.SignInPage(driver);
            String pdr_user_password = Files.readAllLines(Paths.get("C:\\Users\\Mandelkow\\Nextcloud\\Dokumente\\Freizeit\\Verschlüsselung\\pdr_user_password_selenium")).get(0);
            String pdr_user_name = "selenium_test_user";
            HomePage homePage = signInPage.loginValidUser(pdr_user_name, pdr_user_password);
            assertEquals(pdr_user_name, homePage.getUserNameText());
        } catch (MalformedURLException exception) {
            Logger.getLogger(TestLogin.class.getName()).log(Level.SEVERE, null, exception);
        } catch (IOException exception) {
            Logger.getLogger(TestLogin.class.getName()).log(Level.SEVERE, null, exception);
        }
    }

    @AfterMethod
    public void tearDown(ITestResult testResult) {
        WebDriver driver = Selenium.driver.Wrapper.getDriver();
        new ScreenShot(testResult);
        driver.quit();

    }

}
//
