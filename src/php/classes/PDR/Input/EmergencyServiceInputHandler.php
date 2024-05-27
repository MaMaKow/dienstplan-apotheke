<?php

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

namespace PDR\Input;

/**
 * Handles user input for managing pharmacy emergency service entries.
 *
 * This class provides a method to process user input related to pharmacy emergency services.
 * It checks user privileges, extracts relevant input variables, and delegates actions such as
 * adding, updating, or deleting emergency service entries to the corresponding methods in the
 * \PDR\Database\EmergencyServiceDatabaseHandler class.
 *
 * @author Mandelkow
 * @namespace PDR\Input
 */
class EmergencyServiceInputHandler {

    /**
     * Handles user input to manage pharmacy emergency service entries.
     *
     * This method validates user privileges, extracts input variables such as command, dates,
     * branch ID, and employee key. Depending on the command, it delegates actions to methods
     * in the \PDR\Database\EmergencyServiceDatabaseHandler class for adding, updating, or deleting
     * emergency service entries in the database.
     *
     * @param \sessions $session The user session object for checking privileges.
     * @return void
     */
    public static function handleUserInput(\sessions $session): void {
        if (!$session->user_has_privilege('create_roster')) {
            return;
        }

        $command = \user_input::get_variable_from_any_input('command', FILTER_SANITIZE_SPECIAL_CHARS);
        $dateNew = \user_input::get_variable_from_any_input('emergency_service_date', FILTER_SANITIZE_NUMBER_INT);
        $dateOld = \user_input::get_variable_from_any_input('emergency_service_date_old', FILTER_SANITIZE_NUMBER_INT);
        $branchId = \user_input::get_variable_from_any_input('emergency_service_branch', FILTER_SANITIZE_NUMBER_INT);
        $employeeKey = \user_input::get_variable_from_any_input('emergency_service_employee', FILTER_SANITIZE_NUMBER_INT);

        if ("" === $dateNew || "" === $branchId) {
            return;
        }

        if ("" === $employeeKey && "" === $dateOld) {
            \PDR\Database\EmergencyServiceDatabaseHandler::add_emergency_service_entry($dateNew, $branchId);
        } else if ("replace" === $command) {
            \PDR\Database\EmergencyServiceDatabaseHandler::update_emergency_service_entry($employeeKey, $dateNew, $dateOld, $branchId);
        } else if ("delete" === $command) {
            \PDR\Database\EmergencyServiceDatabaseHandler::delete_emergency_service_entry($dateOld, $branchId);
        }
    }
}
