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
import java.time.LocalDate;
import java.time.Month;
import java.time.format.DateTimeFormatter;
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
        super.signIn();
        RosterHoursPage rosterHoursPage = new RosterHoursPage(driver);

        /**
         * Move to specific month:
         */
        Workforce workforce = new Workforce();
        Optional<Employee> firstEmployeeOptional = workforce.getListOfEmployees().values().stream().findFirst();
        if (firstEmployeeOptional.isEmpty()) {
            throw new Exception("No employee was found in the workforce. There has to be at least one employee!");
        }
        String firstEmployeeLastName = firstEmployeeOptional.get().getLastName();
        rosterHoursPage.selectMonth("Juni");
        rosterHoursPage.selectYear("2020");
        rosterHoursPage.selectEmployee(firstEmployeeLastName);
        Assert.assertEquals("Juni", rosterHoursPage.getMonth());
        Assert.assertEquals("2020", rosterHoursPage.getYear());
        Assert.assertEquals(firstEmployeeLastName, rosterHoursPage.getEmployeeName());
    }

    @Test(enabled = true)/*failed*/
    public void testRosterDispay() throws Exception {
        /**
         * Sign in:
         */
        super.signIn();
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
            rosterHoursPage.selectEmployee(rosterItem.getEmployeeName());
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
        super.signIn();
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
        Workforce workforce = new Workforce();
        AbsenceEmployeePage absenceEmployeePage = new AbsenceEmployeePage();
        int employeeKey = 7;
        absenceEmployeePage = absenceEmployeePage.goToYear(2020);
        absenceEmployeePage = absenceEmployeePage.goToEmployee(employeeKey);
        absenceEmployeePage = absenceEmployeePage.createNewAbsence("01.07.2020", "01.07.2020", 8, "Foo comment", "not_yet_approved"); // 1 = Urlaub
        absenceEmployeePage = absenceEmployeePage.createNewAbsence("02.07.2020", "02.07.2020", 8, "Bar comment", "not_yet_approved"); // 1 = Urlaub
        absenceEmployeePage = absenceEmployeePage.createNewAbsence("03.07.2020", "03.07.2020", 8, "Baz comment", "not_yet_approved"); // 1 = Urlaub
        absenceEmployeePage = absenceEmployeePage.createNewAbsence("01.07.2020", "01.07.2020", 8, "123 comment", "not_yet_approved"); // 1 = Urlaub
        Absence currentAbsence;
        currentAbsence = absenceEmployeePage.getExistingAbsence("01.07.2020", employeeKey);
        Assert.assertEquals(currentAbsence.getCommentString(), "Foo comment");
        Assert.assertEquals(currentAbsence.getDurationString(), "1");
        Assert.assertEquals(currentAbsence.getEmployeeKey(), employeeKey);
        Assert.assertEquals(currentAbsence.getStartDateString(), "01.07.2020");
        Assert.assertEquals(currentAbsence.getEndDateString(), "01.07.2020");

        RosterHoursPage rosterHoursPage = new RosterHoursPage(driver);
        rosterHoursPage.selectEmployee(workforce.getEmployeeNameById(employeeKey));
        rosterHoursPage.selectMonth("Juli");
        rosterHoursPage.selectYear("2020");

        String absenceString = rosterHoursPage.getAbsenceStringOnLocalDate(LocalDate.of(2020, Month.JULY, 1));
        Assert.assertEquals(absenceString, "Elternzeit");
    }
}
