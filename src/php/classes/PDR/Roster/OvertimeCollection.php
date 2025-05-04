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

namespace PDR\Roster;

/**
 * Description of OvertimeCollection
 *
 * @author Mandelkow
 * @implements \IteratorAggregate<int, \PDR\Roster\Overtime>
 */
class OvertimeCollection implements \IteratorAggregate, \Countable {

    private array $listOfOvertimes = array();

    public function addOvertime(\PDR\Roster\Overtime $overtime): void {
        $this->listOfOvertimes[] = $overtime;
    }

    /**
     * Implement the IteratorAggregate interface
     *
     * @return \ArrayIterator<\PDR\Roster\Overtime>
     */
    public function getIterator(): \ArrayIterator {
        return new \ArrayIterator($this->listOfOvertimes);
    }

    /**
     * Implement the Countable interface
     * @return int
     */
    public function count(): int {
        return count($this->listOfOvertimes);
    }
}
