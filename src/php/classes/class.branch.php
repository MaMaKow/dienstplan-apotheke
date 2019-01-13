<?php

/*
 * Copyright (C) 2017 Mandelkow
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
 * Pharmacies and connected branches
 *
 * <p>
 * In German law one Pharmacist is allowed to own one main pharmacy and three branches.
 * Each branch is led by one pharmacist branch manager.
 * § 2 Abs. 4 und 5 Gesetz über das Apothekenwesen (Apothekengesetz - ApoG)
 * (4) Die Erlaubnis zum Betrieb mehrerer öffentlicher Apotheken ist auf Antrag zu erteilen, wenn
 * 1. der Antragsteller die Voraussetzungen nach den Absätzen 1 bis 3 für jede der beantragten Apotheken erfüllt und
 * 2. die von ihm zu betreibende Apotheke und die von ihm zu betreibenden Filialapotheken innerhalb desselben Kreises
 *     oder derselben kreisfreien Stadt oder in einander benachbarten Kreisen oder kreisfreien Städten liegen.
 *
 * (5) Für den Betrieb mehrerer öffentlicher Apotheken gelten die Vorschriften dieses Gesetzes mit folgenden Maßgaben entsprechend:
 * 1. Der Betreiber hat eine der Apotheken (Hauptapotheke) persönlich zu führen.
 * 2. Für jede weitere Apotheke (Filialapotheke) hat der Betreiber schriftlich einen Apotheker als Verantwortlichen zu benennen,
 *     der die Verpflichtungen zu erfüllen hat, wie sie in diesem Gesetz und in der Apothekenbetriebsordnung für Apothekenleiter festgelegt sind.
 * </p>
 *
 * It is possible to create unlimited theoretical branches like "Field service", "Compounding" or "Hospital" to pricisely define, who is working where and for which task at which time.
 *
 * @author Martin Mandelkow
 */
class branch {

    private static $List_of_branch_objects;
    public $branch_id;
    public $name;
    public $short_name;
    public $address;
    public $manager;
    public $Opening_times;
    public $PEP;

    /**
     * read the branch data from the database
     * @return array An array ob objects of the class branch
     */
    public static function read_branches_from_database() {
        $sql_query = 'SELECT * FROM `branch`;';
        $result = database_wrapper::instance()->run($sql_query);
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            if ($row->short_name != "") {
                $Branches[$row->branch_id] = new branch();
                $Branches[$row->branch_id]->branch_id = $row->branch_id;
                $Branches[$row->branch_id]->name = $row->name;
                $Branches[$row->branch_id]->short_name = $row->short_name;
                $Branches[$row->branch_id]->address = $row->address;
                $Branches[$row->branch_id]->manager = $row->manager;
                $Branches[$row->branch_id]->PEP = $row->PEP;
                $Branches[$row->branch_id]->read_opening_times_from_database();
            }
        }

        if (empty(array_keys($Branches))) {
            branch::redirect_to_input_form_on_missing_setup();
        } else {
            self::$List_of_branch_objects = $Branches;
            return $Branches;
        }
    }

    public static function get_list_of_branch_objects() {
        if (empty(self::$List_of_branch_objects)) {
            self::read_branches_from_database();
        }
        return self::$List_of_branch_objects;
    }

    public static function update_list_of_branch_objects() {
        self::read_branches_from_database();
        return self::$List_of_branch_objects;
    }

    public static function exists($branch_id) {
        if (in_array($branch_id, array_keys(self::$List_of_branch_objects))) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Read the opening times from the database.
     *
     * Currently only one opening time per weekday is possible.
     * There are pharmacies, which open from:
     * 08:00 - 12:00
     * and
     * 13:30 - 18:00
     * This case might be supported in a later version.
     *
     * @return void
     */
    private function read_opening_times_from_database() {
        $this->Opening_times = array();
        for ($weekday = 1; $weekday <= 7; $weekday++) {

            $sql_query = "SELECT * FROM `opening_times` WHERE `branch_id` = :branch_id AND `weekday` = :weekday";
            $result = database_wrapper::instance()->run($sql_query, array('branch_id' => $this->branch_id, 'weekday' => $weekday));
            $row = $result->fetch(PDO::FETCH_OBJ);
            $day_opening_start = isset($row->start) ? $row->start : NULL;
            $day_opening_end = isset($row->end) ? $row->end : NULL;
            $this->Opening_times[$weekday]['day_opening_start'] = roster_item::format_time_string_correct($day_opening_start);
            $this->Opening_times[$weekday]['day_opening_end'] = roster_item::format_time_string_correct($day_opening_end);
        }
    }

    /**
     * Redirect the browser to the branch management form
     *
     * If no branch is setup yet (e.g, directly after installation, or if all the branches have been deleted)
     * then the browser is redirected to the branch management form
     * @return void
     */
    private static function redirect_to_input_form_on_missing_setup() {
        if (!isset($_SESSION['user_object']->employee_id)) {
            /*
             * If we are not logged in yet, then there is no sense in redirecting.
             */
            return FALSE;
        }
        $script_name = filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING);
        if (!in_array(basename($script_name), array('branch-management.php'))) {
            $location = PDR_HTTP_SERVER_APPLICATION_PATH . 'src/php/pages/branch-management.php';
            header('Location:' . $location);
            die('<p><a href="' . $location . '>Please configure at least one branch first!</a></p>');
        }
    }

}
