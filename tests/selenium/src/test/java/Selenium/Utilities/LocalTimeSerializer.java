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

import com.google.gson.JsonElement;
import com.google.gson.JsonPrimitive;
import com.google.gson.JsonSerializationContext;
import com.google.gson.JsonSerializer;
import java.lang.reflect.Type;
import java.time.LocalTime;
import java.time.format.DateTimeFormatter;

/**
 * Gson serializer for LocalTime objects.
 * Converts a LocalTime object into a JSON primitive element.
 * Date-time format: "HH:mm"
 * Handles null LocalTime values gracefully.
 *
 * @author Mandelkow
 */
public class LocalTimeSerializer implements JsonSerializer<LocalTime> {

    private static final DateTimeFormatter formatter = DateTimeFormatter.ofPattern("HH:mm");

    @Override
    public JsonElement serialize(LocalTime localTime, Type type, JsonSerializationContext jsonSerializationContext) {
        if (localTime == null) {
            return null;
        }
        String timeString = formatter.format(localTime);
        JsonPrimitive jsonPrimitive = new JsonPrimitive(timeString);
        return jsonPrimitive;
    }
}
