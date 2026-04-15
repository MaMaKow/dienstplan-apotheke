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
import java.time.format.DateTimeFormatter;
import java.util.Locale;

/**
 *
 * @author Mandelkow
 */
public class RosterItem {

    private final Integer employeeKey;
    private final Employee employeeObject;
    private final LocalDate localDate;
    private final Integer branchId;
    private final String comment;
    private LocalDateTime dutyStartLocalDateTime;
    private LocalDateTime dutyEndLocalDateTime;
    private LocalDateTime breakStartLocalDateTime;
    private LocalDateTime breakEndLocalDateTime;
    public static final DateTimeFormatter DATE_TIME_FORMATTER_HOUR_MINUTE = DateTimeFormatter.ofPattern("HH:mm", Locale.GERMANY);

    /**
     *
     * @param employeeKey
     * @param localDate
     * @param dutyStart
     * @param dutyEnd
     * @param breakStart
     * @param breakEnd
     * @param comment
     * @param branchId
     * @deprecated Do not use employeeKeys anymore!
     */
    public RosterItem(Integer employeeKey, LocalDate localDate, String dutyStart, String dutyEnd, String breakStart, String breakEnd, String comment, Integer branchId) {
        this.employeeKey = employeeKey;
        Workforce workforce = TestPage.workforce;
        this.employeeObject = workforce.getEmployeeByKey(employeeKey);
        this.localDate = LocalDate.from(localDate);

        try {
            LocalTime dutyStartLocalTime = LocalTime.parse(dutyStart);
            dutyStartLocalDateTime = localDate.atTime(dutyStartLocalTime);
        } catch (Exception e) {
            dutyStartLocalDateTime = null;
        }

        try {
            LocalTime dutyEndLocalTime = LocalTime.parse(dutyEnd);
            dutyEndLocalDateTime = localDate.atTime(dutyEndLocalTime);
        } catch (Exception e) {
            dutyEndLocalDateTime = null;
        }

        try {
            LocalTime breakStartLocalTime = LocalTime.parse(breakStart);
            breakStartLocalDateTime = localDate.atTime(breakStartLocalTime);
        } catch (Exception e) {
            breakStartLocalDateTime = null;
        }

        try {
            LocalTime breakEndLocalTime = LocalTime.parse(breakEnd);
            breakEndLocalDateTime = localDate.atTime(breakEndLocalTime);
        } catch (Exception e) {
            breakEndLocalDateTime = null;
        }

        this.comment = comment;
        this.branchId = branchId;
    }

    public RosterItem(String fullName, LocalDate localDate, String dutyStart, String dutyEnd, String breakStart, String breakEnd, String comment, Integer branchId) {
        Workforce workforce = TestPage.workforce;
        this.employeeObject = workforce.getEmployeeByFullName(fullName);
        if (null == employeeObject) {
            this.employeeKey = null;
        } else {
            this.employeeKey = employeeObject.getEmployeeKey();
        }
        this.localDate = LocalDate.from(localDate);

        try {
            LocalTime dutyStartLocalTime = LocalTime.parse(dutyStart);
            dutyStartLocalDateTime = localDate.atTime(dutyStartLocalTime);
        } catch (Exception e) {
            dutyStartLocalDateTime = null;
        }

        try {
            LocalTime dutyEndLocalTime = LocalTime.parse(dutyEnd);
            dutyEndLocalDateTime = localDate.atTime(dutyEndLocalTime);
        } catch (Exception e) {
            dutyEndLocalDateTime = null;
        }

        try {
            LocalTime breakStartLocalTime = LocalTime.parse(breakStart);
            breakStartLocalDateTime = localDate.atTime(breakStartLocalTime);
        } catch (Exception e) {
            breakStartLocalDateTime = null;
        }

        try {
            LocalTime breakEndLocalTime = LocalTime.parse(breakEnd);
            breakEndLocalDateTime = localDate.atTime(breakEndLocalTime);
        } catch (Exception e) {
            breakEndLocalDateTime = null;
        }

        this.comment = comment;
        this.branchId = branchId;
    }

    public String getEmployeeLastName() {
        return employeeObject.getLastName();
    }

    public String getEmployeeFullName() {
        return employeeObject.getFullName();
    }

    public Integer getEmployeeKey() {
        return employeeKey;
    }

    public LocalDate getLocalDate() {
        return localDate;
    }

    public String getDutyStart() {
        if (null != dutyStartLocalDateTime) {
            return dutyStartLocalDateTime.format(DATE_TIME_FORMATTER_HOUR_MINUTE);
        }
        return "";
    }

    public LocalDateTime getDutyStartLocalDateTime() {
        return dutyStartLocalDateTime;
    }

    public String getDutyEnd() {
        if (null != dutyEndLocalDateTime) {
            return dutyEndLocalDateTime.format(DATE_TIME_FORMATTER_HOUR_MINUTE);
        }
        return "";
    }

    public LocalDateTime getDutyEndLocalDateTime() {
        return dutyEndLocalDateTime;
    }

    public String getBreakStart() {
        if (null != breakStartLocalDateTime) {
            return breakStartLocalDateTime.format(DATE_TIME_FORMATTER_HOUR_MINUTE);
        }
        return "";
    }

    public LocalDateTime getBreakStartLocalDateTime() {
        return breakStartLocalDateTime;
    }

    public String getBreakEnd() {
        if (null != breakEndLocalDateTime) {
            return breakEndLocalDateTime.format(DATE_TIME_FORMATTER_HOUR_MINUTE);
        }
        return "";
    }

    public LocalDateTime getBreakEndLocalDateTime() {
        return breakEndLocalDateTime;
    }

    public int getBranchId() {
        return branchId;
    }

    public String getComment() {
        return comment;
    }

}
