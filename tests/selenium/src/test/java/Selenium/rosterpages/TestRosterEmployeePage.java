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
package Selenium.rosterpages;

import Selenium.HomePage;
import Selenium.NetworkOfBranchOffices;
import Selenium.PropertyFile;
import Selenium.Roster;
import Selenium.RosterItem;
import Selenium.ScreenShot;
import Selenium.signin.SignInPage;
import biweekly.Biweekly;
import biweekly.ICalendar;
import biweekly.component.VEvent;
import biweekly.property.DateEnd;
import biweekly.property.DateStart;
import java.io.File;
import java.io.IOException;
import java.nio.charset.Charset;
import java.nio.file.Files;
import java.nio.file.Path;
import java.text.DateFormat;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.time.DayOfWeek;
import java.time.LocalDate;
import java.time.ZoneId;
import java.time.format.DateTimeFormatter;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.testng.annotations.Test;
import org.testng.Assert;

import org.openqa.selenium.WebDriver;
import org.testng.ITestResult;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;
import org.testng.asserts.SoftAssert;
import org.threeten.extra.YearWeek;

/**
 *
 * @author Mandelkow
 */
public class TestRosterEmployeePage {

    WebDriver driver;
    String iCalendarFileName = "Calendar.ics";
    SoftAssert softAssert = new SoftAssert();

    @Test(enabled = true)/*passed*/
    public void testDateNavigation() {
        driver = Selenium.driver.Wrapper.getDriver();
        PropertyFile propertyFile = new PropertyFile();
        String urlPageTest = propertyFile.getUrlPageTest();
        driver.get(urlPageTest);

        /**
         * Sign in:
         */
        SignInPage signInPage = new SignInPage(driver);
        String pdr_user_password = propertyFile.getPdrUserPassword();
        String pdr_user_name = propertyFile.getPdrUserName();
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

    @Test(enabled = true)/*failed*/
    public void testRosterDisplay() throws Exception {
        driver = Selenium.driver.Wrapper.getDriver();
        PropertyFile propertyFile = new PropertyFile();
        String urlPageTest = propertyFile.getUrlPageTest();
        driver.get(urlPageTest);
        /**
         * Sign in:
         */
        SignInPage signInPage = new SignInPage(driver);
        String pdr_user_password = propertyFile.getPdrUserPassword();
        String pdr_user_name = propertyFile.getPdrUserName();
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
         * Read the roster from json files and find the same values in the
         * employee pages:
         */
        Roster roster = new Roster();
        HashMap<LocalDate, HashMap> listOfRosterDays = roster.getListOfRosterDays();
        for (HashMap<Integer, RosterItem> listOfRosterItems : listOfRosterDays.values()) {
            for (RosterItem rosterItemFromPrediction : listOfRosterItems.values()) {
                /**
                 * Move to specific date to get a specific roster:
                 */
                rosterEmployeePage.goToDate(rosterItemFromPrediction.getLocalDate());
                rosterEmployeePage = rosterEmployeePage.selectEmployee(rosterItemFromPrediction.getEmployeeId());
                /**
                 * Get roster items and compare to assertions:
                 */
                RosterItem rosterItemReadOnPage = rosterEmployeePage.getRosterItem(
                        rosterItemFromPrediction.getLocalDate().getDayOfWeek()
                );

                softAssert.assertEquals(rosterItemFromPrediction.getEmployeeName(), rosterItemReadOnPage.getEmployeeName());
                softAssert.assertEquals(rosterItemFromPrediction.getLocalDate(), rosterItemReadOnPage.getLocalDate());
                softAssert.assertEquals(rosterItemFromPrediction.getDutyStart(), rosterItemReadOnPage.getDutyStart());
                softAssert.assertEquals(rosterItemFromPrediction.getDutyEnd(), rosterItemReadOnPage.getDutyEnd());
                softAssert.assertEquals(rosterItemFromPrediction.getBreakStart(), rosterItemReadOnPage.getBreakStart());
                softAssert.assertEquals(rosterItemFromPrediction.getBreakEnd(), rosterItemReadOnPage.getBreakEnd());
                softAssert.assertAll();
            }
        }

    }

    @Test(enabled = true)/*failed*/
    public void testDownloadICSFile() throws IOException, ParseException {

        driver = Selenium.driver.Wrapper.getDriver();
        PropertyFile propertyFile = new PropertyFile();
        String urlPageTest = propertyFile.getUrlPageTest();
        driver.get(urlPageTest);

        //Sign in:
        SignInPage signInPage = new SignInPage(driver);
        String pdr_user_password = propertyFile.getPdrUserPassword();
        String pdr_user_name = propertyFile.getPdrUserName();
        HomePage homePage = signInPage.loginValidUser(pdr_user_name, pdr_user_password);
        RosterEmployeePage rosterEmployeePage = new RosterEmployeePage(driver);
        Assert.assertEquals(rosterEmployeePage.getUserNameText(), pdr_user_name);
        //Move to specific date to get a specific roster:
        int employeeId = 5;
        rosterEmployeePage = rosterEmployeePage.selectEmployee(employeeId);
        /**
         * Get roster items and compare to assertions:
         */
        ZoneId timeZoneCET = ZoneId.of("CET");
        ZoneId timeZoneBerlin = ZoneId.of("Europe/Berlin");
        Roster roster = new Roster();
        HashMap<YearWeek, HashMap> listOfRosterWeeksForEmployee = roster.getRosterWeeksByEmployeeId(employeeId);
        for (YearWeek yearWeek : listOfRosterWeeksForEmployee.keySet()) {
            HashMap<Integer, RosterItem> rosterWeek = listOfRosterWeeksForEmployee.get(yearWeek);
            LocalDate mondayLocaldate = yearWeek.atDay(DayOfWeek.MONDAY);
            rosterEmployeePage = rosterEmployeePage.goToDate(mondayLocaldate);

            /**
             * //Download the ICS file:
             *
             */
            rosterEmployeePage.downloadICSFile();

            String iCalendarString;
            iCalendarString = Files.readString(Path.of(iCalendarFileName), Charset.forName("UTF-8"));
            ICalendar ical = Biweekly.parse(iCalendarString).first();
            List<VEvent> listOfEvents = ical.getEvents();
            /**
             * Make sure, that the number of events matche the number of roster
             * items: I hope, that the order is the same and constant.
             */
            Assert.assertEquals(listOfEvents.size(), rosterWeek.entrySet().size());
            /**
             * Get all the roster items in the list and compare them against the
             * events in the iCalendar file:
             */
            DateFormat dateFormatHourColonMinute = new SimpleDateFormat("HH:mm");
            DateFormat dateFormatDayDotMonth = new SimpleDateFormat("dd.MM.");
            DateTimeFormatter dateTimeFormatterDayDotMonth = DateTimeFormatter.ofPattern("dd.MM.");
            for (Map.Entry<Integer, RosterItem> rosterItemEntry : rosterWeek.entrySet()) {
                /**
                 * <p lang=de>Das Ziel ist es jetzt dieses RosterItem mit allen
                 * iCalendar Items zu vergleichen. Wenn eines dieser Items
                 * passt, ist die Bedingung erfüllt. Ich denke, dass dieses Item
                 * dann nach Möglichkeit aus dem Array entfernt werden
                 * sollte.</p>
                 */
                RosterItem rosterItem = rosterItemEntry.getValue();
                VEvent matchingEvent;

                /**
                 * Convert the DutyStart of the iCalendar event and the
                 * DutyStart of the rosterItem to long timestamp in
                 * milliseconds. Compare the times. Return the found iCalendar
                 * event:
                 */
                matchingEvent = listOfEvents.stream()
                        .filter(eventBar -> eventBar.getDateStart().getValue().getTime() == rosterItem.getDutyStartLocalDateTime().atZone(timeZoneBerlin).toInstant().toEpochMilli())
                        .findFirst()
                        .orElse(null);
                Assert.assertNotNull(matchingEvent);

                /**
                 * We found a matching VEvent. We can now compare all of the
                 * parameters to the roster values:
                 */
                String summary = matchingEvent.getSummary().getValue();
                DateStart dateStart = matchingEvent.getDateStart();
                DateEnd dateEnd = matchingEvent.getDateEnd();
                Assert.assertEquals(dateFormatDayDotMonth.format(dateStart.getValue()), rosterItem.getLocalDate().format(dateTimeFormatterDayDotMonth));
                Assert.assertEquals(dateFormatDayDotMonth.format(dateEnd.getValue()), rosterItem.getLocalDate().format(dateTimeFormatterDayDotMonth));
                Assert.assertEquals(dateFormatHourColonMinute.format(dateStart.getValue()), rosterItem.getDutyStart());
                Assert.assertEquals(dateFormatHourColonMinute.format(dateEnd.getValue()), rosterItem.getDutyEnd());
                /**
                 * <p lang=de>Die Zusammenfassung des Events beinhaltet unter
                 * anderem den Namen der Apotheke.</p>
                 */
                NetworkOfBranchOffices networkOfBranchOffices = new NetworkOfBranchOffices();
                String branchName = networkOfBranchOffices.getBranchById(rosterItem.getBranchId()).getBranchName();
                Assert.assertTrue(summary.contains(branchName));
            }

            /**
             * Finally delete the iCalendar file:
             */
            rosterEmployeePage.deleteICSFile();
        }
    }

    @BeforeMethod
    public void setUp() {
        try {
            File file = new File("Calendar.ics");
            Files.deleteIfExists(file.toPath());

        } catch (IOException ex) {
            Logger.getLogger(TestRosterEmployeePage.class
                    .getName()).log(Level.SEVERE, null, ex);
        }

        Selenium.driver.Wrapper.createNewDriver();
    }

    @AfterMethod
    public void tearDown(ITestResult testResult
    ) {
        driver = Selenium.driver.Wrapper.getDriver();
        new ScreenShot(testResult);
        if (testResult.getStatus() != ITestResult.FAILURE) {
            driver.quit();
        }
    }

}
