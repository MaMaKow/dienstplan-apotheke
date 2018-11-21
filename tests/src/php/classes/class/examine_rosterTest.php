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
 * Tests for the class examine_roster
 *
 * @author Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class examine_rosterTest extends PHPUnit_Framework_TestCase {

    private $instance;

    public function setUp() {
        $this->List_of_branch_objects = branch::read_branches_from_database();
        $this->branch_id = 1;
    }

    /**
     * Enable testing different days one after the other
     *
     * @param string $date_sql Date string in mysql date format
     */
    public function date_instantiator($date_sql) {
        $this->workforce = new workforce($date_sql);
        $date_unix = strtotime($date_sql);
        $this->Roster = roster::read_roster_from_database($this->branch_id, $date_sql);
        $this->instance = new examine_roster($this->Roster, $date_unix, $this->branch_id, $this->workforce);
    }

    public function tearDown() {

    }

    public function testcheck_for_overlap_date_sql_provider() {
        return array(
            array("2018-10-01", array()),
            array("2018-10-04", array(0 => array(
                        'text' => '<pre>Konflikt bei Mitarbeiter Issel <br>15:00:00 bis 20:00:00 (Außendienst) <br>mit <br>08:00:00 bis 16:00:00 (Hauptapotheke)</pre>',
                        'type' => 'error',
                    )))
        );
    }

    /**
     * @dataProvider testcheck_for_overlap_date_sql_provider
     */
    public function testcheck_for_overlap($date_sql, $result) {
        $this->date_instantiator($date_sql);
        user_dialog::$Messages = array();
        $workforce = new workforce($date_sql);
        $this->assertTrue($this->instance->check_for_overlap($date_sql, $this->List_of_branch_objects, $workforce));
        $this->assertTrue($result === user_dialog::$Messages);
    }

    public function check_for_sufficient_employee_count_provider() {
        return array(
            array("2018-10-01", array()),
            array("2018-10-02", array(0 => array(
                        'text' => 'Um 08:00 Uhr sind weniger als  2 Mitarbeiter anwesend.',
                        'type' => 'warning',
                    ))),
        );
    }

    /**
     * @dataProvider check_for_sufficient_employee_count_provider
     */
    public function testcheck_for_sufficient_employee_count($date_sql, $result) {
        $this->date_instantiator($date_sql);
        user_dialog::$Messages = array();
        $this->assertTrue(FALSE !== $this->instance->check_for_sufficient_employee_count());

        $this->assertTrue($result === user_dialog::$Messages);
    }

    public function check_for_sufficient_goods_receipt_count_provider() {
        return array(
            array("2018-10-01", array()),
            array("2018-10-02", array(0 => array(
                        'text' => 'Um 08:00 Uhr ist niemand f&uuml;r den Wareneingang anwesend.',
                        'type' => 'warning',
                    ),
                )),
        );
    }

    /**
     * @dataProvider check_for_sufficient_goods_receipt_count_provider
     */
    public function testcheck_for_sufficient_goods_receipt_count($date_sql, $result) {
        $this->date_instantiator($date_sql);
        user_dialog::$Messages = array();
        $this->assertTrue(FALSE !== $this->instance->check_for_sufficient_goods_receipt_count());
        $this->assertTrue($result === user_dialog::$Messages);
    }

    public function check_for_sufficient_qualified_pharmacist_count_provider() {
        return array(
            array("2018-10-01", array()),
            array("2018-10-02", array(0 => array(
                        'text' => 'Um 08:00 Uhr ist kein Approbierter anwesend.',
                        'type' => 'error'),
                )),
        );
    }

    /**
     * @dataProvider check_for_sufficient_qualified_pharmacist_count_provider
     */
    public function testcheck_for_sufficient_qualified_pharmacist_count($date_sql, $result) {
        $this->date_instantiator($date_sql);
        user_dialog::$Messages = array();
        $this->assertTrue(FALSE !== $this->instance->check_for_sufficient_qualified_pharmacist_count());
        $this->assertTrue($result === user_dialog::$Messages);
    }

}
