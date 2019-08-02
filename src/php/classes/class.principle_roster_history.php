<?php

/*
 * Copyright (C) 2019 Mandelkow <netbeans@martin-mandelkow.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * This class contains the functions handling the historic data about principle_rosters
 *
 * @author Mandelkow <netbeans@martin-mandelkow.de>
 */
class principle_roster_history {

    public static function get_list_of_history_dates($weekday, $alternating_week_id, $branch_id) {
        $List_of_history_dates = array();

        $sql_query = "SELECT DISTINCT `valid_from` FROM `principle_roster`"
                . " WHERE "
                . " `branch_id` = :branch_id AND "
                . " `alternating_week_id` = :alternating_week_id AND "
                . " `weekday` = :weekday"
                . " ORDER BY `valid_from` DESC";
        $result = database_wrapper::instance()->run($sql_query, array(
            'weekday' => $weekday,
            'alternating_week_id' => $alternating_week_id,
            'branch_id' => $branch_id,
        ));

        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            if (NULL === $row->valid_from) {
                /*
                 * TODO: We should also insert a date here. But how do we decide which one?
                 *     We could just take the first date, which any still stored roster has.
                 */
//                continue;
$row->valid_from = "1970-01-01";
            }
            $List_of_history_dates[] = new DateTime($row->valid_from);
        }

        return $List_of_history_dates;
    }

    public static function get_list_of_change_dates(int $alternating_week_id) {
        $List_of_change_dates = array();
        /*
         * Define a valid_from for all the entries in the database. 1970-01-01
         * Read all the valid_until values.
         * Make an array of those values.
         * Make a list of weeks.
         * Make an array of Rosters in those weeks, with the valid_from as key
         */
        $sql_query = "SELECT DISTINCT `valid_from` "
                . " FROM `principle_roster` "
                . " WHERE `alternating_week_id` = :alternating_week_id ORDER BY `valid_from`;";
        $result = database_wrapper::instance()->run($sql_query, array(
            'alternating_week_id' => $alternating_week_id,
        ));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $date_of_change = $row->valid_from;
            if (NULL === $date_of_change) {
                continue;
            }
            $List_of_change_dates[] = new DateTime($date_of_change);
        }
        if (array() === $List_of_change_dates) {
            /*
             * There has to be at least one entry in the list:
             */
            $List_of_change_dates = array(new DateTime('1970-01-01'));
        }
        return $List_of_change_dates;
    }

}
