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

import Selenium.Roster;
import Selenium.RosterItem;
import Selenium.TestPage;
import Selenium.driver.Wrapper;
import java.text.ParseException;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.Month;
import java.time.format.DateTimeFormatter;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;
import java.util.Optional;
import org.testng.Assert;
import org.testng.annotations.Test;
import static org.testng.Assert.assertEquals;

/**
 *
 * @author Mandelkow
 * @todo add test for rosterActionCommand insertMissingEmployee
 */
public class TestRosterDayEditPage extends TestPage {

    @Test(enabled = true)/*passed*/
    public void testDateNavigation() {
        /**
         * Sign in:
         */
        super.signIn();
        RosterDayEditPage rosterDayEditPage = new RosterDayEditPage(driver);
        /**
         * Move to specific date and go foreward and backward from there:
         */
        LocalDate localDate = LocalDate.of(2020, Month.JULY, 1);
        rosterDayEditPage.goToDate(localDate); //This date is a wednesday.
        assertEquals(localDate.format(Wrapper.DATE_TIME_FORMATTER_YEAR_MONTH_DAY), rosterDayEditPage.getDateString()); //This is the corresponding monday.
        rosterDayEditPage.moveDayBackward();
        LocalDate dayBackward = localDate.minusDays(1);
        assertEquals(dayBackward.format(Wrapper.DATE_TIME_FORMATTER_YEAR_MONTH_DAY), rosterDayEditPage.getDateString()); //This is the corresponding monday.
        rosterDayEditPage.moveDayForward();
        assertEquals(localDate.format(Wrapper.DATE_TIME_FORMATTER_YEAR_MONTH_DAY), rosterDayEditPage.getDateString()); //This is the corresponding monday.
    }

    @Test(enabled = true, dependsOnMethods = {"testDateNavigation", "testRosterEditAdd"})/*passed*/
    public void testRosterDisplay() throws Exception {
        /**
         * Sign in:
         */
        super.signIn();
        RosterDayEditPage rosterDayEditPage = new RosterDayEditPage(driver);

        /**
         * Get roster items and compare to assertions:
         */
        Workforce workforce = new Workforce();
        Roster roster = new Roster();
        HashMap<LocalDate, HashMap> listOfRosterDays = roster.getListOfRosterDays();
        for (HashMap<Integer, RosterItem> listOfRosterItems : listOfRosterDays.values()) {
            for (RosterItem rosterItemFromPrediction : listOfRosterItems.values()) {
                /**
                 * Move to specific date to get a specific roster:
                 */
                rosterDayEditPage.goToDate(rosterItemFromPrediction.getLocalDate());
                RosterItem rosterItemReadOnPage = rosterDayEditPage.getRosterItem(rosterItemFromPrediction.getEmployeeKey());

                softAssert.assertEquals(rosterItemFromPrediction.getEmployeeName(workforce), rosterItemReadOnPage.getEmployeeName(workforce));
                softAssert.assertEquals(rosterItemFromPrediction.getLocalDate(), rosterItemReadOnPage.getLocalDate());
                softAssert.assertEquals(rosterItemFromPrediction.getDutyStart(), rosterItemReadOnPage.getDutyStart());
                softAssert.assertEquals(rosterItemFromPrediction.getDutyEnd(), rosterItemReadOnPage.getDutyEnd());
                softAssert.assertEquals(rosterItemFromPrediction.getBreakStart(), rosterItemReadOnPage.getBreakStart());
                softAssert.assertEquals(rosterItemFromPrediction.getBreakEnd(), rosterItemReadOnPage.getBreakEnd());
                softAssert.assertAll();
            }
        }
    }

    @Test(enabled = true)/*new*/
    public void testRosterEditAdd() {
        /**
         * Sign in:
         */
        super.signIn();
        RosterDayEditPage rosterDayEditPage = new RosterDayEditPage(driver);

        Roster roster = new Roster();
        HashMap<LocalDate, HashMap> listOfRosterDays = roster.getListOfRosterDays();
        for (Map.Entry<LocalDate, HashMap> listOfRosterDaysEntrySet : listOfRosterDays.entrySet()) {
            LocalDate localDate = listOfRosterDaysEntrySet.getKey();
            rosterDayEditPage.goToDate(localDate);
            assertEquals(localDate.format(Wrapper.DATE_TIME_FORMATTER_YEAR_MONTH_DAY), rosterDayEditPage.getDateString());
            HashMap<Integer, RosterItem> listOfRosterItems = listOfRosterDaysEntrySet.getValue();
            listOfRosterItems.values().forEach(rosterItem -> {
                rosterDayEditPage.rosterInputAddRow(rosterItem);
            });
            rosterDayEditPage.rosterFormSubmit();
        }

    }

    @Test(enabled = true, dependsOnMethods = {"testDateNavigation", "testRosterEditAdd", "testRosterDisplay"})
    public void testRosterEditChangeDutyEnd() throws Exception {
        /**
         * Sign in:
         */
        super.signIn();
        RosterDayEditPage rosterDayEditPage = new RosterDayEditPage(driver);

        int numberOfEditsMax = 5;
        int numberOfEdits = 0;

        Roster roster = new Roster();
        HashMap<LocalDate, HashMap> listOfRosterDays = roster.getListOfRosterDays();
        for (Map.Entry<LocalDate, HashMap> listOfRosterDaysEntrySet : listOfRosterDays.entrySet()) {
            LocalDate localDate = listOfRosterDaysEntrySet.getKey();
            rosterDayEditPage.goToDate(localDate);
            HashMap<Integer, RosterItem> listOfRosterItems = listOfRosterDaysEntrySet.getValue();
            for (RosterItem rosterItem : listOfRosterItems.values()) {
                /**
                 * apply a random change by +- three hours to the rosterItem
                 */
                long hourDifference = (long) Math.round(Math.random() * 6) - 3;
                LocalDateTime dutyEndNew = rosterItem.getDutyEndLocalDateTime().plusHours(hourDifference);
                RosterItem rosterItemNew = new RosterItem(
                        rosterItem.getEmployeeKey(),
                        rosterItem.getLocalDate(),
                        rosterItem.getDutyStart(),
                        dutyEndNew.format(DateTimeFormatter.ofPattern("HH:mm")),
                        rosterItem.getBreakStart(),
                        rosterItem.getBreakEnd(),
                        rosterItem.getComment(),
                        rosterItem.getBranchId());
                rosterDayEditPage.rosterInputEditRow(rosterItem, rosterItemNew);
                RosterItem rosterItemFound;
                rosterItemFound = rosterDayEditPage.getRosterItem(rosterItemNew.getEmployeeKey());
                rosterDayEditPage.rosterFormSubmit();
                /**
                 * Finally change item back again:
                 */
                rosterDayEditPage.rosterInputEditRow(rosterItemNew, rosterItem);
                rosterDayEditPage.rosterFormSubmit();
                /**
                 * Assert that the changes were stored:
                 */
                Assert.assertEquals(rosterItemFound.getDutyEndLocalDateTime(), rosterItemNew.getDutyEndLocalDateTime());
                numberOfEdits++;
                if (numberOfEdits >= numberOfEditsMax) {
                    return;
                }
            }
        }

    }

    @Test(enabled = true, dependsOnMethods = {"testRosterEditAdd"})
    public void testRosterEditDelete() throws ParseException {
        /**
         * Sign in:
         */
        super.signIn();
        RosterDayEditPage rosterDayEditPage = new RosterDayEditPage(driver);

        Roster roster = new Roster();
        HashMap<LocalDate, HashMap> listOfRosterDays = roster.getListOfRosterDays();
        Optional<HashMap> someRosterDayOptional = listOfRosterDays.values().stream().findAny();
        HashMap<Integer, RosterItem> someRosterDay = someRosterDayOptional.get();
        Iterator<RosterItem> someRosterDayIterator = someRosterDay.values().iterator();
        RosterItem rosterItem;
        LocalDate localDate;
        RosterItem emptyRosterItemFound = null;
        /**
         * Check if there is an entry left in this HashMap:
         */
        Assert.assertTrue(someRosterDayIterator.hasNext());
        rosterItem = someRosterDayIterator.next();

        /**
         * Remove an item by applying an empty employeeKey:
         */
        localDate = rosterItem.getLocalDate();
        rosterDayEditPage.goToDate(localDate);
        RosterItem rosterItemNew = new RosterItem(
                null, //empty employeeKey:
                rosterItem.getLocalDate(),
                rosterItem.getDutyStart(),
                rosterItem.getDutyEnd(),
                rosterItem.getBreakStart(),
                rosterItem.getBreakEnd(),
                rosterItem.getComment(),
                rosterItem.getBranchId());
        rosterDayEditPage.rosterInputEditRow(rosterItem, rosterItemNew);
        rosterDayEditPage.rosterFormSubmit();
        try {
            emptyRosterItemFound = rosterDayEditPage.getRosterItem(rosterItem.getEmployeeKey());
        } catch (Exception exception) {
            /**
             * Everything is fine. We expected not to find anything.
             */
            //System.err.println(exception.getLocalizedMessage());
        }
        /**
         * Add the item back in again:
         */
        rosterDayEditPage.rosterInputAddRow(rosterItem);
        rosterDayEditPage.rosterFormSubmit();
        /**
         * Finally make the assertion:
         */
        Assert.assertNull(emptyRosterItemFound);

        /**
         * Check if there is an entry left in this HashMap:
         */
        Assert.assertTrue(someRosterDayIterator.hasNext());
        rosterItem = someRosterDayIterator.next();

        /**
         * Remove an item by applying an empty dutyStart and dutyEnd:
         */
        localDate = rosterItem.getLocalDate();
        rosterDayEditPage.goToDate(localDate);
        rosterItemNew = new RosterItem(
                rosterItem.getEmployeeKey(),
                rosterItem.getLocalDate(),
                "",
                "",
                rosterItem.getBreakStart(),
                rosterItem.getBreakEnd(),
                rosterItem.getComment(),
                rosterItem.getBranchId());
        rosterDayEditPage.rosterInputEditRow(rosterItem, rosterItemNew);
        rosterDayEditPage.rosterFormSubmit();
        try {
            emptyRosterItemFound = rosterDayEditPage.getRosterItem(rosterItem.getEmployeeKey());
        } catch (Exception exception) {
            /**
             * Everything is fine. We expected not to find anything.
             */
            //System.err.println(exception.getLocalizedMessage());
        }
        /**
         * Add the item back in again:
         */
        rosterDayEditPage.rosterInputAddRow(rosterItem);
        rosterDayEditPage.rosterFormSubmit();
        /**
         * Finally make the assertion:
         */
        Assert.assertNull(emptyRosterItemFound);
    }

}
