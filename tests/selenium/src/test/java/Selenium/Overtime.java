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
public class Overtime {

    private final Calendar calendar;
    private final float hours;
    private final float balance;
    private final String reason;

    public Overtime(Calendar calendar, float hours, float balance, String reason) {
        this.calendar = calendar;
        this.hours = hours;
        this.balance = balance;
        this.reason = reason;
    }

    public Calendar getCalendar() {
        return calendar;
    }

    public float getHours() {
        return hours;
    }

    public float getBalance() {
        return balance;
    }

    public String getReason() {
        return reason;
    }
}
