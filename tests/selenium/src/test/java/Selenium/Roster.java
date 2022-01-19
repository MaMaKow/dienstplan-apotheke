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
import com.google.gson.Gson;
import com.google.gson.GsonBuilder;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;
import java.io.IOException;
import java.io.Reader;
import java.io.Writer;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.time.LocalDate;
import java.time.Month;
import java.util.HashMap;
import java.util.Map;
import java.util.Set;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 *
 * @author Mandelkow
 */
public class Roster {

    private HashMap<Integer, RosterItem> listOfRosterItems; //Diese sind die Items in einem Tag.
    private HashMap<LocalDate, HashMap> listOfRosterDays;

    public Roster() {
        readRosterFromFile();
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

    public RosterItem getRosterItemByEmployeeId(LocalDate localDate, int employeeId) {
        if (!listOfRosterDays.containsKey(localDate)) {
            return null;
        }
        listOfRosterItems = listOfRosterDays.get(localDate);
        for (RosterItem rosterItem : listOfRosterItems.values()) {
            if (rosterItem.getEmployeeId() == employeeId) {
                return rosterItem;

            }
        }
        return null;
    }

    /*
    public static void main(String args[]) {
        Roster roster = new Roster();
        //roster.writeRosterToFile();
    }
     */
    //private Roster readRosterFromFile(Date dateFrom, Date dateUntil) {
    //private HashMap<LocalDate, HashMap> readRosterFromFile() {
    private void readRosterFromFile() {

        Reader reader = null;
        try {
            RosterItem rosterItem;
            // create a reader
            reader = Files.newBufferedReader(Paths.get("roster.json"));
            // convert JSON string to Roster object
            String rosterJson;
            rosterJson = Files.readString(Paths.get("roster.json"));
            JsonElement jsonFoo = (new JsonParser()).parse(rosterJson);
            JsonObject jsonObject = jsonFoo.getAsJsonObject();
            Set<Map.Entry<String, JsonElement>> jsonEntrySet = jsonObject.entrySet();

            listOfRosterDays = new HashMap<>();
            for (Map.Entry<String, JsonElement> jsonDayEntry : jsonEntrySet) {
                String dateString = jsonDayEntry.getKey();
                JsonElement jsonRosterDay = jsonDayEntry.getValue();
                LocalDate localDate = LocalDate.parse(dateString);
                Set<Map.Entry<String, JsonElement>> jsonDayRosterEntrySet = jsonRosterDay.getAsJsonObject().entrySet();
                listOfRosterItems = new HashMap<>();
                for (Map.Entry<String, JsonElement> jsonDayRosterEntry : jsonDayRosterEntrySet) {
                    int rowNumber = Integer.valueOf(jsonDayRosterEntry.getKey());
                    JsonElement entryValue = jsonDayRosterEntry.getValue();
                    String dutyStart = entryValue.getAsJsonObject().get("dutyStart").getAsString();
                    String dutyEnd = entryValue.getAsJsonObject().get("dutyEnd").getAsString();
                    String breakStart = entryValue.getAsJsonObject().get("breakStart").getAsString();
                    String breakEnd = entryValue.getAsJsonObject().get("breakEnd").getAsString();
                    int employeeId = entryValue.getAsJsonObject().get("employeeId").getAsInt();
                    int branchId = entryValue.getAsJsonObject().get("branchId").getAsInt();
                    String comment = null;
                    try {
                        comment = entryValue.getAsJsonObject().get("comment").getAsString();
                    } catch (Exception e) {
                        /**
                         * comment was not set. Nothing to do here.
                         */
                    }
                    rosterItem = new RosterItem(employeeId, localDate, dutyStart, dutyEnd, breakStart, breakEnd, comment, branchId);
                    listOfRosterItems.put(rowNumber, rosterItem);
                }
                listOfRosterDays.put(localDate, listOfRosterItems);
            }

        } catch (IOException ex) {
            Logger.getLogger(Workforce.class.getName()).log(Level.SEVERE, null, ex);
        } finally {
            try {
                reader.close();
            } catch (IOException ex) {
                Logger.getLogger(Workforce.class.getName()).log(Level.SEVERE, null, ex);
            }
        }
        //return roster;
    }

    private void writeRosterToFile() {
        LocalDate localDate;
        /**
         * Fill one day into the roster:
         */
        listOfRosterItems.clear();
        localDate = LocalDate.of(2020, Month.JULY, 1);
        listOfRosterItems.put(0, new RosterItem(4, localDate, "09:30", "18:00", "13:00", "13:30", null, 1));
        listOfRosterItems.put(1, new RosterItem(2, localDate, "08:00", "16:30", "12:00", "12:30", null, 1));
        listOfRosterItems.put(2, new RosterItem(5, localDate, "08:00", "16:30", "11:30", "12:00", "Dies ist ein Kommentar", 1));
        listOfRosterItems.put(3, new RosterItem(3, localDate, "09:00", "18:00", "12:30", "13:00", null, 1));
        listOfRosterDays.put(localDate, listOfRosterItems);
        /**
         * Add another day:
         */
        listOfRosterItems.clear();
        localDate = LocalDate.of(2020, Month.JULY, 2);
        listOfRosterItems.put(0, new RosterItem(5, localDate, "09:30", "18:00", "13:00", "13:30", null, 1));
        listOfRosterItems.put(1, new RosterItem(3, localDate, "08:00", "16:30", "12:00", "12:30", null, 1));
        listOfRosterItems.put(2, new RosterItem(4, localDate, "08:00", "16:30", "11:30", "12:00", null, 1));
        listOfRosterItems.put(3, new RosterItem(2, localDate, "09:00", "18:00", "12:30", "13:00", null, 1));
        listOfRosterDays.put(localDate, listOfRosterItems);
        /**
         * Add another day:
         */
        listOfRosterItems.clear();
        localDate = LocalDate.of(2020, Month.JULY, 3);
        listOfRosterItems.put(0, new RosterItem(4, localDate, "09:30", "18:00", "13:00", "13:30", null, 1));
        listOfRosterItems.put(1, new RosterItem(3, localDate, "08:00", "16:30", "12:00", "12:30", null, 1));
        listOfRosterItems.put(2, new RosterItem(5, localDate, "08:00", "16:30", "11:30", "12:00", null, 1));
        listOfRosterItems.put(3, new RosterItem(2, localDate, "09:00", "18:00", "12:30", "13:00", null, 1));
        listOfRosterDays.put(localDate, listOfRosterItems);
        /**
         * Add another day in 2021:
         */
        listOfRosterItems.clear();
        localDate = LocalDate.of(2021, Month.JANUARY, 4);
        listOfRosterItems.put(0, new RosterItem(4, localDate, "09:30", "18:00", "13:00", "13:30", null, 1));
        listOfRosterItems.put(1, new RosterItem(3, localDate, "08:00", "16:30", "12:00", "12:30", null, 1));
        listOfRosterItems.put(2, new RosterItem(5, localDate, "08:00", "16:30", "11:30", "12:00", null, 1));
        listOfRosterItems.put(3, new RosterItem(2, localDate, "09:00", "18:00", "12:30", "13:00", null, 1));
        listOfRosterDays.put(localDate, listOfRosterItems);
        /**
         * Add another day in 2019:
         */
        listOfRosterItems.clear();
        localDate = LocalDate.of(2019, Month.DECEMBER, 30);
        listOfRosterItems.put(0, new RosterItem(4, localDate, "09:30", "18:00", "13:00", "13:30", null, 1));
        listOfRosterItems.put(1, new RosterItem(3, localDate, "08:00", "16:30", "12:00", "12:30", null, 1));
        listOfRosterItems.put(2, new RosterItem(5, localDate, "08:00", "16:30", "11:30", "12:00", null, 1));
        listOfRosterItems.put(3, new RosterItem(2, localDate, "09:00", "18:00", "12:30", "13:00", null, 1));
        listOfRosterDays.put(localDate, listOfRosterItems);

        /**
         * Write to JSON file
         */
        Writer writer = null;
        try {
            Gson gson = new GsonBuilder().setPrettyPrinting().create();
            // create a writer:
            writer = Files.newBufferedWriter(Paths.get("roster.json"));
            gson.toJson(listOfRosterDays, writer);
        } catch (IOException ex) {
            Logger.getLogger(Workforce.class
                    .getName()).log(Level.SEVERE, null, ex);
        } finally {
            try {
                writer.close();

            } catch (IOException ex) {
                Logger.getLogger(Workforce.class
                        .getName()).log(Level.SEVERE, null, ex);
            }
        }
    }

}
