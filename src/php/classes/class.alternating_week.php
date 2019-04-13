<?php

/*
 * Copyright (C) 2019 Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
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
 * This is a helper class for the principle_roster.
 * It provides functions to calculate weekly rotations.
 *
 * TODO: Write some GUI for viewing which week will be which in the future.
 *   Make it viewable with JavaScript. Make it printable perhaps.
 * TODO: Should it be possible to see emphasised (e.g. bold) the exact differences
 *   between the alternations in the principle rosters?
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class alternating_week {

    /**
     * @var DateTime $alternation_start_date is one common date, from wich all alternations start.
     * If the date is not explicitly set, The unix timestamp 0 (01.01.1970 0:00:00 UTC) will be used.
     */
    private static $alternation_start_date;

    /**
     * @var array A list of all available alternating week ids
     * The ids start at 0 and are continuous. Gaps MUST be avoided!
     */
    private static $Alternating_week_ids;

    /**
     * @var int The id of this alternation week
     */
    private $alternating_week_id;

    /**
     * @var DateTime An example date of a sunday, which starts the week with the given alternating_week_id
     *   CAVE: In this case the sunday is the first day of the week.
     */
    private $sunday_date;

    public function __construct(int $alternating_week_id) {
        if (!in_array($alternating_week_id, $this->get_alternating_week_ids())) {
            throw new Exception('This $alternating_week_id does not exist!');
        }
        $this->alternating_week_id = $alternating_week_id;
    }

    public function get_sunday_date_for_alternating_week() {
        if (!isset($this->sunday_date)) {
            $this->sunday_date = $this->calculate_sunday_date_for_alternating_week();
        }
        return $this->sunday_date;
    }

    private function calculate_sunday_date_for_alternating_week() {
        $date_object = new DateTime('this sunday');
        $today_alternating_week_id = self::get_alternating_week_for_date($date_object);
        $difference = $this->alternating_week_id - $today_alternating_week_id;
        if (0 < $difference) {
            $date_object->add(new DateInterval('P' . $difference . 'W'));
        } else {
            $date_object->sub(new DateInterval('P' . abs($difference) . 'W'));
        }
        return $date_object;
    }

    /**
     *
     * @param DateTime $date_object
     * @return int $alternating_week_id
     */
    public static function get_alternating_week_for_date(DateTime $date_object) {
        $alternation_start_date = self::get_alternation_start_date();
        $Alternating_week_ids = self::get_alternating_week_ids();
        $date_difference_in_weeks = self::date_difference_in_weeks($alternation_start_date, $date_object);
        $alternating_week_id = $date_difference_in_weeks % count($Alternating_week_ids);
        /*
         * TODO: Should we store the result in a private static Array?
         *   This could speed up the method in case, it is called multiple times for the same date.
         *   But it will slow down the method if it is queried multiple times with different dates.
         */
        return $alternating_week_id;
    }

    public static function get_min_alternating_week_id() {
        return min(self::get_alternating_week_ids());
    }

    public static function get_alternating_week_ids() {
        if (!isset(self::$Alternating_week_ids)) {
            self::read_alternating_week_ids_from_database();
        }
        return self::$Alternating_week_ids;
    }

    /**
     *
     * @return int alternating_week_id
     */
    public function get_alternating_week_id() {
        return $this->alternating_week_id;
    }

    private static function read_alternating_week_ids_from_database() {
        self::$Alternating_week_ids = array();
        $sql_query = "SELECT DISTINCT `alternation_id` AS `alternating_week_id` FROM `principle_roster` ORDER BY `alternation_id` ASC;";
        $result = database_wrapper::instance()->run($sql_query);
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            self::$Alternating_week_ids[] = $row->alternating_week_id;
        }
    }

    private static function get_alternation_start_date() {
        if (!isset(self::$alternation_start_date)) {
            self::read_alternation_start_date_from_database();
        }
        return self::$alternation_start_date;
    }

    private static function read_alternation_start_date_from_database() {
        $sql_query = "SELECT `principle_roster_start_date` FROM `pdr_self`;";
        $result = database_wrapper::instance()->run($sql_query);
        self::$alternation_start_date = new DateTime('@0'); //Just in case there is no date set yet, we want a reproducible default.
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            self::$alternation_start_date->createFromFormat('Y-m-d', $row->principle_roster_start_date);
            return TRUE;
        }
    }

    private static function date_difference_in_weeks(DateTime $first_date, DateTime $second_date) {
        $first = clone $first_date;
        $second = clone $second_date;
        if ($first > $second) {
            return self::date_difference_in_weeks($second, $first);
        }
        $first->sub(new DateInterval('P' . $first->format('N') . 'D'));
        $second->sub(new DateInterval('P' . $second->format('N') . 'D'));
        return floor($first->diff($second)->days / 7);
    }

    public static function alternations_exist() {
        if (2 > count(self::get_alternating_week_ids())) {
            return FALSE;
        }
        return TRUE;
    }

    private static function get_principle_roster_new_alternation_id() {
        $sql_query = "SELECT MAX(`alternation_id`) + 1 as `new_alternation_id` FROM `principle_roster`;";
        $result = database_wrapper::instance()->run($sql_query);
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            return $row->new_alternation_id;
        }
        return FALSE;
    }

    public static function create_alternation_copy_from_principle_roster($principle_roster_copy_from) {
        $new_alternation_id = self::get_principle_roster_new_alternation_id();
        $sql_query = "INSERT INTO `principle_roster` "
                . "(SELECT :new_alternation_id, `employee_id`, `weekday`, "
                . "`duty_start`, `duty_end`, `break_start`, `break_end`, "
                . "`comment`, `working_hours`, "
                . "`branch_id`, "
                . "`valid_from`, "
                . "`valid_until` "
                . "FROM `principle_roster` WHERE `alternation_id` = :alternation_id);";
        database_wrapper::instance()->run(
                $sql_query, array(
            'alternation_id' => $principle_roster_copy_from,
            'new_alternation_id' => $new_alternation_id,
                )
        );
        self::reorganize_ids();
    }

    public static function delete_alternation($alternation_id) {
        /*
         * TODO: Is it necessary to check if any alternation is left?
         * How does the program respond if there is none?
         * Would create_alternation_empty() help?
         */
        $sql_query = "DELETE FROM `principle_roster` WHERE `alternation_id` = :alternation_id;";
        database_wrapper::instance()->run(
                $sql_query, array(
            'alternation_id' => $alternation_id,
                )
        );
        /*
         * rewrite the static array of week ids:
         */
        self::reorganize_ids();
    }

    public static function reorganize_ids() {
        /*
         * Read fresh information about ids from the database:
         */
        self::read_alternating_week_ids_from_database();
        /*
         * Make really sure, that the ids are properly sorted:
         */
        sort(self::$Alternating_week_ids);
        /*
         * Update the alternation_ids to be continous:
         */
        $number_of_ids = count(self::$Alternating_week_ids);
        for ($index = 0; $index < $number_of_ids; $index++) {
            if ($index != self::$Alternating_week_ids[$index]) {
                $sql_query = "UPDATE `principle_roster` SET `alternation_id` = :alternation_id_new WHERE `alternation_id` = :alternation_id_old";
                database_wrapper::instance()->run($sql_query, array(
                    'alternation_id_old' => self::$Alternating_week_ids[$index],
                    'alternation_id_new' => $index,
                ));
            }
        }
        self::read_alternating_week_ids_from_database();
    }

}
