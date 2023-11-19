<?php

/*
 * Copyright (C) 2017 Mandelkow
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
 * This class will handle the functions dealing with the pharmacy emergency service
 *
 * @author Mandelkow
 */
abstract class pharmacy_emergency_service {
    /*
     * TODO: optimize for vacation year view!
     */

    /**
     * Do we have emergency service at the given date?
     *
     * The preparation for emergency services involves all branches. Therefore the function does not primarily discriminate between branches.
     *
     * @param $date_sql string date in the form 'Y-m-d'
     * @return bool|array FALSE if none of the branches are having emergency service. An array('vk' => employee_key, mandant => branch_id) if one of the branches has emergency service
     */
    public static function having_emergency_service($date_sql) {
        $sql_query = "SELECT *
		FROM `Notdienst`
		WHERE `Datum` = :date";
        $result = database_wrapper::instance()->run($sql_query, array('date' => $date_sql));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $having_emergency_service["employee_key"] = $row->employee_key;
            $having_emergency_service["branch_id"] = $row->Mandant;
        }
        if (!empty($having_emergency_service)) {
            return $having_emergency_service;
        } else {
            return FALSE;
        }
    }

    public static function get_all_emergency_services_in_range($date_start_sql, $date_end_sql) {
        $Emergency_services = array();
        $sql_query = "SELECT *
		FROM `Notdienst`
		WHERE `Datum` >= :date_start AND `Datum` <= :date_end";
        $result = database_wrapper::instance()->run($sql_query, array('date_start' => $date_start_sql, 'date_end' => $date_end_sql));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $Emergency_services[$row->Datum]["employee_key"] = $row->employee_key;
            $Emergency_services[$row->Datum]["branch_id"] = $row->Mandant;
        }
        return $Emergency_services;
    }

}
