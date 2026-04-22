<?php

/*
 * Copyright (C) 2018 Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of class
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class pharmacy_emergency_service_builder {

    public static function build_emergency_service_table_employee_select(?int $employee_key_selected, int $branch_id, string $date_sql, int $emergencyServiceIndex, PDR\Workforce\Workforce $workforce) {
        $table_employee_select = "";
        $table_employee_select .= "<input type='hidden' name=emergency_service_branch value='$branch_id'>";
        $table_employee_select .= "<input type='hidden' name=emergency_service_date_old value='$date_sql'>";
        $table_employee_select .= "<input type='hidden' name='command' id='command_$emergencyServiceIndex' value=''>";
        $table_employee_select .= "<select name='emergency_service_employee' onChange='updateCommandAndSubmit(this, $emergencyServiceIndex)'>";

        /**
         * The empty option is necessary to enable the deletion of employees from the roster:
         */
        $table_employee_select .= "<option value=''>&nbsp;</option>";
        if (!isset($employee_key_selected) or $workforce->employeeExists($employee_key_selected)) {
            foreach ($workforce->getListOfQualifiedPharmacistEmployees() as $employee_key) {
                $employee_object = $workforce->getEmployeeObject($employee_key);
                if ($employee_key_selected == $employee_key and NULL !== $employee_key_selected) {
                    $table_employee_select .= "<option value=$employee_key selected>" . $employee_object->getFullName() . "</option>";
                } else {
                    $table_employee_select .= "<option value=$employee_key>" . $employee_object->getFullName() . "</option>\n";
                }
            }
        } else {
            /*
             * Unknown employee, probably someone from the past.
             */
            $table_employee_select .= "<option value=$employee_key_selected selected>" . $employee_key_selected . " " . gettext("Unknown employee") . "</option>";
        }
        $table_employee_select .= "</select>";
        $table_employee_select .= "";
        return $table_employee_select;
    }
}
