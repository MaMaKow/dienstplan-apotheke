/*
 * Copyright (C) 2021 Mandelkow
 *
 * Dienstplan Apotheke
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
package Selenium.RosterPages;

import Selenium.HomePage;
import Selenium.ReadPropertyFile;
import Selenium.RosterItem;
import Selenium.ScreenShot;
import Selenium.signinpage.SignInPage;
import biweekly.Biweekly;
import biweekly.ICalendar;
import biweekly.component.VEvent;
import biweekly.io.text.ICalReader;
import biweekly.property.DateEnd;
import biweekly.property.DateStart;
import biweekly.property.Summary;
import biweekly.util.ICalDate;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.text.DateFormat;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;
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
public class TestRosterEmployeePage {

    WebDriver driver;
    String iCalendarFileName = "Calendar.ics";

    @Test(enabled = false)/*passed*/
    public void testDateNavigation() {
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
        RosterEmployeePage rosterEmployeePage = new RosterEmployeePage(driver);
        Assert.assertEquals(rosterEmployeePage.getUserNameText(), pdr_user_name);
        /**
         * Move to specific date and go foreward and backward from there:
         */

        rosterEmployeePage = rosterEmployeePage.goToDate("01.07.2020"); //This date is a wednesday.
        Assert.assertEquals(rosterEmployeePage.getDate(), "2020-06-29"); //This is the corresponding monday.
        rosterEmployeePage = rosterEmployeePage.moveWeekBackward();
        Assert.assertEquals(rosterEmployeePage.getDate(), "2020-06-22"); //This is the corresponding monday.
        rosterEmployeePage = rosterEmployeePage.moveWeekForward();
        Assert.assertEquals(rosterEmployeePage.getDate(), "2020-06-29"); //This is the corresponding monday.
    }

    @Test(enabled = false)/*passed*/
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
        RosterEmployeePage rosterEmployeePage = new RosterEmployeePage(driver);
        Assert.assertEquals(rosterEmployeePage.getUserNameText(), pdr_user_name);
        /**
         * Move to specific date to get a specific roster:
         */
        rosterEmployeePage = rosterEmployeePage.selectEmployee(5);
        rosterEmployeePage = rosterEmployeePage.goToDate("01.07.2020"); //This date is a wednesday.
        Assert.assertEquals(rosterEmployeePage.getDate(), "2020-06-29"); //This is the corresponding monday.
        /**
         * Get roster items and compare to assertions:
         */
        RosterItem rosterItem = rosterEmployeePage.getRosterItem(2, 1);
        String employeeNameHash = DigestUtils.md5Hex(rosterItem.getEmployeeName());
        Assert.assertEquals(employeeNameHash, "d41d8cd98f00b204e9800998ecf8427e");
        Assert.assertEquals(30, rosterItem.getDate().get(Calendar.DAY_OF_MONTH));
        Assert.assertEquals(5, rosterItem.getDate().get(Calendar.MONTH)); //5 is June, 0 is January
        Assert.assertEquals("08:00", rosterItem.getDutyStart());
        Assert.assertEquals("16:30", rosterItem.getDutyEnd());
        Assert.assertEquals("12:00", rosterItem.getBreakStart());
        Assert.assertEquals("12:30", rosterItem.getBreakEnd());
    }

    @Test(enabled = false)/*passed*/
    public void testDownloadICSFile() throws IOException, ParseException {

        driver = Selenium.driver.Wrapper.getDriver();
        ReadPropertyFile readPropertyFile = new ReadPropertyFile();
        String urlPageTest = readPropertyFile.getUrlPageTest();
        driver.get(urlPageTest);

        //Sign in:
        SignInPage signInPage = new SignInPage(driver);
        String pdr_user_password = readPropertyFile.getPdrUserPassword();
        String pdr_user_name = readPropertyFile.getPdrUserName();
        HomePage homePage = signInPage.loginValidUser(pdr_user_name, pdr_user_password);
        RosterEmployeePage rosterEmployeePage = new RosterEmployeePage(driver);
        Assert.assertEquals(rosterEmployeePage.getUserNameText(), pdr_user_name);
        //Move to specific date to get a specific roster:

        rosterEmployeePage = rosterEmployeePage.selectEmployee(5);
        rosterEmployeePage = rosterEmployeePage.goToDate("01.07.2020");
        //This date is a wednesday.
        Assert.assertEquals(rosterEmployeePage.getDate(), "2020-06-29");
        //This is the corresponding monday. Download the ICS file:
        /**
         * Get roster items and compare to assertions:
         */
        RosterItem rosterItem = rosterEmployeePage.getRosterItem(2, 1);
        rosterEmployeePage.downloadICSFile();

        try {
            File file = new File("Calendar.ics");
            ICalReader reader = new ICalReader(file);
            try {
                ICalendar ical;
                DateFormat df = new SimpleDateFormat("dd.MM.yyyy HH:mm");
                while ((ical = reader.readNext()) != null) {
                    for (VEvent event : ical.getEvents()) {
                        DateStart dateStart = event.getDateStart();
                        String dateStartStr = (dateStart == null) ? null : df.format(dateStart.getValue());
                        DateEnd dateEnd = event.getDateEnd();
                        //String dateEndStr = (dateEnd == null) ? null : df.format(dateEnd.getValue());
                        ICalDate dateEndICalDate = dateEnd.getValue();
                        String dateEndStr = dateEndICalDate.toString();

                        Summary summary = event.getSummary();
                        String summaryStr = (summary == null) ? null : summary.getValue();

                        if (summaryStr != null && dateStartStr != null) {
                            System.out.println(dateStartStr + " - " + dateEndStr + ": " + summaryStr);
                            continue;
                        }

                        if (summaryStr != null) {
                            System.out.println(summaryStr);
                            continue;
                        }

                        if (dateStartStr != null) {
                            System.out.println(dateStartStr);
                            continue;
                        }
                    }
                }
            } catch (IOException ex) {
                Logger.getLogger(TestRosterEmployeePage.class.getName()).log(Level.SEVERE, null, ex);
            } finally {
                reader.close();
            }
        } catch (FileNotFoundException ex) {
            Logger.getLogger(TestRosterEmployeePage.class.getName()).log(Level.SEVERE, null, ex);

        }
        String iCalendarString;
        iCalendarString = Files.readString(Path.of(iCalendarFileName));
        ICalendar ical = Biweekly.parse(iCalendarString).first();
        VEvent event = ical.getEvents().get(1);

        DateFormat timeDateFormat = new SimpleDateFormat("HH:mm");
        DateFormat dayDateFormat = new SimpleDateFormat("dd.MM.");

        String summary = event.getSummary().getValue();
        DateStart dateStart = event.getDateStart();
        DateEnd dateEnd = event.getDateEnd();

        Assert.assertEquals(dayDateFormat.format(dateStart.getValue()), dayDateFormat.format(rosterItem.getDate().getTime()));
        Assert.assertEquals(dayDateFormat.format(dateEnd.getValue()), dayDateFormat.format(rosterItem.getDate().getTime()));
        Assert.assertEquals(timeDateFormat.format(dateStart.getValue()), rosterItem.getDutyStart());
        Assert.assertEquals(timeDateFormat.format(dateEnd.getValue()), rosterItem.getDutyEnd());
        Assert.assertTrue(summary.contains(rosterItem.getBranchName()));
        /*
        Finally delete the iCalendar file:
         */
        File file = new File("Calendar.ics");
        Files.deleteIfExists(file.toPath());

    }

    @BeforeMethod
    public void setUp() {
        try {
            File file = new File("Calendar.ics");
            Files.deleteIfExists(file.toPath());
        } catch (IOException ex) {
            Logger.getLogger(TestRosterEmployeePage.class.getName()).log(Level.SEVERE, null, ex);
        }

        Selenium.driver.Wrapper.createNewDriver();
    }

    @AfterMethod
    public void tearDown(ITestResult testResult) {
        driver = Selenium.driver.Wrapper.getDriver();
        new ScreenShot(testResult);
        if (testResult.getStatus() != ITestResult.FAILURE) {
            driver.quit();
        }
    }

}
