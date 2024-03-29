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
     * @var DateTime An example date of a monday, which starts the week with the given alternating_week_id
     */
    private $monday_date;

    public function __construct(int $alternating_week_id) {
        if (!in_array($alternating_week_id, $this->get_alternating_week_ids())) {
            throw new Exception('This $alternating_week_id does not exist!');
        }
        $this->alternating_week_id = $alternating_week_id;
    }

    /**
     *
     * @param DateTime $date_minimum
     * @return DateTime $this->monday_date
     * @throws Exception
     */
    public function get_monday_date_for_alternating_week(DateTime $date_minimum) {
        if (!isset($this->monday_date)) {
            $this->monday_date = $this->calculate_monday_date_for_alternating_week(clone $date_minimum);
        }
        /**
         * The date_minimum is the first date on which an alternation becomes valid.
         * There are edge cases, where this date is not a monday.
         *   e.g. if no $date_minimum is set, the `start_of_employment` of some current or former employee will be chosen.
         *     Such a $date_minimum would not have to be a monday. Therefore we convert to monday before comparison.
         */
        $date_compare = clone $date_minimum;
        if ($this->monday_date < $date_compare->modify('Monday this week')) {
            throw new Exception('A date minimum was given. But the monday_date was already set to an earlier value.');
        }
        return $this->monday_date;
    }

    private function calculate_monday_date_for_alternating_week(DateTime $date_minimum = NULL) {
        if (NULL === $date_minimum) {
            $date_object = new DateTime('Monday this week');
        } else {
            $date_object = clone $date_minimum;
            $date_object->modify('Monday this week');
        }
        $today_alternating_week_id = self::get_alternating_week_for_date($date_object);
        $difference = $this->alternating_week_id - $today_alternating_week_id;
        $number_of_alternations = count(self::get_alternating_week_ids());
        if ($difference < 0) {
            $difference += $number_of_alternations;
        }
        $date_object->add(new DateInterval('P' . $difference . 'W'));
        return $date_object;
    }

    /**
     *
     * @param DateTime $date_object
     * @return int $alternating_week_id
     * @todo Is this function broken? Does it really return the correct alternation, on days, which are not a monday?
     */
    public static function get_alternating_week_for_date(DateTime $date_object) {
        $alternation_start_date = self::get_alternation_start_date();
        $Alternating_week_ids = self::get_alternating_week_ids();
        $date_difference_in_weeks = self::date_difference_in_weeks(clone $alternation_start_date, clone $date_object);
        $alternating_week_id = $date_difference_in_weeks % count($Alternating_week_ids);
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
        $sql_query = "SELECT DISTINCT `alternating_week_id` AS `alternating_week_id` FROM `principle_roster` ORDER BY `alternating_week_id` ASC;";
        $result = database_wrapper::instance()->run($sql_query);
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            self::$Alternating_week_ids[] = $row->alternating_week_id;
        }
        if (array() === self::$Alternating_week_ids) {
            /*
             * There should at least be one alternating week id, even if there is no principle_roster setup at all.
             */
            self::$Alternating_week_ids[] = 0;
        }
    }

    private static function get_alternation_start_date() {
        if (!isset(self::$alternation_start_date)) {
            self::getAlternationStartDate();
        }
        return self::$alternation_start_date;
    }

    /**
     *
     * @return bool
     * @todo Remove principle_roster_start_date from database table pdr_self!
     */
    private static function getAlternationStartDate(): void {
        self::$alternation_start_date = new DateTime('@0'); //We want a reproducible default.
    }

    private static function date_difference_in_weeks(DateTime $first_date, DateTime $second_date) {
        $first = clone $first_date;
        $second = clone $second_date;
        if ($first > $second) {
            return self::date_difference_in_weeks(clone $second, clone $first);
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

    private static function get_principle_roster_new_alternating_week_id() {
        $sql_query = "SELECT MAX(`alternating_week_id`) + 1 as `new_alternating_week_id` FROM `principle_roster`;";
        $result = database_wrapper::instance()->run($sql_query);
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            return $row->new_alternating_week_id;
        }
        return FALSE;
    }

    public static function create_alternation_copy_from_principle_roster($principle_roster_copy_from) {
        $new_alternating_week_id = self::get_principle_roster_new_alternating_week_id();
        $sql_query = "INSERT INTO `principle_roster` "
                . "(SELECT NULL, :new_alternating_week_id, `employee_key`, `weekday`, "
                . "`duty_start`, `duty_end`, `break_start`, `break_end`, "
                . "`comment`, `working_hours`, "
                . "`branch_id` "
                . "FROM `principle_roster` WHERE `alternating_week_id` = :alternating_week_id);";
        database_wrapper::instance()->run(
                $sql_query, array(
            'alternating_week_id' => $principle_roster_copy_from,
            'new_alternating_week_id' => $new_alternating_week_id,
                )
        );
        self::reorganize_ids();
    }

    public static function delete_alternation($alternating_week_id) {
        if (!self::alternations_exist()) {
            /*
             * Prohibit deleting the last remaining alternation:
             *   This is also enforced in the GUI by build_button_principle_roster_delete()
             */
            return FALSE;
        }

        $sql_query = "DELETE FROM `principle_roster` WHERE `alternating_week_id` = :alternating_week_id;";
        database_wrapper::instance()->run(
                $sql_query, array(
            'alternating_week_id' => $alternating_week_id,
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
         * Update the alternating_week_ids to be continous:
         */
        $number_of_ids = count(self::$Alternating_week_ids);
        for ($index = 0; $index < $number_of_ids; $index++) {
            if ($index != self::$Alternating_week_ids[$index]) {
                $sql_query = "UPDATE `principle_roster` SET `alternating_week_id` = :alternating_week_id_new WHERE `alternating_week_id` = :alternating_week_id_old";
                database_wrapper::instance()->run($sql_query, array(
                    'alternating_week_id_old' => self::$Alternating_week_ids[$index],
                    'alternating_week_id_new' => $index,
                ));
            }
        }
        self::read_alternating_week_ids_from_database();
    }

    /**
     * Get a string, that is readable and easily understandable by a human.
     *
     * For example the first alternation will be A-week, followed by B-week and C-week.
     *
     * @param type $alternating_week_id
     * @return boolean|string
     */
    public static function get_human_readable_string($alternating_week_id) {
        if (!self::alternations_exist()) {
            return FALSE;
        }
        $human_readable_string = chr(65 + $alternating_week_id) . '-' . gettext('week');
        return $human_readable_string;
    }
}
