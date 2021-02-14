package Selenium;

import Selenium.rosterpages.weekTablePage.RosterWeekTablePage;
import Selenium.signinpage.SignInPage;
import java.io.File;
import java.io.IOException;
import java.net.MalformedURLException;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.util.Arrays;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.apache.commons.io.FileUtils;
import static org.junit.Assert.assertEquals;
//import org.junit.Test;
import org.testng.annotations.Test;

import org.openqa.selenium.OutputType;
import org.openqa.selenium.TakesScreenshot;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.chrome.ChromeOptions;
import org.testng.ITestResult;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.BeforeTest;

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
            HomePage homePage = signInPage.loginValidUser(pdr_user_name, pdr_user_password);
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
            WebElement duty_roter_table_fooElement = rosterWeekTablePage.getXpathElement();
            assertEquals(duty_roter_table_fooElement.getText(), "16:30");
            throw new Exception("Not implemented yet");
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
        System.out.println(driver);
        if (testResult.getStatus() == ITestResult.FAILURE) {
            File scrFile = ((TakesScreenshot) driver).getScreenshotAs(OutputType.FILE);
            FileUtils.copyFile(scrFile, new File("errorScreenshots\\" + testResult.getName() + "-"
                    + Arrays.toString(testResult.getParameters()) + ".jpg"));
        }
        System.out.println(driver);
        driver.quit();
    }
}
//
