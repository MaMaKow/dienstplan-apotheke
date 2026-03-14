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

import Selenium.Absence;
import Selenium.Employee;
import Selenium.Utilities.LogCollector;
import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;
import java.io.IOException;
import java.net.http.HttpResponse;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

/**
 *
 * @author Mandelkow
 */
public class GET_absenceEndpoint {

    private List<Absence> absences;

    /**
     * Private constructor - use static factory methods instead
     */
    private GET_absenceEndpoint() {
        this.absences = new ArrayList<>();
    }

    /**
     * Get all absences in current year
     *
     * @param testPageUrl Base URL of the application
     */
    public static GET_absenceEndpoint forCurrentYear(String testPageUrl) throws InterruptedException, IOException, Exception {
        GET_absenceEndpoint endpoint = new GET_absenceEndpoint();
        String absenceEndpoint = testPageUrl + "src/php/restful-api/absences";
        endpoint.sendRequest(absenceEndpoint);
        return endpoint;
    }

    /**
     * Get all absences in specific year
     *
     * @param testPageUrl Base URL of the application
     * @param year Year to fetch absences for
     */
    public static GET_absenceEndpoint forYear(String testPageUrl, int year) throws InterruptedException, IOException, Exception {
        GET_absenceEndpoint endpoint = new GET_absenceEndpoint();
        String absenceEndpoint = testPageUrl + "src/php/restful-api/absences/" + year;
        endpoint.sendRequest(absenceEndpoint);
        return endpoint;
    }

    /**
     * Get absences for specific employee in current year
     *
     * @param testPageUrl Base URL of the application
     * @param employee Employee object
     */
    public static GET_absenceEndpoint forEmployee(String testPageUrl, Employee employee) throws InterruptedException, IOException, Exception {
        GET_absenceEndpoint endpoint = new GET_absenceEndpoint();
        int employeeKey;
        try {
            employeeKey = employee.getEmployeeKey();

        } catch (NullPointerException e) {
            employeeKey = 9999; // Invalid employee, null
        }
        String absenceEndpoint = testPageUrl + "src/php/restful-api/employees/" + employeeKey + "/absences";
        endpoint.sendRequest(absenceEndpoint);
        return endpoint;
    }

    /**
     * Get absences for specific employee in specific year
     *
     * @param testPageUrl Base URL of the application
     * @param employee Employee object
     * @param year Year to fetch absences for
     */
    public static GET_absenceEndpoint forEmployeeAndYear(String testPageUrl, Employee employee, int year) throws InterruptedException, IOException, Exception {
        GET_absenceEndpoint endpoint = new GET_absenceEndpoint();
        int employeeKey;
        try {
            employeeKey = employee.getEmployeeKey();

        } catch (NullPointerException e) {
            employeeKey = 9999; // Invalid employee, null
        }
        String absenceEndpoint = testPageUrl + "src/php/restful-api/employees/" + employeeKey + "/absences/" + year;
        endpoint.sendRequest(absenceEndpoint);
        return endpoint;
    }

    private void sendRequest(String endpoint) throws InterruptedException, IOException, Exception {
        // Send the GET request
        HttpResponse<String> response = null;
        try {
            response = ApiHandler.sendAuthorizedGetRequest(endpoint, new HashMap<>());
        } catch (IOException | InterruptedException exception) {
            LogCollector.error(exception.getMessage());
            exception.printStackTrace();
            System.out.println(exception.getMessage());
            throw exception;
        } catch (Exception exception) {
            LogCollector.error(exception.getMessage());
            exception.printStackTrace();
            System.out.println(exception.getMessage());
            throw exception;
        }

        String responseBody = response.body();
        LogCollector.debug("responseBody:");
        LogCollector.debug(responseBody);

        absences = getAbsenceDataFromJsonResponse(responseBody);
    }

    public List<Absence> getAbsences() {
        return absences;
    }

    /**
     * Parse absence response from JSON
     *
     * @param response JSON response string
     * @return List of Absence objects
     */
    private List<Absence> getAbsenceDataFromJsonResponse(String response) {
        List<Absence> receivedAbsences = new ArrayList<>();
        JsonElement jsonElement = JsonParser.parseString(response);

        if (jsonElement.isJsonArray()) {
            // Handle array response
            JsonArray jsonArray = jsonElement.getAsJsonArray();
            for (JsonElement absenceElement : jsonArray) {
                if (absenceElement.isJsonObject()) {
                    JsonObject jsonObject = absenceElement.getAsJsonObject();

                    // Check if it's an error response
                    if (jsonObject.has("error")) {
                        String errorMessage = jsonObject.get("error").getAsString();
                        LogCollector.error("API Error: " + errorMessage);
                        throw new RuntimeException("API Error: " + errorMessage);
                    }

                    Absence absence = createAbsenceFromJsonObject(jsonObject);
                    if (absence != null) {
                        receivedAbsences.add(absence);
                    }
                }
            }
        } else if (jsonElement.isJsonObject()) {
            // Handle single object response or error
            JsonObject jsonObject = jsonElement.getAsJsonObject();

            // Check if it's an error response
            if (jsonObject.has("error")) {
                String errorMessage = jsonObject.get("error").getAsString();
                LogCollector.error("API Error: " + errorMessage);
                throw new RuntimeException("API Error: " + errorMessage);
            }

            Absence absence = createAbsenceFromJsonObject(jsonObject);
            if (absence != null) {
                receivedAbsences.add(absence);
            }
        }

        return receivedAbsences;
    }

    private Absence createAbsenceFromJsonObject(JsonObject jsonObject) {
        try {
            // Extract data from JSON object - matching the actual Absence constructor
            int employeeKey = jsonObject.get("employeeKey").getAsInt();

            // Parse dates
            String startDateString = jsonObject.get("start").getAsString();
            String endDateString = jsonObject.get("end").getAsString();
            LocalDate startDate = LocalDateTime.parse(startDateString, DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss")).toLocalDate();
            LocalDate endDate = LocalDateTime.parse(endDateString, DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss")).toLocalDate();

            // Get reason ID (not reason string)
            int reasonId = jsonObject.get("reasonId").getAsInt();

            // Get optional comment
            String commentString = jsonObject.has("comment") && !jsonObject.get("comment").isJsonNull()
                    ? jsonObject.get("comment").getAsString() : "";

            // Get approval status as string
            String approvalString = jsonObject.has("approval") && !jsonObject.get("approval").isJsonNull()
                    ? jsonObject.get("approval").getAsString() : "";

            // Create Absence using the correct constructor
            return new Absence(employeeKey, startDate, endDate, reasonId, commentString, approvalString);

        } catch (Exception e) {
            LogCollector.error("Error parsing absence JSON: " + e.getMessage());
            LogCollector.debug("Problematic JSON object: " + jsonObject.toString());
            return null;
        }
    }
}
