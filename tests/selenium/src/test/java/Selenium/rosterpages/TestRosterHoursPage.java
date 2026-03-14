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

import Selenium.Absence;
import Selenium.Employee;
import Selenium.Roster;
import Selenium.RosterItem;
import Selenium.TestPage;
import Selenium.absencepages.AbsenceEmployeePage;
import static Selenium.driver.Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR;
import java.time.DayOfWeek;
import java.time.LocalDate;
import java.time.Month;
import java.time.format.DateTimeFormatter;
import java.time.temporal.TemporalAdjusters;
import java.util.HashMap;
import java.util.Locale;
import java.util.Optional;
import org.testng.annotations.Test;
import org.testng.Assert;

/**
 *
 * @author Mandelkow
 */
public class TestRosterHoursPage extends TestPage {

    @Test(enabled = true)/*failed*/
    public void testDateNavigation() throws Exception {
        /**
         * Sign in:
         */
        try {
            super.signIn();
        } catch (Exception exception) {
            logger.error("Sign in failed.");
            Assert.fail();
        }
        RosterHoursPage rosterHoursPage = new RosterHoursPage(driver);

        /**
         * Move to specific month:
         */
        int currentYear = LocalDate.now().getYear();
        int nextYear = currentYear + 1;
        Employee employee = workforce.getEmployeeByFullName("Alexandra Probst");
        String someEmployeeFullName = employee.getFullName();
        rosterHoursPage.selectMonth("Juni");
        rosterHoursPage.selectYear(String.valueOf(nextYear));
        logger.debug("Select employee " + someEmployeeFullName);
        rosterHoursPage.selectEmployee(someEmployeeFullName);

        Assert.assertEquals("Juni", rosterHoursPage.getMonth());
        Assert.assertEquals(String.valueOf(nextYear), rosterHoursPage.getYear());
        Assert.assertEquals(someEmployeeFullName, rosterHoursPage.getEmployeeName());
    }

    @Test(enabled = true)/*failed*/
    public void testRosterDispay() throws Exception {
        /**
         * Sign in:
         */
        try {
            super.signIn();
        } catch (Exception exception) {
            logger.error("Sign in failed.");
            Assert.fail();
        }
        RosterHoursPage rosterHoursPage = new RosterHoursPage(driver);

        /**
         * Test if the correct roster information is displayed:
         */
        Roster roster = new Roster();

        HashMap<LocalDate, HashMap> listOfRosterDays = roster.getListOfRosterDays();
        Optional<HashMap> firstRosterDayOptional = listOfRosterDays.values().stream().findFirst();
        if (firstRosterDayOptional.isEmpty()) {
            throw new Exception("No roster day was found in the roster. There has to be at least one roster day!");
        }
        Optional<RosterItem> firstRosterItemOptional = firstRosterDayOptional.get().values().stream().findFirst();
        if (firstRosterItemOptional.isEmpty()) {
            throw new Exception("No roster item was found in the roster day. There has to be at least one roster item!");
        }
        RosterItem firstRosterItem = firstRosterItemOptional.get();
        int employeeKey = firstRosterItem.getEmployeeKey();

        for (HashMap<Integer, RosterItem> rosterDay : listOfRosterDays.values()) {
            LocalDate dayInRoster = rosterDay.values().stream().findFirst().get().getLocalDate();
            RosterItem rosterItem = roster.getRosterItemByEmployeeKey(dayInRoster, employeeKey);
            if (null == rosterItem) {
                /**
                 * <p lang=de>Wir haben weiter oben abgesichert, dass es
                 * mindestens ein roster item gibt. Wir können daher hier ohne
                 * Bedenken die Schleife abkürzen und weitergehen. Das passiert
                 * immer dann, wenn es an einem Tag einen roster gibt, an dem
                 * dieser eine Employee nicht eingepant ist.</p>
                 */
                continue;
            }
            LocalDate rosterLocalDate = rosterItem.getLocalDate();
            /**
             * Go to page:
             */
            rosterHoursPage.selectMonth(rosterLocalDate.format(DateTimeFormatter.ofPattern("MMMM", Locale.GERMANY)));
            rosterHoursPage.selectYear(rosterLocalDate.format(DateTimeFormatter.ofPattern("yyyy", Locale.GERMANY)));
            try {
                rosterHoursPage.selectEmployee(rosterItem.getEmployeeFullName());
            } catch (Exception exception) {
                exception.printStackTrace();
                throw exception;
            }
            RosterItem foundRosterItem = rosterHoursPage.getRosterOnDate(rosterLocalDate);
            /**
             * Test if the values match:
             */
            Assert.assertEquals(foundRosterItem.getLocalDate(), rosterLocalDate);
            Assert.assertEquals(foundRosterItem.getDutyStart(), rosterItem.getDutyStart());
            Assert.assertEquals(foundRosterItem.getDutyEnd(), rosterItem.getDutyEnd());
        }
    }

    public void testAbsenceDispay() throws Exception {
        /**
         * Sign in:
         */
        try {
            super.signIn();
        } catch (Exception exception) {
            logger.error("Sign in failed.");
            Assert.fail();
        }
        /**
         * We do not directly go to the RosterHoursPage. Instead we first create
         * an absence. We want to view this absence in the RosterHoursPage.
         */

        /**
         * Test if absence information is displayed:
         *
         * @todo If absences will ever be written to from a json data file, use
         * that instead of hardcoding the values here!
         */
        AbsenceEmployeePage absenceEmployeePage = new AbsenceEmployeePage();
        int employeeKey = 7;
        int currentYear = LocalDate.now().getYear();
        LocalDate testMonday = LocalDate.of(currentYear, Month.JULY, 1).with(TemporalAdjusters.nextOrSame(DayOfWeek.MONDAY));
        String testTuesdayFormatted = testMonday.plusDays(1).format(DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
        String testWednesdayFormatted = testMonday.plusDays(2).format(DATE_TIME_FORMATTER_DAY_MONTH_YEAR);

        String testMondayFormatted = testMonday.format(DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
        absenceEmployeePage = absenceEmployeePage.goToYear(currentYear);
        absenceEmployeePage = absenceEmployeePage.goToEmployee(employeeKey);
        absenceEmployeePage = absenceEmployeePage.createNewAbsence(testMondayFormatted, testMondayFormatted, 8, "Foo comment", "not_yet_approved"); // 1 = Urlaub
        absenceEmployeePage = absenceEmployeePage.createNewAbsence(testTuesdayFormatted, testTuesdayFormatted, 8, "Bar comment", "not_yet_approved"); // 1 = Urlaub
        absenceEmployeePage = absenceEmployeePage.createNewAbsence(testWednesdayFormatted, testWednesdayFormatted, 8, "Baz comment", "not_yet_approved"); // 1 = Urlaub
        absenceEmployeePage = absenceEmployeePage.createNewAbsence(testMondayFormatted, testMondayFormatted, 8, "123 comment", "not_yet_approved"); // 1 = Urlaub
        Absence currentAbsence;
        currentAbsence = absenceEmployeePage.getExistingAbsence(testMondayFormatted, employeeKey);
        Assert.assertEquals(currentAbsence.getCommentString(), "Foo comment");
        Assert.assertEquals(currentAbsence.getDurationDays(), 1);
        Assert.assertEquals(currentAbsence.getEmployeeKey(), employeeKey);
        Assert.assertEquals(currentAbsence.getStartDate(), testMonday);
        Assert.assertEquals(currentAbsence.getEndDate(), testMonday);

        RosterHoursPage rosterHoursPage = new RosterHoursPage(driver);
        rosterHoursPage.selectEmployee(workforce.getEmployeeLastNameByKey(employeeKey));
        rosterHoursPage.selectMonth("Juli");
        rosterHoursPage.selectYear(String.valueOf(currentYear));

        String absenceString = rosterHoursPage.getAbsenceStringOnLocalDate(testMonday);
        Assert.assertEquals(absenceString, "Elternzeit");
    }
}
