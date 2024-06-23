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
import Selenium.driver.Wrapper;
import Selenium.NetworkOfBranchOffices;
import Selenium.rosterpages.Workforce;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.testng.annotations.Test;
import org.testng.Assert;

/**
 *
 * @author Mandelkow
 */
public class TestWorkforceManagementPage extends Selenium.TestPage {

    @Test(enabled = true, dependsOnMethods = {"testCreateEmployee"})/*new*/
    public void testReadEmployee() {
        /**
         * Sign in:
         */
        super.signIn();
        WorkforceManagementPage workforceManagementPage = new WorkforceManagementPage(driver);

        Map<Integer, Employee> listOfEmployeesMap = workforce.getListOfEmployees();
        Employee employeeObjectShould = listOfEmployeesMap.get(7);

        try {
            workforceManagementPage.selectEmployee(employeeObjectShould);
        } catch (Exception ex) {
            Logger.getLogger(TestWorkforceManagementPage.class.getName()).log(Level.SEVERE, null, ex);
            Assert.fail();
        }

        Employee employeeObject = workforceManagementPage.getEmployeeObject();

        Assert.assertEquals(employeeObject.getLastName(), employeeObjectShould.getLastName());
        Assert.assertEquals(employeeObject.getFirstName(), employeeObjectShould.getFirstName());
        Assert.assertEquals(employeeObject.getProfession(), employeeObjectShould.getProfession());
        Assert.assertEquals(employeeObject.getWorkingHours(), employeeObjectShould.getWorkingHours());
        Assert.assertEquals(employeeObject.getLunchBreakMinutes(), employeeObjectShould.getLunchBreakMinutes());
        Assert.assertEquals(employeeObject.getHolidays(), employeeObjectShould.getHolidays());
        /**
         * @todo <p lang=de>Ich möchte versuchen, ohne den EmployeeKee zu arbeiten.
         * Statt dessen können wir sicherlich den vollen Namen verwenden.
         * </p>
         */
        //Assert.assertEquals(employeeObject.getEmployeeKey(), employeeObjectShould.getEmployeeKey());

        NetworkOfBranchOffices networkOfBranchOffices = new NetworkOfBranchOffices();
        Assert.assertEquals(employeeObject.getBranchString(networkOfBranchOffices), employeeObjectShould.getBranchString(networkOfBranchOffices));
        Assert.assertEquals(employeeObject.getAbilitiesGoodsReceipt(), employeeObjectShould.getAbilitiesGoodsReceipt());
        Assert.assertEquals(employeeObject.getAbilitiesCompounding(), employeeObjectShould.getAbilitiesCompounding());
        Assert.assertEquals(employeeObject.getStartOfEmployment(), employeeObjectShould.getStartOfEmployment());
        Assert.assertEquals(employeeObject.getEndOfEmployment(), employeeObjectShould.getEndOfEmployment());
    }

    @Test(enabled = true)/*new*/
    public void testCreateEmployee() {
        Map<Integer, Employee> listOfEmployeesMap = workforce.getListOfEmployees();
        NetworkOfBranchOffices networkOfBranchOffices = new NetworkOfBranchOffices();
        /**
         * Sign in:
         */
        super.signIn();
        WorkforceManagementPage workforceManagementPage = new WorkforceManagementPage(driver);

        /**
         * CAVE! Old employees will be overwritten with new data.
         */
        listOfEmployeesMap.forEach((employeeKey, employee) -> {
            workforceManagementPage.createEmployee(employee);
        });
        Employee firstEmployee = getFirstEmployee(listOfEmployeesMap);
        Employee changedEmployee = new Employee(String.valueOf(firstEmployee.getEmployeeKey()),
                firstEmployee.getLastName(),
                firstEmployee.getFirstName(),
                firstEmployee.getProfession(),
                String.valueOf(firstEmployee.getWorkingHours()),
                String.valueOf(firstEmployee.getLunchBreakMinutes()),
                String.valueOf(0), //no holidays
                firstEmployee.getBranchString(networkOfBranchOffices),
                String.valueOf(firstEmployee.getAbilitiesGoodsReceipt()),
                String.valueOf(firstEmployee.getAbilitiesCompounding()),
                firstEmployee.getStartOfEmployment().format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR),
                firstEmployee.getEndOfEmployment().format(Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR)
        );
        workforceManagementPage.createEmployee(changedEmployee);
        Employee readEmployeeChanged = workforceManagementPage.getEmployeeObject();
        Assert.assertEquals(readEmployeeChanged.getHolidays(), changedEmployee.getHolidays());
        Assert.assertEquals(readEmployeeChanged.getFullName(), changedEmployee.getFullName());
        workforceManagementPage.createEmployee(firstEmployee);
        Employee readEmployeeFirst = workforceManagementPage.getEmployeeObject();
        Assert.assertEquals(readEmployeeFirst.getFullName(), firstEmployee.getFullName());
        Assert.assertEquals(readEmployeeFirst.getHolidays(), firstEmployee.getHolidays());
    }

    public static Employee getFirstEmployee(Map<Integer, Employee> listOfEmployeesMap) {
        if (listOfEmployeesMap.isEmpty()) {
            return null;
        }
        return listOfEmployeesMap.entrySet().iterator().next().getValue();
    }
}
