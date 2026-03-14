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

import Selenium.Branch;
import Selenium.Utilities.LogCollector;
import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;
import java.io.IOException;
import java.net.http.HttpResponse;
import java.util.HashMap;

/**
 *
 * @author Mandelkow
 */
public class GET_branchEndpoint {

    private final Branch branch;

    public GET_branchEndpoint(String testPageUrl, int branchId) throws InterruptedException, IOException, Exception {
        String branchEndpoint = testPageUrl + "src/php/restful-api/branches/" + branchId;
        HashMap<String, String> listOfParameters = new HashMap<>();

        //listOfParameters.put("branchId", String.valueOf(branchId));
        // Send the POST request
        HttpResponse<String> response = null;
        try {
            response = ApiHandler.sendAuthorizedGetRequest(branchEndpoint, listOfParameters);
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
        /**
         * Check if branch was successfully fetched
         */
        LogCollector.debug("responseBody:");
        LogCollector.debug(responseBody);
        branch = getBranchDataFromJsonResponse(responseBody);
    }

    public Branch getBranch() {
        return branch;
    }

    /**
     * @todo The data should be interpreted as roster objects!
     * @param response
     * @return roster data
     */
    private Branch getBranchDataFromJsonResponse(String response) {
        JsonElement jsonElement = JsonParser.parseString(response);
        Branch receivedBranch = null;

        if (jsonElement.isJsonArray()) {
            // Handle array response (if multiple branches are returned)
            JsonArray jsonArray = jsonElement.getAsJsonArray();
            for (JsonElement dayJsonElement : jsonArray) {
                JsonObject jsonObject = dayJsonElement.getAsJsonObject();
                receivedBranch = createBranchFromJsonObject(jsonObject);
                break; // Take the first branch if multiple are returned
            }
        } else if (jsonElement.isJsonObject()) {
            // Handle single object response
            JsonObject jsonObject = jsonElement.getAsJsonObject();
            receivedBranch = createBranchFromJsonObject(jsonObject);
        }

        return receivedBranch;
    }

    private Branch createBranchFromJsonObject(JsonObject jsonObject) {
        // Extract data from JSON object - note the different field names in the actual response
        Integer branchId = jsonObject.get("branch_id").getAsInt(); // Note: "branch_id" not "branchId"
        Integer branchPepId = jsonObject.get("PEP").getAsInt();
        String branchName = jsonObject.get("name").getAsString();
        String branchShortName = jsonObject.get("short_name").getAsString(); // Note: "short_name" not "shortName"
        String branchAddress = jsonObject.get("address").getAsString(); // Note: "address" not "branchAddress"
        String branchManager = jsonObject.get("manager").getAsString(); // Note: "manager" not "branchManager"

        // Parse opening times
        HashMap<Integer, String[]> openingTimesMap = new HashMap<>();
        if (jsonObject.has("Opening_times")) {
            JsonObject openingTimes = jsonObject.getAsJsonObject("Opening_times");
            for (String day : openingTimes.keySet()) {
                JsonObject dayTimes = openingTimes.getAsJsonObject(day);
                String startTime = dayTimes.get("day_opening_start").isJsonNull()
                        ? null : dayTimes.get("day_opening_start").getAsString();
                String endTime = dayTimes.get("day_opening_end").isJsonNull()
                        ? null : dayTimes.get("day_opening_end").getAsString();

                // Store as String array: [startTime, endTime]
                openingTimesMap.put(Integer.valueOf(day), new String[]{startTime, endTime});
            }
        }

        return new Branch(branchId, branchPepId, branchName, branchShortName,
                branchAddress, branchManager, openingTimesMap);
    }
}
