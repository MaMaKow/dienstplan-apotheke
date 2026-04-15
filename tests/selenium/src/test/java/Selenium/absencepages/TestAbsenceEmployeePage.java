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
package Selenium.absencepages;

import Selenium.Absence;
import Selenium.Utilities.LogCollector;
import static Selenium.driver.Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR;
import java.time.DayOfWeek;
import java.time.LocalDate;
import java.time.Month;
import java.time.temporal.TemporalAdjusters;
import java.util.List;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.testng.Assert;
import static org.testng.Assert.assertEquals;
import static org.testng.Assert.assertTrue;
import org.testng.annotations.Listeners;
import org.testng.annotations.Test;

/**
 *
 * @author Mandelkow
 */
@Listeners(Selenium.Utilities.Listener.class)
public class TestAbsenceEmployeePage extends Selenium.TestPage {

    @Test()
    public void testCreateAbsence() {
        /**
         * Sign in:
         */
        try {
            super.signIn();
        } catch (Exception exception) {
            logger.error("Sign in failed.");
            Assert.fail();
        }
        AbsenceEmployeePage absenceEmployeePage = new AbsenceEmployeePage(driver);
        /**
         * Create a new absence:
         */
        int employeeKey = 7;
        LocalDate now = LocalDate.now();
        int currentYear = now.getYear();
        absenceEmployeePage = absenceEmployeePage.goToYear(currentYear);
        absenceEmployeePage = absenceEmployeePage.goToEmployee(employeeKey);
        assertEquals(absenceEmployeePage.getYear(), currentYear);
        assertEquals(absenceEmployeePage.getEmployeeKey(), employeeKey);
        LocalDate referenceWorkday = LocalDate.of(currentYear, Month.JULY, 1).with(TemporalAdjusters.nextOrSame(DayOfWeek.TUESDAY));
        LocalDate fullYearStartDate = LocalDate.of(currentYear, Month.JANUARY, 1);
        LocalDate fullYearEndDate = LocalDate.of(currentYear, Month.DECEMBER, 31);
        String referenceWorkdayFormatted = referenceWorkday.format(DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
        String fullYearStartDateFormatted = fullYearStartDate.format(DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
        String fullYearEndDateFormatted = fullYearEndDate.format(DATE_TIME_FORMATTER_DAY_MONTH_YEAR);

        absenceEmployeePage = absenceEmployeePage.createNewAbsence(referenceWorkdayFormatted, referenceWorkdayFormatted, Absence.REASON_VACATION, "Foo comment", "not_yet_approved"); // 1 = Urlaub
        // There should be no error.
        assertTrue(absenceEmployeePage.getUserDialogErrors().isEmpty());
        // Insert the same absence again:
        absenceEmployeePage = absenceEmployeePage.createNewAbsence(referenceWorkdayFormatted, referenceWorkdayFormatted, Absence.REASON_VACATION, "Foo comment", "not_yet_approved"); // 1 = Urlaub
        // Now there should be an error:
        List<String> userDialogErrors = absenceEmployeePage.getUserDialogErrors();
        // Ensure there's at least one error message
        assertTrue(!userDialogErrors.isEmpty());
        assertEquals(userDialogErrors.get(0), "An diesem Datum existiert bereits ein Eintrag. Die Daten wurden daher nicht in die Datenbank eingefügt.");

        // Insert another absence:
        absenceEmployeePage = absenceEmployeePage.createNewAbsence(fullYearStartDateFormatted, fullYearEndDateFormatted, Absence.REASON_VACATION, "ganzes Jahr", "not_yet_approved"); //gesetzliche Feiertage
        List<String> userDialogNotifications = absenceEmployeePage.getUserDialogNotifications();
        assertTrue(!userDialogNotifications.isEmpty());
        assertEquals(userDialogNotifications.get(0), fullYearStartDateFormatted + " ist ein Feiertag (Neujahr) und wird nicht berechnet.");
        assertTrue(userDialogNotifications.get(1).contains("ist kein Arbeitstag für"));
        assertTrue(userDialogNotifications.get(1).contains("und wird nicht gezählt."));
        /**
         * Check this absence:
         */
        Absence currentAbsence;
        currentAbsence = absenceEmployeePage.getExistingAbsence(fullYearStartDateFormatted, employeeKey);
        softAssert.assertEquals(currentAbsence.getEmployeeKey(), employeeKey);
        softAssert.assertEquals(currentAbsence.getStartDate(), fullYearStartDate);
        softAssert.assertEquals(currentAbsence.getEndDate(), fullYearEndDate);
        softAssert.assertEquals(currentAbsence.getCommentString(), "ganzes Jahr");
        try {
            softAssert.assertEquals(currentAbsence.getDurationDays(), currentAbsence.calculateWorkingDays(fullYearStartDate, fullYearEndDate));
        } catch (Exception exception) {
            LogCollector.error(exception.getLocalizedMessage());
            softAssert.fail();
        }
        softAssert.assertEquals(currentAbsence.getReasonString(), "Urlaub");
        softAssert.assertEquals(currentAbsence.getapprovalString(), "not_yet_approved");
        softAssert.assertAll();
        currentAbsence = absenceEmployeePage.getExistingAbsence(referenceWorkdayFormatted, employeeKey);
        softAssert.assertEquals(currentAbsence.getEmployeeKey(), employeeKey);
        softAssert.assertEquals(currentAbsence.getStartDate(), referenceWorkday);
        softAssert.assertEquals(currentAbsence.getEndDate(), referenceWorkday);
        softAssert.assertEquals(currentAbsence.getCommentString(), "Foo comment");
        softAssert.assertEquals(currentAbsence.getDurationDays(), 1);
        softAssert.assertEquals(currentAbsence.getReasonString(), "Urlaub");
        softAssert.assertEquals(currentAbsence.getapprovalString(), "not_yet_approved");
        softAssert.assertAll();
        /**
         * Manipulate this absence: 1. No manipulation:
         */
        absenceEmployeePage = absenceEmployeePage.editExistingAbsenceNot(referenceWorkdayFormatted, referenceWorkday.plusDays(1).format(DATE_TIME_FORMATTER_DAY_MONTH_YEAR), referenceWorkday.plusDays(2).format(DATE_TIME_FORMATTER_DAY_MONTH_YEAR), Absence.REASON_TAKEN_OVERTIME, "Changed Foo comment", "approved");
        currentAbsence = absenceEmployeePage.getExistingAbsence(referenceWorkdayFormatted, employeeKey);
        softAssert.assertEquals(currentAbsence.getCommentString(), "Foo comment");
        softAssert.assertEquals(currentAbsence.getDurationDays(), 1); // Funktioniert nicht an Wochenenden oder Feiertagen, daher ist localDate1 auf den ersten Dienstag im Juli definiert.
        softAssert.assertEquals(currentAbsence.getEmployeeKey(), employeeKey);
        softAssert.assertEquals(currentAbsence.getStartDate(), referenceWorkday);
        softAssert.assertEquals(currentAbsence.getEndDate(), referenceWorkday);
        softAssert.assertEquals(currentAbsence.getReasonString(), "Urlaub");
        softAssert.assertEquals(currentAbsence.getReasonString(), Absence.absenceReasonsMap.get(Absence.REASON_VACATION)); //This is the same as the line above, but using the Absence class for help with the string.
        softAssert.assertEquals(currentAbsence.getapprovalString(), "not_yet_approved");
        softAssert.assertAll();
        /**
         * 2. Edit
         */
        absenceEmployeePage = absenceEmployeePage.editExistingAbsence(referenceWorkdayFormatted, referenceWorkday.plusDays(1).format(DATE_TIME_FORMATTER_DAY_MONTH_YEAR), referenceWorkday.plusDays(2).format(DATE_TIME_FORMATTER_DAY_MONTH_YEAR), Absence.REASON_TAKEN_OVERTIME, "Changed Foo comment", "approved");
        currentAbsence = absenceEmployeePage.getExistingAbsence(referenceWorkday.plusDays(1).format(DATE_TIME_FORMATTER_DAY_MONTH_YEAR), employeeKey);
        softAssert.assertEquals(currentAbsence.getCommentString(), "Changed Foo comment");
        softAssert.assertEquals(currentAbsence.getDurationDays(), 2);
        softAssert.assertEquals(currentAbsence.getEmployeeKey(), employeeKey);
        softAssert.assertEquals(currentAbsence.getStartDate(), referenceWorkday.plusDays(1));
        softAssert.assertEquals(currentAbsence.getEndDate(), referenceWorkday.plusDays(2));
        softAssert.assertEquals(currentAbsence.getReasonString(), "Überstunden genommen");
        softAssert.assertEquals(currentAbsence.getReasonString(), Absence.absenceReasonsMap.get(Absence.REASON_TAKEN_OVERTIME)); //This is the same as the line above, but using the Absence class for help with the string.
        softAssert.assertEquals(currentAbsence.getapprovalString(), "approved");
        softAssert.assertAll();
        /**
         * Remove the absence:
         */
        absenceEmployeePage = absenceEmployeePage.deleteExistingAbsence(referenceWorkdayFormatted);
        currentAbsence = absenceEmployeePage.getExistingAbsence(referenceWorkdayFormatted, employeeKey);
        Assert.assertNull(currentAbsence);
        absenceEmployeePage = absenceEmployeePage.deleteExistingAbsence(referenceWorkday.plusDays(1).format(DATE_TIME_FORMATTER_DAY_MONTH_YEAR));
        currentAbsence = absenceEmployeePage.getExistingAbsence(referenceWorkday.plusDays(1).format(DATE_TIME_FORMATTER_DAY_MONTH_YEAR), employeeKey);
        Assert.assertNull(currentAbsence);

        try {
            absenceEmployeePage = absenceEmployeePage.deleteExistingAbsence(fullYearStartDateFormatted);
        } catch (Exception exception) {
            logger.error("Exception occurred in deleteExistingAbsence() method:");
            logger.error("Exception Message: " + exception.getMessage());
            logger.error("Stack Trace:");
            exception.printStackTrace();
            throw exception;
        }
        currentAbsence = absenceEmployeePage.getExistingAbsence(fullYearStartDateFormatted, employeeKey);
        assertEquals(currentAbsence, null);
    }

    @Test
    public void testOverlapDetectionAndCut() {
        /**
         * Sign in:
         */
        try {
            super.signIn();
        } catch (Exception exception) {
            logger.error("Sign in failed.");
            Assert.fail();
        }
        AbsenceEmployeePage absenceEmployeePage = new AbsenceEmployeePage(driver);
        /**
         * Create a new absence:
         */
        int employeeKey = 7;
        LocalDate now = LocalDate.now();
        int currentYear = now.getYear();

        // Main absence vacation:
        LocalDate mainAbsenceStartDate = LocalDate.of(currentYear, Month.AUGUST, 1);
        String mainAbsenceStartDateFormatted = mainAbsenceStartDate.format(DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
        LocalDate mainAbsenceEndDate = LocalDate.of(currentYear, Month.AUGUST, 7);
        String mainAbsenceEndDateFormatted = mainAbsenceEndDate.format(DATE_TIME_FORMATTER_DAY_MONTH_YEAR);

        // Overlap at the end of this absence:
        LocalDate overlapAtEndAbsenceStartDate = LocalDate.of(currentYear, Month.JANUARY, 1);
        String overlapAtEndAbsenceStartDateFormatted = overlapAtEndAbsenceStartDate.format(DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
        LocalDate overlapAtEndAbsenceEndDate = LocalDate.of(currentYear, Month.AUGUST, 1);
        String overlapAtEndAbsenceEndDateFormatted = overlapAtEndAbsenceEndDate.format(DATE_TIME_FORMATTER_DAY_MONTH_YEAR);

        // Overlap at the start of this absence:
        LocalDate overlapAtStartAbsenceStartDate = LocalDate.of(currentYear, Month.AUGUST, 5);
        String overlapAtStartAbsenceStartDateFormatted = overlapAtStartAbsenceStartDate.format(DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
        LocalDate overlapAtStartAbsenceEndDate = LocalDate.of(currentYear, Month.DECEMBER, 31);
        String overlapAtStartAbsenceEndDateFormatted = overlapAtStartAbsenceEndDate.format(DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
        // Dates after cutting:
        LocalDate overlapAtStartAbsenceStartDateAfterCut = mainAbsenceEndDate.plusDays(1);
        String overlapAtStartAbsenceStartDateAfterCutFormatted = overlapAtStartAbsenceStartDateAfterCut.format(DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
        LocalDate overlapAtEndAbsenceEndDateAfterCut = mainAbsenceStartDate.minusDays(1);
        String overlapAtEndAbsenceEndDateAfterCutFormatted = overlapAtEndAbsenceEndDateAfterCut.format(DATE_TIME_FORMATTER_DAY_MONTH_YEAR);

        absenceEmployeePage = absenceEmployeePage.goToYear(currentYear);
        absenceEmployeePage = absenceEmployeePage.goToEmployee(employeeKey);
        assertEquals(absenceEmployeePage.getYear(), currentYear);
        assertEquals(absenceEmployeePage.getEmployeeKey(), employeeKey);
        try {
            absenceEmployeePage = absenceEmployeePage.createNewAbsence(mainAbsenceStartDateFormatted, mainAbsenceEndDateFormatted, Absence.REASON_VACATION, "main absence", "not_yet_approved");
            absenceEmployeePage = absenceEmployeePage.createNewAbsence(overlapAtEndAbsenceStartDateFormatted, overlapAtEndAbsenceEndDateFormatted, Absence.REASON_PARENTAL_LEAVE, "overlap at end", "not_yet_approved");
            absenceEmployeePage = absenceEmployeePage.createNewAbsence(overlapAtStartAbsenceStartDateFormatted, overlapAtStartAbsenceEndDateFormatted, Absence.REASON_MATERNITY_LEAVE, "overlap at start", "not_yet_approved");
        } catch (Exception exception) {
            logger.error("Exception occurred in deleteExistingAbsence() method:");
            logger.error("Exception Message: " + exception.getMessage());
            logger.error("Stack Trace:");
            exception.printStackTrace();
            throw exception;
        }

        /**
         * Check this overlap detection:
         */
        Assert.assertTrue(absenceEmployeePage.absenceHasAnOverlap(overlapAtEndAbsenceStartDateFormatted, employeeKey));
        Assert.assertTrue(absenceEmployeePage.absenceHasAnOverlap(mainAbsenceStartDateFormatted, employeeKey));
        Assert.assertTrue(absenceEmployeePage.absenceHasAnOverlap(overlapAtStartAbsenceStartDateFormatted, employeeKey));
        /**
         * Check this overlap cut:
         */
        absenceEmployeePage = absenceEmployeePage.cutOverlapOnAbsence(overlapAtEndAbsenceStartDateFormatted, employeeKey);
        absenceEmployeePage = absenceEmployeePage.cutOverlapOnAbsence(overlapAtStartAbsenceStartDateFormatted, employeeKey);
        Absence currentAbsence;
        // main absence has not been cut:
        currentAbsence = absenceEmployeePage.getExistingAbsence(mainAbsenceStartDateFormatted, employeeKey);
        assertEquals(currentAbsence.getStartDate(), mainAbsenceStartDate);
        assertEquals(currentAbsence.getEndDate(), mainAbsenceEndDate);
        // absence has been cut at start:
        currentAbsence = absenceEmployeePage.getExistingAbsence(overlapAtStartAbsenceStartDateAfterCutFormatted, employeeKey);
        assertEquals(currentAbsence.getStartDate(), overlapAtStartAbsenceStartDateAfterCut);
        assertEquals(currentAbsence.getEndDate(), overlapAtStartAbsenceEndDate);
        // absence has been cut at end:
        currentAbsence = absenceEmployeePage.getExistingAbsence(overlapAtEndAbsenceStartDateFormatted, employeeKey);
        assertEquals(currentAbsence.getStartDate(), overlapAtEndAbsenceStartDate);
        assertEquals(currentAbsence.getEndDate(), overlapAtEndAbsenceEndDateAfterCut);
        /**
         * Remove the absence:
         */
        try {
            absenceEmployeePage = absenceEmployeePage.deleteExistingAbsence(overlapAtEndAbsenceStartDateFormatted);
            absenceEmployeePage = absenceEmployeePage.deleteExistingAbsence(mainAbsenceStartDateFormatted);
            absenceEmployeePage = absenceEmployeePage.deleteExistingAbsence(overlapAtStartAbsenceStartDateAfterCutFormatted);
        } catch (Exception exception) {
            System.out.println("Exception occurred in deleteExistingAbsence() method:");
            System.out.println("Exception Message: " + exception.getMessage());
            System.out.println("Stack Trace:");
            exception.printStackTrace();
            throw exception;
        }

    }
}
