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
 * Description of examine_rosterTest
 *
 * @author Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class examine_rosterTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        $workforce = new workforce();
        $List_of_branch_objects = branch::read_branches_from_database();
        $date_sql = "2018-01-01";
    }

    public function tearDown() {

    }

    public function testEmpty() {
        $stack = [];
        $this->assertEmpty($stack);

        return $stack;
    }

    public function testcheck_for_overlap($date_sql, $List_of_branch_objects, $workforce) {
        return TRUE;
    }

}
