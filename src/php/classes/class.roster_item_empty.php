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
class roster_item_empty extends roster_item {

    public $date_sql;
    public $date_unix;
    public $employee_id;
    public $branch_id;
    public $comment;
    protected $duty_start_int;
    protected $duty_start_sql;
    protected $duty_end_int;
    protected $duty_end_sql;
    protected $break_start_int;
    protected $break_start_sql;
    protected $break_end_int;
    protected $break_end_sql;
    public $working_hours;
    public $break_duration;
    public $duty_duration;
    public $working_seconds;
    public $empty;

    public function __construct(string $date_sql, int $branch_id = NULL) {

        $this->empty = TRUE;
        $this->date_sql = $this->format_time_string_correct($date_sql, '%Y-%m-%d');
        $this->branch_id = $branch_id;
    }

}
