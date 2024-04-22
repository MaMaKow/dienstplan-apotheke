/*
 * Copyright (C) 2024 Mandelkow
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
import java.time.LocalDate;
import java.time.Month;
import org.testng.Assert;
import static org.testng.Assert.assertEquals;

/**
 *
 * @author Mandelkow
 */
public class TestAbsenceYearPage extends Selenium.TestPage {

    @org.testng.annotations.Test()
    public void testCreateAbsence() {
        /**
         * Sign in:
         */
        super.signIn();
        AbsenceYearPage absenceYearPage = new AbsenceYearPage();

        /**
         * Create a new absence:
         */
        int employeeKey = 7;
        String employeeFullName = "Albert Kremer";
        int year = 2021;
        absenceYearPage = absenceYearPage.goToYear(year);
        assertEquals(absenceYearPage.getYear(), year);
        LocalDate start = LocalDate.of(2021, Month.JULY, 1);
        LocalDate end = LocalDate.of(2021, Month.JULY, 1);
        Absence absence = new Absence(employeeKey, start, end, Absence.REASON_VACATION, "Foo comment", "not_yet_approved");
        absenceYearPage = absenceYearPage.createNewAbsence(absence.getEmployeeKey(), absence.getStartDate(), absence.getEndDate(), absence.getReasonString(), absence.getCommentString(), absence.getapprovalString());
        /**
         * Check this absence:
         */
        Absence foundAbsence = absenceYearPage.getAbsence(absence.getStartDate(), absence.getEmployeeKey());
        Assert.assertTrue(foundAbsence.equals(absence), "The found absence does not match the input absence.");
        softAssert.assertTrue(foundAbsence.equals(absence), "The found absence does not match the input absence.");
        /**
         * Manipulate this absence:
         */
        LocalDate newStartDate = LocalDate.of(2021, Month.JULY, 6);
        LocalDate newEndDate = LocalDate.of(2021, Month.JULY, 6);
        Absence changedAbsence = new Absence(employeeKey, newStartDate, newEndDate, Absence.REASON_TAKEN_OVERTIME, "Foo comment", "not_yet_approved");
        absenceYearPage = absenceYearPage.editExistingAbsence(absence.getEmployeeKey(), absence.getStartDate(),
                changedAbsence.getEmployeeKey(), changedAbsence.getStartDate(), changedAbsence.getEndDate(),
                changedAbsence.getReasonString(), changedAbsence.getCommentString(), changedAbsence.getapprovalString());
        foundAbsence = absenceYearPage.getAbsence(changedAbsence.getStartDate(), changedAbsence.getEmployeeKey());
        softAssert.assertTrue(foundAbsence.equals(changedAbsence), "The found absence does not match the changed absence.");
        /**
         * Remove the absence:
         */
        absenceYearPage = absenceYearPage.deleteExistingAbsence(absence.getEmployeeKey(), absence.getStartDate());
        absenceYearPage = absenceYearPage.deleteExistingAbsence(changedAbsence.getEmployeeKey(), changedAbsence.getStartDate());
        softAssert.assertAll();
    }
}
