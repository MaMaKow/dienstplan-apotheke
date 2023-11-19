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

namespace PDR\Pharmacy;

class Branch {

    private $branch_id;
    private $name;
    private $short_name;
    private $address;
    private $manager;
    private $Opening_times;
    private $PEP;

    public function __construct(int $branch_id = null) {
        if (null !== $branch_id) {
            $this->read_branch_data_from_database($branch_id);
            return;
        }
        /**
         * In case, the object is constructed with the branch_id NULL, we build an empty branch.
         * This is used in branch-management.php to create a new branch.
         */
        $this->branch_id = "";
        $this->name = gettext("create new branch");
        $this->short_name = "";
        $this->address = "";
        $this->manager = "";
        $this->Opening_times = array();
        $this->PEP = "";
    }

    /**
     * @param string $variable_name
     * @return misc Value of the variable.
     * @todo Get rid of this! Make one function for each variable.
     */
    public function __get($variable_name) {
        return $this->$variable_name;
    }

    /**
     * read the branch data from the database
     * @return array An array ob objects of the class branch
     */
    private function read_branch_data_from_database($branch_id) {

        $sql_query = 'SELECT * FROM `branch` WHERE `branch_id` = :branch_id;';
        $result = \database_wrapper::instance()->run($sql_query, array('branch_id' => $branch_id));
        while ($row = $result->fetch(\PDO::FETCH_OBJ)) {
            $this->branch_id = $row->branch_id;
            $this->name = $row->name;
            $this->short_name = $row->short_name;
            $this->address = $row->address;
            $this->manager = $row->manager;
            $this->PEP = $row->PEP;
            $this->read_opening_times_from_database();
            if ("" === $this->short_name) {
                $location = \PDR_HTTP_SERVER_APPLICATION_PATH . 'src/php/pages/branch-management.php';
                $message = \sprintf(\gettext('A short name for the branch should be <a href="%1$s">configured.</a>'), $location);
                $user_dialog = new \user_dialog();
                $user_dialog->add_message($message, \E_USER_NOTICE, TRUE);
            }
        }
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
     * Weekday 1 is Monday, weekday 7 is Sunday
     *
     * @return void
     */
    private function read_opening_times_from_database() {
        $this->Opening_times = array();
        for ($weekday = 1; $weekday <= 7; $weekday++) {

            $sql_query = "SELECT * FROM `opening_times` WHERE `branch_id` = :branch_id AND `weekday` = :weekday";
            $result = \database_wrapper::instance()->run($sql_query, array('branch_id' => $this->branch_id, 'weekday' => $weekday));
            $row = $result->fetch(\PDO::FETCH_OBJ);
            $day_opening_start = isset($row->start) ? $row->start : NULL;
            $day_opening_end = isset($row->end) ? $row->end : NULL;
            $this->Opening_times[$weekday]['day_opening_start'] = \roster_item::format_time_string_correct($day_opening_start);
            $this->Opening_times[$weekday]['day_opening_end'] = \roster_item::format_time_string_correct($day_opening_end);
        }
    }
}
