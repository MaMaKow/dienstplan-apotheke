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

import Selenium.Utilities.Holidays;
import com.google.common.collect.ImmutableMap;
import java.time.DayOfWeek;
import java.time.LocalDate;
import java.time.temporal.ChronoUnit;
import java.time.format.DateTimeFormatter;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 *
 * @author Mandelkow
 */
public class Absence {

    private LocalDate startDate;
    private LocalDate endDate;
    private final int reasonId;
    private final String commentString;
    private int durationDays;
    private final String approvalString;
    private final int employeeKey;

    public static Map<Integer, String> absenceReasonsMap = ImmutableMap.<Integer, String>builder()
            .put(1, "Urlaub")
            .put(2, "Resturlaub")
            .put(3, "Krankheit")
            .put(4, "Krankheit des Kindes")
            .put(5, "Überstunden genommen")
            .put(6, "bezahlte Freistellung")
            .put(7, "Mutterschutz")
            .put(8, "Elternzeit")
            .build();

    public static final int REASON_VACATION = 1;
    public static final int REASON_REMAINING_VACATION = 2;
    public static final int REASON_SICKNESS = 3;
    public static final int REASON_SICKNESS_OF_CHILD = 4;
    public static final int REASON_TAKEN_OVERTIME = 5;
    public static final int REASON_PAID_LEAVE_OF_ABSENCE = 6;
    public static final int REASON_MATERNITY_LEAVE = 7;
    public static final int REASON_PARENTAL_LEAVE = 8;

    public Absence(int employeeKey, LocalDate startDate, LocalDate endDate, int reasonId, String commentString, String approvalString) {
        this.employeeKey = employeeKey;
        this.startDate = startDate;
        this.endDate = endDate;
        this.commentString = commentString;
        try {
            this.durationDays = calculateWorkingDays(startDate, endDate);
        } catch (Exception ex) {
            this.durationDays = 0;
            Logger.getLogger(Absence.class.getName()).log(Level.SEVERE, null, ex);
        }
        this.approvalString = approvalString;
        this.reasonId = reasonId;
    }

    private int calculateWorkingDays(LocalDate startDate, LocalDate endDate) throws Exception {
        int workingDays = 0;
        LocalDate date = startDate;
        Holidays holidays = new Holidays(startDate.getYear());
        while (!date.isAfter(endDate)) {
            if (date.getDayOfWeek() != DayOfWeek.SATURDAY && date.getDayOfWeek() != DayOfWeek.SUNDAY && !holidays.isHoliday(date)) {
                workingDays++;
            }
            date = date.plusDays(1);
        }
        return workingDays;
    }

    public int getEmployeeKey() {
        return employeeKey;
    }

    public LocalDate getStartDate() {
        return startDate;
    }

    public LocalDate getEndDate() {
        return endDate;
    }

    public String getReasonString() {
        return absenceReasonsMap.get(this.reasonId);
    }

    public int getReasonId() {
        return reasonId;
    }

    public String getCommentString() {
        return commentString;
    }

    public int getDurationDays() {
        return durationDays;
    }

    public String getapprovalString() {
        return approvalString;
    }

    public boolean equals(Absence otherAbsence) {
        if (otherAbsence.getEmployeeKey() != this.employeeKey) {
            return false;
        }
        if (!otherAbsence.getStartDate().equals(this.startDate)) {
            return false;
        }
        if (!otherAbsence.getEndDate().equals(this.endDate)) {
            return false;
        }
        if (otherAbsence.getDurationDays() != this.durationDays) {
            return false;
        }
        if (!otherAbsence.getCommentString().equals(this.commentString)) {
            return false;
        }
        if (!otherAbsence.getReasonString().equals(this.getReasonString())) {
            return false;
        }
        if (!otherAbsence.getapprovalString().equals(this.approvalString)) {
            return false;
        }
        return true;
    }
}
