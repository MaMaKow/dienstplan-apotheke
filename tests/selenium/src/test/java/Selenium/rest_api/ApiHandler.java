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

import java.io.IOException;
import java.io.UnsupportedEncodingException;
import java.net.URI;
import java.net.URLEncoder;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.nio.charset.StandardCharsets;
import java.util.HashMap;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 *
 * @author Mandelkow
 */
public class ApiHandler {

    public static HttpResponse<String> sendPostRequest(String url, String payload) throws IOException, InterruptedException {
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

    public static HttpResponse<String> sendAuthorizedGetRequest(String url, HashMap<String, String> listOfParameters) throws IOException, InterruptedException, Exception {
        String accessToken = null;
        try {
            accessToken = POST_authenticateEndpoint.getAccessToken();
        } catch (Exception exception) {
            Logger.getLogger(ApiHandler.class.getName()).log(Level.SEVERE, null, exception);
            exception.printStackTrace();
            System.out.println(exception.getMessage());
            throw exception;
        }
        url = buildGetRequestUrl(url, listOfParameters);
        HttpClient client = HttpClient.newHttpClient();
        HttpRequest request = HttpRequest.newBuilder()
                .uri(URI.create(url))
                .header("Content-Type", "application/json")
                .header("Authorization", "Bearer" + accessToken)
                .GET()
                .build();
        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());

        // Handle the response
        return response;
    }

    /**
     * Build the URL with parameters
     */
    private static String buildGetRequestUrl(String url, HashMap<String, String> parameters) throws UnsupportedEncodingException {
        StringBuilder urlWithParams = new StringBuilder(url);
        if (parameters != null && !parameters.isEmpty()) {
            urlWithParams.append("?");
            for (HashMap.Entry<String, String> entry : parameters.entrySet()) {
                String encodedKey = URLEncoder.encode(entry.getKey(), StandardCharsets.UTF_8.toString());
                String encodedValue = URLEncoder.encode(entry.getValue(), StandardCharsets.UTF_8.toString());
                urlWithParams.append(encodedKey)
                        .append("=")
                        .append(encodedValue)
                        .append("&");
            }
            // Remove the last "&"
            urlWithParams.setLength(urlWithParams.length() - 1);
        }
        return urlWithParams.toString();
    }
}
