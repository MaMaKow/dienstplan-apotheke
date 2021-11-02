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

import java.util.Calendar;

/**
 *
 * @author Mandelkow
 */
public class RosterItem {

    //private int employeeId;
    private String employeeName;
    private String dateString;
    private Calendar date;
    private String dutyStart;
    private String dutyEnd;
    private String breakStart;
    private String breakEnd;
    //private String branchName;
    private int branchId;
    private String comment;

    public RosterItem(String employeeName, Calendar calendar, String dutyStart, String dutyEnd, String breakStart, String breakEnd) {
        this.employeeName = employeeName;
        this.date = calendar;
        this.dutyStart = dutyStart;
        this.dutyEnd = dutyEnd;
        this.breakStart = breakStart;
        this.breakEnd = breakEnd;
        this.comment = "";
    }

    public RosterItem(String employeeName, Calendar calendar, String dutyStart, String dutyEnd, String breakStart, String breakEnd, int branchId) {
        this.employeeName = employeeName;
        this.date = calendar;
        this.dutyStart = dutyStart;
        this.dutyEnd = dutyEnd;
        this.breakStart = breakStart;
        this.breakEnd = breakEnd;
        //this.branchName = branchName;
        this.branchId = branchId;
        this.comment = "";

    }

    public RosterItem(String employeeName, Calendar calendar, String dutyStart, String dutyEnd, String breakStart, String breakEnd, String branchString, String comment) {
        this.employeeName = employeeName;
        this.date = calendar;
        this.dutyStart = dutyStart;
        this.dutyEnd = dutyEnd;
        this.breakStart = breakStart;
        this.breakEnd = breakEnd;
        this.branchName = branchName;
        this.comment = comment;
    }

    public String getEmployeeName() {
        return this.employeeName;
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
        return this.branchId;
    }

    public String getComment() throws Exception {
        return this.comment;
    }

}
