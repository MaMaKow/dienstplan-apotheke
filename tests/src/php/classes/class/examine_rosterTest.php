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

    private $instance;

    public function setUp() {
        $workforce = new workforce();
        $List_of_branch_objects = branch::read_branches_from_database();
        $date_sql = "2018-01-02";
        $date_unix = strtotime($date_sql);
        $branch_id = 1;
        $Roster = roster::read_roster_from_database($branch_id, $date_sql);

        $this->instance = new examine_roster($Roster, $date_unix, $branch_id, $workforce);
    }

    public function tearDown() {

    }

    /*
      public function testEmpty() {
      $stack = [];
      $this->assertEmpty($stack);

      return $stack;
      }
     */

    public function check_for_overlap_provider() {
        $List_of_branch_objects = branch::read_branches_from_database();
        return array(
            array("2018-01-02", $List_of_branch_objects, new workforce("2018-01-02")),
            array("2018-01-03", $List_of_branch_objects, new workforce("2018-01-03")),
        );
    }

    /**
     * @dataProvider check_for_overlap_provider
     * @return boolean
     */
    public function testcheck_for_overlap($date_sql, $List_of_branch_objects, $workforce) {
        $this->assertTrue($this->instance->check_for_overlap($date_sql, $List_of_branch_objects, $workforce));
        $this->assertTrue(1 !== user_dialog::$Messages);
        var_export(user_dialog::$Messages);
        echo PHP_EOL;
        return TRUE;
    }

}
