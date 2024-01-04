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

import Selenium.RealData.RealNetworkOfBranchOffices;
import Selenium.driver.Wrapper;
import java.time.LocalDate;
import java.util.HashMap;

/**
 * The Employee class represents an individual employee's information and attributes.
 * It provides constructors and methods to create, access, and manage employee data.
 * Employees' personal information, employment details, and branch affiliations are stored within instances of this class.
 *
 * Attributes:
 * - employeeKey: Unique identifier for the employee.
 * - employeeLastName: Last name of the employee.
 * - employeeFirstName: First name of the employee.
 * - employeeProfession: Job profession of the employee.
 * - employeeWorkingHours: Weekly working hours of the employee.
 * - employeeLunchBreakMinutes: Duration of the employee's lunch break in minutes.
 * - employeeHolidays: Number of holidays allocated to the employee.
 * - employeeBranchString: Name of the branch where the employee is assigned.
 * - employeeBranchId: Identifier of the branch where the employee is assigned.
 * - employeeAbilitiesGoodsReceipt: Indicates whether the employee can handle goods receipt tasks.
 * - employeeAbilitiesCompounding: Indicates whether the employee can perform compounding tasks.
 * - employeeStartOfEmployment: Start date of the employee's employment.
 * - employeeEndOfEmployment: End date of the employee's employment (if applicable).
 *
 * Constructors:
 * - Employee(employeeKey, employeeLastName, employeeFirstName, employeeProfession, employeeWorkingHours, employeeLunchBreakMinutes, employeeHolidays, employeeBranch, employeeAbilitiesGoodsReceipt, employeeAbilitiesCompounding, employeeStartOfEmployment, employeeEndOfEmployment):
 * Initializes attributes based on provided parameters.
 *
 * - Employee(employeeHashMap):
 * Initializes attributes based on values provided in a HashMap containing employee details.
 *
 * Getter Methods:
 * - Various getter methods are provided to access individual attributes of an employee object.
 *
 * Note:
 * - Date parsing is used to convert date strings to LocalDate objects for employment start and end dates.
 * - Branch information is fetched from a NetworkOfBranchOffices using either branch name or branch ID.
 * - Various data types are parsed from strings using appropriate conversion methods.
 *
 * @author Mandelkow
 * @since 2021-11-10
 */
public class Employee {

    private final int employeeKey;
    private final String employeeLastName;
    private final String employeeFirstName;
    private final String employeeProfession;
    private final float employeeWorkingHours;
    private final int employeeLunchBreakMinutes;
    private final int employeeHolidays;
    private int employeeBranchId;
    private final boolean employeeAbilitiesGoodsReceipt;
    private final boolean employeeAbilitiesCompounding;
    private LocalDate employeeStartOfEmployment;
    private LocalDate employeeEndOfEmployment;

    public Employee(String employeeKey,
            String employeeLastName,
            String employeeFirstName,
            String employeeProfession,
            String employeeWorkingHours,
            String employeeLunchBreakMinutes,
            String employeeHolidays,
            String employeeBranchName,
            String employeeAbilitiesGoodsReceipt,
            String employeeAbilitiesCompounding,
            String employeeStartOfEmployment,
            String employeeEndOfEmployment
    ) {
        this.employeeKey = Integer.parseInt(employeeKey);
        this.employeeLastName = employeeLastName;
        this.employeeFirstName = employeeFirstName;
        this.employeeProfession = employeeProfession;
        this.employeeWorkingHours = Float.parseFloat(employeeWorkingHours);
        this.employeeLunchBreakMinutes = Integer.parseInt(employeeLunchBreakMinutes);
        this.employeeHolidays = Integer.parseInt(employeeHolidays);
        NetworkOfBranchOffices networkOfBranchOffices = new NetworkOfBranchOffices();
        Branch branch = networkOfBranchOffices.getBranchByName(employeeBranchName);
        this.employeeBranchId = branch.getBranchId();
        this.employeeAbilitiesGoodsReceipt = Boolean.parseBoolean(employeeAbilitiesGoodsReceipt);
        this.employeeAbilitiesCompounding = Boolean.parseBoolean(employeeAbilitiesCompounding);
        /**
         * Employment:
         */
        this.employeeStartOfEmployment = null;
        this.employeeEndOfEmployment = null;
        this.employeeStartOfEmployment = LocalDate.parse(employeeStartOfEmployment, Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
        this.employeeEndOfEmployment = LocalDate.parse(employeeEndOfEmployment, Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
    }

    public int getEmployeeKey() {
        return employeeKey;
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

    public String getBranchString(NetworkOfBranchOffices networkOfBranchOffices) {
        String branchName = networkOfBranchOffices.getBranchById(employeeBranchId).getBranchName();
        return branchName;
    }

    public String getRealBranchString(RealNetworkOfBranchOffices realNetworkOfBranchOffices) {
        String branchName = realNetworkOfBranchOffices.getRealBranchById(employeeBranchId).getBranchName();
        return branchName;
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
        this.employeeKey = Integer.parseInt(employeeHashMap.get("employeeKey"));
        this.employeeLastName = employeeHashMap.get("employeeLastName");
        this.employeeFirstName = employeeHashMap.get("employeeFirstName");
        this.employeeProfession = employeeHashMap.get("employeeProfession");
        this.employeeWorkingHours = Float.parseFloat(employeeHashMap.get("employeeWorkingHours"));
        this.employeeLunchBreakMinutes = Integer.parseInt(employeeHashMap.get("employeeLunchBreakMinutes"));
        this.employeeHolidays = Integer.parseInt(employeeHashMap.get("employeeHolidays"));

        if (employeeHashMap.containsKey("employeeBranchId")) {
            String employeeBranchIdString = employeeHashMap.get("employeeBranchId");
            this.employeeBranchId = Integer.parseInt(employeeBranchIdString);
        }

        this.employeeAbilitiesGoodsReceipt = Boolean.parseBoolean(employeeHashMap.get("employeeAbilitiesGoodsReceipt"));
        this.employeeAbilitiesCompounding = Boolean.parseBoolean(employeeHashMap.get("employeeAbilitiesCompounding"));
        /**
         * Employment:
         */
        this.employeeStartOfEmployment = null;
        this.employeeEndOfEmployment = null;
        if (!"".equals(employeeHashMap.get("employeeStartOfEmployment"))) {
            this.employeeStartOfEmployment = LocalDate.parse(employeeHashMap.get("employeeStartOfEmployment"), Wrapper.DATE_TIME_FORMATTER_YEAR_MONTH_DAY);
        }
        if (!"".equals(employeeHashMap.get("employeeEndOfEmployment"))) {
            this.employeeEndOfEmployment = LocalDate.parse(employeeHashMap.get("employeeEndOfEmployment"), Wrapper.DATE_TIME_FORMATTER_YEAR_MONTH_DAY);
        }

    }
}
