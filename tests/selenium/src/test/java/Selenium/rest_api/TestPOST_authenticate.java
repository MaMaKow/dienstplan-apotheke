/*
 * Copyright (C) 2023 Mandelkow
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

import Selenium.PropertyFile;
import Selenium.TestPage;
import java.io.IOException;
import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import org.testng.Assert;
import org.testng.annotations.Test;

/**
 * @todo <p lang=de>Die Klasse muss noch geteilt werden. Die Seitenspezifischen
 * Teile wandern in die Klasse POST_authenticatePage.java verschoben.
 * Die Teile, die von anderen API Seiten geteilt werden, wandern in eine TestApiPage.java Klasse.
 * </p>
 * @author Mandelkow
 */
public class TestPOST_authenticate {

    private PropertyFile propertyFile;

    @Test(enabled = true)
    public void testDateNavigation() {
        propertyFile = new PropertyFile();
        try {
            // Replace with your authentication endpoint
            String authenticationEndpoint = propertyFile.getRealTestPageUrl() + "src/php/restful-api/authentication/POST-authenticate.php";

            // Define authentication payload
            String user_name = propertyFile.getRealUsername();
            String user_password = propertyFile.getRealPassword();

            String payload = "{\"user_name\":\"" + user_name + "\",\"user_password\":\"" + user_password + "\"}";

            // Send the POST request
            HttpResponse<String> response = sendPostRequest(authenticationEndpoint, payload);
            String responseBody = response.body();

            // Check if authentication was successful
            if (responseBody.contains("access_token")) {
                System.out.println("Authentication successful!");
            } else {
                System.out.println("Authentication failed!");
            }
            Assert.assertTrue(responseBody.contains("access_token"));

        } catch (IOException | InterruptedException e) {
            e.printStackTrace();
        }

    }

    private static HttpResponse<String> sendPostRequest(String url, String payload) throws IOException, InterruptedException {
        HttpClient client = HttpClient.newHttpClient();
        HttpRequest request = HttpRequest.newBuilder()
                .uri(URI.create(url))
                .header("Content-Type", "application/json")
                .POST(HttpRequest.BodyPublishers.ofString(payload))
                .build();

        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());

        // Handle the response
        return response;
    }
}
