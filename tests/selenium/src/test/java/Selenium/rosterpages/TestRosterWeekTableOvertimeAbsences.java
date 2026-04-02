/*
 * Copyright (C) 2025 Mandelkow
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
package Selenium.rosterpages;

import Selenium.Absence;
import Selenium.Branch;
import Selenium.Employee;
import Selenium.NetworkOfBranchOffices;
import Selenium.PrincipleRoster;
import Selenium.PrincipleRosterDay;
import Selenium.PrincipleRosterItem;
import Selenium.TestPage;
import Selenium.Utilities.LogCollector;
import Selenium.absencepages.AbsenceEmployeePage;
import Selenium.driver.Wrapper;
import java.time.DayOfWeek;
import java.time.LocalDate;
import java.time.Month;
import java.time.Year;
import java.time.temporal.TemporalAdjusters;
import java.util.HashMap;
import java.util.Map;
import org.testng.annotations.Test;

/**
 * @todo Alle Varianten durchrechnen und Zahlen korrigieren
 * @author Martin Mandelkow
 */
public class TestRosterWeekTableOvertimeAbsences extends TestPage {

    @Test()
    public void testOvertimeAbsenceCalculationInCurrentYear() {
        try {
            /**
             * Sign in:
             */
            super.signIn();
        } catch (Exception exception) {
            LogCollector.error(exception.getMessage());
        }
        Year currentYear = Year.now();

        /**
         * Create a roster in the second week of July:
         */
        LocalDate firstMondayInJuly = LocalDate.of(currentYear.getValue(), Month.JULY, 1).with(TemporalAdjusters.nextOrSame(DayOfWeek.MONDAY));
        int alternationId = 0;
        LocalDate secondMondayInJuly = firstMondayInJuly.plusDays(7);
        LocalDate firstDayInWeek = secondMondayInJuly;
        LocalDate lastDayInWeek = secondMondayInJuly.plusDays(4); // Ohne Samstag!
        try {
            createRosterFromPrincipleInRange(firstDayInWeek, lastDayInWeek);
        } catch (Exception ex) {
            LogCollector.fatal("Programmierfehler! Das Enddatum muss nach dem Startdatum liegen!");
            softAssert.fail();
        }

        /**
         * Read the calculated hours from the weekly roster table page:
         */
        RosterWeekTablePage rosterWeekTablePage = new RosterWeekTablePage(driver);
        rosterWeekTablePage.goToDate(secondMondayInJuly);
        HashMap<Integer, Employee> listOfEmployees = workforce.getListOfEmployees();
        for (Employee employee : listOfEmployees.values()) {
            if (null != employee.getStartOfEmployment() && employee.getStartOfEmployment().isAfter(secondMondayInJuly)) {
                continue;
            }
            if (null != employee.getEndOfEmployment() && employee.getEndOfEmployment().isBefore(secondMondayInJuly)) {
                continue;
            }
            //float employeePrincipleWorkingHoursHave = employee.getWorkingHours();
            //float employeePrincipleWorkingHoursShould = employee.getWorkingHours();
            //float employeePrinciplePrincipleWorkingHoursDiff = employeePrincipleWorkingHoursHave - employeePrincipleWorkingHoursShould;
            PrincipleRoster principleRoster = new PrincipleRoster(employee.getBranchId(), alternationId);
            float expectedHave = calculateExpectedHaveHours(employee, principleRoster, firstDayInWeek, lastDayInWeek);
            float expectedShould = employee.getWorkingHours(); // Vertragsstunden
            float expectedDiff = expectedHave - expectedShould;
            float scheduledWorkingHoursHave = rosterWeekTablePage.getWorkingHoursHaveByLastName(employee.getLastName());
            float scheduledWorkingHoursShould = rosterWeekTablePage.getWorkingHoursShouldByLastName(employee.getLastName());
            float scheduledWorkingHoursDiff = rosterWeekTablePage.getWorkingHoursDiffByLastName(employee.getLastName());
            softAssert.assertEquals(scheduledWorkingHoursHave, expectedHave, "Have bei Mitarbeiter: " + employee.getFullName());
            softAssert.assertEquals(scheduledWorkingHoursShould, expectedShould, "Should bei Mitarbeiter: " + employee.getFullName());
            softAssert.assertEquals(scheduledWorkingHoursDiff, expectedDiff, "Diff bei Mitarbeiter: " + employee.getFullName());
        }
        softAssert.assertAll();
    }

    @Test(enabled = true)
    public void testOvertimeAbsenceCalculationInThirdJulyWeek() {
        try {
            super.signIn();
        } catch (Exception exception) {
            LogCollector.error(exception.getMessage());
        }

        Year currentYear = Year.now();
        LocalDate firstMondayInJuly = LocalDate.of(currentYear.getValue(), Month.JULY, 1).with(TemporalAdjusters.nextOrSame(DayOfWeek.MONDAY));
        LocalDate thirdMondayInJuly = firstMondayInJuly.plusDays(14);
        LocalDate firstDayInWeek = thirdMondayInJuly;
        LocalDate lastDayInWeek = thirdMondayInJuly.plusDays(4); // Mo–Fr, kein Samstag

        Employee elisabethLehmann = workforce.getEmployeeByFullName("Elisabeth Lehmann");   // REASON_SICKNESS ohne Feiertag
        Employee emmaGrimm = workforce.getEmployeeByFullName("Emma Grimm");                 // REASON_MATERNITY_LEAVE
        Employee franziskaHartmann = workforce.getEmployeeByFullName("Franziska Hartmann"); // REASON_TAKEN_OVERTIME
        Employee marieFischer = workforce.getEmployeeByFullName("Marie Fischer");           // REASON_REMAINING_VACATION

        createAbsencesInThirdJulyWeek(thirdMondayInJuly);

        try {
            createRosterFromPrincipleInRange(firstDayInWeek, lastDayInWeek);
        } catch (Exception exception) {
            LogCollector.fatal(exception.getMessage());
            softAssert.fail();
        }

        float workingHoursHave;
        float workingHoursShould;
        float workingHoursDiff;
        RosterWeekTablePage rosterWeekTablePage = new RosterWeekTablePage(driver);
        rosterWeekTablePage.goToDate(thirdMondayInJuly);

        /**
         * Elisabeth Lehmann: Krank am Montag (kein Feiertag). § 4 EFZG:
         * Have-Stunden werden laut Grundplan gutgeschrieben (8,5 h/Tag × 5 =
         * 42,5 h). Should = calculateContractualDailyHours = 40 / 5 = 8 h × 5 =
         * 40 h. Diff = +2,5 h.
         */
        workingHoursHave = rosterWeekTablePage.getWorkingHoursHaveByLastName(elisabethLehmann.getLastName());
        workingHoursShould = rosterWeekTablePage.getWorkingHoursShouldByLastName(elisabethLehmann.getLastName());
        workingHoursDiff = rosterWeekTablePage.getWorkingHoursDiffByLastName(elisabethLehmann.getLastName());
        softAssert.assertEquals(workingHoursHave, 42.5f, "Elisabeth Lehmann Have");
        softAssert.assertEquals(workingHoursShould, 40.0f, "Elisabeth Lehmann Should");
        softAssert.assertEquals(workingHoursDiff, 2.5f, "Elisabeth Lehmann Diff");

        /**
         * Emma Grimm: Mutterschutz am Montag. REASON_MATERNITY_LEAVE ist in
         * noWorkAbsenceReasonIds: Montag Have = 0, Should = 0. Di–Fr Grundplan:
         * 09:00–18:00 − 0,5 h = 8,5 h Have / 8 h Should je Tag. Total: Have =
         * 34 h, Should = 32 h, Diff = +2 h.
         */
        workingHoursHave = rosterWeekTablePage.getWorkingHoursHaveByLastName(emmaGrimm.getLastName());
        workingHoursShould = rosterWeekTablePage.getWorkingHoursShouldByLastName(emmaGrimm.getLastName());
        workingHoursDiff = rosterWeekTablePage.getWorkingHoursDiffByLastName(emmaGrimm.getLastName());
        softAssert.assertEquals(workingHoursHave, 34.0f, "Emma Grimm Have");
        softAssert.assertEquals(workingHoursShould, 32.0f, "Emma Grimm Should");
        softAssert.assertEquals(workingHoursDiff, 2.0f, "Emma Grimm Diff");

        /**
         * Franziska Hartmann: Überstundenabbau am Montag.
         * REASON_TAKEN_OVERTIME: kein Feiertag, kein SICKNESS/PAID_LEAVE →
         * hoursWorkedTheoretically = 0, Have Montag = 0. TAKEN_OVERTIME nicht
         * in noWork- oder vacationAbsenceReasonIds → Should Montag =
         * calculateContractualDailyHours = 40 / 5 = 8 h. Di–Fr Grundplan:
         * 08:00–16:30 − 0,5 h = 8 h Have / 8 h Should je Tag. Total: Have = 32
         * h, Should = 40 h, Diff = −8 h.
         */
        workingHoursHave = rosterWeekTablePage.getWorkingHoursHaveByLastName(franziskaHartmann.getLastName());
        workingHoursShould = rosterWeekTablePage.getWorkingHoursShouldByLastName(franziskaHartmann.getLastName());
        workingHoursDiff = rosterWeekTablePage.getWorkingHoursDiffByLastName(franziskaHartmann.getLastName());
        softAssert.assertEquals(workingHoursHave, 32.0f, "Franziska Hartmann Have");
        softAssert.assertEquals(workingHoursShould, 40.0f, "Franziska Hartmann Should");
        softAssert.assertEquals(workingHoursDiff, -8.0f, "Franziska Hartmann Diff");

        /**
         * Marie Fischer: Resturlaub am Montag. § 11 BUrlG analog zu
         * REASON_VACATION: Have Montag = calculateContractualDailyHours = 40 /
         * 5 = 8 h. Should Montag = calculateContractualDailyHours = 8 h. Di–Fr
         * Grundplan: 09:30–18:00 − 0,5 h = 8 h Have / 8 h Should je Tag. Total:
         * Have = 40 h, Should = 40 h, Diff = 0 h.
         */
        workingHoursHave = rosterWeekTablePage.getWorkingHoursHaveByLastName(marieFischer.getLastName());
        workingHoursShould = rosterWeekTablePage.getWorkingHoursShouldByLastName(marieFischer.getLastName());
        workingHoursDiff = rosterWeekTablePage.getWorkingHoursDiffByLastName(marieFischer.getLastName());
        softAssert.assertEquals(workingHoursHave, 40.0f, "Marie Fischer Have");
        softAssert.assertEquals(workingHoursShould, 40.0f, "Marie Fischer Should");
        softAssert.assertEquals(workingHoursDiff, 0.0f, "Marie Fischer Diff");

        softAssert.assertAll();
    }

    @Test(enabled = true)
    public void testOvertimeAbsenceCalculationInMay2025() {
        try {
            /**
             * Sign in:
             */
            super.signIn();
        } catch (Exception exception) {
            LogCollector.error(exception.getMessage());
        }
        LocalDate tagDerArbeit = LocalDate.of(2025, Month.MAY, 1);
        Employee franziskaHartmann = workforce.getEmployeeByFullName("Franziska Hartmann");  // Feiertag normal, ohne weitere Abwesenheit
        Employee anabellNeuhaus = workforce.getEmployeeByFullName("Anabell Neuhaus");        // Feiertag plus Urlaub
        Employee alexandaProbst = workforce.getEmployeeByFullName("Alexandra Probst");       // Feiertag plus Freistellung
        Employee albertKremer = workforce.getEmployeeByFullName("Albert Kremer");            // Feiertag plus Elternzeit
        Employee leaDietrich = workforce.getEmployeeByFullName("Lea Dietrich");              // Feiertag plus Kind krank
        Employee albertJansen = workforce.getEmployeeByFullName("Albert Jansen");            // 2 Tage-Woche

        /**
         * Create some absences:
         */
        createAbsencesInMay2025();

        /**
         * Create a roster from 28.04. to 02.05.2025
         */
        LocalDate firstDayInWeek = tagDerArbeit.minusDays(3);
        LocalDate lastDayInWeek = tagDerArbeit.plusDays(1); // CAVE! Nicht den Samstag mitplanen. Sonst bekommen wir zusätzliche Stunden für irgendwen.
        try {
            createRosterFromPrincipleInRange(firstDayInWeek, lastDayInWeek);
        } catch (Exception exception) {
            LogCollector.fatal(exception.getMessage());
            softAssert.fail();
        }

        /**
         * Read the calculated hours from the weekly roster table page:
         */
        float workingHoursHave;
        float workingHoursShould;
        float workingHoursDiff;
        RosterWeekTablePage rosterWeekTablePage = new RosterWeekTablePage(driver);
        rosterWeekTablePage.goToDate(tagDerArbeit);

        // Annabell Neuhaus mit Urlaub
        workingHoursHave = rosterWeekTablePage.getWorkingHoursHaveByLastName(anabellNeuhaus.getLastName());
        workingHoursShould = rosterWeekTablePage.getWorkingHoursShouldByLastName(anabellNeuhaus.getLastName());
        workingHoursDiff = rosterWeekTablePage.getWorkingHoursDiffByLastName(anabellNeuhaus.getLastName());
        softAssert.assertEquals(workingHoursHave, 40.0f, "Annabell Neuhaus Have");
        softAssert.assertEquals(workingHoursShould, 40.0f, "Annabell Neuhaus Should");
        softAssert.assertEquals(workingHoursDiff, 0.0f, "Annabell Neuhaus Diff");

        // Alexandra Probst mit Freistellung
        workingHoursHave = rosterWeekTablePage.getWorkingHoursHaveByLastName(alexandaProbst.getLastName());
        workingHoursShould = rosterWeekTablePage.getWorkingHoursShouldByLastName(alexandaProbst.getLastName());
        workingHoursDiff = rosterWeekTablePage.getWorkingHoursDiffByLastName(alexandaProbst.getLastName());
        softAssert.assertEquals(workingHoursHave, 40.0f, "Alexanda Probst  Have");
        softAssert.assertEquals(workingHoursShould, 40.0f, "Alexanda Probst Should");
        softAssert.assertEquals(workingHoursDiff, 0.0f, "Alexanda Probst Diff");

        // Albert Kremer in Elternzeit
        workingHoursHave = rosterWeekTablePage.getWorkingHoursHaveByLastName(albertKremer.getLastName());
        workingHoursShould = rosterWeekTablePage.getWorkingHoursShouldByLastName(albertKremer.getLastName());
        workingHoursDiff = rosterWeekTablePage.getWorkingHoursDiffByLastName(albertKremer.getLastName());
        softAssert.assertEquals(workingHoursHave, 32.0f, "Albert Kremer Have");
        softAssert.assertEquals(workingHoursShould, 32.0f, "Albert Kremer Should");
        softAssert.assertEquals(workingHoursDiff, 0.0f, "Albert Kremer Diff");

        // Franziska Hartmann ohne weitere Abwesenheit
        workingHoursHave = rosterWeekTablePage.getWorkingHoursHaveByLastName(franziskaHartmann.getLastName());
        workingHoursShould = rosterWeekTablePage.getWorkingHoursShouldByLastName(franziskaHartmann.getLastName());
        workingHoursDiff = rosterWeekTablePage.getWorkingHoursDiffByLastName(franziskaHartmann.getLastName());
        softAssert.assertEquals(workingHoursHave, 40.0f, "Franziska Hartmann Have");
        softAssert.assertEquals(workingHoursShould, 40.0f, "Franziska Hartmann Should");
        softAssert.assertEquals(workingHoursDiff, 0.0f, "Franziska Hartmann Diff");

        // Lea Dietrich mit Kind krank
        workingHoursHave = rosterWeekTablePage.getWorkingHoursHaveByLastName(leaDietrich.getLastName());
        workingHoursShould = rosterWeekTablePage.getWorkingHoursShouldByLastName(leaDietrich.getLastName());
        workingHoursDiff = rosterWeekTablePage.getWorkingHoursDiffByLastName(leaDietrich.getLastName());
        softAssert.assertEquals(workingHoursHave, 40.0f, "Lea Dietrich Have");
        softAssert.assertEquals(workingHoursShould, 32.0f, "Lea Dietrich Should");
        softAssert.assertEquals(workingHoursDiff, 8.0f, "Lea Dietrich Diff");

        // Albert Jansen 2-Tage Woche
        workingHoursHave = rosterWeekTablePage.getWorkingHoursHaveByLastName(albertJansen.getLastName());
        workingHoursShould = rosterWeekTablePage.getWorkingHoursShouldByLastName(albertJansen.getLastName());
        workingHoursDiff = rosterWeekTablePage.getWorkingHoursDiffByLastName(albertJansen.getLastName());
        softAssert.assertEquals(workingHoursHave, 10.0f, "Albert Jansen Have");
        softAssert.assertEquals(workingHoursShould, 10.0f, "Albert Jansen Should");
        softAssert.assertEquals(workingHoursDiff, 0.0f, "Albert Jansen Diff");

        // Assert all:
        softAssert.assertAll();
    }

    private AbsenceEmployeePage createAbsencesInMay2025() {

        LocalDate tagDerArbeit = LocalDate.of(2025, Month.MAY, 1);
        Employee anabellNeuhaus = workforce.getEmployeeByFullName("Anabell Neuhaus");
        Employee alexandaProbst = workforce.getEmployeeByFullName("Alexandra Probst");
        Employee albertKremer = workforce.getEmployeeByFullName("Albert Kremer");
        Employee leaDietrich = workforce.getEmployeeByFullName("Lea Dietrich");              // Feiertag plus Kind krank

        AbsenceEmployeePage absenceEmployeePage = new AbsenceEmployeePage();
        // Erstelle Abwesenheit Urlaub für Annabell Neuhaus
        absenceEmployeePage.goToEmployee(anabellNeuhaus.getEmployeeKey());
        absenceEmployeePage = absenceEmployeePage.createNewAbsence(tagDerArbeit.format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR), tagDerArbeit.format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR), Absence.REASON_VACATION, "comment", "approved");

        // Erstelle Abwesenheit Freistellung für Alexandra Probst
        absenceEmployeePage.goToEmployee(alexandaProbst.getEmployeeKey());
        absenceEmployeePage = absenceEmployeePage.createNewAbsence(tagDerArbeit.format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR), tagDerArbeit.format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR), Absence.REASON_PAID_LEAVE_OF_ABSENCE, "comment", "approved");

        // Erstelle Abwesenheit Elternzeit für Albert Kremer
        absenceEmployeePage.goToEmployee(albertKremer.getEmployeeKey());
        absenceEmployeePage = absenceEmployeePage.createNewAbsence(tagDerArbeit.format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR), tagDerArbeit.format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR), Absence.REASON_PARENTAL_LEAVE, "comment", "approved");

        // Erstelle Abwesenheit Kind krank für Lea Dietrich
        absenceEmployeePage.goToEmployee(leaDietrich.getEmployeeKey());
        absenceEmployeePage = absenceEmployeePage.createNewAbsence(tagDerArbeit.format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR), tagDerArbeit.format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR), Absence.REASON_SICKNESS_OF_CHILD, "comment", "approved");
        return absenceEmployeePage;
        // Franziska Hartmann ohne weitere Abwesenheit
    }

    /**
     * <p lang=de>Die Funktion steuert einen Bereich von Daten an und erstellt
     * dort den Dienstplan gemäß Grundplan. Das funktioniert, weil die
     * rosterDayEditPage bei leeren Plänen immer einen Plan vorschlägt. Dieser
     * berücksichtigt den Grundplan, Abwesenheiten und Feiertage. Die Funktion
     * tut dies im gegebenen Datumsbereich für alle Filialen.</p>
     *
     * @param startDate
     * @param endDate
     */
    private void createRosterFromPrincipleInRange(LocalDate startDate, LocalDate endDate) throws Exception {
        if (startDate.isAfter(endDate)) {
            throw new Exception("Start Date must be before end Date!");
        }
        RosterDayEditPage rosterDayEditPage = new RosterDayEditPage(driver);
        NetworkOfBranchOffices networkOfBranchOffices = new NetworkOfBranchOffices();
        Map<Integer, Branch> listOfBranches = networkOfBranchOffices.getListOfBranches();
        for (Branch branch : listOfBranches.values()) {
            rosterDayEditPage.selectBranch(branch.getBranchId());
            for (LocalDate localDate = startDate; !localDate.isAfter(endDate); localDate = localDate.plusDays(1)) {
                rosterDayEditPage.goToDate(localDate);
                rosterDayEditPage.rosterFormSubmit();
            }
        }
    }

    private float calculateExpectedHaveHours(Employee employee, PrincipleRoster principleRoster,
            LocalDate startDate, LocalDate endDate) {
        float totalHours = 0.0f;
        for (LocalDate date = startDate; !date.isAfter(endDate); date = date.plusDays(1)) {
            DayOfWeek dayOfWeek = date.getDayOfWeek();
            if (dayOfWeek == DayOfWeek.SATURDAY || dayOfWeek == DayOfWeek.SUNDAY) {
                continue;
            }
            // Feiertage ggf. auch überspringen
            PrincipleRosterDay principleRosterEmployeeDay = principleRoster.getPrincipleRosterByEmployee(employee.getEmployeeKey()).get(dayOfWeek);
            //List<PrincipleRosterItem> items = principleRoster.getItemsByDayAndEmployee(dow, employee.getEmployeeKey());
            for (PrincipleRosterItem principleRosterItem : principleRosterEmployeeDay.getlistOfPrincipleRosterItems().values()) {
                totalHours += principleRosterItem.getWorkHours();; // dutyEnd - dutyStart - breakDuration
            }
        }
        return totalHours;
    }

    private void createAbsencesInThirdJulyWeek(LocalDate thirdMondayInJuly) {
        Employee elisabethLehmann = workforce.getEmployeeByFullName("Elisabeth Lehmann");
        Employee emmaGrimm = workforce.getEmployeeByFullName("Emma Grimm");
        Employee franziskaHartmann = workforce.getEmployeeByFullName("Franziska Hartmann");
        Employee marieFischer = workforce.getEmployeeByFullName("Marie Fischer");

        String mondayFormatted = thirdMondayInJuly.format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR);

        AbsenceEmployeePage absenceEmployeePage = new AbsenceEmployeePage();

        // Krank: Elisabeth Lehmann
        absenceEmployeePage.goToEmployee(elisabethLehmann.getEmployeeKey());
        absenceEmployeePage = absenceEmployeePage.createNewAbsence(
                mondayFormatted, mondayFormatted,
                Absence.REASON_SICKNESS, "comment", "approved");

        // Mutterschutz: Emma Grimm
        absenceEmployeePage.goToEmployee(emmaGrimm.getEmployeeKey());
        absenceEmployeePage = absenceEmployeePage.createNewAbsence(
                mondayFormatted, mondayFormatted,
                Absence.REASON_MATERNITY_LEAVE, "comment", "approved");

        // Überstundenabbau: Franziska Hartmann
        absenceEmployeePage.goToEmployee(franziskaHartmann.getEmployeeKey());
        absenceEmployeePage = absenceEmployeePage.createNewAbsence(
                mondayFormatted, mondayFormatted,
                Absence.REASON_TAKEN_OVERTIME, "comment", "approved");

        // Resturlaub: Marie Fischer
        absenceEmployeePage.goToEmployee(marieFischer.getEmployeeKey());
        absenceEmployeePage = absenceEmployeePage.createNewAbsence(
                mondayFormatted, mondayFormatted,
                Absence.REASON_REMAINING_VACATION, "comment", "approved");
    }
}
