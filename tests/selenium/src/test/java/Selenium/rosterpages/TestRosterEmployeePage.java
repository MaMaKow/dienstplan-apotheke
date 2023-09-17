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

import Selenium.NetworkOfBranchOffices;
import Selenium.Roster;
import Selenium.RosterItem;
import Selenium.TestPage;
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

import org.testng.annotations.BeforeMethod;
import org.threeten.extra.YearWeek;

/**
 *
 * @author Mandelkow
 */
public class TestRosterEmployeePage extends TestPage {

    private final String iCalendarFileName = "Calendar.ics";

    @Test(enabled = true)/*passed*/
    public void testDateNavigation() {
        /**
         * Sign in:
         */
        super.signIn();
        RosterEmployeePage rosterEmployeePage = new RosterEmployeePage(driver);
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
        /**
         * Sign in:
         */
        super.signIn();
        RosterEmployeePage rosterEmployeePage = new RosterEmployeePage(driver);
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
                rosterEmployeePage = rosterEmployeePage.selectEmployee(rosterItemFromPrediction.getEmployeeKey());
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
        /**
         * Sign in:
         */
        super.signIn();
        RosterEmployeePage rosterEmployeePage = new RosterEmployeePage(driver);
        /**
         * Move to specific date to get a specific roster:
         */
        int employeeKey = 7;
        rosterEmployeePage = rosterEmployeePage.selectEmployee(employeeKey);
        /**
         * Get roster items and compare to assertions:
         */
        ZoneId timeZoneBerlin = ZoneId.of("Europe/Berlin");
        Roster roster = new Roster();
        HashMap<YearWeek, HashMap> listOfRosterWeeksForEmployee = roster.getRosterWeeksByEmployeeKey(employeeKey);
        for (YearWeek yearWeek : listOfRosterWeeksForEmployee.keySet()) {
            HashMap<Integer, RosterItem> rosterWeek = listOfRosterWeeksForEmployee.get(yearWeek);
            LocalDate mondayLocaldate = yearWeek.atDay(DayOfWeek.MONDAY);
            rosterEmployeePage = rosterEmployeePage.goToDate(mondayLocaldate);

            /**
             * //Download the ICS file:
             *
             */
            File downloadedICalendarFile = rosterEmployeePage.downloadICSFile();

            String iCalendarString;
            iCalendarString = Files.readString(downloadedICalendarFile.toPath(), Charset.forName("UTF-8"));
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

}
