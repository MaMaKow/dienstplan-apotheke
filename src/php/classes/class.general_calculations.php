<?php

/*
 * Copyright (C) 2018 Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
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
 * @author Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
abstract class general_calculations {
    /*
     * Get the first day of the week.
     *
     * We aim for the monday of the given week.
     * Be aware though, that strtotime('Monday this week') on a sunday will return the monday, which follows the sunday.
     * This is desired for this application.
     * If this is not acceptable for you please consider:
     * https://gist.github.com/stecman/0203410aa4da0ef01ea9
     *
     * @param $date_sql string
     * @return $first_day_of_week_sql string
     *
     */

    public static function get_first_day_of_week($date_sql = NULL) {

        /*
         * TODO: Perhaps include a configuration option to select Sunday as the first day of the week.
         */
        if (NULL === $date_sql) {
            $date_sql = date('Y-m-d');
        }
        $date_unix = strtotime('Monday this week', strtotime($date_sql));
        $first_day_of_week_sql = date('Y-m-d', $date_unix);
        return $first_day_of_week_sql;
    }

}
