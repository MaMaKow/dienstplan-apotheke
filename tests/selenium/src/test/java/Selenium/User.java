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

import java.util.List;
import java.util.Map;

/**
 *
 * @author Mandelkow
 */
public class User {

    private final String userName;
    private final String userEmail;
    private final String passphrase;
    private final Map<String, Boolean> privileges;

    public User(String userName, String userEmail, String passphrase, Map<String, Boolean> privileges) {
        this.userName = userName;
        this.userEmail = userEmail;
        this.passphrase = passphrase;
        this.privileges = privileges;
    }

    // Getters for encapsulation
    public String getUserName() {
        return userName;
    }

    public String getUserEmail() {
        return userEmail;
    }

    public String getPassphrase() {
        return passphrase;
    }

    public Map<String, Boolean> getPrivileges() {
        return privileges;
    }

    @Override
    public String toString() {
        return "{"
                + "userName=\"" + userName + '"'
                + ", userEmail=\"" + userEmail + '"'
                + ", passphrase=\"" + passphrase + '"'
                + ", privileges=\"" + privileges
                + '}';
    }
}
