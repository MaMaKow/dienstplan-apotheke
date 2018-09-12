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

class absence {
    /*
     * This function gets a list of absent employees
     *
     * @param date_sql string date in the format 'Y-m-d' a unix date is accepted. This might be removed in the future
     *
     * @return array $Absentees array(employee_id => reason)
     */

    public static $List_of_absence_reasons = array(
        'vacation',
        'remaining holiday',
        'sickness',
        'sickness of child',
        'unpaid leave of absence',
        'paid leave of absence',
        'parental leave',
        'maternity leave',
    );
    public static $List_of_approval_states = array(
        'approved',
        'not_yet_approved',
        'disapproved',
        'changed_after_approval',
    );

    /**
     * poEdit and gettext are not willing to include words, that are not in the source files.
     * Therefore we randomly include some words here, which are necessary.
     */
    private function gettext_fake() {
        return TRUE;
        gettext('vacation');
        gettext('remaining holiday');
        gettext('sickness');
        gettext('sickness of child');
        gettext('unpaid leave of absence');
        gettext('paid leave of absence');
        gettext('parental leave');
        gettext('maternity leave');
        gettext('approved');
        gettext('not_yet_approved');
        gettext('disapproved');
        gettext('changed_after_approval');
    }

    /**
     * Build a select element for easy input of absence entries.
     *
     * The list contains reasons of absence (like [de_DE] "Urlaub" or "Krankheit").
     * The values are stored in the database in a SET column accepting only predefined english terms.
     * Those terms can also be found in absence::$List_of_absence_reasons.
     *
     * @return string $html_text HTML datalist element.
     */
    public static function build_reason_input_select($reason_specified, $html_id = NULL, $html_form = NULL) {
        $html_text = "<select id='$html_id' form='$html_form' class='absence_reason_input_select' name='reason'>\n";
        foreach (absence::$List_of_absence_reasons as $reason) {
            if ($reason == $reason_specified) {
                $html_text .= "<option value='$reason' selected>" . pdr_gettext($reason) . "</option>\n";
            } else {
                $html_text .= "<option value='$reason'>" . pdr_gettext($reason) . "</option>\n";
            }
        }
        $html_text .= "</select>\n";
        return $html_text;
    }

    public static function build_approval_input_select($approval_specified, $html_id = NULL, $html_form = NULL) {
        $html_text = "<select id='$html_id' form='$html_form' class='absence_approval_input_select' name='approval'>\n";
        foreach (absence::$List_of_approval_states as $approval) {
            if ($approval == $approval_specified) {
                $html_text .= "<option value='$approval' selected>" . pdr_gettext($approval) . "</option>\n";
            } else {
                $html_text .= "<option value='$approval'>" . pdr_gettext($approval) . "</option>\n";
            }
        }
        $html_text .= "</select>\n";
        return $html_text;
    }

    /**
     *
     * @global object $workforce
     * @param string $date_sql
     * @return array $Absentees[$employee_id] = $reason;
     * @throws Exception
     * @throws UnexpectedValueException
     */
    public static function read_absentees_from_database($date_sql) {

        $Absentees = array();
        global $workforce;
        if (is_numeric($date_sql) && (int) $date_sql == $date_sql) {
            throw new Exception("\$date_sql has to be a string! $date_sql given.");
        }
        /*
         * We define a list of still existing coworkers. There might be workers in the database, that do not work anymore, but still have vacations registered in the database.
         */
        if (!isset($workforce)) {
            throw new UnexpectedValueException("\$workforce must be set but was '$workforce'. ");
        }
        list($in_placeholder, $IN_employees_list) = database_wrapper::create_placeholder_for_mysql_IN_function(array_keys($workforce->List_of_employees), TRUE);

        $sql_query = "SELECT * FROM `absence` "
                . "WHERE `start` <= :start "
                . "AND `end` >= :end "
                . "AND `employee_id` IN ($in_placeholder)"; //Employees, whose absence has started but not ended yet.
        $result = database_wrapper::instance()->run($sql_query, array_merge($IN_employees_list, array('start' => $date_sql, 'end' => $date_sql)));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $Absentees[$row->employee_id] = $row->reason;
        }
        return $Absentees;
    }

    public static function get_absence_data_specific($date_sql, $employee_id) {
        $query = "SELECT *
		FROM `absence`
		WHERE `start` <= :start AND `end` >= :end AND `employee_id` = :employee_id";
        $result = database_wrapper::instance()->run($query, array('start' => $date_sql, 'end' => $date_sql, 'employee_id' => $employee_id));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $Absence['employee_id'] = $row->employee_id;
            $Absence['reason'] = $row->reason;
            $Absence['comment'] = $row->comment;
            $Absence['start'] = $row->start;
            $Absence['end'] = $row->end;
            $Absence['approval'] = $row->approval;
        }
        return $Absence;
    }

    /*
      function get_all_absence_data_in_period($start_date_sql, $end_date_sql) {
      $query = "SELECT *
      FROM `absence`
      WHERE `start` <= :start AND `end` >= :end";
      $result = database_wrapper::instance()->run($query, array('start'=>$start_date_sql,'end'=>$end_date_sql));
      while ($row = $result->fetch(PDO::FETCH_OBJ)) {
      $Absences[]['employee_id'] = $row->employee_id;
      $Absences[]['reason'] = $row->reason;
      $Absences[]['start'] = $row->start;
      $Absences[]['end'] = $row->end;
      }
      return $Absences;
      }
     */

    public static function handle_user_input() {
        global $session;
        if (!$session->user_has_privilege('create_absence')) {
            return FALSE;
        }
        $command = filter_input(INPUT_POST, 'command', FILTER_SANITIZE_STRING);
        /*
         * Deleting existing entries:
         */
        if ('delete' === $command) {
            self::delete_absence_data();
        }
        /*
         * We create new entries or edit old entries. (Empty values are not accepted.)
         */
        if (('insert_new' === $command or 'replace' === $command)
                and $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT)
                and $beginn = filter_input(INPUT_POST, 'beginn', FILTER_SANITIZE_STRING)
                and $ende = filter_input(INPUT_POST, 'ende', FILTER_SANITIZE_STRING)
                and $reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_STRING)
                and $approval = filter_input(INPUT_POST, 'approval', FILTER_SANITIZE_STRING)
        ) {
            /*
             * $comment is allowed to be empty.
             * Therefore it is not part of the if():
             */
            $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING);
            self::write_absence_data_to_database($employee_id, $beginn, $ende, $reason, $comment, $approval);
        }
    }

    private static function write_absence_data_to_database($employee_id, $beginn, $ende, $reason, $comment = NULL, $approval = 'approved') {
        if ($employee_id === FALSE) {
            return FALSE;
        }
        $days = self::calculate_absence_days($beginn, $ende);
        if ('replace' === filter_input(INPUT_POST, 'command', FILTER_SANITIZE_STRING)) {
            $start_old = filter_input(INPUT_POST, 'start_old', FILTER_SANITIZE_STRING);
            $sql_query = "DELETE FROM `absence` WHERE `employee_id` = :employee_id AND `start` = :start";
            database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id, 'start' => $start_old));
        }
        $sql_query = "INSERT INTO `absence` "
                . "(employee_id, start, end, days, reason, comment, user, approval) "
                . "VALUES (:employee_id, :start, :end, :days, :reason, :comment, :user, :approval)";
        try {
            database_wrapper::instance()->run($sql_query, array(
                'employee_id' => $employee_id,
                'start' => $beginn,
                'end' => $ende,
                'days' => $days,
                'reason' => $reason,
                'comment' => $comment,
                'user' => $_SESSION['user_name'],
                'approval' => $approval
            ));
        } catch (Exception $exception) {
            if (database_wrapper::ERROR_MESSAGE_DUPLICATE_ENTRY_FOR_KEY === $exception->getMessage()) {
                $message = gettext("There is already an entry on this date. The data was therefore not inserted in the database.");
                user_dialog::add_message($message, E_USER_ERROR);
            } else {
                print_debug_variable($exception);
                $message = gettext('There was an error while querying the database.')
                        . " " . gettext('Please see the error log for more details!');
                die("<p>$message</p>");
            }
        }
    }

    private static function delete_absence_data() {
        $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
        $start = filter_input(INPUT_POST, 'beginn', FILTER_SANITIZE_STRING);
        $sql_query = "DELETE FROM `absence` WHERE `employee_id` = :employee_id AND `start` = :start";
        $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id, 'start' => $start));
        return $result;
    }

    public static function calculate_absence_days($start_date_string, $end_date_string) {
        $days = 0;
        for ($date_unix = strtotime($start_date_string); $date_unix <= strtotime($end_date_string); $date_unix += PDR_ONE_DAY_IN_SECONDS) {
            if (date('w', $date_unix) != 6 and date('w', $date_unix) != 0) {
                /*
                 * Saturday and Sunday are not counted
                 * TODO: Instead we should look into the principle roster!
                 * If there is no entry there, then we will not count the day.
                 * This is espacially relevent to the 450 € workers.
                 * The are only there for two days a week.
                 */
                $holiday = holidays::is_holiday($date_unix);
                if (FALSE !== $holiday) {
                    /*
                     * Holidays are not counted
                     * Also we inform the user about not counting those days.
                     */
                    $date_string = strftime('%x', $date_unix);
                    $message = $date_string . " " . gettext('is a holiday') . " (" . $holiday . ") " . gettext('and will not be counted.');
                    user_dialog::add_message($message, E_USER_NOTICE);
                } else {
                    /*
                     * Only days which are neither a holiday nor a weekend are counted
                     */
                    $days++;
                }
            }
        }
        return $days;
    }

    /**
     *
     * @return array $Years <p>An array containing all the years, that are stored with at least one day in the `Dienstplan`table.
     *
     * </p>
     */
    private static function get_rostering_month_names() {
        $Months = array();
        for ($i = 1; $i <= 12; $i++) {
            $timestamp = mktime(0, 0, 0, $i, 1);
            $Months[date('n', $timestamp)] = strftime('%B', $timestamp);
        }
        return $Months;
    }

    private static function get_rostering_years() {
        $Years = array();
        $sql_query = "SELECT DISTINCT YEAR(`Datum`) AS `year` FROM `Dienstplan` ORDER BY `Datum`";
        $result = database_wrapper::instance()->run($sql_query);
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $Years[] = $row->year;
        }
        if (array() === $Years) {
            $Years = array(0 => (int) (new DateTime())->format('Y'));
        }
        $Years[] = max($Years) + 1;
        return $Years;
    }

    public static function build_html_select_year($current_year) {
        $Years = self::get_rostering_years();
        $html_select_year = "";
        $html_select_year .= "<form id='select_year' class='inline_form' method=post>";
        $html_select_year .= "<select name=year class='large' onchange=this.form.submit()>";
        foreach ($Years as $year_number) {
            $html_select_year .= "<option value='$year_number'";
            if ($year_number == $current_year) {
                $html_select_year .= " SELECTED ";
            }
            $html_select_year .= ">$year_number</option>\n";
        }
        $html_select_year .= "</select>";
        $html_select_year .= "</form>";
        return $html_select_year;
    }

    public static function build_html_select_month($current_month) {
        $Months = self::get_rostering_month_names();
        $html_select_month = "";
        $html_select_month .= "<form id='select_month' class='inline_form' method=post>";
        $html_select_month .= "<select name=month_number class='large' onchange=this.form.submit()>";
        foreach ($Months as $month_number => $month_name) {
            $html_select_month .= "<option value='$month_number'";
            if ($month_number == $current_month) {
                $html_select_month .= " SELECTED ";
            }
            $html_select_month .= ">$month_name</option>\n";
        }
        $html_select_month .= "</select>";
        $html_select_month .= "</form>";
        return $html_select_month;
    }

    public static function get_number_of_holidays_due($employee_id, $workforce, $year) {
        $months_worked_in_this_year = 0;

        $employee_object = $workforce->List_of_employees[$employee_id];
        $number_of_holidays_principle = $employee_object->holidays;
        $number_of_working_week_days = $employee_object->working_week_days;
        $number_of_holidays_due = $number_of_holidays_principle;
        if (NULL !== $employee_object->start_of_employment) {
            $start_of_employment = new DateTime($employee_object->start_of_employment);
        } else {
            $start_of_employment = NULL;
        }
        if (NULL !== $employee_object->end_of_employment) {
            $end_of_employment = new DateTime($employee_object->end_of_employment);
        } else {
            $end_of_employment = NULL;
        }

        $first_day_of_this_year = new DateTime("01.01." . $year);
        $last_day_of_this_year = new DateTime("31.12." . $year);
        $interval = new DateInterval('P1M');
        for ($start_of_month = $first_day_of_this_year; $start_of_month <= $last_day_of_this_year; $start_of_month->add($interval)) {
            $end_of_month = (new DateTime($start_of_month->format('Y-m-d')))->modify('last day of');
            /*
             * Bundesrahmentarifvertrag für Apothekenmitarbeiter
             * gültig ab 1. Januar 2015
             * § 11 Erholungsurlaub
             * Für jeden vollen Monat der Betriebszugehörigkeit hat der Mitarbeiter Anspruch auf 1/12 des tariflichen Jahresurlaubs.
             * Besteht das Arbeitsverhältnis länger als sechs Monate, darf der gesetzliche Mindesturlaub von 24 Werktagen nicht unterschritten werden.
             */
            if ($start_of_employment > $start_of_month or ( NULL !== $end_of_employment and $end_of_employment < $end_of_month)) {
                $number_of_holidays_due -= $number_of_holidays_principle / 12;
            } else {
                $months_worked_in_this_year++;
            }
            if ($months_worked_in_this_year >= 6) {
                /*
                 * Mindesturlaubsgesetz für Arbeitnehmer (Bundesurlaubsgesetz)
                 * § 3 Dauer des Urlaubs
                 * (1) Der Urlaub beträgt jährlich mindestens 24 Werktage.
                 * This seems to be the definite minimum whenever at least 6 months have passed in the year
                 *
                 * § 4 Wartezeit
                 * Der volle Urlaubsanspruch wird erstmalig nach sechsmonatigem Bestehen des Arbeitsverhältnisses erworben.
                 *
                 * § 5 Teilurlaub
                 * (1) Anspruch auf ein Zwölftel des Jahresurlaubs für jeden vollen Monat des Bestehens des Arbeitsverhältnisses hat der Arbeitnehmer
                 * c) wenn er nach erfüllter Wartezeit in der ersten Hälfte eines Kalenderjahrs aus dem Arbeitsverhältnis ausscheidet.
                 */
                $legal_minimum_holidays = 24 * ($number_of_working_week_days / 6);
                $number_of_holidays_due = max($legal_minimum_holidays, $number_of_holidays_due);
            }
            /*
             * TODO:
             * It is possible to also reduce on Elternzeit:
             * Gesetz zum Elterngeld und zur Elternzeit (Bundeselterngeld- und Elternzeitgesetz - BEEG)
             * § 17 Abs. 1
             * Der Arbeitgeber kann den Erholungsurlaub, der dem Arbeitnehmer oder der Arbeitnehmerin für das Urlaubsjahr zusteht,
             * für jeden vollen Kalendermonat der Elternzeit um ein Zwölftel kürzen.
             * Dies gilt nicht, wenn der Arbeitnehmer oder die Arbeitnehmerin während der Elternzeit bei seinem oder ihrem Arbeitgeber Teilzeitarbeit leistet.
             *
             * This is facultative and to be decided by the employer.
             */
        }
        /*
         * Mindesturlaubsgesetz für Arbeitnehmer (Bundesurlaubsgesetz)
         * § 5 Teilurlaub
         * (2) Bruchteile von Urlaubstagen, die mindestens einen halben Tag ergeben, sind auf volle Urlaubstage aufzurunden.
         */
        return round($number_of_holidays_due, 0);
    }

    /**
     * Read the number of remaining holidays, which have been submitted already in the following year from the database.
     *
     * @param int $employee_id
     * @param int $year <p>
     * The actual year to which the holidays belong.
     * The query looks for 'remaining holiday' in the following year.</p>
     * @return int number of remaining holidays
     */
    public static function get_number_of_remaining_holidays_submitted($employee_id, $year) {
        $sql_query = "SELECT sum(`days`) FROM `absence` "
                . "WHERE `employee_id` = :employee_id AND "
                . "'remaining holiday' = `reason` and :year = YEAR(`start`)-1";

        $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id, 'year' => $year));
        $number_of_remaining_holidays_submitted = (int) $result->fetch(PDO::FETCH_COLUMN);
        return $number_of_remaining_holidays_submitted;
    }

    /**
     *
     * @param type $employee_id
     * @param type $year
     * @return int
     */
    public static function get_number_of_holidays_taken($employee_id, $year) {
        $sql_query = "SELECT sum(`days`) FROM `absence` "
                . "WHERE `employee_id` = :employee_id AND "
                . "`reason` = 'vacation' and :year = YEAR(`start`)";

        $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id, 'year' => $year));
        $number_of_holidays_taken = (int) $result->fetch(PDO::FETCH_COLUMN);
        return $number_of_holidays_taken;
    }

}
