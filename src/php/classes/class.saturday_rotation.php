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
        $this->adjust_team_ids();
        $List_of_teams = array();
        $sql_query = 'SELECT `team_id`, `employee_id` FROM `saturday_rotation_teams` WHERE `branch_id` = :branch_id ORDER BY `team_id`';
        $result = database_wrapper::instance()->run($sql_query, array('branch_id' => $this->branch_id));
        /**
         * <p lang=de>
         * Die Team_ids sollten bei 0 beginnen und fortlaufend ohne Lücken sein.
         * Daher werden die Team_ids hier bei jedem Lesen aufgeräumt.
         * </p>
         */
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

    public function build_input_row_employee_select(int $roster_employee_id = null, int $team_id, int $roster_row_iterator = null, $session) {
        $workforce = new workforce();
        $option_set_select_disabled_for_unprivileged_user = "";
        if (!$session->user_has_privilege(sessions::PRIVILEGE_CREATE_ROSTER)) {
            $option_set_select_disabled_for_unprivileged_user = "disabled";
        }

        $roster_input_row_employee_select = "<select "
                . " $option_set_select_disabled_for_unprivileged_user "
                . " name=Saturday_rotation_team[" . $team_id . "][" . $roster_row_iterator . "][employee_id] "
                . " data-team_id='$team_id' "
                . " onChange='this.form.submit();' "
                . ">";
        /**
         * The empty option is necessary to enable the deletion of employees from the roster:
         */
        $roster_input_row_employee_select .= "<option value=''>&nbsp;</option>";
        foreach ($workforce->List_of_employees as $employee_id => $employee_object) {
            if ($roster_employee_id == $employee_id and NULL !== $roster_employee_id) {
                $roster_input_row_employee_select .= "<option value=$employee_id selected>" . $employee_id . " " . $employee_object->last_name . "</option>";
            } else {
                $roster_input_row_employee_select .= "<option value=$employee_id>" . $employee_id . " " . $employee_object->last_name . "</option>";
            }
        }
        if (NULL !== $roster_employee_id and!isset($workforce->List_of_employees[$roster_employee_id]->last_name)) {
            /*
             * Unknown employee, probably someone from the past.
             */
            $roster_input_row_employee_select .= "<option value=$roster_employee_id selected>" . $roster_employee_id . " " . gettext("Unknown employee") . "</option>";
        }

        $roster_input_row_employee_select .= "</select>\n";
        return $roster_input_row_employee_select;
    }

    public function buildSaturdayRotationTeamsAddEmployee($team_id, $branch_id, $session) {
        $saturday_rotation = new saturday_rotation($branch_id);
        return $saturday_rotation->build_input_row_employee_select(null, $team_id, null, $session);
    }

    public function buildSaturdayRotationTeamsAddTeam(int $team_id, int $branch_id, DateTime $saturday_date_object, $session) {
        $saturday_rotation = new saturday_rotation($branch_id);
        if (!array_key_exists($team_id, $saturday_rotation->List_of_teams)) {
            /**
             * <p>Wir erstellen gerade ein neues Team.
             *  Es wird ein leeres Team in den Array eingefÃ¼gt.</p>
             */
            $saturday_rotation->List_of_teams[$team_id] = array(null);
        }
        $team_array = $saturday_rotation->List_of_teams[$team_id];

        $buildSaturdayRotationTeamsAddTeamHtml = "";
        $buildSaturdayRotationTeamsAddTeamHtml .= "<tr data-team_id='$team_id' data-branch_id='$branch_id'>";
        $buildSaturdayRotationTeamsAddTeamHtml .= "<td>" . $saturday_date_object->format('d.m.Y') . "</td>";
        $buildSaturdayRotationTeamsAddTeamHtml .= "<td>";
        $buildSaturdayRotationTeamsAddTeamHtml .= "<span class='team_id_span'>" . $team_id . "</span>&nbsp;";
        if ($session->user_has_privilege(sessions::PRIVILEGE_CREATE_ROSTER)) {

            $buildSaturdayRotationTeamsAddTeamHtml .= "<a class='saturdayRotationTeamsRemoveTeamLink' onclick='saturdayRotationTeamsRemoveTeam(" . $team_id . ", " . $branch_id . " )'>";
            $buildSaturdayRotationTeamsAddTeamHtml .= "-" . gettext('Remove team');
            $buildSaturdayRotationTeamsAddTeamHtml .= "</a>";
        }
        $buildSaturdayRotationTeamsAddTeamHtml .= "</td>";
        $buildSaturdayRotationTeamsAddTeamHtml .= "<td>";
        $buildSaturdayRotationTeamsAddTeamHtml .= "<form method='POST'>";
        $buildSaturdayRotationTeamsAddTeamHtml .= "<input type='hidden' name='mandant' value='$branch_id'>";

        $roster_row_iterator = 0;
        foreach ($team_array as $employee_id) {

            $buildSaturdayRotationTeamsAddTeamHtml .= "<span>";
            $buildSaturdayRotationTeamsAddTeamHtml .= $saturday_rotation->build_input_row_employee_select($employee_id, $team_id, $roster_row_iterator, $session);
            $buildSaturdayRotationTeamsAddTeamHtml .= "</span>";

            $roster_row_iterator++;
        }

        if ($session->user_has_privilege(sessions::PRIVILEGE_CREATE_ROSTER)) {

            $buildSaturdayRotationTeamsAddTeamHtml .= "<span>";
            $buildSaturdayRotationTeamsAddTeamHtml .= "<a onclick='saturdayRotationTeamsAddEmployee(this);' >";
            $buildSaturdayRotationTeamsAddTeamHtml .= "+" . gettext('Add another employee') . "</a>";
            $buildSaturdayRotationTeamsAddTeamHtml .= "</span>";
        }
        $buildSaturdayRotationTeamsAddTeamHtml .= "</form>";
        $buildSaturdayRotationTeamsAddTeamHtml .= "</td>";
        $buildSaturdayRotationTeamsAddTeamHtml .= "</tr>";



        return $buildSaturdayRotationTeamsAddTeamHtml;
    }

    public function update_team_to_database(int $branch_id = null, int $team_id = null, $team_array = array()) {
        database_wrapper::instance()->beginTransaction();
        $sql_query_remove = "DELETE FROM `saturday_rotation_teams` WHERE `branch_id` = :branch_id AND `team_id` = :team_id";
        database_wrapper::instance()->run($sql_query_remove, array(
            'team_id' => $team_id,
            'branch_id' => $branch_id,
        ));
        foreach ($team_array as $roster_row) {
            $employee_id = $roster_row["employee_id"];
            if ("" == $employee_id) {
                continue;
            }
            $sql_query_insert = "INSERT INTO `saturday_rotation_teams` (`branch_id`, `team_id`, `employee_id`) VALUES (:branch_id, :team_id, :employee_id)";
            database_wrapper::instance()->run($sql_query_insert, array(
                'branch_id' => $branch_id,
                'team_id' => $team_id,
                'employee_id' => $employee_id,
            ));
        }
        database_wrapper::instance()->commit();
        /*
         * Finally read the new data from the database into the current memory:
         */
        $this->List_of_teams = $this->read_teams_from_database();
    }

    public function remove_team_from_database(int $branch_id, int $team_id) {
        $sql_query_remove = "DELETE FROM `saturday_rotation_teams` WHERE `branch_id` = :branch_id AND `team_id` = :team_id";
        database_wrapper::instance()->run($sql_query_remove, array(
            'team_id' => $team_id,
            'branch_id' => $branch_id,
        ));
        /*
         * Finally read the new data from the database into the current memory:
         */
        $this->List_of_teams = $this->read_teams_from_database();
    }

    public function get_maximum_team_id() {
        if (array() === $this->List_of_teams) {
            /**
             * If exactly one team exists. Than the maximum will be "0".
             * In case there is no team at all, we will write "-1".
             * This is important for the JavaScript function
             * "saturdayRotationTeamsAddTeam" to differentiate between these two
             * situations.
             */
            return -1;
        }
        return max(array_keys($this->List_of_teams));
    }

    private function adjust_team_ids() {
        $sql_query_count = 'SELECT count(DISTINCT `team_id`) AS `number_of_teams`, max(`team_id`) AS `max_team_id` FROM `saturday_rotation_teams` WHERE `branch_id` = :branch_id';
        $result_count = database_wrapper::instance()->run($sql_query_count, array('branch_id' => $this->branch_id));
        while ($row = $result_count->fetch(PDO::FETCH_OBJ)) {
            if ($row->number_of_teams == ($row->max_team_id + 1)) {
                /**
                 * It seems, that the order of the team_ids is correct and continuous.
                 * There is nothing to do here.
                 */
                return null;
            }
        }
        $sql_query_update = "UPDATE `saturday_rotation_teams` SET `team_id` = :team_id_new WHERE `team_id` = :team_id_old";
        $prepared_statement_update = database_wrapper::instance()->prepare($sql_query_update);
        $sql_query_select = 'SELECT DISTINCT `team_id` FROM `saturday_rotation_teams` WHERE `branch_id` = :branch_id ORDER BY `team_id`';
        $result_select = database_wrapper::instance()->run($sql_query_select, array('branch_id' => $this->branch_id));
        /**
         * <p lang=de>
         * Die Team_ids sollten bei 0 beginnen und fortlaufend ohne Lücken sein.
         * Daher werden die Team_ids hier bei jedem Lesen aufgeräumt.
         * </p>
         */
        $team_id_should = 0;
        while ($row = $result_select->fetch(PDO::FETCH_OBJ)) {
            if ($row->team_id != $team_id_should) {
                error_log("setting the old team_id " . $row->team_id . " with the new id $team_id_should");
                $prepared_statement_update->bindValue("team_id_old", $row->team_id);
                $prepared_statement_update->bindValue("team_id_new", $team_id_should);
                $prepared_statement_update->execute();
            }
            $team_id_should++;
        }
    }

}
