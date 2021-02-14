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

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;
import java.util.Locale;
import java.util.logging.Level;
import java.util.logging.Logger;

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
    private String comment;

    public RosterItem(String employeeName, String date, String dutyStart, String dutyEnd, String breakStart, String breakEnd) {
        this.employeeName = employeeName;
        this.dateString = date;
        try {
            Date dateParsed = new SimpleDateFormat("EE dd.MM.", Locale.ENGLISH).parse(this.dateString);
            Calendar calendar = Calendar.getInstance();
            calendar.setTime(dateParsed);
            this.date = calendar;
        }
        catch (ParseException exception) {
            Logger.getLogger(RosterItem.class.getName()).log(Level.SEVERE, null, exception);
        }
        this.dutyStart = dutyStart;
        this.dutyEnd = dutyEnd;
        this.breakStart = breakStart;
        this.breakEnd = breakEnd;
        this.comment = "";
    }

    public RosterItem(String employeeName, String date, String dutyStart, String dutyEnd, String breakStart, String breakEnd, String comment) throws Exception {
        throw new Exception("We do not work with comments, yet.");
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

}
