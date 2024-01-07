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
import java.time.DayOfWeek;
import java.time.LocalTime;

/**
 *
 * @author Mandelkow
 */
public class PrincipleRosterItem {

    private final int employeeKey;
    private final DayOfWeek weekday;
    private final LocalTime dutyStart;
    private final LocalTime dutyEnd;
    private final LocalTime breakStart;
    private final LocalTime breakEnd;
    private final int branchId;
    private final String comment;

    public PrincipleRosterItem(int employeeKey, DayOfWeek weekday, LocalTime dutyStart, LocalTime dutyEnd, LocalTime breakStart, LocalTime breakEnd, String comment, Integer branchId) {
        this.employeeKey = employeeKey;
        this.weekday = weekday;
        this.dutyStart = dutyStart;
        this.dutyEnd = dutyEnd;
        this.breakStart = breakStart;
        this.breakEnd = breakEnd;
        this.comment = comment;
        this.branchId = branchId;
    }

    public String getEmployeeLastName(Workforce workforce) {
        return workforce.getEmployeeLastNameByKey(employeeKey);
    }

    public int getEmployeeKey() {
        return this.employeeKey;
    }

    public DayOfWeek getWeekday() {
        return this.weekday;
    }

    public LocalTime getDutyStart() {
        return this.dutyStart;
    }

    public LocalTime getDutyEnd() {
        return this.dutyEnd;
    }

    public LocalTime getBreakStart() {
        return this.breakStart;
    }

    public LocalTime getBreakEnd() {
        return this.breakEnd;
    }

    public int getBranchId() {
        return this.branchId;
    }

    public String getComment() {
        return this.comment;
    }

}
