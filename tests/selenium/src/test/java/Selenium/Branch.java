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
public class Branch {

    private final int branchId;
    private final int branchPepId;
    private final String branchName;
    private final String branchShortName;
    private final String branchAddress;
    private final String branchManager;
    private final HashMap<Integer, String[]> openingTimesMap;

    public Branch(int branchId,
            int branchPepId,
            String branchName,
            String branchShortName,
            String branchAddress,
            String branchManager,
            HashMap<Integer, String[]> openingTimesMap) {
        this.branchId = branchId;
        this.branchPepId = branchPepId;
        this.branchName = branchName;
        this.branchShortName = branchShortName;
        this.branchAddress = branchAddress;
        this.branchManager = branchManager;
        this.openingTimesMap = openingTimesMap;
    }

    public int getBranchId() {
        return branchId;
    }

    public int getBranchPepId() {
        return branchPepId;
    }

    public String getBranchName() {
        return branchName;
    }

    public String getBranchShortName() {
        return branchShortName;
    }

    public String getBranchAddress() {
        return branchAddress;
    }

    public String getBranchManager() {
        return branchManager;
    }

    public HashMap<Integer, String[]> getOpeningTimesMap() {
        return openingTimesMap;
    }
}
