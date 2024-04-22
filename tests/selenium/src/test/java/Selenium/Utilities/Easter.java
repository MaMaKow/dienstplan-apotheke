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

import java.time.LocalDate;

/**
 *
 * @author Mandelkow
 */
public class Easter {

    /**
     *
     * @param year
     * @return
     * @see https://www.baeldung.com/java-determine-easter-date-specific-year
     */
    public static LocalDate computeEasterDateWithConwayAlgorithm(int year) {
        int secularYear = year / 100;
        int vintage = year % 100;
        int leapYear = vintage / 4;
        int p = secularYear % 4;
        int secularPivotalDay = (9 - 2 * p) % 7;
        int currentYearsPivotalDay = (secularPivotalDay + vintage + leapYear) % 7;
        int g = year % 19;
        int goldenNumber = g + 1;
        int metemptosis = secularYear / 4;
        int proemptosis = 8 * (secularYear + 11) / 25;
        int secularCorrection = -secularYear + metemptosis + proemptosis;
        int paschalFullMoonsDay = (11 * goldenNumber + secularCorrection) % 30;
        paschalFullMoonsDay = (paschalFullMoonsDay + 30) % 30;
        int h = (551 - 19 * paschalFullMoonsDay + goldenNumber) / 544;
        int e = (50 - paschalFullMoonsDay - h) % 7;
        int dayOfTheWeek = (e + currentYearsPivotalDay) % 7;
        int dayInMonth = 57 - paschalFullMoonsDay - dayOfTheWeek - h;

        if (dayInMonth <= 31) {
            return LocalDate.of(year, 3, dayInMonth);
        }
        return LocalDate.of(year, 4, dayInMonth - 31);
    }
}
