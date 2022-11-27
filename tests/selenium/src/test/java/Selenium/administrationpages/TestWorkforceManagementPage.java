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
package Selenium.administrationpages;

import Selenium.Employee;
import Selenium.TestPage;
import Selenium.rosterpages.Workforce;
import java.util.Map;
import org.testng.annotations.Test;
import org.testng.Assert;

/**
 *
 * @author Mandelkow
 */
public class TestWorkforceManagementPage extends TestPage {

    @Test(enabled = true, dependsOnMethods = {"testCreateEmployee"})/*new*/
    public void testReadEmployee() {
        /**
         * Sign in:
         */
        super.signIn();
        WorkforceManagementPage workforceManagementPage = new WorkforceManagementPage(driver);

        workforceManagementPage.selectEmployee(1);

        Workforce workforce = new Workforce();
        Map<Integer, Employee> listOfEmployeesMap = workforce.getListOfEmployees();
        Employee employeeObjectShould = listOfEmployeesMap.get(5);

        workforceManagementPage.selectEmployee(employeeObjectShould.getEmployeeId());

        Employee employeeObject = workforceManagementPage.getEmployeeObject();
        Assert.assertEquals(employeeObject.getEmployeeId(), employeeObjectShould.getEmployeeId());
        Assert.assertEquals(employeeObject.getLastName(), employeeObjectShould.getLastName());
        Assert.assertEquals(employeeObject.getFirstName(), employeeObjectShould.getFirstName());
        Assert.assertEquals(employeeObject.getProfession(), employeeObjectShould.getProfession());
        Assert.assertEquals(employeeObject.getWorkingHours(), employeeObjectShould.getWorkingHours());
        Assert.assertEquals(employeeObject.getLunchBreakMinutes(), employeeObjectShould.getLunchBreakMinutes());
        Assert.assertEquals(employeeObject.getHolidays(), employeeObjectShould.getHolidays());
        Assert.assertEquals(employeeObject.getBranchString(), employeeObjectShould.getBranchString());
        Assert.assertEquals(employeeObject.getAbilitiesGoodsReceipt(), employeeObjectShould.getAbilitiesGoodsReceipt());
        Assert.assertEquals(employeeObject.getAbilitiesCompounding(), employeeObjectShould.getAbilitiesCompounding());
        Assert.assertEquals(employeeObject.getStartOfEmployment(), employeeObjectShould.getStartOfEmployment());
        Assert.assertEquals(employeeObject.getEndOfEmployment(), employeeObjectShould.getEndOfEmployment());
    }

    @Test(enabled = true)/*new*/
    public void testCreateEmployee() {

        Workforce workforce = new Workforce();
        //workforce.writeToFile(listOfEmployees);
        Map<Integer, Employee> listOfEmployeesMap = workforce.getListOfEmployees();
        /**
         * Sign in:
         */
        super.signIn();
        WorkforceManagementPage workforceManagementPage = new WorkforceManagementPage(driver);

        /**
         * TODO: CAVE! Old employees seem to be overwritten.
         */
        listOfEmployeesMap.forEach((employeeId, employee) -> {
            workforceManagementPage.createEmployee(employee);
        });
    }
}
