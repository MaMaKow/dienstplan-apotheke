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
import java.util.logging.Level;
import java.util.logging.Logger;
import org.testng.annotations.Test;

/**
 * @todo Alle Varianten durchrechnen und Zahlen korrigieren
 * @todo Aktuell sind im Plan nur die Nachnamen. Es müssen die vollen Namen dort
 * stehen. Sonst werden die Zeilen nicht gefunden.
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
            Logger.getLogger(TestRosterWeekTablePage.class.getName()).log(Level.SEVERE, null, exception);
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
    public void testOvertimeAbsenceCalculationInMay2025() {
        try {
            /**
             * Sign in:
             */
            super.signIn();
        } catch (Exception exception) {
            Logger.getLogger(TestRosterWeekTablePage.class.getName()).log(Level.SEVERE, null, exception);
        }
        LocalDate tagDerArbeit = LocalDate.of(2025, Month.MAY, 1);
        Employee anabellNeuhaus = workforce.getEmployeeByFullName("Anabell Neuhaus");
        Employee alexandaProbst = workforce.getEmployeeByFullName("Alexandra Probst");
        Employee albertKremer = workforce.getEmployeeByFullName("Albert Kremer");

        /**
         * Create some absences:
         */
        createAbsencesInMay2025();

        /**
         * Create a roster from 28.04. to 04.05.2025
         */
        LocalDate firstDayInWeek = tagDerArbeit.minusDays(3);
        LocalDate lastDayInWeek = tagDerArbeit.plusDays(1); // CAVE! Nicht den Samstag mitplanen. Sonst bekommen wir zusätzliche Stunden für irgendwen.
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
        rosterWeekTablePage.goToDate(tagDerArbeit);
        //LogCollector.debug(driver.getCurrentUrl());
        //LogCollector.debug(driver.getPageSource());
        float workingHoursHave = rosterWeekTablePage.getWorkingHoursHaveByLastName(anabellNeuhaus.getLastName());
        float workingHoursShould = rosterWeekTablePage.getWorkingHoursShouldByLastName(anabellNeuhaus.getLastName());
        float workingHoursDiff = rosterWeekTablePage.getWorkingHoursDiffByLastName(anabellNeuhaus.getLastName());
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

        // Assert all:
        softAssert.assertAll();
    }

    private AbsenceEmployeePage createAbsencesInMay2025() {
        LocalDate tagDerArbeit = LocalDate.of(2025, Month.MAY, 1);
        Employee anabellNeuhaus = workforce.getEmployeeByFullName("Anabell Neuhaus");
        Employee alexandaProbst = workforce.getEmployeeByFullName("Alexandra Probst");
        Employee albertKremer = workforce.getEmployeeByFullName("Albert Kremer");

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
        return absenceEmployeePage;
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
}
