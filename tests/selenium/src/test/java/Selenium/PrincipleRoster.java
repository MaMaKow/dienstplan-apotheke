/*
 * Copyright (C) 2021 Mandelkow
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
package Selenium;

import Selenium.Utilities.GsonProvider;
import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import java.io.IOException;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.time.DayOfWeek;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

/**
 * @todo Stand März 2026 wird die alternationId und die branchId nicht
 * tatsächlich genutzt.
 * @author Mandelkow
 */
public class PrincipleRoster {

    private final HashMap<DayOfWeek, PrincipleRosterDay> principleRoster;
    private final int alternationId;
    private final int branchId;

    /**
     * @todo: Beide Parameter sollten entfernt werden. Sie werden nicht
     * vernünftig genutzt. Die Information sollte fest in den einzelnen Items
     * stehen.
     * @param branchId
     * @param alternationId
     */
    public PrincipleRoster(int branchId, int alternationId) {
        this.alternationId = alternationId;
        this.branchId = branchId;
        principleRoster = (HashMap<DayOfWeek, PrincipleRosterDay>) readPrincipleRosterFromFile();
        //writePrincipleRostertoJson();
    }

    public PrincipleRoster() {
        this(1, 0);
    }

    private void writePrincipleRostertoJson() {
        try {
            Gson gson = GsonProvider.createGson();
            String principleRosterJson = gson.toJson(principleRoster);

            Files.write(Paths.get("principleRoster.json"), principleRosterJson.getBytes(StandardCharsets.UTF_8));
        } catch (IOException exception) {
            exception.printStackTrace();
        }
    }

    private Map<DayOfWeek, PrincipleRosterDay> readPrincipleRosterFromFile() {
        try {
            // Read the JSON content from the file
            String jsonContent = new String(Files.readAllBytes(Paths.get("principleRoster.json")), StandardCharsets.UTF_8);

            // Use Gson to deserialize the JSON content into a Map<DayOfWeek, PrincipleRosterDay>
            Gson gson = GsonProvider.createGson();
            TypeToken<HashMap<DayOfWeek, PrincipleRosterDay>> token = new TypeToken<HashMap<DayOfWeek, PrincipleRosterDay>>() {
            };
            return gson.fromJson(jsonContent, token.getType());
        } catch (Exception e) {
            e.printStackTrace();
            return null;
        }
    }

    public List<DayOfWeek> getAllWeekdays() {
        return new ArrayList<>(principleRoster.keySet());
    }

    public PrincipleRosterItem getPrincipleRosterItem(DayOfWeek dayOfWeek, int rowNumber) {
        PrincipleRosterDay principleRosterDay = principleRoster.get(dayOfWeek);
        PrincipleRosterItem principleRosterItem = principleRosterDay.getlistOfPrincipleRosterItems().get(rowNumber);
        return principleRosterItem;
    }

    public HashMap<DayOfWeek, PrincipleRosterDay> getPrincipleRosterByEmployee(int employeeKey) {
        HashMap<DayOfWeek, PrincipleRosterDay> principleRosterByEmployee = new HashMap<>();
        principleRoster.entrySet().forEach(principleRosterDayEntry -> {
            principleRosterByEmployee.put(principleRosterDayEntry.getKey(), principleRosterDayEntry.getValue().getPrincipleRosterDayByEmployeeKey(employeeKey));
        });
        return principleRosterByEmployee;
    }

    public PrincipleRosterDay getPrincipleRosterDay(DayOfWeek dayOfWeek) {
        return principleRoster.get(dayOfWeek);
    }

    public int getAlternationId() {
        return alternationId;
    }

    public int getBranchId() {
        return branchId;
    }

}
