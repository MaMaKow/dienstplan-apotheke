<?php

/*
 * Copyright (C) 2021 Mandelkow
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

/**
 * <p lang=de>
 *   Ein Notdienst ist eine Öffnung der Apotheke nachts, an Wochenenden oder Feiertagen.
 *   Die Apotheke bleibt bis zum folgenden Tag um 08:00 Uht geöffnet.
 * </p>
 *
 * @author Mandelkow
 */

namespace PDR\Roster;

class EmergencyService {

    private $date_object;
    private $employee_key;
    private $branch_id;

    public function __construct(\DateTime $date_object) {
        $sql_query = "SELECT * FROM `Notdienst` WHERE `Datum` = :date";
        $result = \database_wrapper::instance()->run($sql_query, array('date' => $date_object->format('Y-m-d')));
        while ($row = $result->fetch(\PDO::FETCH_OBJ)) {
            $this->date_object = $date_object;
            $this->employee_key = $row->employee_key;
            $this->branch_id = $row->Mandant;
            return true;
        }
        throw new \Exception("We don't have the pharmacy emergency service today.");
    }

    public function get_branch_id(): int {
        return $this->branch_id;
    }

    public function get_employee_short_descriptor() {
        $workforce = new \workforce();
        return $workforce->get_employee_short_descriptor($this->employee_key);
    }

    public function get_employee_name(): string {
        $workforce = new \workforce($this->date_object->format('Y-m-d'));
        if (is_integer($this->employee_key)) {
            $employee_name = $workforce->get_employee_last_name($this->employee_key);
            return $employee_name;
        }
        return '???';
    }

    public function get_branch_name_short(): string {
        $network_of_branch_offices = new \PDR\Pharmacy\NetworkOfBranchOffices();
        $List_of_branch_objects = $network_of_branch_offices->get_list_of_branch_objects();
        if (is_integer($this->branch_id) and isset($List_of_branch_objects[$this->branch_id])) {
            $branch_name_short = $List_of_branch_objects[$this->branch_id]->getShortName();
            return $branch_name_short;
        }
        return '???';
    }

    public function get_date_object(): \DateTime {
        return $this->date_object;
    }

    public static function is_our_service_day(\DateTime $date_object): bool {
        $sql_query = "SELECT * FROM `Notdienst` WHERE `Datum` = :date";
        $result = \database_wrapper::instance()->run($sql_query, array('date' => $date_object->format('Y-m-d')));
        while ($row = $result->fetch(\PDO::FETCH_OBJ)) {
            return true;
        }
        return false;
    }
}
