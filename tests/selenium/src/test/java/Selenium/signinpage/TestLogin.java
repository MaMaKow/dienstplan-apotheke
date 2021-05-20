package Selenium.signinpage;

import Selenium.HomePage;
import Selenium.ReadPropertyFile;
import Selenium.ScreenShot;
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

    WebDriver driver;

    // static WebDriver driver;
    @Test(enabled = false)/*passed*/
    public void testLogin() {
        driver = Selenium.driver.Wrapper.getDriver();
        ReadPropertyFile readPropertyFile = new ReadPropertyFile();
        String urlPageTest = readPropertyFile.getUrlPageTest();
        driver.get(urlPageTest);

        Selenium.signinpage.SignInPage signInPage = new Selenium.signinpage.SignInPage(driver);
        String pdr_user_password = readPropertyFile.getPdrUserPassword();
        String pdr_user_name = readPropertyFile.getPdrUserName();
        HomePage homePage = signInPage.loginValidUser(pdr_user_name, pdr_user_password);
        assertEquals(pdr_user_name, homePage.getUserNameText());
    }

    @AfterMethod
    public void tearDown(ITestResult testResult) {
        driver = Selenium.driver.Wrapper.getDriver();
        new ScreenShot(testResult);
        driver.quit();

    }

}
