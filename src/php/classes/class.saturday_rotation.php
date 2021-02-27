<?php

/*
 * Copyright (C) 2018 Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
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
 * This class provides the option to assign a specific work shift in a rotation.
 *
 * The shift will be a specific shift on saturday.
 * The rotation will use a predefined team.
 * This class does not take into consideration the absence of employees from the teams.
 * The function examine_attendance::check_for_attendant_absentees() will however create a warning/error if an absent employee is chosen to work.
 * @deprecated since version 0.14.2 <p>We do not use teams anymore. If there is no other user, depending on this feature, it will be removed or completely rewritten in a later version.</p>
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class saturday_rotation {
    /*
     * TODO: This class needs a bit more error_handling.
     * Also a function to setup the necessary database tables might be needed.
     */

    private $target_date_object;
    private $branch_id;
    public $List_of_teams;
    public $team_id;

    public function __construct($branch_id) {
        $this->branch_id = $branch_id;
        $this->List_of_teams = $this->read_teams_from_database();
    }

    public function get_participation_team_id(DateTime $target_date_object) {
        if (6 != $target_date_object->format('N')) {
            /*
             * Until now, this function is specified to only handle saturdays.
             */
            throw new Exception("saturday_rotation->__construct only accepts saturdays as input.");
        }
        $holiday = holidays::is_holiday($target_date_object);
        if (FALSE !== $holiday) {
            /*
             * <p lang=DE>An Feiertagen findet kein Samstagsdienst statt.</p>
             */
            return FALSE;
        }
        $this->target_date_object = $target_date_object;
        $this->team_id = $this->read_participation_from_database();
        if (NULL === $this->team_id) {
            $this->team_id = $this->set_new_participation();
            if (NULL !== $this->team_id and FALSE !== $this->team_id) {
                $this->write_participation_to_database();
            }
        }
        return $this->team_id;
    }

    protected function read_participation_from_database() {
        $sql_query = 'SELECT `date`, `team_id` FROM `saturday_rotation` WHERE `date` = :date and `branch_id` = :branch_id';
        $result = database_wrapper::instance()->run($sql_query, array(
            'date' => $this->target_date_object->format('Y-m-d'),
            'branch_id' => $this->branch_id
        ));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            return $row->team_id;
        }
        return NULL;
    }

    protected function read_teams_from_database() {
        $List_of_teams = array();
        $sql_query = 'SELECT `team_id`, `employee_id` FROM `saturday_rotation_teams` WHERE `branch_id` = :branch_id';
        $result = database_wrapper::instance()->run($sql_query, array('branch_id' => $this->branch_id));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $List_of_teams[$row->team_id][] = $row->employee_id;
        }
        return $List_of_teams;
    }

    protected function set_new_participation() {
        $last_team_id = NULL;
        $sql_query = 'SELECT `date`, `team_id` FROM `saturday_rotation` WHERE `branch_id` = :branch_id and `date` <= :date ORDER BY `date` DESC LIMIT 1';
        $result = database_wrapper::instance()->run($sql_query, array(
            'branch_id' => $this->branch_id,
            'date' => $this->target_date_object->format('Y-m-d')
        ));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $last_team_id = (int) $row->team_id;
            $last_date_sql = $row->date;
            $last_date_object = new DateTime($last_date_sql);
        }
        if (NULL === $last_team_id) {
            return FALSE;
        }
        /*
         * move the pointer for the array $this->List_of_teams to the position given by $last_team_id:
         */
        reset($this->List_of_teams);
        while (key($this->List_of_teams) !== $last_team_id) {
            if (FALSE === next($this->List_of_teams)) {
                /*
                 * next() will advance the pointer forward to the "correct" key.
                 * But it might not exist in the array.
                 * Prevent an infinite loop:
                 * TODO: Choose, which team to send in that case
                 */
                error_log('Could not find $last_team_id ' . $last_team_id . ' in $this->List_of_teams');
                print_debug_variable($this);
                return FALSE;
            }
        }

        for ($date_object = (clone $last_date_object)->add(new DateInterval('P7D')); $date_object <= $this->target_date_object; $date_object->add(new DateInterval('P7D'))) {
            /*
             * move the pointer in $this->List_of_teams to next()
             * In case, we meet the end, just start at the first item again.
             */
            $holiday = holidays::is_holiday($date_object);

            if (FALSE !== $holiday) {
                /*
                 * <p lang=DE>An Feiertagen findet kein Samstagsdienst statt.</p>
                 */
                continue;
            }

            if (FALSE === next($this->List_of_teams)) {
                reset($this->List_of_teams);
            }
        }
        return key($this->List_of_teams);
    }

    protected function write_participation_to_database() {
        if (FALSE === $this->team_id) {
            return FALSE;
        }
        $sql_query = "INSERT INTO `saturday_rotation` (`date`, `team_id`, `branch_id`) VALUES (:date, :team_id, :branch_id)";
        database_wrapper::instance()->run($sql_query, array(
            'date' => $this->target_date_object->format('Y-m-d'),
            'team_id' => $this->team_id,
            'branch_id' => $this->branch_id
        ));
        $this->cleanup_database_table_saturday_rotation();
    }

    /**
     * This function cleans up old entries in the table saturday_rotation.
     *
     * It also does not allow entries in the too distant future, as these might change.
     *
     * @return void
     */
    protected function cleanup_database_table_saturday_rotation() {
        $sql_query = "DELETE FROM `saturday_rotation` WHERE `date` <= now()-interval 12 month";
        database_wrapper::instance()->run($sql_query);
        $sql_query = "DELETE FROM `saturday_rotation` WHERE `date` >= now()+interval 2 month";
        database_wrapper::instance()->run($sql_query);
    }

    public function fill_roster($team_id = NULL) {
        $Roster = array();
        $date_unix = $this->target_date_object->getTimestamp();
        if (NULL === $team_id) {
            $team_id = $this->team_id;
        }
        if (!isset($this->List_of_teams[$team_id])) {
            $Roster[$date_unix][] = new roster_item_empty($this->target_date_object->format('Y-m-d'), $this->branch_id);
            return $Roster;
        }
        $comment = "";

        /*
         * TODO: This should be saved inside the database
         */

        $duty_start = '10:00';
        $duty_end = '16:00';
        $Opening_times = roster_headcount::read_opening_hours_from_database($date_unix, $this->branch_id);
        if (NULL !== $Opening_times['day_opening_start']) {
            $duty_start = roster_item::format_time_integer_to_string($Opening_times['day_opening_start']);
        }
        if (NULL !== $Opening_times['day_opening_end']) {
            $duty_end = roster_item::format_time_integer_to_string($Opening_times['day_opening_end']);
        }
        $break_start = NULL;
        $break_end = NULL;


        foreach ($this->List_of_teams[$team_id] as $employee_id) {
            $Roster[$date_unix][] = new roster_item($this->target_date_object->format('Y-m-d'), $employee_id, $this->branch_id, $duty_start, $duty_end, $break_start, $break_end, $comment);
        }
        return $Roster;
    }

}
