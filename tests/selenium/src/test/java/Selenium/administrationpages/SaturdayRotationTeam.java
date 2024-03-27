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

import Selenium.Employee;
import Selenium.rosterpages.Workforce;
import java.util.HashMap;
import java.util.HashSet;

/**
 *
 * @author Mandelkow
 */
public class SaturdayRotationTeam {

    private Integer teamId;
    private final HashSet<Integer> listOfTeamMemberIds;
    private final HashSet<Employee> listOfTeamMemberEmployees;

    public SaturdayRotationTeam(Integer teamIdInput, HashSet<Integer> listOfTeamMemberIdsInput) {
        teamId = teamIdInput;
        Workforce workforce = new Workforce();
        listOfTeamMemberIds = listOfTeamMemberIdsInput;
        listOfTeamMemberEmployees = new HashSet<>();
        for (int TeamMemberId : listOfTeamMemberIdsInput) {
            Employee employee = workforce.getEmployeeByKey(TeamMemberId);
            listOfTeamMemberEmployees.add(employee);
        }

    }

    public SaturdayRotationTeam(Integer teamIdInput, Employee[] arrayOfTeamMemberEmployeesInput) {
        Workforce workforce = new Workforce();
        teamId = teamIdInput;
        listOfTeamMemberIds = new HashSet<>();
        listOfTeamMemberEmployees = new HashSet<>();
        for (Employee employee : arrayOfTeamMemberEmployeesInput) {
            int TeamMemberId = employee.getEmployeeKey();
            listOfTeamMemberIds.add(TeamMemberId);
            listOfTeamMemberEmployees.add(employee);
        }
    }

    public int getTeamId() {
        return teamId;
    }

    public void setTeamId(int newTeamId) {
        teamId = newTeamId;
    }

    public HashSet<Integer> getListOfTeamMemberIds() {
        return listOfTeamMemberIds;
    }

    public HashSet<Employee> getListOfTeamEmployees() {
        return listOfTeamMemberEmployees;
    }

    public boolean containsEmployee(Employee searchedEmployee) {
        for (Employee storedEmployee : listOfTeamMemberEmployees) {
            if (searchedEmployee.getFullName().equals(storedEmployee.getFullName())) {
                return true;
            }
        }
        return false;
    }

    public boolean equalsTeam(SaturdayRotationTeam secondSaturdayRotationTeam) {
        if (secondSaturdayRotationTeam.getListOfTeamEmployees().size() != listOfTeamMemberEmployees.size()) {
            return false;
        }
        for (Employee employee : listOfTeamMemberEmployees) {
            if (!secondSaturdayRotationTeam.containsEmployee(employee)) {
                return false;
            }
        }
        return true;
    }

    public static HashMap<Integer, SaturdayRotationTeam> getSaturdayTeams() {
        Workforce workforce = new Workforce();
        HashMap<Integer, SaturdayRotationTeam> saturdayTeamList = new HashMap<>();
        SaturdayRotationTeam saturdayRotationTeam0 = new SaturdayRotationTeam(null,
                new Employee[]{workforce.getEmployeeByFullName("Elisabeth Lehmann"), workforce.getEmployeeByFullName("Emma Grimm")});
        SaturdayRotationTeam saturdayRotationTeam1 = new SaturdayRotationTeam(null,
                new Employee[]{workforce.getEmployeeByFullName("Alexandra Probst"), workforce.getEmployeeByFullName("Jule Dambach")});
        SaturdayRotationTeam saturdayRotationTeam2 = new SaturdayRotationTeam(null,
                new Employee[]{workforce.getEmployeeByFullName("Albert Kremer"), workforce.getEmployeeByFullName("Lea Dietrich")});
        SaturdayRotationTeam saturdayRotationTeam3 = new SaturdayRotationTeam(null,
                new Employee[]{workforce.getEmployeeByFullName("Anabell Neuhaus"), workforce.getEmployeeByFullName("Albert Krüger")});
        SaturdayRotationTeam saturdayRotationTeam4 = new SaturdayRotationTeam(null,
                new Employee[]{workforce.getEmployeeByFullName("Hannah Eckert"), workforce.getEmployeeByFullName("Albert Jansen")});
        saturdayTeamList.put(0, saturdayRotationTeam0);
        saturdayTeamList.put(1, saturdayRotationTeam1);
        saturdayTeamList.put(2, saturdayRotationTeam2);
        saturdayTeamList.put(3, saturdayRotationTeam3);
        saturdayTeamList.put(4, saturdayRotationTeam4);
        return saturdayTeamList;
    }

    public static int getSaturdayTeamsSize() {
        HashMap<Integer, SaturdayRotationTeam> saturdayTeamList = getSaturdayTeams();
        return saturdayTeamList.size();
    }
}
