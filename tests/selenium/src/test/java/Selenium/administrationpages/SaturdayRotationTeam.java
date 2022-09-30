/*
 * Copyright (C) 2022 Mandelkow
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
package Selenium.administrationpages;

import java.util.HashSet;

/**
 *
 * @author Mandelkow
 */
public class SaturdayRotationTeam {

    private Integer teamId;
    private final HashSet<Integer> listOfTeamMemerIds;

    public SaturdayRotationTeam(Integer teamIdInput, HashSet<Integer> listOfTeamMemerIdsInput) {
        teamId = teamIdInput;
        listOfTeamMemerIds = listOfTeamMemerIdsInput;
    }

    public SaturdayRotationTeam(Integer teamIdInput, int[] arrayOfTeamMemerIdsInput) {
        teamId = teamIdInput;
        listOfTeamMemerIds = new HashSet<>();
        for (int teamMemerId : arrayOfTeamMemerIdsInput) {
            listOfTeamMemerIds.add(teamMemerId);
        }
    }

    public int getTeamId() {
        return teamId;
    }

    public void setTeamId(int newTeamId) {
        teamId = newTeamId;
    }

    public HashSet<Integer> getListOfTeamMembers() {
        return listOfTeamMemerIds;
    }
}