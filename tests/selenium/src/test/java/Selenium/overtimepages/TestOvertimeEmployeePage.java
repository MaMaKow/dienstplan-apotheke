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
package Selenium.overtimepages;

import Selenium.TestPage;
import Selenium.Overtime;
import java.time.LocalDate;
import java.time.Month;
import org.testng.annotations.Test;

/**
 *
 * @author Mandelkow
 */
/**
 *
 * @author Martin Mandelkow <netbeans@martin-mandelkow.de>
 */
public class TestOvertimeEmployeePage extends TestPage {

    @Test(enabled = true)/*passed*/
    public void testDisplay() {
        /**
         * Sign in:
         */
        super.signIn();
        OvertimeEmployeePage overtimeEmployeePage = new OvertimeEmployeePage(driver);

        /**
         * Move to specific year:
         */
        LocalDate localDate0 = LocalDate.of(2019, Month.JANUARY, 2);
        LocalDate localDate1 = LocalDate.of(2019, Month.MARCH, 3);
        LocalDate localDate2 = LocalDate.of(2019, Month.JULY, 5);
        LocalDate localDate3 = LocalDate.of(2019, Month.DECEMBER, 24);
        overtimeEmployeePage.selectYear(localDate0.getYear());
        overtimeEmployeePage.selectEmployee(5);
        /**
         * Create new overtime:
         */
        overtimeEmployeePage.addNewOvertime(localDate0, 8, "Foo");
        overtimeEmployeePage.addNewOvertime(localDate1, 0.5f, "FloatFoo");
        overtimeEmployeePage.addNewOvertime(localDate2, -8, "NoFoo");
        overtimeEmployeePage.addNewOvertime(localDate3, 1, "Bar");
        overtimeEmployeePage.addNewOvertime(localDate3, 99, "Error"); //Should not get inserted
        /**
         * Find the newly created overtime:
         */
        Overtime overtime = overtimeEmployeePage.getOvertimeByCalendar(localDate3);
        softAssert.assertEquals(overtime.getBalance(), (float) 1.5f);
        softAssert.assertEquals(overtime.getHours(), (float) 1.0f);
        softAssert.assertEquals(overtime.getReason(), "Bar");
        /**
         * remove the created overtime:
         */
        overtimeEmployeePage.removeOvertimeByLocalDate(localDate0);
        overtimeEmployeePage.removeOvertimeByLocalDate(localDate1);
        overtimeEmployeePage.removeOvertimeByLocalDate(localDate2);
        overtimeEmployeePage.removeOvertimeByLocalDate(localDate3);
        softAssert.assertAll();
    }
}
