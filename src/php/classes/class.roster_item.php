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
class roster_item {

    public $date;
    public $employee_id;
    public $duty_start;
    public $duty_end;
    public $break_start;
    public $break_end;
    public $working_hours;
    public $comment;

    function __construct($date, $employee_id, $duty_start, $duty_end, $break_start, $break_end, $comment = NULL) {
        $this->date = $date;
        $this->employee_id = $employee_id;
        $this->duty_start = $duty_start;
        $this->duty_end = $duty_end;
        $this->break_start = $break_start;
        $this->break_end = $break_end;
        $this->comment = $comment;

        $duty_duration = $this->duty_end - $this->duty_start;
        $break_duration = $this->break_end - $this->break_start;
        /*
         * TODO: This might be a good place to issue an error, if the break times are not within the working times.
         * Is it possible to define a roster_logic_exception and throw it here to be catched by the page-rendering-script?
         */
        /*
         * TODO: This does not take into account, that emergency service is not calculated as full hours.
         * Emergeny service calculation might differ between states, federal states, or even employees with different contracts.
         */
        $this->working_hours = ($duty_duration - $break_duration) / 3600;
    }

    /*
     * @param $date_unix int A unix timestamp
     * @param $format string A valid format for the date() function
     * @return string A string representing the unix date in a given format.
     */

    public function format_date_string($date_unix, $format = 'Y-m-d') {
        $date_string = date($format, $date_unix);
        return $date_string;
    }

}
