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
import java.time.LocalDate;

/**
 *
 * @author Mandelkow
 */
public class RosterItem {

    private final int employeeId;
    private final LocalDate localDate;
    private final String dutyStart;
    private final String dutyEnd;
    private final String breakStart;
    private final String breakEnd;
    private final int branchId;
    private final String comment;

    public RosterItem(int employeeId, LocalDate localDate, String dutyStart, String dutyEnd, String breakStart, String breakEnd, String comment, Integer branchId) {
        this.employeeId = employeeId;
        this.localDate = LocalDate.from(localDate);
        this.dutyStart = dutyStart;
        this.dutyEnd = dutyEnd;
        this.breakStart = breakStart;
        this.breakEnd = breakEnd;
        this.comment = comment;
        this.branchId = branchId;
    }

    public String getEmployeeName() {
        Workforce workforce = new Workforce();
        Employee employeeObject = workforce.getEmployeeById(employeeId);
        return employeeObject.getLastName();
    }

    public int getEmployeeId() {
        return this.employeeId;
    }

    public LocalDate getLocalDate() {
        return this.localDate;
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
        return this.branchId;
    }

    public String getComment() {
        return this.comment;
    }

}
