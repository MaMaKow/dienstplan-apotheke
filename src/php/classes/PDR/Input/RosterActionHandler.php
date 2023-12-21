<?php

/*
 * Copyright (C) 2023 Mandelkow
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
 * Description of RosterActionHandler
 *
 * @author Mandelkow
 */
class RosterActionHandler {

    /**
     * Process the POST request related to roster actions.
     * @param Sessions $session The Sessions instance for user privileges.
     * @return bool True if the request was successfully processed, otherwise false.
     */
    public function processRosterActionInsertMissingEmployee(\Sessions $session, string $principleRosterObjectJson): bool {
        // Read the POST data
        if (!$session->user_has_privilege(\sessions::PRIVILEGE_CREATE_ROSTER)) {
            return false;
        }

        // Decode the JSON string into an associative array
        $principleRosterObjectArray = json_decode($principleRosterObjectJson, true);

        // Extract values from the decoded array
        $dateSql = $principleRosterObjectArray['date_sql'];
        $dateObject = new \DateTime($dateSql);
        $employeeKey = $principleRosterObjectArray['employee_key'];
        $branchId = $principleRosterObjectArray['branch_id'];
        $dutyStartSql = $principleRosterObjectArray['duty_start_sql'];
        $dutyEndSql = $principleRosterObjectArray['duty_end_sql'];
        $breakStartSql = $principleRosterObjectArray['break_start_sql'];
        $breakEndSql = $principleRosterObjectArray['break_end_sql'];
        $comment = $principleRosterObjectArray['comment'];

        $rosterItem = new \roster_item($dateSql, $employeeKey, $branchId, $dutyStartSql, $dutyEndSql, $breakStartSql, $breakEndSql, $comment);
        $roster = \roster::read_roster_from_database($branchId, $dateSql);
        // Add the new element to the existing roster:
        $roster[$dateObject->format('U')][] = $rosterItem;
        \user_input::roster_write_user_input_to_database($roster, $branchId);
        return true;
    }

    /**
     *
     * @param \Sessions $session
     * @param string $principleRosterObjectJson
     * @return bool
     * @todo implement processRosterActionRemoveAbsentEmployee
     */
    public function processRosterActionRemoveAbsentEmployee(\Sessions $session, string $principleRosterObjectJson): bool {
        throw new Exception("Not yet implemented.");
    }
}
