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
 * This class provides the option to assign a specific work shift in a rotation.
 *
 * The shift will be a specific shift on saturday.
 * The rotation will use a predefined team.
 * This class does not take into consideration the absence of employees from the teams.
 * The function examine_attendance::check_for_attendant_absentees() will however create a warning/error if an absent employee is chosen to work.
 *
 * @author Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class saturday_rotation {
    /*
     * TODO: This class needs a bit more error_handling.
     * Also a function to setup the necessary database tables might be needed.
     */

    protected $target_date_sql;
    protected $branch_id;
    protected $List_of_teams;
    public $team_id;

    public function __construct($date_sql, $branch_id) {
        if (6 != strftime('%u', strtotime($date_sql))) {
            /*
             * Until now, this function is specified to only handle saturdays.
             */
            throw new Exception("saturday_rotation->__construct only accepts saturdays as input.");
        }
        $this->target_date_sql = $date_sql;
        $this->branch_id = $branch_id;
        $this->List_of_teams = $this->read_teams_from_database();
        $this->team_id = $this->read_participation_from_database();
        if (NULL === $this->team_id) {
            $this->team_id = $this->set_new_participation();
        }
        if (NULL !== $this->team_id) {
            $this->write_participation_to_database();
        }
    }

    protected function read_participation_from_database() {
        global $pdo;
        $sql_query = 'SELECT `date`, `team_id` FROM `saturday_rotation` WHERE `date` = :date and `branch_id` = :branch_id';
        $statement = $pdo->prepare($sql_query);
        $statement->execute(array(
            'date' => $this->target_date_sql,
            'branch_id' => $this->branch_id
        ));
        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
            return $row->team_id;
        }
        return NULL;
    }

    protected function read_teams_from_database() {
        global $pdo;
        $List_of_teams = array();
        $sql_query = 'SELECT `team_id`, `employee_id` FROM `saturday_rotation_teams` WHERE `branch_id` = :branch_id';
        $statement = $pdo->prepare($sql_query);
        $statement->execute(array('branch_id' => $this->branch_id));
        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
            $List_of_teams[$row->team_id][] = $row->employee_id;
        }
        return $List_of_teams;
    }

    protected function set_new_participation() {
        global $pdo;
        $sql_query = 'SELECT `date`, `team_id` FROM `saturday_rotation` WHERE `branch_id` = :branch_id ORDER BY `date` DESC LIMIT 1';
        $statement = $pdo->prepare($sql_query);
        $statement->execute(array('branch_id' => $this->branch_id));
        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
            $last_team_id = (int) $row->team_id;
            $last_date_sql = $row->date;
        }
        reset($this->List_of_teams);
        while (key($this->List_of_teams) !== $last_team_id) {
            if (FALSE === next($this->List_of_teams)) {
                /*
                 * next() will advance the pointer forward to the "correct" key.
                 * But it might not exist in the array.
                 * Prevent an infinite loop:
                 * TODO: Choose, which team to send in that case
                 */
                return FALSE;
            }
        }
        for ($date_unix = strtotime($last_date_sql) + PDR_ONE_DAY_IN_SECONDS * 7; $date_unix <= strtotime($this->target_date_sql); $date_unix += PDR_ONE_DAY_IN_SECONDS * 7) {
            if (FALSE === next($this->List_of_teams)) {
                reset($this->List_of_teams);
            }
        }
        return key($this->List_of_teams);
    }

    protected function write_participation_to_database() {
        global $pdo;
        $sql_query = "INSERT INTO `saturday_rotation` (`date`, `team_id`, `branch_id`) VALUES (:date, :team_id, :branch_id)";
        $statement = $pdo->prepare($sql_query);
        $statement->execute(array(
            'date' => $this->target_date_sql,
            'team_id' => $this->team_id,
            'branch_id' => $this->branch_id
        ));
        $this->cleanup_database_table_saturday_rotation();
    }

    /*
     * This function cleans up old entries in the table saturday_rotation.
     *
     * It also does not allow entries in the too distant future, as these might change.
     *
     * @return void
     */

    protected function cleanup_database_table_saturday_rotation() {
        global $pdo;
        $sql_query = "DELETE FROM `saturday_rotation` WHERE `date` <= now()-interval 3 month";
        $statement = $pdo->prepare($sql_query);
        $statement->execute();
        $sql_query = "DELETE FROM `saturday_rotation` WHERE `date` >= now()+interval 3 month";
        $statement = $pdo->prepare($sql_query);
        $statement->execute();
    }

    public function fill_roster() {
        $Roster = array();
        $date_unix = strtotime($this->target_date_sql);
        $comment = "algorithmic rotation by " . __METHOD__ . ". Chosen rotation team: " . $this->team_id;

        /*
         * TODO: This should be saved inside the database
         */
        $duty_start = '9:00';
        $duty_end = '18:00';
        $break_start = NULL;
        $break_end = NULL;


        foreach ($this->List_of_teams[$this->team_id] as $employee_id) {
            $Roster[$date_unix][] = new roster_item($this->target_date_sql, $employee_id, $this->branch_id, $duty_start, $duty_end, $break_start, $break_end, $comment);
        }
        return $Roster;
    }

}
