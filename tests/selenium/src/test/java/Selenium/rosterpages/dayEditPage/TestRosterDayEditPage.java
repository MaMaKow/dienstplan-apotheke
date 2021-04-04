package Selenium.rosterpages.dayEditPage;

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

    @Test(enabled = true)
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
        } catch (MalformedURLException exception) {
            Logger.getLogger(TestRosterDayEditPage.class.getName()).log(Level.SEVERE, null, exception);
        } catch (IOException exception) {
            Logger.getLogger(TestRosterDayEditPage.class.getName()).log(Level.SEVERE, null, exception);
        } catch (Exception exception) {
            Logger.getLogger(TestRosterDayEditPage.class.getName()).log(Level.SEVERE, null, exception);
        }
    }

    @Test(enabled = true)
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
            /*
            System.out.println(rosterItem);
            for (Field field : rosterItem.getClass().getDeclaredFields()) {
                field.setAccessible(true);
                String name = field.getName();
                Object value = field.get(rosterItem);
                System.out.printf("%s: %s%n", name, value);
            }
             */
            String employeeNameHash = DigestUtils.md5Hex(rosterItem.getEmployeeName());
            assertEquals("3013ebe621dbc5e7f4791d17913f0950", employeeNameHash);
            assertEquals(1, rosterItem.getDate().get(Calendar.DAY_OF_MONTH));
            assertEquals(6, rosterItem.getDate().get(Calendar.MONTH)); //5 is June, 0 is January
            assertEquals("08:00", rosterItem.getDutyStart());
            assertEquals("15:00", rosterItem.getDutyEnd());
            assertEquals("11:30", rosterItem.getBreakStart());
            assertEquals("12:00", rosterItem.getBreakEnd());
        } catch (MalformedURLException exception) {
            Logger.getLogger(TestRosterDayEditPage.class.getName()).log(Level.SEVERE, null, exception);
        } catch (IOException exception) {
            Logger.getLogger(TestRosterDayEditPage.class.getName()).log(Level.SEVERE, null, exception);
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
