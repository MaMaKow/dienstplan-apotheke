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

import java.time.DayOfWeek;
import java.time.LocalTime;
import java.util.HashMap;

/**
 *
 * @author Mandelkow
 */
public class PrincipleRoster {

    private HashMap<DayOfWeek, PrincipleRosterDay> principleRoster;
    private final int alternationId;
    private final int branchId;

    public PrincipleRoster(int branchId, int alternationId) {
        this.alternationId = alternationId;
        this.branchId = branchId;
        setPrincipleRoster();
    }

    private void setPrincipleRoster() {
        /**
         * @TODO: Create a real pinciple roster:
         */
        principleRoster = new HashMap<>();
        PrincipleRosterDay principleMonday = new PrincipleRosterDay();
        PrincipleRosterItem principleRosterItem0 = new PrincipleRosterItem(3, DayOfWeek.MONDAY, LocalTime.of(8, 00), LocalTime.of(16, 30), LocalTime.of(11, 30), LocalTime.of(12, 0), null, 1);
        PrincipleRosterItem principleRosterItem1 = new PrincipleRosterItem(4, DayOfWeek.MONDAY, LocalTime.of(8, 00), LocalTime.of(17, 00), LocalTime.of(12, 0), LocalTime.of(12, 30), null, 1);
        PrincipleRosterItem principleRosterItem2 = new PrincipleRosterItem(5, DayOfWeek.MONDAY, LocalTime.of(9, 00), LocalTime.of(18, 00), LocalTime.of(12, 30), LocalTime.of(13, 0), null, 1);
        PrincipleRosterItem principleRosterItem3 = new PrincipleRosterItem(6, DayOfWeek.MONDAY, LocalTime.of(9, 30), LocalTime.of(18, 00), LocalTime.of(13, 0), LocalTime.of(13, 30), null, 1);
        principleMonday.put(principleRosterItem0);
        principleMonday.put(principleRosterItem1);
        principleMonday.put(principleRosterItem2);
        principleMonday.put(principleRosterItem3);
        principleRoster.put(DayOfWeek.MONDAY, principleMonday);
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
