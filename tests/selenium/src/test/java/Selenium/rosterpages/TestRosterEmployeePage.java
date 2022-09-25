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
import biweekly.io.chain.ChainingTextStringParser;
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
import java.time.ZonedDateTime;
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

    @Test(enabled = false)/*passed*/
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

    @Test(enabled = false)/*failed*/
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
            /*
             * Make sure, that the number of events matche the number of roster items:
             * I hope, that the order is the same and constant.
             */
            System.out.println("rosterWeek.size()");
            System.out.println(rosterWeek.size());
            System.out.println("rosterWeek.entrySet().size()");
            System.out.println(rosterWeek.entrySet().size());
            System.out.println("listOfEvents.size()");
            System.out.println(listOfEvents.size());
            Assert.assertEquals(listOfEvents.size(), rosterWeek.entrySet().size());
            /*
             * Get all the roster items in the list and compare them against the events in the iCalendar file:
             */
            System.out.println("rosterWeek");
            System.out.println(rosterWeek);
            int eventKey = 0;
            for (Map.Entry<Integer, RosterItem> rosterItemEntry : rosterWeek.entrySet()) {
                RosterItem rosterItem = rosterItemEntry.getValue();
                //rosterItem.getDutyStart();
                //ZonedDateTime dutyStartZonedDateTime = rosterItem.getDutyStartLocalDateTime().atZone(timeZoneBerlin);
                System.out.println("listOfEvents.get(eventKey)" + eventKey);
                VEvent event = listOfEvents.get(eventKey);
                DateFormat dateFormatHourColonMinute = new SimpleDateFormat("HH:mm");
                DateFormat dateFormatDayDotMonth = new SimpleDateFormat("dd.MM.");
                DateTimeFormatter dateTimeFormatterDayDotMonth = DateTimeFormatter.ofPattern("dd.MM.");
                String summary = event.getSummary().getValue();
                DateStart dateStart = event.getDateStart();
                DateEnd dateEnd = event.getDateEnd();
                /*
                System.out.println("dateStart.getValue()");
                System.out.println(dateStart.getValue());
                System.out.println("dateStart.getValue().getRawComponents()");
                System.out.println(dateStart.getValue().getRawComponents());
                System.out.println("dateStart.getValue().toInstant()");
                System.out.println(dateStart.getValue().toInstant());
                System.out.println("dateStart.getValue().toInstant().atZone(timeZone)");
                System.out.println(dateStart.getValue().toInstant().atZone(timeZoneBerlin));
                System.out.println("dateStart.getValue().toInstant().atZone(timeZoneBerlin).getHour()");
                System.out.println(dateStart.getValue().toInstant().atZone(timeZoneBerlin).getHour());
                System.out.println("dateStart.getValue().toInstant().atZone(timeZoneBerlin).format(DateTimeFormatter.ISO_LOCAL_TIME)");
                System.out.println(dateStart.getValue().toInstant().atZone(timeZoneBerlin).format(DateTimeFormatter.ISO_LOCAL_TIME));
                 */
                //Assert.assertEquals(dayDateFormat.format(dateStart.getValue()), dayDateFormat.format(rosterItem.getDateCalendar().getTime()));
                System.out.println(dateFormatDayDotMonth.format(dateStart.getValue()) + " = " + rosterItem.getLocalDate().format(dateTimeFormatterDayDotMonth));
                Assert.assertEquals(dateFormatDayDotMonth.format(dateStart.getValue()), rosterItem.getLocalDate().format(dateTimeFormatterDayDotMonth));
                //Assert.assertEquals(dateFormatDayDotMonth.format(dateEnd.getValue()), dateFormatDayDotMonth.format(rosterItem.getDateCalendar().getTime()));
                Assert.assertEquals(dateFormatDayDotMonth.format(dateEnd.getValue()), rosterItem.getLocalDate().format(dateTimeFormatterDayDotMonth));
                Assert.assertEquals(dateFormatHourColonMinute.format(dateStart.getValue()), rosterItem.getDutyStart());
                Assert.assertEquals(dateFormatHourColonMinute.format(dateEnd.getValue()), rosterItem.getDutyEnd());
                NetworkOfBranchOffices networkOfBranchOffices = new NetworkOfBranchOffices();
                String branchName = networkOfBranchOffices.getBranchById(rosterItem.getBranchId()).getBranchName();
                Assert.assertTrue(summary.contains(branchName));
                eventKey++;
            }
            /*
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
