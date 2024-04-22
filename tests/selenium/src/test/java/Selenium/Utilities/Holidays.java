/*
 * Copyright (C) 2024 Mandelkow
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
package Selenium.Utilities;

import java.time.DayOfWeek;
import java.time.LocalDate;
import java.time.Month;
import java.time.format.DateTimeFormatter;
import java.util.HashMap;
import java.util.Map;

public class Holidays {

    private Map<LocalDate, String> holidays;
    private int yearOnConstruct;

    public Holidays(int year) {
        // Add holidays to the map
        yearOnConstruct = year;
        this.holidays = new HashMap<>();
        holidays.put(LocalDate.of(year, Month.JANUARY, 1), "Neujahr");
        holidays.put(LocalDate.of(year, Month.MAY, 1), "Tag der Arbeit");
        holidays.put(LocalDate.of(year, Month.OCTOBER, 3), "Tag der Deutschen Einheit");
        holidays.put(LocalDate.of(year, Month.OCTOBER, 31), "Reformationstag");
        holidays.put(LocalDate.of(year, Month.DECEMBER, 25), "1. Weihnachtsfeiertag");
        holidays.put(LocalDate.of(year, Month.DECEMBER, 26), "2. Weihnachtsfeiertag");
        holidays.put(LocalDate.of(year, Month.MARCH, 8), "Internationaler Frauentag");
        // Add more holidays as needed
        LocalDate easterDate = Easter.computeEasterDateWithConwayAlgorithm(year);
        holidays.put(easterDate.minusDays(2), "Karfreitag");
        holidays.put(easterDate, "Ostersonntag");
        holidays.put(easterDate.plusDays(1), "Ostermontag");
        holidays.put(easterDate.plusDays(39), "Himmelfahrt");
        holidays.put(easterDate.plusDays(49), "Pfingstsonntag");
        holidays.put(easterDate.plusDays(50), "Pfingstmontag");
    }

    public boolean isHoliday(LocalDate date) throws Exception {
        // Check if the given date is a holiday
        if (yearOnConstruct != date.getYear()) {
            throw new Exception("The year of the date must be: " + yearOnConstruct + " but the date is " + date.format(DateTimeFormatter.ISO_DATE));
        }
        return holidays.containsKey(date);
    }

}
