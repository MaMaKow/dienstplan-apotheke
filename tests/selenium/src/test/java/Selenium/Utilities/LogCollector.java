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

import java.util.ArrayList;
import java.util.List;

/**
 *
 * @author Mandelkow
 */
public class LogCollector {

    private static final ThreadLocal<List<String>> logMessages = ThreadLocal.withInitial(ArrayList::new);

    private static void addMessage(String message) {
        logMessages.get().add(message);
    }

    public static void clear() {
        logMessages.get().clear();
    }

    public static void debug(String message) {
        addMessage("DEBUG " + message);
    }

    public static void debug(Number message) {
        addMessage("DEBUG " + String.valueOf(message));
    }

    public static void warn(String message) {
        addMessage("WARN  " + message);
    }

    public static void error(String message) {
        addMessage("ERROR " + message);
    }

    public static void fatal(String message) {
        addMessage("FATAL " + message);
    }

    public static void info(String message) {
        addMessage("INFO  " + message);
    }

    public static Iterable<String> getMessages() {
        return logMessages.get();
    }

}
