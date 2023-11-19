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
import Selenium.TestPage;
import static org.testng.Assert.assertEquals;
import org.testng.annotations.Test;

/**
 *
 * @author Mandelkow
 */
public class TestAbsenceEmployeePage extends TestPage {

    @Test(enabled = true)/*passed*/
    public void testCreateAbsence() {
        /**
         * Sign in:
         */
        super.signIn();
        AbsenceEmployeePage absenceEmployeePage = new AbsenceEmployeePage();
        /**
         * Create a new absence:
         */
        int employeeKey = 7;
        int year = 2020;
        absenceEmployeePage = absenceEmployeePage.goToYear(year);
        absenceEmployeePage = absenceEmployeePage.goToEmployee(employeeKey);
        assertEquals(absenceEmployeePage.getYear(), year);
        assertEquals(absenceEmployeePage.getEmployeeKey(), employeeKey);
        absenceEmployeePage = absenceEmployeePage.createNewAbsence("01.07.2020", "01.07.2020", Absence.REASON_VACATION, "Foo comment", "not_yet_approved"); // 1 = Urlaub
        absenceEmployeePage = absenceEmployeePage.createNewAbsence("01.01.2020", "01.01.2020", Absence.REASON_VACATION, "Neujahr", "not_yet_approved"); //gesetzlicher Feiertag
        /**
         * Check this absence:
         */
        Absence currentAbsence;
        currentAbsence = absenceEmployeePage.getExistingAbsence("01.07.2020", employeeKey);
        assertEquals(currentAbsence.getCommentString(), "Foo comment");
        assertEquals(currentAbsence.getDurationString(), "1");
        assertEquals(currentAbsence.getEmployeeKey(), employeeKey);
        assertEquals(currentAbsence.getStartDateString(), "01.07.2020");
        assertEquals(currentAbsence.getEndDateString(), "01.07.2020");
        assertEquals(currentAbsence.getReasonString(), "Urlaub");
        assertEquals(currentAbsence.getapprovalStringString(), "not_yet_approved");
        /**
         * Manipulate this absence: 1. No manipulation:
         */
        absenceEmployeePage = absenceEmployeePage.editExistingAbsenceNot("01.07.2020", "02.07.2020", "03.07.2020", Absence.REASON_TAKEN_OVERTIME, "Changed Foo comment", "approved");
        currentAbsence = absenceEmployeePage.getExistingAbsence("01.07.2020", employeeKey);
        assertEquals(currentAbsence.getCommentString(), "Foo comment");
        assertEquals(currentAbsence.getDurationString(), "1");
        assertEquals(currentAbsence.getEmployeeKey(), employeeKey);
        assertEquals(currentAbsence.getStartDateString(), "01.07.2020");
        assertEquals(currentAbsence.getEndDateString(), "01.07.2020");
        assertEquals(currentAbsence.getReasonString(), "Urlaub");
        assertEquals(currentAbsence.getReasonString(), Absence.absenceReasonsMap.get(Absence.REASON_VACATION)); //This is the same as the line above, but using the Absence class for help with the string.
        assertEquals(currentAbsence.getapprovalStringString(), "not_yet_approved");
        /**
         * 2. Edit
         */
        absenceEmployeePage = absenceEmployeePage.editExistingAbsence("01.07.2020", "02.07.2020", "03.07.2020", Absence.REASON_TAKEN_OVERTIME, "Changed Foo comment", "approved");
        currentAbsence = absenceEmployeePage.getExistingAbsence("02.07.2020", employeeKey);
        assertEquals(currentAbsence.getCommentString(), "Changed Foo comment");
        assertEquals(currentAbsence.getDurationString(), "2");
        assertEquals(currentAbsence.getEmployeeKey(), employeeKey);
        assertEquals(currentAbsence.getStartDateString(), "02.07.2020");
        assertEquals(currentAbsence.getEndDateString(), "03.07.2020");
        assertEquals(currentAbsence.getReasonString(), "Überstunden genommen");
        assertEquals(currentAbsence.getReasonString(), Absence.absenceReasonsMap.get(Absence.REASON_TAKEN_OVERTIME)); //This is the same as the line above, but using the Absence class for help with the string.
        assertEquals(currentAbsence.getapprovalStringString(), "approved");
        /**
         * Remove the absence:
         */
        absenceEmployeePage = absenceEmployeePage.deleteExistingAbsence("01.07.2020");
        currentAbsence = absenceEmployeePage.getExistingAbsence("01.07.2020", employeeKey);
        assertEquals(currentAbsence, null);
        absenceEmployeePage = absenceEmployeePage.deleteExistingAbsence("02.07.2020");
        currentAbsence = absenceEmployeePage.getExistingAbsence("02.07.2020", employeeKey);
        assertEquals(currentAbsence, null);

        absenceEmployeePage = absenceEmployeePage.deleteExistingAbsence("01.01.2020");
        currentAbsence = absenceEmployeePage.getExistingAbsence("01.01.2020", employeeKey);
        assertEquals(currentAbsence, null);
    }
}
