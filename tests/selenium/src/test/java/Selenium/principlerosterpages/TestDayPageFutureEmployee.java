/*
 * Copyright (C) 2026 Martin Mandelkow <netbeans@martin-mandelkow.de>
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
package Selenium.principlerosterpages;

import Selenium.Employee;
import Selenium.NetworkOfBranchOffices;
import Selenium.PrincipleRosterItem;
import Selenium.Utilities.LogCollector;
import Selenium.administrationpages.WorkforceManagementPage;
import Selenium.driver.Wrapper;
import java.time.DayOfWeek;
import java.time.LocalDate;
import java.time.LocalTime;
import org.testng.Assert;
import org.testng.annotations.Test;

/**
 *
 * @author Martin Mandelkow
 */
public class TestDayPageFutureEmployee extends Selenium.TestPage {

    @Test()
    public void testFutureEmployee() {
        /**
         * Sign in:
         */
        try {
            super.signIn();
        } catch (Exception exception) {
            logger.error("Sign in failed.");
            Assert.fail();
        }
        NetworkOfBranchOffices networkOfBranchOffices = new NetworkOfBranchOffices();
        int branchId = networkOfBranchOffices.getListOfBranches().entrySet().iterator().next().getKey(); // get first Branch
        // from list of
        // branches.
        // 1. Create employee with start date = tomorrow's date + 1 year
        WorkforceManagementPage workforceManagementPage = new WorkforceManagementPage(driver);
        LocalDate today = LocalDate.now();
        LocalDate nextYear = today.plusYears(1).minusDays(14); // If the employee is exactly one year from now, it is not in the select element of employees anymore.
        Employee futureEmployee = new Employee("999", "Future", "Test", "PTA", "40", "30", "28",
                "Hauptapotheke am großen Platz", "true", "true",
                nextYear.format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR), "");
        workforceManagementPage.createEmployee(futureEmployee);
        try {
            workforceManagementPage.selectEmployee(futureEmployee);
        } catch (Exception ex) {
            LogCollector.warn("Employee not found on page.");
        }
        /*
         * The employeeKey will not actually be 999. We have to find the key, that the
         * database gave to the new employee:
         */
        futureEmployee = workforceManagementPage.getEmployeeObject();
        LogCollector.debug("The newly created employee " + futureEmployee.getFullName() + " has the employeeKey "
                + futureEmployee.getEmployeeKey());
        // 2. Go to principle roster day for a date after that start date
        Selenium.principlerosterpages.DayPage principleRosterDayPage = new DayPage(driver);
        principleRosterDayPage.goToWeekday(DayOfWeek.MONDAY);
        try {
            principleRosterDayPage.goToAlternation(0);
        } catch (Exception ex) {
            LogCollector.error("Could not open alternation 0 in principle roster day page.");
            softAssert.fail();
        }
        principleRosterDayPage.goToBranch(branchId);

        // 3. Add a shift for this employee and submit
        PrincipleRosterItem principleRosterItem = new PrincipleRosterItem(futureEmployee.getEmployeeKey(),
                DayOfWeek.MONDAY, LocalTime.of(10, 30), LocalTime.of(17, 45), LocalTime.of(13, 0), LocalTime.of(13, 30),
                "future comment", branchId);
        principleRosterDayPage.createNewRosterItem(principleRosterItem);

        // 4. Verify employee appears in the roster
        PrincipleRosterItem foundPrincipleRosterItem = principleRosterDayPage
                .getRosterItemByEmployeeKey(futureEmployee.getEmployeeKey());
        softAssert.assertEquals(foundPrincipleRosterItem.getEmployeeKey(), principleRosterItem.getEmployeeKey());
        softAssert.assertEquals(foundPrincipleRosterItem.getDutyStart(), principleRosterItem.getDutyStart());
        softAssert.assertEquals(foundPrincipleRosterItem.getDutyEnd(), principleRosterItem.getDutyEnd());

        // 5. Delete the row (either clear the fields or remove the row)
        principleRosterDayPage.changeRosterInputEmployee(futureEmployee.getEmployeeKey(), null);

        // 6. Verify employee no longer appears
        try {
            principleRosterDayPage.getRosterItemByEmployeeKey(futureEmployee.getEmployeeKey());
            softAssert.fail("The command getRosterItemByEmployeeKey MUST NOT find the deleted employee anymore.");
        } catch (Exception exception) {
            /**
             * This is the expected result. There should be an exception,
             * because the row has not been found anymore..
             */
            LogCollector.debug("future employee could successfully be deleted.");
        }

        // 7. Cleanup
        workforceManagementPage = new WorkforceManagementPage(driver);
        workforceManagementPage.deleteEmployee(futureEmployee);

    }
}
