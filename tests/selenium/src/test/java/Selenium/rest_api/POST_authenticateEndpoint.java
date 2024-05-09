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

import com.google.gson.Gson;
import com.google.gson.JsonObject;
import java.io.IOException;
import java.net.http.HttpResponse;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 *
 * @author Mandelkow
 */
public class POST_authenticateEndpoint {

    private static boolean isAuthenticated = false;
    private static String accessToken = null;

    public POST_authenticateEndpoint(String userName, String userPassphrase, String testPageUrl) throws InterruptedException, IOException {
        String authenticationEndpoint = testPageUrl + "src/php/restful-api/authentication/POST-authenticate.php";
        String payload = "{\"userName\":\"" + userName + "\",\"userPassphrase\":\"" + userPassphrase + "\"}";

        // Send the POST request
        HttpResponse<String> response = null;
        try {
            response = ApiHandler.sendPostRequest(authenticationEndpoint, payload);
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
        }
        String responseBody = response.body();

        // Check if authentication was successful
        isAuthenticated = responseBody.contains("accessToken");
        if (isAuthenticated) {
            accessToken = getTokenFromJsonResponse(responseBody);
        } else {
            System.err.println("Authentication failed!");
        }
    }

    private String getTokenFromJsonResponse(String response) {
        // Create a Gson object
        Gson gson = new Gson();

        // Parse the JSON string into a JsonObject
        JsonObject jsonObject = gson.fromJson(response, JsonObject.class);

        // Get the accessToken value from the JsonObject
        String accessToken = jsonObject.get("accessToken").getAsString();
        return accessToken;
    }

    public static boolean isAuthenticated() {
        return isAuthenticated;
    }

    public static String getAccessToken() throws Exception {
        if (null == accessToken) {
            throw new Exception("The access token is null!");
        }
        return accessToken;
    }
}
