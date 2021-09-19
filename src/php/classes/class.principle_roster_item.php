<?php

/*
 * Copyright (C) 2020 Mandelkow <netbeans@martin-mandelkow.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * A principle roster item is similar to a roster_item.
 *
 * <p>The difference is, that a principle_roster_item is not bound to a specific date.</p>
 *
 * @author Mandelkow <netbeans@martin-mandelkow.de>
 */
class principle_roster_item extends roster_item {

    /**
     *
     * @var int <p>primary key in the database</p>
     * @todo <p>The class roster_item might get a primary_key too.
     *     When that happens, this declaration is probably not neccessary anymore.</p>
     */
    public $primary_key;

    /**
     *
     * @param int $primary_key
     * @param string $date_sql
     * @param int $employee_id
     * @param int $branch_id
     * @param string $duty_start
     * @param string $duty_end
     * @param string $break_start
     * @param string $break_end
     * @param string $comment
     * @throws \InvalidArgumentException
     */
    public function __construct(int $primary_key, string $date_sql, int $employee_id = NULL, int $branch_id, string $duty_start, string $duty_end, string $break_start = NULL, string $break_end = NULL, string $comment = NULL) {
        if (NULL === $primary_key) {
            throw new \InvalidArgumentException('$primary_key MUST NOT be NULL');
        }
        $this->primary_key = $primary_key;
        parent::__construct($date_sql, $employee_id, $branch_id, $duty_start, $duty_end, $break_start, $break_end, $comment);
    }

}
