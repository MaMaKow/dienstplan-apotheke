package Selenium.rosterpages.weekTablePage;

import Selenium.HomePage;
import Selenium.RosterItem;
import Selenium.signinpage.SignInPage;
import java.io.File;
import java.io.IOException;
import java.net.MalformedURLException;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.text.SimpleDateFormat;
import java.util.Arrays;
import java.util.Calendar;
import java.util.GregorianCalendar;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.apache.commons.codec.digest.DigestUtils;
import org.apache.commons.io.FileUtils;
import static org.junit.Assert.assertEquals;
//import org.junit.Test;
import org.testng.annotations.Test;

import org.openqa.selenium.OutputType;
import org.openqa.selenium.TakesScreenshot;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.testng.ITestResult;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;

/**
 *
 * @author Mandelkow
 */
public class TestRosterWeekTablePage {

    @Test
    public void testDateNavigation() {
        try {
            WebDriver driver = Selenium.driver.Wrapper.getDriver();
            //driver.get("https://martin-mandelkow.de/apotheke/dienstplan-test/");
            driver.get("https://localhost/dienstplan/");

            /**
             * Sign in:
             */
            SignInPage signInPage = new SignInPage(driver);
            String pdr_user_password = Files.readAllLines(Paths.get("C:\\Users\\Mandelkow\\Nextcloud\\Dokumente\\Freizeit\\Verschlüsselung\\pdr_user_password_selenium")).get(0);
            String pdr_user_name = "selenium_test_user";
            signInPage.loginValidUser(pdr_user_name, pdr_user_password);
            RosterWeekTablePage rosterWeekTablePage = new RosterWeekTablePage(driver);
            assertEquals(rosterWeekTablePage.getUserNameText(), pdr_user_name);
            /**
             * Move to specific date and go foreward and backward from there:
             */
            rosterWeekTablePage.goToDate("01.07.2020"); //This date is a wednesday.
            assertEquals(rosterWeekTablePage.getDate(), "2020-06-29"); //This is the corresponding monday.
            rosterWeekTablePage.moveWeekBackward();
            assertEquals(rosterWeekTablePage.getDate(), "2020-06-22"); //This is the corresponding monday.
            rosterWeekTablePage.moveWeekForward();
            assertEquals(rosterWeekTablePage.getDate(), "2020-06-29"); //This is the corresponding monday.
        }
        catch (MalformedURLException exception) {
            Logger.getLogger(TestRosterWeekTablePage.class.getName()).log(Level.SEVERE, null, exception);
        }
        catch (IOException exception) {
            Logger.getLogger(TestRosterWeekTablePage.class.getName()).log(Level.SEVERE, null, exception);
        }
        catch (Exception exception) {
            Logger.getLogger(TestRosterWeekTablePage.class.getName()).log(Level.SEVERE, null, exception);
        }
    }

    @Test
    public void testRosterDisplay() throws Exception {
        try {
            WebDriver driver = Selenium.driver.Wrapper.getDriver();
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
            /**
             * Move to specific date to get a specific roster:
             */
            rosterWeekTablePage.goToDate("01.07.2020"); //This date is a wednesday.
            assertEquals(rosterWeekTablePage.getDate(), "2020-06-29"); //This is the corresponding monday.
            /**
             * Get roster items and compare to assertions:
             * <p lang=en>
             * TODO: Create the following classes: RosterTable, RosterTableRow.
             * RosterTableItem RosterTableDay will not work, I guess
             * </p>
             * <p lang=de>
             * TODO: Jetzt funktioniert der Test erst mal. Der sollte aber eine
             * ordentliche Methode bekommen. Das PageObjectModel muss die
             * notwendigen Funktionen/Klassen bekommen.
             * </p>
             */
            RosterItem rosterItem = rosterWeekTablePage.getRosterItem(2, 3);
            String hash = DigestUtils.md5Hex(rosterItem.getEmployeeName());
            assertEquals(hash, "7224dea417825343c5645dd5c6f2cde8");
            assertEquals(rosterItem.getDateString(), "Tuesday 30.06.");
            assertEquals(rosterItem.getDate().get(Calendar.DAY_OF_MONTH), 30);
            assertEquals(rosterItem.getDate().get(Calendar.MONTH), 5); //5 is June, 0 is January
            assertEquals(rosterItem.getDutyStart(), "08:00");
            assertEquals(rosterItem.getDutyEnd(), "16:30");
            assertEquals(rosterItem.getBreakStart(), "11:30");
            assertEquals(rosterItem.getBreakEnd(), "12:00");
            //TODO: Also test other roster items?
        }
        catch (MalformedURLException exception) {
            Logger.getLogger(TestRosterWeekTablePage.class.getName()).log(Level.SEVERE, null, exception);
        }
        catch (IOException exception) {
            Logger.getLogger(TestRosterWeekTablePage.class.getName()).log(Level.SEVERE, null, exception);
        }
    }

    @BeforeMethod
    public void setUp() {
        Selenium.driver.Wrapper.createNewDriver();
    }

    @AfterMethod
    public void takeScreenShotOnFailure(ITestResult testResult) throws IOException {
        WebDriver driver = Selenium.driver.Wrapper.getDriver();
        if (testResult.getStatus() == ITestResult.FAILURE) {
            File scrFile = ((TakesScreenshot) driver).getScreenshotAs(OutputType.FILE);
            FileUtils.copyFile(scrFile, new File("errorScreenshots\\" + testResult.getName() + "-"
                    + Arrays.toString(testResult.getParameters()) + ".jpg"));
        }
        driver.quit();
    }
}
