package Selenium.rosterpages.weekTablePage;

import Selenium.HomePage;
import Selenium.RosterItem;
import Selenium.ScreenShot;
import Selenium.signinpage.SignInPage;
import java.io.IOException;
import java.net.MalformedURLException;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.util.Calendar;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.apache.commons.codec.digest.DigestUtils;
//import org.junit.Test;
import org.testng.annotations.Test;
import org.testng.Assert;

import org.openqa.selenium.WebDriver;
import org.testng.ITestResult;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;

/**
 *
 * @author Mandelkow
 */
public class TestRosterWeekTablePage {

    @Test(enabled = false)
    public void testDateNavigation() {
        try {
            WebDriver driver = Selenium.driver.Wrapper.getDriver();
            driver.get("https://martin-mandelkow.de/apotheke/dienstplan-test/");
            //driver.get("https://localhost/dienstplan/");

            /**
             * Sign in:
             */
            SignInPage signInPage = new SignInPage(driver);
            String pdr_user_password = Files.readAllLines(Paths.get("C:\\Users\\Mandelkow\\Nextcloud\\Dokumente\\Freizeit\\Verschlüsselung\\pdr_user_password_selenium")).get(0);
            String pdr_user_name = "selenium_test_user";
            signInPage.loginValidUser(pdr_user_name, pdr_user_password);
            RosterWeekTablePage rosterWeekTablePage = new RosterWeekTablePage(driver);
            Assert.assertEquals(rosterWeekTablePage.getUserNameText(), pdr_user_name);
            /**
             * Move to specific date and go foreward and backward from there:
             */
            rosterWeekTablePage.goToDate("01.07.2020"); //This date is a wednesday.
            Assert.assertEquals(rosterWeekTablePage.getDate(), "2020-06-29"); //This is the corresponding monday.
            rosterWeekTablePage.moveWeekBackward();
            Assert.assertEquals(rosterWeekTablePage.getDate(), "2020-06-22"); //This is the corresponding monday.
            rosterWeekTablePage.moveWeekForward();
            Assert.assertEquals(rosterWeekTablePage.getDate(), "2020-06-29"); //This is the corresponding monday.
        } catch (MalformedURLException exception) {
            Logger.getLogger(TestRosterWeekTablePage.class.getName()).log(Level.SEVERE, null, exception);
        } catch (IOException exception) {
            Logger.getLogger(TestRosterWeekTablePage.class.getName()).log(Level.SEVERE, null, exception);
        } catch (Exception exception) {
            Logger.getLogger(TestRosterWeekTablePage.class.getName()).log(Level.SEVERE, null, exception);
        }
    }

    @Test(enabled = false)
    public void testRosterDisplay() throws Exception {
        try {
            WebDriver driver = Selenium.driver.Wrapper.getDriver();
            driver.get("https://martin-mandelkow.de/apotheke/dienstplan-test/");
            //driver.get("https://localhost/dienstplan/");

            /**
             * Sign in:
             */
            SignInPage signInPage = new SignInPage(driver);
            String pdr_user_password = Files.readAllLines(Paths.get("C:\\Users\\Mandelkow\\Nextcloud\\Dokumente\\Freizeit\\Verschlüsselung\\pdr_user_password_selenium")).get(0);
            String pdr_user_name = "selenium_test_user";
            HomePage homePage = signInPage.loginValidUser(pdr_user_name, pdr_user_password);
            RosterWeekTablePage rosterWeekTablePage = new RosterWeekTablePage(driver);
            Assert.assertEquals(rosterWeekTablePage.getUserNameText(), pdr_user_name);
            /**
             * Move to specific date to get a specific roster:
             */
            rosterWeekTablePage.goToDate("01.07.2020"); //This date is a wednesday.
            Assert.assertEquals(rosterWeekTablePage.getDate(), "2020-06-29"); //This is the corresponding monday.
            /**
             * Get roster items and compare to assertions:
             */
            RosterItem rosterItem = rosterWeekTablePage.getRosterItem(2, 3);
            String employeeNameHash = DigestUtils.md5Hex(rosterItem.getEmployeeName());
            Assert.assertEquals(employeeNameHash, "7224dea417825343c5645dd5c6f2cde8");
            Assert.assertEquals(30, rosterItem.getDate().get(Calendar.DAY_OF_MONTH));
            Assert.assertEquals(5, rosterItem.getDate().get(Calendar.MONTH)); //5 is June, 0 is January
            Assert.assertEquals("08:00", rosterItem.getDutyStart());
            Assert.assertEquals("16:30", rosterItem.getDutyEnd());
            Assert.assertEquals("11:30", rosterItem.getBreakStart());
            Assert.assertEquals("12:00", rosterItem.getBreakEnd());
        } catch (MalformedURLException exception) {
            Logger.getLogger(TestRosterWeekTablePage.class.getName()).log(Level.SEVERE, null, exception);
        } catch (IOException exception) {
            Logger.getLogger(TestRosterWeekTablePage.class.getName()).log(Level.SEVERE, null, exception);
        }
    }

    @BeforeMethod
    public void setUp() {
        Selenium.driver.Wrapper.createNewDriver();
    }

    @AfterMethod
    public void tearDown(ITestResult testResult) {
        WebDriver driver = Selenium.driver.Wrapper.getDriver();
        new ScreenShot(testResult);
        driver.quit();

    }

}
