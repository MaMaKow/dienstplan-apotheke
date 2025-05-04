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
package Selenium;

/**
 *
 * @author Mandelkow
 */
import java.security.SecureRandom;
import java.util.HashMap;
import java.util.Map;
import java.util.List;
import java.util.concurrent.ConcurrentHashMap;

public class UserRegistry {

    private static final Map<String, User> users = new ConcurrentHashMap<>();
    // List of all available privileges
    public static final String PRIVILEGE_ADMINISTRATION = "administration";
    public static final String PRIVILEGE_CREATE_EMPLOYEE = "create_employee";
    public static final String PRIVILEGE_CREATE_ROSTER = "create_roster";
    public static final String PRIVILEGE_APPROVE_ROSTER = "approve_roster";
    public static final String PRIVILEGE_CREATE_OVERTIME = "create_overtime";
    public static final String PRIVILEGE_CREATE_ABSENCE = "create_absence";
    public static final String PRIVILEGE_REQUEST_OWN_ABSENCE = "request_own_absence";
    public static final List<String> PDR_LIST_OF_PRIVILEGES = List.of(
            PRIVILEGE_ADMINISTRATION,
            PRIVILEGE_CREATE_EMPLOYEE,
            PRIVILEGE_CREATE_ROSTER,
            PRIVILEGE_APPROVE_ROSTER,
            PRIVILEGE_CREATE_OVERTIME,
            PRIVILEGE_CREATE_ABSENCE,
            PRIVILEGE_REQUEST_OWN_ABSENCE
    );

    public static void addUser(User user) {
        users.put(user.getUserName(), user);
    }

    public static User getUserByName(String userName) {
        return users.get(userName);
    }

    public static Map<String, User> getAllUsers() {
        return users;
    }

    public static void initializeUsers() {
        Map<String, Boolean> adminPrivileges = Map.of(
                UserRegistry.PRIVILEGE_ADMINISTRATION, true,
                UserRegistry.PRIVILEGE_CREATE_EMPLOYEE, true,
                UserRegistry.PRIVILEGE_CREATE_ROSTER, true,
                UserRegistry.PRIVILEGE_APPROVE_ROSTER, true,
                UserRegistry.PRIVILEGE_CREATE_OVERTIME, true,
                UserRegistry.PRIVILEGE_CREATE_ABSENCE, true,
                UserRegistry.PRIVILEGE_REQUEST_OWN_ABSENCE, true
        );
        Map<String, Boolean> managerPrivileges = Map.of(
                UserRegistry.PRIVILEGE_ADMINISTRATION, false,
                UserRegistry.PRIVILEGE_CREATE_EMPLOYEE, false,
                UserRegistry.PRIVILEGE_CREATE_ROSTER, true,
                UserRegistry.PRIVILEGE_APPROVE_ROSTER, true,
                UserRegistry.PRIVILEGE_CREATE_OVERTIME, true,
                UserRegistry.PRIVILEGE_CREATE_ABSENCE, true,
                UserRegistry.PRIVILEGE_REQUEST_OWN_ABSENCE, true
        );
        Map<String, Boolean> employeePrivileges = Map.of(
                UserRegistry.PRIVILEGE_ADMINISTRATION, false,
                UserRegistry.PRIVILEGE_CREATE_EMPLOYEE, false,
                UserRegistry.PRIVILEGE_CREATE_ROSTER, false,
                UserRegistry.PRIVILEGE_APPROVE_ROSTER, false,
                UserRegistry.PRIVILEGE_CREATE_OVERTIME, false,
                UserRegistry.PRIVILEGE_CREATE_ABSENCE, false,
                UserRegistry.PRIVILEGE_REQUEST_OWN_ABSENCE, true
        );
        addUser(new User("AdminUser", "admin@localhost", generateRandomString(16), adminPrivileges));
        addUser(new User("EmployeeUser", "employee@localhost", generateRandomString(16), employeePrivileges));
        addUser(new User("ManagerUser", "manager@localhost", generateRandomString(16), managerPrivileges));
    }

    static final String CHARACTERS = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_";
    static final SecureRandom RANDOM = new SecureRandom();

    private static String generateRandomString(int length) {
        StringBuilder sb = new StringBuilder(length);
        for (int i = 0; i < length; i++) {
            int randomIndex = RANDOM.nextInt(CHARACTERS.length());
            sb.append(CHARACTERS.charAt(randomIndex));
        }
        return sb.toString();
    }

}
