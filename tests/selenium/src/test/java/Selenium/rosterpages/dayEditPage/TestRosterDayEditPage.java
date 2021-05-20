package Selenium.rosterpages.dayEditPage;

import Selenium.HomePage;
import Selenium.ReadPropertyFile;
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
import org.testng.annotations.Test;

import org.openqa.selenium.WebDriver;
import static org.testng.Assert.assertEquals;
import org.testng.ITestResult;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;

/**
 *
 * @author Mandelkow
 */
public class TestRosterDayEditPage {

    WebDriver driver;

    @Test(enabled = false)/*passed*/
    public void testDateNavigation() {
        try {
            driver = Selenium.driver.Wrapper.getDriver();
            ReadPropertyFile readPropertyFile = new ReadPropertyFile();
            String urlPageTest = readPropertyFile.getUrlPageTest();
            driver.get(urlPageTest);

            /**
             * Sign in:
             */
            SignInPage signInPage = new SignInPage(driver);
            String pdr_user_password = readPropertyFile.getPdrUserPassword();
            String pdr_user_name = readPropertyFile.getPdrUserName();
            signInPage.loginValidUser(pdr_user_name, pdr_user_password);
            RosterDayEditPage rosterWeekTablePage = new RosterDayEditPage(driver);
            assertEquals(rosterWeekTablePage.getUserNameText(), pdr_user_name);
            /**
             * Move to specific date and go foreward and backward from there:
             */
            rosterWeekTablePage.goToDate("01.07.2020"); //This date is a wednesday.
            assertEquals("2020-07-01", rosterWeekTablePage.getDate()); //This is the corresponding monday.
            rosterWeekTablePage.moveDayBackward();
            assertEquals("2020-06-30", rosterWeekTablePage.getDate()); //This is the corresponding monday.
            rosterWeekTablePage.moveDayForward();
            assertEquals("2020-07-01", rosterWeekTablePage.getDate()); //This is the corresponding monday.
        } catch (Exception exception) {
            Logger.getLogger(TestRosterDayEditPage.class.getName()).log(Level.SEVERE, null, exception);
        }
    }

    @Test(enabled = true)/*passed*/
    public void testRosterDisplay() throws Exception {
        driver = Selenium.driver.Wrapper.getDriver();
        ReadPropertyFile readPropertyFile = new ReadPropertyFile();
        String urlPageTest = readPropertyFile.getUrlPageTest();
        driver.get(urlPageTest);
        /**
         * Sign in:
         */
        SignInPage signInPage = new SignInPage(driver);
        String pdr_user_password = readPropertyFile.getPdrUserPassword();
        String pdr_user_name = readPropertyFile.getPdrUserName();
        HomePage homePage = signInPage.loginValidUser(pdr_user_name, pdr_user_password);
        RosterDayEditPage rosterWeekTablePage = new RosterDayEditPage(driver);
        assertEquals(rosterWeekTablePage.getUserNameText(), pdr_user_name);
        /**
         * Move to specific date to get a specific roster:
         */
        rosterWeekTablePage.goToDate("01.07.2020"); //This date is a wednesday.
        assertEquals("2020-07-01", rosterWeekTablePage.getDate()); //This is the corresponding monday.
        /**
         * Get roster items and compare to assertions:
         */
        RosterItem rosterItem = rosterWeekTablePage.getRosterItem(2);
        //assertEquals("Tuesday 30.06.", rosterItem.getDateString());
        String employeeNameHash = DigestUtils.md5Hex(rosterItem.getEmployeeName());
        assertEquals("74f66fde3d90d47d20c8402fec499fb8", employeeNameHash);
        assertEquals(1, rosterItem.getDate().get(Calendar.DAY_OF_MONTH));
        assertEquals(6, rosterItem.getDate().get(Calendar.MONTH)); //5 is June, 0 is January
        assertEquals("08:00", rosterItem.getDutyStart());
        assertEquals("16:30", rosterItem.getDutyEnd());
        assertEquals("12:00", rosterItem.getBreakStart());
        assertEquals("12:30", rosterItem.getBreakEnd());
    }

    @BeforeMethod
    public void setUp() {
        /*driver = */
        Selenium.driver.Wrapper.createNewDriver();
    }

    @AfterMethod
    public void tearDown(ITestResult testResult) {
        driver = Selenium.driver.Wrapper.getDriver();
        new ScreenShot(testResult);
        driver.quit();

    }
}
