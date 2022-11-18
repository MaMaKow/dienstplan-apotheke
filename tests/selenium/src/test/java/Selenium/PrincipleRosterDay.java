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

import java.util.HashMap;

/**
 *
 * @author Mandelkow
 */
public class PrincipleRosterDay {

    private final HashMap<Integer, PrincipleRosterItem> listOfPrincipleRosterItems;

    public PrincipleRosterDay() {
        listOfPrincipleRosterItems = new HashMap();
    }

    public void put(PrincipleRosterItem principleRosterItem) {
        listOfPrincipleRosterItems.put(this.size(), principleRosterItem);
    }

    public HashMap<Integer, PrincipleRosterItem> getlistOfPrincipleRosterItems() {
        return listOfPrincipleRosterItems;
    }

    public PrincipleRosterItem getPrincipleRosterItem(int rowNumber) {
        return listOfPrincipleRosterItems.get(rowNumber);
    }

    public PrincipleRosterItem getPrincipleRosterItemByEmployeeId(int employeeId) {
        for (PrincipleRosterItem principleRosterItem : listOfPrincipleRosterItems.values()) {
            if (principleRosterItem.getEmployeeId() == employeeId) {
                return principleRosterItem;
            }
        }
        return null;
    }

    public PrincipleRosterDay getPrincipleRosterDayByEmployeeId(int employeeId) {
        PrincipleRosterDay principleEmployeeDay = new PrincipleRosterDay();
        listOfPrincipleRosterItems.values().stream()
                .filter(principleRosterItem -> (principleRosterItem.getEmployeeId() == employeeId))
                .forEachOrdered(principleRosterItem -> {
                    principleEmployeeDay.put(principleRosterItem);
                });
        return principleEmployeeDay;
    }

    public int size() {
        return listOfPrincipleRosterItems.size();
    }
}
