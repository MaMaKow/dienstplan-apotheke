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
package Selenium;

import Selenium.rosterpages.Workforce;
import java.util.Calendar;
import java.util.HashMap;

/**
 *
 * @author Mandelkow
 */
public class RosterItem {

    //private int employeeId;
    private final Employee employeeObject;
    private String dateString;
    private final Calendar date;
    private final String dutyStart;
    private final String dutyEnd;
    private final String breakStart;
    private final String breakEnd;
    private final Branch branchObject;
    private final String comment;

    public RosterItem(int employeeId, Calendar calendar, String dutyStart, String dutyEnd, String breakStart, String breakEnd, String comment, Integer branchId) {
        Workforce workforce = new Workforce();
        HashMap<Integer, Employee> listOfEmployees = workforce.getListOfEmployees();
        Employee employee = listOfEmployees.get(employeeId);
        this.employeeObject = employee;
        this.date = calendar;
        this.dutyStart = dutyStart;
        this.dutyEnd = dutyEnd;
        this.breakStart = breakStart;
        this.breakEnd = breakEnd;
        this.comment = comment;

        NetworkOfBranchOffices networkOfBranchOffices = new NetworkOfBranchOffices();
        this.branchObject = networkOfBranchOffices.getBranchById(branchId);
    }

    public String getEmployeeName() {
        return this.employeeObject.getLastName();
    }

    public int getEmployeeId() {
        return this.employeeObject.getEmployeeId();
    }

    public String getDateString() {
        return this.dateString;
    }

    public Calendar getDate() {
        return this.date;
    }

    public String getDutyStart() {
        return this.dutyStart;
    }

    public String getDutyEnd() {
        return this.dutyEnd;
    }

    public String getBreakStart() {
        return this.breakStart;
    }

    public String getBreakEnd() {
        return this.breakEnd;
    }

    public int getBranchId() {
        return this.branchObject.getBranchId();
    }

    public String getComment() {
        return this.comment;
    }

}
