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

import com.google.common.collect.ImmutableMap;
import java.util.Map;

/**
 *
 * @author Mandelkow
 */
public class Absence {

    private final String startDateString;
    private final String endDateString;
    private final int reasonId;
    private final String reasonString;
    private final String commentString;
    private final String durationString;
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

    public Absence(int employeeKey, String startDateString, String endDateString, int reasonId, String commentString, String durationString, String approvalString) {
        this.employeeKey = employeeKey;
        this.startDateString = startDateString;
        this.endDateString = endDateString;
        this.commentString = commentString;
        this.durationString = durationString;
        this.approvalString = approvalString;
        this.reasonId = reasonId;
        this.reasonString = absenceReasonsMap.get(this.reasonId);
    }

    public int getEmployeeKey() {
        return employeeKey;
    }

    public String getStartDateString() {
        return startDateString;
    }

    public String getEndDateString() {
        return endDateString;
    }

    public String getReasonString() {
        return reasonString;
    }

    public int getReasonId() {
        return reasonId;
    }

    public String getCommentString() {
        return commentString;
    }

    public String getDurationString() {
        return durationString;
    }

    public String getapprovalStringString() {
        return approvalString;
    }
}
