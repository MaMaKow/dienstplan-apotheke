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
package Selenium.models;

import Selenium.Employee;

/**
 *
 * @author martin
 */
public class EmployeeHoursRow {

    private Employee employee;
    private float hoursHave;
    private float hoursShould;

    public EmployeeHoursRow(Employee employeeInput, float hoursHaveInput, float hoursShouldInput) {
        employee = employeeInput;
        hoursHave = hoursHaveInput;
        hoursShould = hoursShouldInput;
    }

    public Employee getEmployee() {
        return employee;
    }

    public float getHoursHave() {
        return hoursHave;
    }

    public void setHoursHave(float hoursHaveInput) {
        hoursHave = hoursHaveInput;
    }

    public float getHoursShould() {
        return hoursShould;
    }

    public void setHoursShould(float hoursShouldInput) {
        hoursShould = hoursShouldInput;
    }

    public float getHoursDiff() {
        return hoursHave - hoursShould;
    }
}
