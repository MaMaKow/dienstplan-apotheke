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
package Selenium.rest_api;

import Selenium.RosterItem;
import Selenium.driver.Wrapper;
import com.google.gson.Gson;
import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;
import java.io.IOException;
import java.net.http.HttpResponse;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.HashMap;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 *
 * @author Mandelkow
 */
public class GET_rosterEndpoint {

    private HashMap<LocalDate, HashMap> foundRoster;

    public GET_rosterEndpoint(String testPageUrl) throws InterruptedException, IOException, Exception {
        String rosterEndpoint = testPageUrl + "src/php/restful-api/roster/GET-roster.php";
        String payload = "{}";

        // Send the POST request
        HttpResponse<String> response = null;
        try {
            response = ApiHandler.sendAuthorizedGetRequest(rosterEndpoint);
        } catch (IOException exception) {
            Logger.getLogger(POST_authenticateEndpoint.class.getName()).log(Level.SEVERE, null, exception);
            exception.printStackTrace();
            System.out.println(exception.getMessage());
            throw exception;
        } catch (InterruptedException exception) {
            Logger.getLogger(POST_authenticateEndpoint.class.getName()).log(Level.SEVERE, null, exception);
            exception.printStackTrace();
            System.out.println(exception.getMessage());
            throw exception;
        } catch (Exception exception) {
            Logger.getLogger(GET_rosterEndpoint.class.getName()).log(Level.SEVERE, null, exception);
            exception.printStackTrace();
            System.out.println(exception.getMessage());
            throw exception;
        }
        String responseBody = response.body();

        /**
         * Check if roster was successfully fetched
         */
        boolean isRosterData = responseBody.contains("date") && responseBody.contains("roster");
        if (isRosterData) {
            foundRoster = getRosterDataFromJsonResponse(responseBody);
        }
    }

    public HashMap<LocalDate, HashMap> getFoundRosterHashMap() {
        return foundRoster;
    }

    /**
     * @todo The data should be interpreted as roster objects!
     * @param response
     * @return roster data
     */
    private HashMap<LocalDate, HashMap> getRosterDataFromJsonResponse(String response) {
        //HashMap<Integer, RosterItem> listOfRosterItems = new HashMap<>(); //Diese sind die Items in einem Tag.
        HashMap<LocalDate, HashMap> listOfRosterDays = new HashMap<>();

        /**
         * Create a Gson object
         */
        Gson gson = new Gson();
        JsonElement jsonElement = JsonParser.parseString(response);

        /**
         * Check if the root element is an array
         */
        if (jsonElement.isJsonArray()) {
            /**
             * Iterate over each element in the array
             */
            JsonArray jsonArray = jsonElement.getAsJsonArray();
            for (JsonElement dayJsonElement : jsonArray) {
                JsonObject jsonObject = dayJsonElement.getAsJsonObject();

                /**
                 * Extract date from the current object
                 */
                String date = jsonObject.get("date").getAsString();
                LocalDate localDate = LocalDate.parse(date, Wrapper.DATE_TIME_FORMATTER_YEAR_MONTH_DAY);
                HashMap<Integer, RosterItem> listOfRosterItems = new HashMap<>();
                int rowNumber = 0;
                /**
                 * Extract roster array from the current object
                 */
                if (!jsonObject.has("roster")) {
                    System.err.println("no data, continue");
                    continue;
                }
                if (jsonObject.get("roster").isJsonNull()) {
                    System.err.println("no data, continue");
                    continue;
                }
                JsonArray rosterArray = jsonObject.getAsJsonArray("roster");
                for (JsonElement rosterElement : rosterArray) {
                    JsonObject rosterObject = rosterElement.getAsJsonObject();

                    /**
                     * Extract employee details from the roster object
                     */
                    int employeeKey = rosterObject.get("employee_key").getAsInt();
                    String dateString = rosterObject.get("date").getAsString();
                    LocalDate dateInRosterItem = LocalDate.parse(dateString, Wrapper.DATE_TIME_FORMATTER_YEAR_MONTH_DAY);
                    int branchId = rosterObject.get("branch_id").getAsInt();
                    String dutyStart = rosterObject.get("duty_start").getAsString();
                    String dutyEnd = rosterObject.get("duty_end").getAsString();
                    String breakStart = rosterObject.get("break_start").getAsString();
                    String breakEnd = rosterObject.get("break_end").getAsString();
                    String comment = null;
                    if (rosterObject.has("comment") && !rosterObject.get("comment").isJsonNull()) {
                        comment = rosterObject.get("comment").getAsString();
                    }
                    //double workingHours = rosterObject.get("working_hours").getAsDouble();

                    /**
                     * Process employee details as needed
                     */
                    RosterItem rosterItem = new RosterItem(employeeKey, dateInRosterItem, dutyStart, dutyEnd, breakStart, breakEnd, comment, branchId);
                    listOfRosterItems.put(rowNumber, rosterItem);
                    rowNumber++;
                }
                listOfRosterDays.put(localDate, listOfRosterItems);
            }
        }
        return listOfRosterDays;
    }
}
