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
import com.google.gson.reflect.TypeToken;
import java.io.IOException;
import java.io.Reader;
import java.io.Writer;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 *
 * @author Mandelkow
 */
public class NetworkOfBranchOffices {

    Map<Integer, Branch> listOfBranches;

    public NetworkOfBranchOffices() {
        listOfBranches = readFromFile();
    }

    public Map<Integer, Branch> getListOfBranches() {
        return listOfBranches;
    }

    public Branch getBranchById(int brachId) {
        return listOfBranches.get(brachId);
    }

    public Branch getBranchByName(String name) {
        Branch branchObject = null;
        for (Iterator iterator = listOfBranches.values().iterator(); iterator.hasNext();) {
            branchObject = (Branch) iterator.next();
            if (branchObject.getBranchName().equals(name)) {
                return branchObject;
            }
        }
        return branchObject;
    }

    private Map<Integer, Branch> readFromFile() {
        listOfBranches = new HashMap<>();
        Reader reader = null;
        try {
            // create a reader
            reader = Files.newBufferedReader(Paths.get("networkOfBranchOffices.json"));
            // convert JSON string to Branch object
            ArrayList<Branch> branches = new Gson().fromJson(reader, new TypeToken<ArrayList<Branch>>() {
            }.getType());
            branches.forEach(branch -> {
                listOfBranches.put(branch.getBranchId(), branch);
            });
        } catch (IOException ex) {
            Logger.getLogger(Workforce.class.getName()).log(Level.SEVERE, null, ex);
        } finally {
            try {
                reader.close();
            } catch (IOException ex) {
                Logger.getLogger(Workforce.class.getName()).log(Level.SEVERE, null, ex);
            }
        }
        return listOfBranches;

    }

    private static void writeToFile() {
        HashMap<Integer, String[]> openingTimes = new HashMap<>();
        String[] openingTimeWeekdays = {"08:00", "18:00"};
        String[] openingTimeSaturday = {"10:00", "16:00"};
        openingTimes.put(1, openingTimeWeekdays);
        openingTimes.put(2, openingTimeWeekdays);
        openingTimes.put(3, openingTimeWeekdays);
        openingTimes.put(4, openingTimeWeekdays);
        openingTimes.put(5, openingTimeWeekdays);
        openingTimes.put(6, openingTimeSaturday);
        Branch hauptapotheke = new Branch(1, 1, "Hauptapotheke am großen Platz", "Hauptapotheke", "Hauptplatz 4\n12345 Berlin", "Zeidler", openingTimes);
        openingTimes.remove(6);
        Branch filiale = new Branch(2, 2, "Filiale in der Nebenstraße", "Filiale", "Nebenstraße 5\n12345 Berlin", "Porsch", openingTimes);
        Branch außendienst = new Branch(99, 99, "Unterwegs im Außendienst", "Außendienst", "Überall\nim Umkreis", "Zeidler", new HashMap<>());
        ArrayList<Branch> listOfBranches = new ArrayList<>();
        listOfBranches.add(hauptapotheke);
        listOfBranches.add(filiale);
        listOfBranches.add(außendienst);

        /**
         * Write to JSON file
         */
        Writer writer = null;
        try {
            Gson gson = new GsonBuilder().setPrettyPrinting().create();
            // create a writer:
            writer = Files.newBufferedWriter(Paths.get("networkOfBranchOffices.json"));
            gson.toJson(listOfBranches, writer);
        } catch (IOException ex) {
            Logger.getLogger(Workforce.class.getName()).log(Level.SEVERE, null, ex);
        } finally {
            try {
                writer.close();
            } catch (IOException ex) {
                Logger.getLogger(Workforce.class.getName()).log(Level.SEVERE, null, ex);
            }
        }

    }

}
