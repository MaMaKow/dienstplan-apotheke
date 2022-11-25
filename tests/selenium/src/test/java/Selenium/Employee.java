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

import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.HashMap;
import java.util.Locale;

/**
 *
 * @author Mandelkow
 */
public class Employee {

    public static final DateTimeFormatter DATE_TIME_FORMATTER_DAY_MONTH_YEAR = DateTimeFormatter.ofPattern("dd.MM.yyyy", Locale.GERMANY);
    public static final DateTimeFormatter DATE_TIME_FORMATTER_YEAR_MONTH_DAY = DateTimeFormatter.ofPattern("yyyy-MM-dd", Locale.GERMANY);

    private int employeeId;
    private String employeeLastName;
    private String employeeFirstName;
    private String employeeProfession;
    private float employeeWorkingHours;
    private int employeeLunchBreakMinutes;
    private int employeeHolidays;
    private String employeeBranchString;
    private int employeeBranchId;
    private boolean employeeAbilitiesGoodsReceipt;
    private boolean employeeAbilitiesCompounding;
    private LocalDate employeeStartOfEmployment;
    private LocalDate employeeEndOfEmployment;

    public Employee(String employeeId,
            String employeeLastName,
            String employeeFirstName,
            String employeeProfession,
            String employeeWorkingHours,
            String employeeLunchBreakMinutes,
            String employeeHolidays,
            String employeeBranch,
            String employeeAbilitiesGoodsReceipt,
            String employeeAbilitiesCompounding,
            String employeeStartOfEmployment,
            String employeeEndOfEmployment
    ) {
        this.employeeId = Integer.valueOf(employeeId);
        this.employeeLastName = employeeLastName;
        this.employeeFirstName = employeeFirstName;
        this.employeeProfession = employeeProfession;
        this.employeeWorkingHours = Float.valueOf(employeeWorkingHours);
        this.employeeLunchBreakMinutes = Integer.valueOf(employeeLunchBreakMinutes);
        this.employeeHolidays = Integer.valueOf(employeeHolidays);
        NetworkOfBranchOffices networkOfBranchOffices = new NetworkOfBranchOffices();
        Branch branch = networkOfBranchOffices.getBranchByName(employeeBranch);
        this.employeeBranchString = branch.getBranchName();
        this.employeeBranchId = branch.getBranchId();
        this.employeeAbilitiesGoodsReceipt = Boolean.parseBoolean(employeeAbilitiesGoodsReceipt);
        this.employeeAbilitiesCompounding = Boolean.parseBoolean(employeeAbilitiesCompounding);
        /**
         * Employment:
         */
        this.employeeStartOfEmployment = null;
        this.employeeEndOfEmployment = null;
        this.employeeStartOfEmployment = LocalDate.parse(employeeStartOfEmployment, DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
        this.employeeEndOfEmployment = LocalDate.parse(employeeEndOfEmployment, DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
    }

    public int getEmployeeId() {
        return employeeId;
    }

    public String getLastName() {
        return employeeLastName;
    }

    public String getFirstName() {
        return employeeFirstName;
    }

    public String getProfession() {
        return employeeProfession;
    }

    public float getWorkingHours() {
        return employeeWorkingHours;
    }

    public int getHolidays() {
        return employeeHolidays;
    }

    public int getLunchBreakMinutes() {
        return employeeLunchBreakMinutes;
    }

    public String getBranchString() {
        return employeeBranchString;
    }

    public int getBranchId() {
        return employeeBranchId;
    }

    public boolean getAbilitiesCompounding() {
        return employeeAbilitiesCompounding;
    }

    public boolean getAbilitiesGoodsReceipt() {
        return employeeAbilitiesGoodsReceipt;
    }

    public LocalDate getStartOfEmployment() {
        return employeeStartOfEmployment;
    }

    public LocalDate getEndOfEmployment() {
        return employeeEndOfEmployment;
    }

    public Employee(HashMap<String, String> employeeHashMap) {
        this.employeeId = Integer.valueOf(employeeHashMap.get("employeeId"));
        this.employeeLastName = employeeHashMap.get("employeeLastName");
        this.employeeFirstName = employeeHashMap.get("employeeFirstName");
        this.employeeProfession = employeeHashMap.get("employeeProfession");
        this.employeeWorkingHours = Float.valueOf(employeeHashMap.get("employeeWorkingHours"));
        this.employeeLunchBreakMinutes = Integer.valueOf(employeeHashMap.get("employeeLunchBreakMinutes"));
        this.employeeHolidays = Integer.valueOf(employeeHashMap.get("employeeHolidays"));

        NetworkOfBranchOffices networkOfBranchOffices = new NetworkOfBranchOffices();
        if (employeeHashMap.containsKey("employeeBranchId")) {
            String employeeBranchIdString = employeeHashMap.get("employeeBranchId");
            int branchId = Integer.valueOf(employeeBranchIdString);
            Branch branch = networkOfBranchOffices.getBranchById(branchId);
            this.employeeBranchString = branch.getBranchName();
        }

        this.employeeAbilitiesGoodsReceipt = Boolean.parseBoolean(employeeHashMap.get("employeeAbilitiesGoodsReceipt"));
        this.employeeAbilitiesCompounding = Boolean.parseBoolean(employeeHashMap.get("employeeAbilitiesCompounding"));
        /**
         * Employment:
         */
        this.employeeStartOfEmployment = null;
        this.employeeEndOfEmployment = null;
        if (!"".equals(employeeHashMap.get("employeeStartOfEmployment"))) {
            this.employeeStartOfEmployment = LocalDate.parse(employeeHashMap.get("employeeStartOfEmployment"), DATE_TIME_FORMATTER_YEAR_MONTH_DAY);
        }
        if (!"".equals(employeeHashMap.get("employeeEndOfEmployment"))) {
            this.employeeEndOfEmployment = LocalDate.parse(employeeHashMap.get("employeeEndOfEmployment"), DATE_TIME_FORMATTER_YEAR_MONTH_DAY);
        }

    }
}
