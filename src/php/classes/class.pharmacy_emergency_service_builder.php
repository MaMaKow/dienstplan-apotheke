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

    public static function build_emergency_service_table_employee_select($employee_id_selected, $branch_id, $date_sql) {
        global $workforce;
        if (NULL === $workforce) {
            $workforce = new workforce();
        }
        $table_employee_select = "";
        $table_employee_select = "<form method='post'>";
        $table_employee_select .= "<input type='hidden' name=emergency_service_branch value='$branch_id'>";
        $table_employee_select .= "<input type='hidden' name=emergency_service_date value='$date_sql'>";
        $table_employee_select .= "<select name=emergency_service_employee onChange='this.form.submit()'>";
        /*
         * The empty option is necessary to enable the deletion of employees from the roster:
         */
        $table_employee_select .= "<option value=''>&nbsp;</option>";
        if (isset($workforce->List_of_employees[$employee_id_selected]->last_name) or ! isset($employee_id_selected)) {
            foreach ($workforce->List_of_qualified_pharmacist_employees as $employee_id) {
                $employee_object = $workforce->List_of_employees[$employee_id];
                if ($employee_id_selected == $employee_id and NULL !== $employee_id_selected) {
                    $table_employee_select .= "<option value=$employee_id selected>" . $employee_id . " " . $employee_object->last_name . "</option>";
                } else {
                    $table_employee_select .= "<option value=$employee_id>" . $employee_id . " " . $employee_object->last_name . "</option>";
                }
            }
        } else {
            /*
             * Unknown employee, probably someone from the past.
             */
            $table_employee_select .= "<option value=$employee_id_selected selected>" . $employee_id_selected . " Unknown employee" . "</option>";
        }
        $table_employee_select .= "</select>";
        $table_employee_select .= "</form>\n";
        return $table_employee_select;
    }

}
