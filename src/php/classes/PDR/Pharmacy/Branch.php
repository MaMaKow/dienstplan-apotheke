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

    private $branchId;
    private $name;
    private $shortName;
    private $address;
    private $manager;
    private $openingTimes;
    private $pep;

    public function __construct(int $branchId = null) {
        if (null !== $branchId) {
            $this->readBranchDataFromDatabase($branchId);
            return;
        }
        /**
         * In case, the object is constructed with the branch_id NULL, we build an empty branch.
         * This is used in branch-management.php to create a new branch.
         */
        $this->branchId = null;
        $this->name = gettext("create new branch");
        $this->shortName = null;
        $this->address = null;
        $this->manager = null;
        $this->openingTimes = array();
        $this->pep = null;
        $this->openingTimes = array(
            1 => array('day_opening_start' => "", 'day_opening_end' => ""),
            2 => array('day_opening_start' => "", 'day_opening_end' => ""),
            3 => array('day_opening_start' => "", 'day_opening_end' => ""),
            4 => array('day_opening_start' => "", 'day_opening_end' => ""),
            5 => array('day_opening_start' => "", 'day_opening_end' => ""),
            6 => array('day_opening_start' => "", 'day_opening_end' => ""),
            7 => array('day_opening_start' => "", 'day_opening_end' => ""),
        );
    }

    public function __get($name) {
        error_log("Trying to access $name in branch object.");
        throw new \Exception("Thou shall not access the variables directly. Use the getter methods!");
    }

    public function getBranchId(): ?int {
        return $this->branchId;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function getShortName(): ?string {
        return $this->shortName;
    }

    public function getAddress(): ?string {
        return $this->address;
    }

    public function getManager(): ?string {
        return $this->manager;
    }

    public function getOpeningTimes(): ?array {
        return $this->openingTimes;
    }

    public function getPep(): ?int {
        return $this->pep;
    }

    /**
     * read the branch data from the database
     * @return array An array ob objects of the class branch
     */
    private function readBranchDataFromDatabase(int $branchId) {

        $sqlQuery = 'SELECT * FROM `branch` WHERE `branch_id` = :branch_id;';
        $result = \database_wrapper::instance()->run($sqlQuery, array('branch_id' => $branchId));
        while ($row = $result->fetch(\PDO::FETCH_OBJ)) {
            $this->branchId = (int) $row->branch_id;
            $this->name = $row->name;
            $this->shortName = $row->short_name;
            $this->address = $row->address;
            $this->manager = $row->manager;
            $this->pep = (int) $row->PEP;
            $this->readOpeningTimesFromDatabase();
            if ("" === $this->shortName) {
                $location = \PDR_HTTP_SERVER_APPLICATION_PATH . 'src/php/pages/branch-management.php';
                $message = \sprintf(\gettext('A short name for the branch should be <a href="%1$s">configured.</a>'), $location);
                $userDialog = new \user_dialog();
                $userDialog->add_message($message, \E_USER_NOTICE, TRUE);
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
    private function readOpeningTimesFromDatabase(): void {
        $this->openingTimes = array();
        for ($weekday = 1; $weekday <= 7; $weekday++) {

            $sqlQuery = "SELECT * FROM `opening_times` WHERE `branch_id` = :branch_id AND `weekday` = :weekday";
            $result = \database_wrapper::instance()->run($sqlQuery, array('branch_id' => $this->branchId, 'weekday' => $weekday));
            $row = $result->fetch(\PDO::FETCH_OBJ);
            $dayOpeningStart = isset($row->start) ? $row->start : NULL;
            $dayOpeningEnd = isset($row->end) ? $row->end : NULL;
            $this->openingTimes[$weekday]['day_opening_start'] = \roster_item::format_time_string_correct($dayOpeningStart);
            $this->openingTimes[$weekday]['day_opening_end'] = \roster_item::format_time_string_correct($dayOpeningEnd);
        }
    }

    public function encodeToJson(): string {
        $jsonArray = array();
        $jsonArray['Opening_times'] = $this->openingTimes;
        $jsonArray['branch_id'] = $this->branchId;
        $jsonArray['name'] = $this->name;
        $jsonArray['short_name'] = $this->shortName;
        $jsonArray['address'] = $this->address;
        $jsonArray['PEP'] = $this->pep;
        $jsonArray['manager'] = $this->manager;
        return json_encode($jsonArray);
    }
}
