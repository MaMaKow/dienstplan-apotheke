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

import java.time.DayOfWeek;
import java.time.LocalDate;
import java.time.Month;
import java.time.temporal.TemporalAdjusters;
import org.threeten.extra.YearWeek;
import java.util.HashMap;

/**
 *
 * @author Mandelkow
 */
public class Roster {

    private HashMap<Integer, RosterItem> listOfRosterItems; //Diese sind die Items in einem Tag.
    private HashMap<LocalDate, HashMap> listOfRosterDays;
    private LocalDate firstMondayInJuly;
    private LocalDate secondMondayInJuly;

    public Roster() {
        createTestRoster();
    }

    public HashMap<LocalDate, HashMap> getListOfRosterDays() {
        return listOfRosterDays;
    }

    public RosterItem getRosterItem(LocalDate localDate, int rowNumber) {
        if (!listOfRosterDays.containsKey(localDate)) {
            return null;
        }
        listOfRosterItems = listOfRosterDays.get(localDate);
        if (!listOfRosterItems.containsKey(rowNumber)) {
            return null;
        }
        RosterItem rosterItem = listOfRosterItems.get(rowNumber);
        return rosterItem;
    }

    public RosterItem getRosterItemByEmployeeKey(LocalDate localDate, int employeeKey) {
        if (!listOfRosterDays.containsKey(localDate)) {
            return null;
        }
        listOfRosterItems = listOfRosterDays.get(localDate);
        for (RosterItem rosterItem : listOfRosterItems.values()) {
            if (rosterItem.getEmployeeKey() == employeeKey) {
                return rosterItem;

            }
        }
        return null;
    }

    public HashMap<YearWeek, HashMap> getRosterWeeksByEmployeeKey(int employeeKey) {
        HashMap<LocalDate, HashMap> listOfRosterDaysEmployee = (HashMap<LocalDate, HashMap>) listOfRosterDays.clone();
        HashMap<YearWeek, HashMap> rosterWeeksByEmployeeKey = new HashMap<>();
        int newRosterRowKey = 0;
        for (HashMap<Integer, RosterItem> rosterDay : listOfRosterDaysEmployee.values()) {
            for (Integer rosterRowKey : rosterDay.keySet()) {
                newRosterRowKey++;
                RosterItem rosterItem = rosterDay.get(rosterRowKey);
                YearWeek yearWeek = YearWeek.from(rosterItem.getLocalDate());
                if (employeeKey == rosterItem.getEmployeeKey()) {
                    if (rosterWeeksByEmployeeKey.containsKey(yearWeek)) {
                        /**
                         * <p lang=de>Wenn in dieser Woche bereits ein Eintrag
                         * existiert, fügen wir unsere Werte zu dem Eintrag
                         * hinzu.</p>
                         */
                        rosterWeeksByEmployeeKey.get(yearWeek).put(newRosterRowKey, rosterItem);
                    } else {
                        /**
                         * <p lang=de>Wenn in dieser Woche noch kein Eintrag
                         * existiert, erstellen wir einen neuen.</p>
                         */
                        HashMap<Integer, RosterItem> rosterWeekNew = new HashMap<>();
                        rosterWeekNew.put(rosterRowKey, rosterItem);
                        rosterWeeksByEmployeeKey.put(yearWeek, rosterWeekNew);
                    }
                }
            }
        }
        return rosterWeeksByEmployeeKey;
    }

    private void createTestRoster() {
        LocalDate localDate;
        /**
         * Fill one day into the roster:
         */
        LocalDate today = LocalDate.now();
        int currentYear = today.getYear();
        int nextYear = currentYear + 1;
        int lastYear = currentYear - 1;
        listOfRosterItems = new HashMap<>();
        listOfRosterDays = new HashMap<>();
        firstMondayInJuly = LocalDate.of(currentYear, Month.JULY, 1).with(TemporalAdjusters.nextOrSame(DayOfWeek.MONDAY));
        localDate = firstMondayInJuly;
        listOfRosterItems.put(0, new RosterItem("Albert Kremer", localDate, "09:30", "18:00", "13:00", "13:30", null, 1));
        listOfRosterItems.put(1, new RosterItem("Elisabeth Lehmann", localDate, "08:00", "16:30", "12:00", "12:30", null, 1));
        listOfRosterItems.put(2, new RosterItem("Albert Krüger", localDate, "08:00", "16:30", "11:30", "12:00", "Dies ist ein Kommentar", 1));
        listOfRosterItems.put(3, new RosterItem("Albert Baumann", localDate, "09:00", "18:00", "12:30", "13:00", null, 1));
        listOfRosterDays.put(localDate, listOfRosterItems);

        /**
         * Add another day:
         */
        listOfRosterItems = new HashMap<>();
        localDate = LocalDate.of(currentYear, Month.JULY, 2).with(TemporalAdjusters.nextOrSame(DayOfWeek.TUESDAY));
        listOfRosterItems.put(0, new RosterItem("Albert Krüger", localDate, "09:30", "18:00", "13:00", "13:30", null, 1));
        listOfRosterItems.put(1, new RosterItem("Albert Baumann", localDate, "08:00", "16:30", "12:00", "12:30", null, 1));
        listOfRosterItems.put(2, new RosterItem("Albert Kremer", localDate, "08:00", "16:30", "11:30", "12:00", null, 1));
        listOfRosterItems.put(3, new RosterItem("Elisabeth Lehmann", localDate, "09:00", "18:00", "12:30", "13:00", null, 1));
        listOfRosterDays.put(localDate, listOfRosterItems);
        /**
         * Add another day:
         */
        listOfRosterItems = new HashMap<>();
        localDate = LocalDate.of(currentYear, Month.JULY, 3).with(TemporalAdjusters.nextOrSame(DayOfWeek.WEDNESDAY));
        listOfRosterItems.put(0, new RosterItem("Albert Kremer", localDate, "09:30", "18:00", "13:00", "13:30", null, 1));
        listOfRosterItems.put(1, new RosterItem("Albert Baumann", localDate, "08:00", "16:30", "12:00", "12:30", null, 1));
        listOfRosterItems.put(2, new RosterItem("Albert Krüger", localDate, "08:00", "16:30", "11:30", "12:00", null, 1));
        listOfRosterItems.put(3, new RosterItem("Elisabeth Lehmann", localDate, "09:00", "18:00", "12:30", "13:00", null, 1));
        listOfRosterDays.put(localDate, listOfRosterItems);
        /**
         * Add another day in next year:
         */
        listOfRosterItems = new HashMap<>();
        localDate = LocalDate.of(nextYear, Month.JANUARY, 4).with(TemporalAdjusters.nextOrSame(DayOfWeek.THURSDAY));
        listOfRosterItems.put(0, new RosterItem("Albert Kremer", localDate, "09:30", "18:00", "13:00", "13:30", null, 1));
        listOfRosterItems.put(1, new RosterItem("Albert Baumann", localDate, "08:00", "16:30", "12:00", "12:30", null, 1));
        listOfRosterItems.put(2, new RosterItem("Albert Krüger", localDate, "08:00", "16:30", "11:30", "12:00", null, 1));
        listOfRosterItems.put(3, new RosterItem("Elisabeth Lehmann", localDate, "09:00", "18:00", "12:30", "13:00", null, 1));
        listOfRosterDays.put(localDate, listOfRosterItems);
        /**
         * Add another day in last year:
         */
        listOfRosterItems = new HashMap<>();

        localDate = LocalDate.of(lastYear, Month.DECEMBER, 30).with(TemporalAdjusters.previousOrSame(DayOfWeek.FRIDAY));
        listOfRosterItems.put(0, new RosterItem("Albert Kremer", localDate, "09:30", "18:00", "13:00", "13:30", null, 1));
        listOfRosterItems.put(1, new RosterItem("Albert Baumann", localDate, "08:00", "16:30", "12:00", "12:30", null, 1));
        listOfRosterItems.put(2, new RosterItem("Albert Krüger", localDate, "08:00", "16:30", "11:30", "12:00", null, 1));
        listOfRosterItems.put(3, new RosterItem("Elisabeth Lehmann", localDate, "09:00", "18:00", "12:30", "13:00", null, 1));
        listOfRosterDays.put(localDate, listOfRosterItems);
    }

    public LocalDate getFirstMondayInJuly() {
        return firstMondayInJuly;
    }

    public LocalDate getSecondMondayInJuly() {
        return secondMondayInJuly;
    }
}
