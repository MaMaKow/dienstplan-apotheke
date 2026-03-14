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
import Selenium.Employee;
import Selenium.TestPage;
import Selenium.absencepages.AbsenceEmployeePage;
import Selenium.driver.Wrapper;
import java.time.LocalDate;
import java.time.Month;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.testng.Assert;
import org.testng.annotations.Test;

/**
 * @todo Alle Varianten durchrechnen und Zahlen korrigieren
 * @todo Aktuell sind im Plan nur die Nachnamen. Es müssen die vollen Namen dort
 * stehen. Sonst werden die Zeilen nicht gefunden.
 * @author Martin Mandelkow
 */
public class TestRosterWeekTableOvertimeAbsences extends TestPage {

    @Test()
    public void testOvertimeAbsenceCalculation() {
        try {
            /**
             * Sign in:
             */
            super.signIn();
        } catch (Exception exception) {
            Logger.getLogger(TestRosterWeekTablePage.class.getName()).log(Level.SEVERE, null, exception);
        }
        /**
         * Create some absences:
         */
        AbsenceEmployeePage absenceEmployeePage = new AbsenceEmployeePage();
        Workforce workforce = new Workforce();//Todo, use preloaded workforce from TestPage class.
        Employee valentinaArnold = workforce.getEmployeeByFullName("Valentina Arnold");
        absenceEmployeePage.goToEmployee(valentinaArnold.getEmployeeKey());
        LocalDate tagDerArbeit = LocalDate.of(2025, Month.MAY, 1);
        LocalDate firstDayInWeek = tagDerArbeit.minusDays(3);
        LocalDate lastDayInWeek = tagDerArbeit.plusDays(3);
        absenceEmployeePage.createNewAbsence(tagDerArbeit.format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR), tagDerArbeit.format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR), Absence.REASON_VACATION, "comment", "approved");

        /**
         * Create a roster from 28.04. to 04.05.2025
         */
        RosterDayEditPage rosterDayEditPage = new RosterDayEditPage(driver);
        for (LocalDate localDate = firstDayInWeek; localDate.compareTo(lastDayInWeek) <= 0; localDate.plusDays(1)) {
            rosterDayEditPage.goToDate(tagDerArbeit);
            rosterDayEditPage.rosterFormSubmit();
        }
        /**
         * Read the calculated hours from the weekly roster table page:
         */
        RosterWeekTablePage rosterWeekTablePage = new RosterWeekTablePage(driver);
        rosterWeekTablePage.goToDate(tagDerArbeit);
        float workingHoursHave = rosterWeekTablePage.getWorkingHoursHaveByFullName(valentinaArnold.getFullName());
        float workingHoursShould = rosterWeekTablePage.getWorkingHoursShouldByFullName(valentinaArnold.getFullName());
        float workingHoursDiff = rosterWeekTablePage.getWorkingHoursDiffByFullName(valentinaArnold.getFullName());
        Assert.assertEquals(workingHoursHave, 1.2); //TODO: use the correct numbers here
        Assert.assertEquals(workingHoursShould, 1.3);
        Assert.assertEquals(workingHoursDiff, 1.4);
    }
}
