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

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.HashMap;
import java.util.Locale;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 *
 * @author Mandelkow
 */
public class Employee {

    private int employeeId;
    private String employeeLastName;
    private String employeeFirstName;
    private String employeeProfession;
    private float employeeWorkingHours;
    private int employeeLunchBreakMinutes;
    private int employeeHolidays;
    private String employeeBranchString;
    private boolean employeeAbilitiesGoodsReceipt;
    private boolean employeeAbilitiesCompounding;
    private Date employeeStartOfEmployment;
    private Date employeeEndOfEmployment;

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
        this.employeeBranchString = employeeBranch;
        this.employeeAbilitiesGoodsReceipt = Boolean.parseBoolean(employeeAbilitiesGoodsReceipt);
        this.employeeAbilitiesCompounding = Boolean.parseBoolean(employeeAbilitiesCompounding);
        /**
         * Employment:
         */
        SimpleDateFormat simpleDateFormat = new SimpleDateFormat("dd.MM.yyyy", Locale.GERMANY);
        this.employeeStartOfEmployment = null;
        this.employeeEndOfEmployment = null;
        try {
            this.employeeStartOfEmployment = simpleDateFormat.parse(employeeStartOfEmployment);
            this.employeeEndOfEmployment = simpleDateFormat.parse(employeeEndOfEmployment);
        } catch (ParseException ex) {
            //Logger.getLogger(Employee.class.getName()).log(Level.SEVERE, null, ex);
        }
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

    public boolean getAbilitiesCompounding() {
        return employeeAbilitiesCompounding;
    }

    public boolean getAbilitiesGoodsReceipt() {
        return employeeAbilitiesGoodsReceipt;
    }

    public Date getStartOfEmployment() {
        return employeeStartOfEmployment;
    }

    public Date getEndOfEmployment() {
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
        this.employeeBranchString = employeeHashMap.get("employeeBranch");
        this.employeeAbilitiesGoodsReceipt = Boolean.parseBoolean(employeeHashMap.get("employeeAbilitiesGoodsReceipt"));
        this.employeeAbilitiesCompounding = Boolean.parseBoolean(employeeHashMap.get("employeeAbilitiesCompounding"));
        /**
         * Employment:
         */
        SimpleDateFormat simpleDateFormat = new SimpleDateFormat("dd.MM.yyyy", Locale.GERMANY);
        this.employeeStartOfEmployment = null;
        this.employeeEndOfEmployment = null;
        try {
            this.employeeStartOfEmployment = simpleDateFormat.parse(employeeHashMap.get("employeeStartOfEmployment"));
            this.employeeEndOfEmployment = simpleDateFormat.parse(employeeHashMap.get("employeeEndOfEmployment"));
        } catch (ParseException ex) {
            Logger.getLogger(Employee.class.getName()).log(Level.SEVERE, null, ex);
        }

    }

}
