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
import java.time.LocalDateTime;
import java.time.LocalTime;

/**
 *
 * @author Mandelkow
 */
public class RosterItem {

    private final Integer employeeKey;
    private final LocalDate localDate;
    private final String dutyStart;
    private final String dutyEnd;
    private final String breakStart;
    private final String breakEnd;
    private final Integer branchId;
    private final String comment;
    private LocalDateTime dutyStartLocalDateTime;
    private LocalDateTime dutyEndLocalDateTime;
    private LocalDateTime breakStartLocalDateTime;
    private LocalDateTime breakEndLocalDateTime;

    public RosterItem(Integer employeeKey, LocalDate localDate, String dutyStart, String dutyEnd, String breakStart, String breakEnd, String comment, Integer branchId) {
        this.employeeKey = employeeKey;
        this.localDate = LocalDate.from(localDate);

        this.dutyStart = dutyStart;
        try {
            LocalTime dutyStartLocalTime = LocalTime.parse(dutyStart);
            dutyStartLocalDateTime = localDate.atTime(dutyStartLocalTime);
        } catch (Exception e) {
            dutyStartLocalDateTime = null;
        }

        this.dutyEnd = dutyEnd;
        try {
            LocalTime dutyEndLocalTime = LocalTime.parse(dutyEnd);
            dutyEndLocalDateTime = localDate.atTime(dutyEndLocalTime);
        } catch (Exception e) {
            dutyEndLocalDateTime = null;
        }

        this.breakStart = breakStart;
        try {
            LocalTime breakStartLocalTime = LocalTime.parse(breakStart);
            breakStartLocalDateTime = localDate.atTime(breakStartLocalTime);
        } catch (Exception e) {
            breakStartLocalDateTime = null;
        }

        this.breakEnd = breakEnd;
        try {
            LocalTime breakEndLocalTime = LocalTime.parse(breakEnd);
            breakEndLocalDateTime = localDate.atTime(breakEndLocalTime);
        } catch (Exception e) {
            breakEndLocalDateTime = null;
        }

        this.comment = comment;
        this.branchId = branchId;
    }

    public String getEmployeeName() {
        /**
         * @todo Use dependency injection and get workforce as a parameter.
         */
        Workforce workforce = new Workforce();
        Employee employeeObject = workforce.getEmployeeByKey(employeeKey);
        return employeeObject.getLastName();
    }

    public Integer getEmployeeKey() {
        return this.employeeKey;
    }

    public LocalDate getLocalDate() {
        return this.localDate;
    }

    public String getDutyStart() {
        return this.dutyStart;
    }

    public LocalDateTime getDutyStartLocalDateTime() {
        return this.dutyStartLocalDateTime;
    }

    public String getDutyEnd() {
        return this.dutyEnd;
    }

    public LocalDateTime getDutyEndLocalDateTime() {
        return this.dutyEndLocalDateTime;
    }

    public String getBreakStart() {
        return this.breakStart;
    }

    public LocalDateTime getBreakStartLocalDateTime() {
        return this.breakStartLocalDateTime;
    }

    public String getBreakEnd() {
        return this.breakEnd;
    }

    public LocalDateTime getBreakEndLocalDateTime() {
        return this.breakEndLocalDateTime;
    }

    public int getBranchId() {
        return this.branchId;
    }

    public String getComment() {
        return this.comment;
    }

}
