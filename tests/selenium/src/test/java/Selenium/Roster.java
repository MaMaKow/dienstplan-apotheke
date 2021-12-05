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

    private HashMap<Integer, RosterItem> listOfRosterItems = new HashMap(); //Diese sind die Items in einem Tag.
    private HashMap<LocalDate, HashMap> listOfRosterDays = new HashMap();

    public Roster() {
        readRosterFromFile();

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

    //public static void main(String args[]) {
    public static void main(String args[]) {
        Roster roster = new Roster();
        roster.writeRosterToFile();
    }

    //private Roster readRosterFromFile(Date dateFrom, Date dateUntil) {
    //private HashMap<LocalDate, HashMap> readRosterFromFile() {
    private void readRosterFromFile() {

        Reader reader = null;
        try {
            Gson gson = new Gson();
            // create a reader
            reader = Files.newBufferedReader(Paths.get("roster.json"));
            // convert JSON string to Roster object
            //HashMap<LocalDate, HashMap<Integer, RosterItem>> rosterDays = new Gson().fromJson(reader, new TypeToken<HashMap<LocalDate, HashMap<Integer, RosterItem>>>() {
            String rosterJson;
            rosterJson = Files.readString(Paths.get("roster.json"));
            //rosterJson = new String(Files.readAllBytes(Paths.get("roster.json")));
            JsonElement jsonFoo = (new JsonParser()).parse(rosterJson);
            System.out.println("printing jsonFoo:");
            System.out.println(jsonFoo);
            System.out.println("after printing jsonFoo");
            JsonObject jsonObject = jsonFoo.getAsJsonObject();
            System.out.println("after setting jsonObject");
            Set<Map.Entry<String, JsonElement>> jsonEntrySet = jsonObject.entrySet();

            //JsonObject jsonObject = jsonArray.getAsJsonObject();
            System.out.println("Before for loop");
            int dayNumber = 0;

            for (Map.Entry<String, JsonElement> jsonDayEntry : jsonEntrySet) {
                System.out.println("dayNumber:");
                System.out.println(dayNumber++);
                System.out.println(jsonDayEntry);
                String dateString = jsonDayEntry.getKey();
                JsonElement jsonRosterDay = jsonDayEntry.getValue();
                listOfRosterItems.clear();
                LocalDate localDate = LocalDate.parse(dateString);

                Set<Map.Entry<String, JsonElement>> jsonDayRosterEntrySet = jsonRosterDay.getAsJsonObject().entrySet();
                for (Map.Entry<String, JsonElement> jsonDayRosterEntry : jsonDayRosterEntrySet) {
                    int rowNumber = Integer.valueOf(jsonDayRosterEntry.getKey());
                    JsonElement entryValue = jsonDayRosterEntry.getValue();
                    RosterItem rosterItem = gson.fromJson(entryValue, RosterItem.class);
                    System.out.println("EmployeeName:");
                    System.out.println(rosterItem.getEmployeeName());
                    listOfRosterItems.put(rowNumber, rosterItem);
                }
                listOfRosterDays.put(localDate, listOfRosterItems);

            }

            System.out.println("before exit");
            //System.exit(1);
            //JsonArray array = JsonParser.parse(rosterJson).getAsJsonArray();
            /*
            Roster rosterDays = new Gson().fromJson(reader, new TypeToken<Roster>() {
            }.getType());
             */
            int rowNumber = 0;
            LocalDate localDate = null;
            /*            for (HashMap<Integer, RosterItem> rosterDay : rosterDays.values()) {
                for (RosterItem rosterItem : rosterDay.values()) {
                    listOfRosterItems.put(rowNumber++, rosterItem);
                    localDate = rosterItem.getLocalDate();
                }
                roster.put(localDate, listOfRosterItems);
            }
             */
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
