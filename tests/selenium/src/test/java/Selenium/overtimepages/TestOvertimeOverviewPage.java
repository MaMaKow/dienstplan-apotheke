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

import Selenium.Employee;
import Selenium.TestPage;
import Selenium.PropertyFile;
import Selenium.rosterpages.Workforce;
import Selenium.signin.SignInPage;
import java.time.LocalDate;
import java.time.Month;
import org.openqa.selenium.WebDriver;
import org.testng.Assert;
import org.testng.annotations.Test;

/**
 *
 * @author Mandelkow
 */
/**
 *
 * @author Martin Mandelkow <netbeans@martin-mandelkow.de>
 */
public class TestOvertimeOverviewPage extends TestPage {

    @Test(enabled = true)/*passed*/
    public void testDisplay() {
        /**
         * Sign in:
         */
        super.signIn();
        OvertimeEmployeePage overtimeEmployeePage = new OvertimeEmployeePage(driver);

        /**
         * create an overtime first;
         */
        LocalDate localDate0 = LocalDate.of(2019, Month.JANUARY, 2);
        overtimeEmployeePage.addNewOvertime(localDate0, 8.15f, "Foo");
        OvertimeOverviewPage overtimeOverviewPage = new OvertimeOverviewPage(driver);

        /**
         * Find the overtime balance:
         */
        Workforce workforce = new Workforce();
        int employeeId = 5;
        Employee employee = workforce.getEmployeeById(employeeId);
        Float balance = overtimeOverviewPage.getBalanceByEmployeeName(employee.getLastName());

        /**
         * go back and remove the overtimes:
         */
        overtimeEmployeePage = new OvertimeEmployeePage(driver);
        overtimeEmployeePage.selectYear(localDate0.getYear());
        overtimeEmployeePage.removeOvertimeByLocalDate(localDate0);
        Assert.assertEquals(balance, 8.15f);
    }
}
