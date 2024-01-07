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
package Selenium.Utilities;

import com.google.gson.JsonDeserializationContext;
import com.google.gson.JsonDeserializer;
import com.google.gson.JsonElement;
import com.google.gson.JsonParseException;
import java.lang.reflect.Type;
import java.time.LocalTime;
import java.time.format.DateTimeFormatter;

/**
 *
 * @author Mandelkow
 */
public class LocalTimeDeserializer implements JsonDeserializer<LocalTime> {

    private static final DateTimeFormatter formatter = DateTimeFormatter.ofPattern("HH:mm");

    @Override
    public LocalTime deserialize(JsonElement jsonElement, Type type, JsonDeserializationContext jsonDeserializationContext) throws JsonParseException {
        try {
            // Check if the JsonElement is a JsonObject
            if (jsonElement.isJsonObject()) {
                // Extract hour, minute, second, and nano values from the JsonObject
                int hour = jsonElement.getAsJsonObject().get("hour").getAsInt();
                int minute = jsonElement.getAsJsonObject().get("minute").getAsInt();
                int second = jsonElement.getAsJsonObject().get("second").getAsInt();
                int nano = jsonElement.getAsJsonObject().get("nano").getAsInt();

                // Create a LocalTime instance using the extracted values
                return LocalTime.of(hour, minute, second, nano);
            } else {
                // If it's a simple string, proceed with the original logic
                String jsonString = jsonElement.getAsString();
                return LocalTime.parse(jsonString, formatter);
            }
        } catch (Exception e) {
            e.printStackTrace();
            throw new JsonParseException("Error parsing LocalTime from JSON: " + jsonElement, e);
        }
    }
}
