/*
 * Copyright (C) 2025 Mandelkow
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

import com.google.gson.*;
import java.lang.reflect.Type;
import java.time.LocalDate;

public class GsonProvider {

    public static Gson createGson() {
        return new GsonBuilder()
                .setPrettyPrinting()
                .registerTypeAdapter(LocalDate.class, new JsonSerializer<LocalDate>() {
                    @Override
                    public JsonElement serialize(LocalDate src, Type typeOfSrc, JsonSerializationContext context) {
                        // immer als ISO-8601 String speichern
                        return new JsonPrimitive(src.toString());
                    }
                })
                .registerTypeAdapter(LocalDate.class, new JsonDeserializer<LocalDate>() {
                    @Override
                    public LocalDate deserialize(JsonElement json, Type typeOfT, JsonDeserializationContext context)
                            throws JsonParseException {
                        if (json.isJsonPrimitive()) {
                            // String-Variante ("2025-08-31")
                            return LocalDate.parse(json.getAsString());
                        } else if (json.isJsonObject()) {
                            // Objekt-Variante {"year":2025,"month":8,"day":31}
                            JsonObject obj = json.getAsJsonObject();
                            int year = obj.get("year").getAsInt();
                            int month = obj.get("month").getAsInt();
                            int day = obj.get("day").getAsInt();
                            return LocalDate.of(year, month, day);
                        }
                        throw new JsonParseException("Unsupported LocalDate format: " + json);
                    }
                })
                .create();
    }
}
