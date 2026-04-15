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
import Selenium.RosterItem;
import Selenium.TestPage;
import Selenium.Utilities.LogCollector;
import Selenium.absencepages.AbsenceEmployeePage;
import Selenium.administrationpages.SaturdayListPage;
import Selenium.driver.Wrapper;
import Selenium.models.EmployeeHoursRow;
import java.time.DayOfWeek;
import java.time.Duration;
import java.time.LocalDate;
import java.time.LocalTime;
import java.time.Month;
import java.time.Year;
import java.time.temporal.TemporalAdjusters;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;

/**
 * @todo Alle Varianten durchrechnen und Zahlen korrigieren
 * @author Martin Mandelkow
 */
public class TestRosterWeekTableOvertimeAbsences extends TestPage {

    private RosterWeekTablePage rosterWeekTablePage;
    private static final float SATURDAY_EXTRA_HOURS = 6.0f; // Könnte eventuell an die Öffnungszeiten angepasst werden. Ist vorerst aber fest auf 6 Stunden gesetzt.

    @BeforeMethod
    public void setUp() {
        driver = Selenium.driver.Wrapper.getDriver();
        rosterWeekTablePage = new RosterWeekTablePage(driver);
    }

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
        rosterWeekTablePage = rosterWeekTablePage.ensureOnRosterWeekTablePage();  // Stellt sicher, dass wir auf der richtigen Seite sind
        rosterWeekTablePage.goToDate(secondMondayInJuly);
        HashMap<Integer, Employee> listOfEmployees = workforce.getListOfEmployees();
        for (Employee employee : listOfEmployees.values()) {
            if (null != employee.getStartOfEmployment() && employee.getStartOfEmployment().isAfter(secondMondayInJuly)) {
                continue;
            }
            if (null != employee.getEndOfEmployment() && employee.getEndOfEmployment().isBefore(secondMondayInJuly)) {
                continue;
            }
            PrincipleRoster principleRoster = new PrincipleRoster(employee.getBranchId(), alternationId);
            float expectedHave = calculateExpectedHaveHours(employee, principleRoster, firstDayInWeek, lastDayInWeek);
            float expectedShould = employee.getWorkingHours(); // Vertragsstunden
            assertEmployeeHours(employee, expectedHave, expectedShould);
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

        List<EmployeeHoursRow> listOfEmployeeHoursRows = new ArrayList<>();
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
        rosterWeekTablePage = rosterWeekTablePage.ensureOnRosterWeekTablePage();  // Stellt sicher, dass wir auf der richtigen Seite sind
        rosterWeekTablePage.goToDate(thirdMondayInJuly);
        /**
         * Elisabeth Lehmann: Krank am Montag (kein Feiertag). § 4 EFZG:
         * Have-Stunden werden laut Grundplan gutgeschrieben (8,5 h/Tag × 5 =
         * 42,5 h). Should = calculateContractualDailyHours = 40 / 5 = 8 h × 5 =
         * 40 h. Diff = +2,5 h.
         */
        listOfEmployeeHoursRows.add(new EmployeeHoursRow(elisabethLehmann, 42.5f, 40.0f));

        /**
         * Emma Grimm: Mutterschutz am Montag. REASON_MATERNITY_LEAVE ist in
         * noWorkAbsenceReasonIds: Montag Have = 0, Should = 0. Di–Fr Grundplan:
         * 09:00–18:00 − 0,5 h = 8,5 h Have / 8 h Should je Tag. Total: Have =
         * 34 h, Should = 32 h, Diff = +2 h.
         */
        listOfEmployeeHoursRows.add(new EmployeeHoursRow(emmaGrimm, 34.0f, 32.0f));

        /**
         * Franziska Hartmann: Überstundenabbau am Montag.
         * REASON_TAKEN_OVERTIME: kein Feiertag, kein SICKNESS/PAID_LEAVE →
         * hoursWorkedTheoretically = 0, Have Montag = 0. TAKEN_OVERTIME nicht
         * in noWork- oder vacationAbsenceReasonIds → Should Montag =
         * calculateContractualDailyHours = 40 / 5 = 8 h. Di–Fr Grundplan:
         * 08:00–16:30 − 0,5 h = 8 h Have / 8 h Should je Tag. Total: Have = 32
         * h, Should = 40 h, Diff = −8 h.
         */
        listOfEmployeeHoursRows.add(new EmployeeHoursRow(franziskaHartmann, 32.0f, 40.0f));

        /**
         * Marie Fischer: Resturlaub am Montag. § 11 BUrlG analog zu
         * REASON_VACATION: Have Montag = calculateContractualDailyHours = 40 /
         * 5 = 8 h. Should Montag = calculateContractualDailyHours = 8 h. Di–Fr
         * Grundplan: 09:30–18:00 − 0,5 h = 8 h Have / 8 h Should je Tag. Total:
         * Have = 40 h, Should = 40 h, Diff = 0 h.
         */
        listOfEmployeeHoursRows.add(new EmployeeHoursRow(marieFischer, 40.0f, 40.0f));

        for (EmployeeHoursRow row : listOfEmployeeHoursRows) {
            assertEmployeeHours(row.getEmployee(), row.getHoursHave(), row.getHoursShould());
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
        rosterWeekTablePage = rosterWeekTablePage.ensureOnRosterWeekTablePage();  // Stellt sicher, dass wir auf der richtigen Seite sind
        rosterWeekTablePage.goToDate(tagDerArbeit);

        // Annabell Neuhaus mit Urlaub
        assertEmployeeHours(anabellNeuhaus, 40, 40);
        // Alexandra Probst mit Freistellung
        assertEmployeeHours(alexandaProbst, 40, 40);
        // Albert Kremer in Elternzeit
        assertEmployeeHours(albertKremer, 32, 32);
        // Franziska Hartmann ohne weitere Abwesenheit
        assertEmployeeHours(franziskaHartmann, 40, 40);
        // Lea Dietrich mit Kind krank
        assertEmployeeHours(leaDietrich, 40, 32);
        // Albert Jansen 2-Tage Woche
        assertEmployeeHours(albertJansen, 10, 10);
        // Assert all:
        softAssert.assertAll();
    }

    @Test(enabled = true)
    public void testOvertimeAbsenceCalculationChristmas() {
        /**
         * Zu Weihnachten und zu Silvester gilt die Regel, dass trotz verkürzter
         * Öffnungszeiten die volle Arbeitszeit als gearbeitet gilt.
         *
         * @todo: <p lang=de>Abwesenheiten als takenOvertime oder
         * paidLeaveOfAbsence markieren. Bei fehlender Markierung von
         * paidLeaveOfAbsence als Standardlösung ausgehen.</p>
         */
        // Definiere Mitarbeiter und Filiale
        Employee franziskaHartmann = workforce.getEmployeeByFullName("Franziska Hartmann"); // Mit Notdienst (berechnet bis 22 Uhr)
        Employee anabellNeuhaus = workforce.getEmployeeByFullName("Anabell Neuhaus");
        Employee alexandaProbst = workforce.getEmployeeByFullName("Alexandra Probst");
        Employee albertKremer = workforce.getEmployeeByFullName("Albert Kremer");
        Employee leaDietrich = workforce.getEmployeeByFullName("Lea Dietrich"); // Nicht eingeplant, bekommt trotzdem Stunden
        Employee albertJansen = workforce.getEmployeeByFullName("Albert Jansen"); // Überstunden genommen
        Employee elisabethLehmann = workforce.getEmployeeByFullName("Elisabeth Lehmann"); // Freistellung

        List<EmployeeHoursRow> listOfEmployeeHoursRows = new ArrayList<>();

        int branchId = 1;

        // Definiere Datum:
        Year currentYear = Year.now();
        LocalDate christmasDate = LocalDate.of(currentYear.getValue(), Month.DECEMBER, 24);
        LocalDate christmasMonday = christmasDate.with(TemporalAdjusters.previousOrSame(DayOfWeek.MONDAY));
        LocalDate christmasSaturday = christmasMonday.plusDays(5);
        LocalDate christmasSunday = christmasMonday.plusDays(6);
        LocalDate silvesterDate = LocalDate.of(currentYear.getValue(), Month.DECEMBER, 31);

        SaturdayListPage saturdayListPage = new SaturdayListPage(driver);
        ArrayList<String> scheduledEmployeesOnSaturday = saturdayListPage.getScheduledEmployeesOnDate(christmasSaturday);
        softAssert.assertNotNull(scheduledEmployeesOnSaturday, "Scheduled employees list should not be null");

        // Erstelle Abwesenheit Überstunden für Albert Jansen
        AbsenceEmployeePage absenceEmployeePage = new AbsenceEmployeePage(driver);
        absenceEmployeePage = absenceEmployeePage.goToEmployee(albertJansen.getEmployeeKey());
        absenceEmployeePage = absenceEmployeePage.createNewAbsence(christmasDate.format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR), christmasDate.format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR), Absence.REASON_TAKEN_OVERTIME, "Überstundenabbau", "approved");
        // Erstelle Abwesenheit Freistellung für Elisabeth Lehmann
        absenceEmployeePage = absenceEmployeePage.goToEmployee(elisabethLehmann.getEmployeeKey());
        absenceEmployeePage.createNewAbsence(christmasDate.format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR), christmasDate.format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR), Absence.REASON_PAID_LEAVE_OF_ABSENCE, "Freistellung", "approved");

        try {
            /* Zunächst wird ein Dienstplan nach Grundplan für die ganze Woche erstellt.
             * Später werden für den 24.12. spezifische Dienste eingetragen.
             * Das ganze funktioniert so nicht, wenn der 24.12. auf ein Wochenende fällt.
             * @todo: Dafür brauchen wir noch spezifische Berechnungen.
             * @todo: Wir brauchen auch Berechnungen für die zwei Personen, die am Samstag zusätzliche Stunden arbeiten.
             */
            createRosterFromPrincipleInRange(christmasMonday, christmasSunday);
        } catch (Exception ex) {
            LogCollector.error(ex.getMessage());
            softAssert.fail();
        }
        createRosterForChristmas();
        /**
         * Read the calculated hours from the weekly roster table page:
         */
        rosterWeekTablePage = rosterWeekTablePage.ensureOnRosterWeekTablePage();  // Stellt sicher, dass wir auf der richtigen Seite sind
        rosterWeekTablePage.goToDate(christmasDate);
        rosterWeekTablePage.selectBranch(branchId);
        // Annabell Neuhaus
        listOfEmployeeHoursRows.add(new EmployeeHoursRow(anabellNeuhaus, 40, anabellNeuhaus.getWorkingHours()));
        // Alexandra Probst
        listOfEmployeeHoursRows.add(new EmployeeHoursRow(alexandaProbst, 40, 40));

        // Franziska Hartmann
        /**
         * Franziska Hartmann wird mit Notdienst eingeteilt. Je nach Wochentag
         * von Heiligabend kann das aber unterschiedliche Überstunden ergeben.
         *
         * @todo: Wir brauchen noch eine Funktion, die den Erwartungswert aus
         * ihrem Grundplan und dem Wochentag korrekt vorausberechnet.
         */
        PrincipleRoster principleRoster = new PrincipleRoster();
        HashMap<DayOfWeek, PrincipleRosterDay> franziskaHartmannWeekRoster = principleRoster.getPrincipleRosterByEmployee(franziskaHartmann.getEmployeeKey());
        PrincipleRosterDay franziskaHartmannDayRoster = franziskaHartmannWeekRoster.get(silvesterDate.getDayOfWeek());
        PrincipleRosterItem franziskaHartmannRosterItem = franziskaHartmannDayRoster.getPrincipleRosterItem(0);
        LocalTime franziskaHartmannDutyEnd = franziskaHartmannRosterItem.getDutyEnd();
        float overtime = Duration.between(franziskaHartmannDutyEnd, LocalTime.of(22, 0)).toMinutes() / 60.0f;
        overtime += 0.5;// Franziska Hartmann arbeitet ohne Pause.
        float workingWeekTime = 40 + overtime;
        listOfEmployeeHoursRows.add(new EmployeeHoursRow(franziskaHartmann, workingWeekTime, 40));

        // Albert Kremer
        listOfEmployeeHoursRows.add(new EmployeeHoursRow(albertKremer, 40, 40));

        // Lea Dietrich ist nicht im Dienstplan eingetragen und sollte trotzdem volle Stunden bekommen.
        listOfEmployeeHoursRows.add(new EmployeeHoursRow(leaDietrich, 40, 40));

        // Elisabeth Lehmann ist freigestellt und sollte daher volle Stunden bekommen.
        listOfEmployeeHoursRows.add(new EmployeeHoursRow(elisabethLehmann, principleRoster.getTotalWorkHoursForEmployee(elisabethLehmann), 40));

        // Albert Jansen nimmt Überstunden und sollte daher keine Stunden bekommen.
        listOfEmployeeHoursRows.add(new EmployeeHoursRow(albertJansen, 5, 10));

        // Gehe durch alle Mitarbeiter und prüfe die Stunden:
        for (EmployeeHoursRow row : listOfEmployeeHoursRows) {
            // Ergänze Stunden bei Mitarbeitern, die am Samstag arbeiten:
            if (scheduledEmployeesOnSaturday.contains(row.getEmployee().getFullName())) {
                row.setHoursHave(row.getHoursHave() + SATURDAY_EXTRA_HOURS);
            }
            assertEmployeeHours(row.getEmployee(), row.getHoursHave(), row.getHoursShould());
        }
        softAssert.assertAll();
    }

    private AbsenceEmployeePage createAbsencesInMay2025() {

        LocalDate tagDerArbeit = LocalDate.of(2025, Month.MAY, 1);
        Employee anabellNeuhaus = workforce.getEmployeeByFullName("Anabell Neuhaus");
        Employee alexandaProbst = workforce.getEmployeeByFullName("Alexandra Probst");
        Employee albertKremer = workforce.getEmployeeByFullName("Albert Kremer");
        Employee leaDietrich = workforce.getEmployeeByFullName("Lea Dietrich");              // Feiertag plus Kind krank

        AbsenceEmployeePage absenceEmployeePage = new AbsenceEmployeePage(driver);
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

    private void createRosterForChristmas() {
        // Definiere Mitarbeiter und Filiale
        Employee franziskaHartmann = workforce.getEmployeeByFullName("Franziska Hartmann");
        Employee anabellNeuhaus = workforce.getEmployeeByFullName("Anabell Neuhaus");
        Employee alexandaProbst = workforce.getEmployeeByFullName("Alexandra Probst");
        Employee albertKremer = workforce.getEmployeeByFullName("Albert Kremer");
        int branchId = 1;

        // Definiere Datum:
        Year currentYear = Year.now();
        LocalDate christmasDate = LocalDate.of(currentYear.getValue(), Month.DECEMBER, 24);
        LocalDate silvesterDate = LocalDate.of(currentYear.getValue(), Month.DECEMBER, 31);

        // Erstelle Dienste:
        RosterItem rosterItemFranziskaHartmann = new RosterItem(franziskaHartmann.getFullName(), christmasDate, "08:00", "22:00", "", "", "Notdienst", branchId);
        RosterItem rosterItemAnabellNeuhaus = new RosterItem(anabellNeuhaus.getFullName(), christmasDate, "08:00", "13:00", "", "", "comment", branchId);
        RosterItem rosterItemAlexandaProbst = new RosterItem(alexandaProbst.getFullName(), christmasDate, "08:00", "13:00", "", "", "comment", branchId);
        RosterItem rosterItemAlbertKremer = new RosterItem(albertKremer.getFullName(), christmasDate, "08:00", "13:00", "", "", "comment", branchId);

        RosterDayEditPage rosterDayEditPage = new RosterDayEditPage(driver);
        rosterDayEditPage.selectBranch(branchId);
        rosterDayEditPage.goToDate(christmasDate);
        rosterDayEditPage.deleteAllRosterRows();
        rosterDayEditPage.rosterInputAddRow(rosterItemFranziskaHartmann);
        rosterDayEditPage.rosterInputAddRow(rosterItemAnabellNeuhaus);
        rosterDayEditPage.rosterInputAddRow(rosterItemAlexandaProbst);
        rosterDayEditPage.rosterInputAddRow(rosterItemAlbertKremer);
        rosterDayEditPage.rosterFormSubmit();
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

        AbsenceEmployeePage absenceEmployeePage = new AbsenceEmployeePage(driver);

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

    private void assertEmployeeHours(Employee employee,
            float expectedHave,
            float expectedShould
    ) {
        //rosterWeekTablePage = rosterWeekTablePage.ensureOnRosterWeekTablePage();  // Stellt sicher, dass wir auf der richtigen Seite sind
        float expectedDiff = expectedHave - expectedShould;
        String lastName = employee.getLastName();
        float have = rosterWeekTablePage.getWorkingHoursHaveByLastName(lastName);
        float should = rosterWeekTablePage.getWorkingHoursShouldByLastName(lastName);
        float diff = rosterWeekTablePage.getWorkingHoursDiffByLastName(lastName);

        softAssert.assertEquals(have, expectedHave, employee.getFullName() + " Have");
        softAssert.assertEquals(should, expectedShould, employee.getFullName() + " Should");
        softAssert.assertEquals(diff, expectedDiff, employee.getFullName() + " Diff");
    }
}
