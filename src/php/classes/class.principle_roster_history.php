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

    public static function get_list_of_history_dates($weekday, $alternation_id, $branch_id) {
        $List_of_history_dates = array();

        $sql_query = "SELECT DISTINCT `valid_from` FROM `principle_roster`"
                . " WHERE "
                . " `branch_id` = :branch_id AND "
                . " `alternation_id` = :alternation_id AND "
                . " `weekday` = :weekday"
                . " ORDER BY `valid_from` DESC";
        $result = database_wrapper::instance()->run($sql_query, array(
            'weekday' => $weekday,
            'alternation_id' => $alternation_id,
            'branch_id' => $branch_id,
        ));

        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            if (NULL === $row->valid_from) {
                /*
                 * TODO: We should also insert a date here. But how do we decide which one?
                 *     We could just take the first date, which any still stored roster has.
                 */
                continue;
            }
            $List_of_history_dates[] = new DateTime($row->valid_from);
        }

        return $List_of_history_dates;
    }

}
