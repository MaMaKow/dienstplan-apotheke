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
package Selenium.rosterpages;

import Selenium.Employee;
import com.google.gson.Gson;
import com.google.gson.GsonBuilder;
import com.google.gson.reflect.TypeToken;
import java.io.IOException;
import java.io.Reader;
import java.io.Writer;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 *
 * @author Mandelkow
 */
public class Workforce {

    public Map readFromFile() {
        Map listOfEmployees;
        listOfEmployees = new HashMap<Integer, Employee>();
        Reader reader = null;
        try {
            // create a reader
            reader = Files.newBufferedReader(Paths.get("workforce.json"));
            // convert JSON string to Employee object
            List<Employee> employees = new Gson().fromJson(reader, new TypeToken<List<Employee>>() {
            }.getType());
            employees.forEach(employee -> {
                listOfEmployees.put(employee.getEmployeeId(), employee);
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
        return listOfEmployees;
    }

    public void writeToFile(List<Employee> listOfEmployees) {
        Writer writer = null;
        try {
            Gson gson = new GsonBuilder().setPrettyPrinting().create();
            // create a writer
            writer = Files.newBufferedWriter(Paths.get("workforce.json"));
            // convert book object to JSON file
            gson.toJson(listOfEmployees, writer);
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
