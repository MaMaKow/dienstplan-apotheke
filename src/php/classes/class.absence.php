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
 * TODO: An absence could be a real object of one absence.
 * What would in that case be the name for the list of all (or some) absences?
 *
 * TODO: Finde Überlappungen zwischen Abwesenheiten der gleichen Person. Zeige dies als Warnung oder Fehler an.
 */
class absence {

    const REASON_VACATION = 1;
    const REASON_REMAINING_VACATION = 2;
    const REASON_SICKNESS = 3;
    const REASON_SICKNESS_OF_CHILD = 4;
    const REASON_TAKEN_OVERTIME = 5;
    const REASON_PAID_LEAVE_OF_ABSENCE = 6;
    const REASON_MATERNITY_LEAVE = 7;
    const REASON_PARENTAL_LEAVE = 8;

    /**
     * @todo Der Array kann perspektivisch auch ganz weg. Er wird nicht mehr relevant genutzt.
     * @var array $List_of_absence_reasons
     */
    private static $List_of_absence_reasons = array(
        self::REASON_VACATION,
        self::REASON_REMAINING_VACATION,
        self::REASON_SICKNESS,
        self::REASON_SICKNESS_OF_CHILD,
        self::REASON_TAKEN_OVERTIME,
        self::REASON_PAID_LEAVE_OF_ABSENCE,
        self::REASON_MATERNITY_LEAVE,
        self::REASON_PARENTAL_LEAVE,
    );

    public static function get_reason_string_localized(int $reason_id) {
        switch ($reason_id) {
            case self::REASON_VACATION: return gettext('vacation');
            case self::REASON_REMAINING_VACATION: return gettext('remaining vacation');
            case self::REASON_SICKNESS: return gettext('sickness');
            case self::REASON_SICKNESS_OF_CHILD: return gettext('sickness of child');
            case self::REASON_TAKEN_OVERTIME: return gettext('taken overtime');
            case self::REASON_PAID_LEAVE_OF_ABSENCE: return gettext('paid leave of absence');
            case self::REASON_MATERNITY_LEAVE: return gettext('maternity leave');
            case self::REASON_PARENTAL_LEAVE: return gettext('parental leave');
            default:
                if (isset(self::$List_of_absence_reasons[$reason_id])) {
                    throw new Exception('The given reason is defined within PHP class absence, but has no human translation yet.');
                }
                throw new OutOfRangeException('The reason with the given id is not defined: ' . $reason_id);
        }
    }

    public static $List_of_approval_states = array(
        'approved',
        'not_yet_approved',
        'disapproved',
        'changed_after_approval',
    );

    /**
     * poEdit and gettext are not willing to include words, that are not in the source files.
     * Therefore we randomly include some words here, which are necessary.
     * @todo Is there a better way? Work with constants and integer numbers?
     */
    private function gettext_fake() {
        return TRUE;
        gettext('approved');
        gettext('not_yet_approved');
        gettext('disapproved');
        gettext('changed_after_approval');
    }

    public static function insert_absence(int $employee_id, string $date_start_string, string $date_end_string, int $days, int $reason_id, string $comment, string $approval) {
        $sql_query = "INSERT INTO `absence` "
                . "(employee_id, start, end, days, reason_id, comment, user, approval) "
                . "VALUES (:employee_id, :start, :end, :days, :reason_id, :comment, :user, :approval)";
        try {
            database_wrapper::instance()->run($sql_query, array(
                'employee_id' => $employee_id,
                'start' => $date_start_string,
                'end' => $date_end_string,
                'days' => $days,
                'reason_id' => $reason_id,
                'comment' => $comment,
                'user' => $_SESSION['user_object']->user_name,
                'approval' => $approval
            ));
        } catch (Exception $exception) {
            if (database_wrapper::ERROR_MESSAGE_DUPLICATE_ENTRY_FOR_KEY === $exception->getMessage()) {
                $message = gettext("There is already an entry on this date. The data was therefore not inserted in the database.");
                $user_dialog = new user_dialog();
                $user_dialog->add_message($message, E_USER_ERROR);
            } else {
                print_debug_variable($exception);
                $message = gettext('There was an error while querying the database.')
                        . " " . gettext('Please see the error log for more details!');
                die("<p>$message</p>");
            }
        }
    }

    public static function delete_absence($employee_id, $start_date_sql) {
        $query = "DELETE FROM absence WHERE `employee_id` = :employee_id AND `start` = :start";
        $result = \database_wrapper::instance()->run($query, array('employee_id' => $employee_id, 'start' => $start_date_sql));
        return $result;
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
    public static function build_reason_input_select(int $reason_specified, string $html_id = NULL, string $html_form = NULL) {
        $html_text = "<select id='$html_id' form='$html_form' class='absence_reason_input_select' name='reason_id'>\n";
        foreach (self::$List_of_absence_reasons as $reason_id) {
            if ($reason_id === $reason_specified) {
                $html_text .= "<option value='$reason_id' selected>" . self::get_reason_string_localized($reason_id) . "</option>\n";
            } else {
                $html_text .= "<option value='$reason_id'>" . self::get_reason_string_localized($reason_id) . "</option>\n";
            }
        }
        $html_text .= "</select>\n";
        return $html_text;
    }

    /**
     *
     * @param type $approval_specified
     * @param type $html_id
     * @param type $html_form
     * @return string
     * @todo Move this into a builder class?
     */
    public static function build_approval_input_select($approval_specified, $html_id = NULL, $html_form = NULL) {
        $html_text = "<select id='$html_id' form='$html_form' class='absence_approval_input_select' name='approval'>\n";
        foreach (absence::$List_of_approval_states as $approval) {
            if ($approval == $approval_specified) {
                $html_text .= "<option value='$approval' selected>" . localization::gettext($approval) . "</option>\n";
            } else {
                $html_text .= "<option value='$approval'>" . localization::gettext($approval) . "</option>\n";
            }
        }
        $html_text .= "</select>\n";
        return $html_text;
    }

    /**
     *
     * @param string $date_sql
     * @return array $Absentees[$employee_id] = $reason_id;
     * @throws Exception
     * @throws UnexpectedValueException
     * @todo: Absentees should/could be an object. It is poorly documented, that $Absentees[$employee_id] equals/contains/holds a reason_id for the absence.
     */
    public static function read_absentees_from_database(string $date_sql) {

        $Absentees = array();
        if (is_numeric($date_sql) && (int) $date_sql == $date_sql) {
            throw new Exception("\$date_sql has to be a string! $date_sql given.");
        }
        /**
         * We define a list of still existing coworkers.
         *  There might be workers in the database, that do not work anymore, but still have vacations registered in the database.
         */
        $workforce = new workforce($date_sql);
        if (!isset($workforce)) {
            throw new UnexpectedValueException("\$workforce must be set but was '$workforce'. ");
        }
        $sql_query = "SELECT * FROM `absence` "
                . "WHERE `start` <= :start "
                . "AND `end` >= :end ;"; //Employees, whose absence has started but not ended yet.
        $result = database_wrapper::instance()->run($sql_query, array('start' => $date_sql, 'end' => $date_sql));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            if (!in_array($row->employee_id, array_keys($workforce->List_of_employees))) {
                /**
                 * Es werden nur Mitarbeiter ausgegeben, die auch noch arbeiten. Abwesenheiten von gekündigten Mitarbeiern werden ignoriert.
                 */
                continue;
            }
            $Absentees[$row->employee_id] = $row->reason_id;
        }
        return $Absentees;
    }

    public static function get_absence_data_specific(string $date_sql, int $employee_id) {
        $Absence = array();
        $query = "SELECT *
		FROM `absence`
		WHERE `start` <= :start AND `end` >= :end AND `employee_id` = :employee_id";
        $result = database_wrapper::instance()->run($query, array('start' => $date_sql, 'end' => $date_sql, 'employee_id' => $employee_id));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $Absence['employee_id'] = $row->employee_id;
            $Absence['reason_id'] = $row->reason_id;
            $Absence['comment'] = $row->comment;
            $Absence['start'] = $row->start;
            $Absence['end'] = $row->end;
            $Absence['days'] = $row->days;
            $Absence['approval'] = $row->approval;
        }
        return $Absence;
    }

    public static function get_all_absence_data_in_period($start_date_sql, $end_date_sql) {
        $Absences = array();
        $query = "SELECT *
      FROM `absence`
      WHERE `start` <= :end AND `end` >= :start ORDER BY `start`";
        $result = database_wrapper::instance()->run($query, array('start' => $start_date_sql, 'end' => $end_date_sql));
        $i = 0;
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $Absences[$i]['employee_id'] = $row->employee_id;
            $Absences[$i]['reason_id'] = $row->reason_id;
            $Absences[$i]['comment'] = $row->comment;
            $Absences[$i]['start'] = $row->start;
            $Absences[$i]['end'] = $row->end;
            $Absences[$i]['days'] = $row->days;
            $Absences[$i]['approval'] = $row->approval;
            $i++;
        }
        return $Absences;
    }

    /**
     * @todo This function probably belongs onto the page, not inside the class.
     * @global type $session
     * @return boolean
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
        if (('insert_new' === $command or 'replace' === $command)) {
            $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
            $beginn = filter_input(INPUT_POST, 'beginn', FILTER_SANITIZE_STRING);
            $ende = filter_input(INPUT_POST, 'ende', FILTER_SANITIZE_STRING);
            $reason_id = filter_input(INPUT_POST, 'reason_id', FILTER_SANITIZE_STRING);
            $approval = filter_input(INPUT_POST, 'approval', FILTER_SANITIZE_STRING);
            $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING);
            if (NULL === $employee_id or FALSE === $employee_id) {
                return FALSE;
            }
            if (empty($beginn)) {
                return FALSE;
            }
            if (empty($ende)) {
                return FALSE;
            }
            if (!array_key_exists($reason_id, self::$List_of_absence_reasons)) {
                return FALSE;
            }
            if (!in_array($approval, self::$List_of_approval_states)) {
                return FALSE;
            }
            $workforce = new workforce();
            $employee_object = $workforce->List_of_employees[$employee_id];
            self::write_absence_data_to_database($employee_object, $beginn, $ende, $reason_id, $comment, $approval);
        }
    }

    /**
     *
     * @param \employee $employee_object
     * @param string $beginn
     * @param string $ende
     * @param string $reason_id
     * @param string $comment
     * @param string $approval
     * @todo Move this somewhere else?
     */
    private static function write_absence_data_to_database(\employee $employee_object, string $beginn, string $ende, int $reason_id, string $comment = NULL, string $approval = 'approved') {
        $date_start_object = new DateTime($beginn);
        $date_end_object = new DateTime($ende);
        $employee_id = $employee_object->employee_id;

        $days = self::calculate_employee_absence_days(clone $date_start_object, clone $date_end_object, $employee_object);
        database_wrapper::instance()->beginTransaction();
        /*
         * TODO: externalize the following part out or get the $start_date_old_sql as a parameter?
         */
        if ('replace' === filter_input(INPUT_POST, 'command', FILTER_SANITIZE_STRING)) {
            $start_date_old_sql = filter_input(INPUT_POST, 'start_old', FILTER_SANITIZE_STRING);
            self::delete_absence($employee_id, $start_date_old_sql);
        }
        self::insert_absence($employee_id, $date_start_object->format('Y-m-d'), $date_end_object->format('Y-m-d'), $days, $reason_id, $comment, $approval);
        database_wrapper::instance()->commit();
    }

    public static function set_approval(string $approval, int $employee_id, string $start_date) {
        if (!in_array($approval, self::$List_of_approval_states)) {
            throw new Exception('Ileagal approval state');
        }
        $query = "UPDATE `absence` SET `approval` = :approval "
                . " WHERE `employee_id` = :employee_id AND `start` = :start";
        database_wrapper::instance()->run($query, array('approval' => $approval, 'employee_id' => $employee_id, 'start' => $start_date));
    }

    /**
     * @todo Move this function directly to where it belongs.
     * @return Statement
     */
    private static function delete_absence_data() {
        $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
        $start_date_sql = filter_input(INPUT_POST, 'beginn', FILTER_SANITIZE_STRING);
        return self::delete_absence($employee_id, $start_date_sql);
    }

    public static function calculate_employee_absence_days(DateTime $date_start_object, DateTime $date_end_object, employee $employee_object) {
        $user_dialog = new user_dialog();
        $days = 0;
        for ($date_object = clone $date_start_object; $date_object <= $date_end_object; $date_object->add(new DateInterval('P1D'))) {
            $current_week_day_number = $date_object->format('N');
            if (!roster::is_empty_roster_day_array($employee_object->get_principle_roster_on_date($date_object))
                    or ( 0 === $employee_object->working_week_days and $current_week_day_number < 6)) {
                /*
                 * The employee normally does not work on this day.
                 * This might be saturdays and sundays.
                 * But it might as well be normal for the employee to just work on Tuesday and Thursday.
                 *
                 * Or if no principle roster is existent (0 === $employee_object->working_week_days) then Saturday and Sunday are excluded.
                 */
                $holiday = holidays::is_holiday($date_object);
                if (FALSE !== $holiday) {
                    /*
                     * Holidays are not counted
                     * Also we inform the user about not counting those days.
                     */
                    $date_string = strftime('%x', $date_object->getTimestamp());
                    $message = $date_string . " " . gettext('is a holiday') . " (" . $holiday . ") " . gettext('and will not be counted.');
                    $user_dialog->add_message($message, E_USER_NOTICE);
                } else {
                    /*
                     * Only days which are neither a holiday nor a weekend/non-working-days are counted
                     */
                    $days++;
                }
            } else {
                $date_string = strftime('%a %x', $date_object->getTimestamp());
                $message = sprintf(gettext('%1$s is not a working day for %2$s and will not be counted.'), $date_string, $employee_object->full_name);
                $user_dialog->add_message($message, E_USER_NOTICE);
            }
        }
        return $days;
    }

    /**
     * @todo Move this somewhere else. Or make it use only absence data. The roser class could use its own version.
     * @return array $Years <p>An array containing all the years, that are stored with at least one day in the `Dienstplan`table.
     * </p>
     */
    public static function get_rostering_years() {
        $Years = array();
        $sql_query = "SELECT DISTINCT YEAR(`Datum`) AS `year` FROM `Dienstplan` ORDER BY `Datum`";
        $result = database_wrapper::instance()->run($sql_query);
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $Years[] = $row->year;
        }
        $sql_query = "SELECT DISTINCT YEAR(`Datum`) AS `year` FROM `Stunden` ORDER BY `Datum`";
        $result = database_wrapper::instance()->run($sql_query);
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $Years[] = $row->year;
        }
        $sql_query = "SELECT DISTINCT YEAR(`start`) AS `year` FROM `absence` ORDER BY `start`";
        $result = database_wrapper::instance()->run($sql_query);
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $Years[] = $row->year;
        }
        if (array() === $Years) {
            $Years = array(0 => (int) (new DateTime())->format('Y'));
        }
        $Years[] = max($Years) + 1;
        sort($Years);
        return array_unique($Years);
    }

    public static function get_number_of_holidays_due($employee_id, $workforce, $year) {
        $first_day_of_this_year = new DateTime("01.01." . $year);
        $last_day_of_this_year = new DateTime("31.12." . $year);
        $months_worked_in_this_year = 0;

        $employee_object = $workforce->List_of_employees[$employee_id];
        $number_of_holidays_principle = $employee_object->holidays;
        $number_of_working_week_days = $employee_object->working_week_days;
        $number_of_holidays_due = $number_of_holidays_principle;
        if (NULL !== $employee_object->start_of_employment) {
            $start_of_employment = new DateTime($employee_object->start_of_employment);
        } else {
            $start_of_employment = $first_day_of_this_year;
        }
        if (NULL !== $employee_object->end_of_employment) {
            $end_of_employment = new DateTime($employee_object->end_of_employment);
        } else {
            $end_of_employment = $last_day_of_this_year;
        }

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
            if ($start_of_employment > $start_of_month or $end_of_employment < $end_of_month) {
                $number_of_holidays_due -= $number_of_holidays_principle / 12;
            } else {
                $months_worked_in_this_year++;
            }
            /*
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
                . " `reason_id` = :reason_remain and :year = YEAR(`start`)-1";

        $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id, 'year' => $year, 'reason_remain' => self::REASON_REMAINING_VACATION));
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
                . "reason_id = :reason_vacation and :year = YEAR(`start`)";

        $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id, 'year' => $year, 'reason_vacation' => self::REASON_VACATION));
        $number_of_holidays_taken = (int) $result->fetch(PDO::FETCH_COLUMN);
        return $number_of_holidays_taken;
    }

}
