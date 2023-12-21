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
 * <p lang=de>
 * TODO: Finde Überlappungen zwischen Abwesenheiten der gleichen Person. Zeige dies als Warnung oder Fehler an.
 * </p>
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
     * @var array $List_of_absence_reasons
     */
    public static $List_of_absence_reasons = array(
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
     * Retrieves a specific absence object based on the provided date and employee key.
     *
     * @param \DateTime $dateObject The date to check for absence.
     * @param int $employeeKey The employee key for whom to retrieve absence information.
     * @return \PDR\Roster\Absence|null An absence object if found, or null if no absence records are found.
     */
    public static function getSpecificAbsenceObject(\DateTime $dateObject, int $employeeKey): ?\PDR\Roster\Absence {
        $absence = null;
        $dateSqlString = $dateObject->format("Y-m-d");
        $query = "SELECT *
		FROM `absence`
		WHERE `start` <= :start AND `end` >= :end AND `employee_key` = :employee_key";
        $result = database_wrapper::instance()->run($query, array('start' => $dateSqlString, 'end' => $dateSqlString, 'employee_key' => $employeeKey));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $absence = new \PDR\Roster\Absence(
                    (int) $row->employee_key,
                    new DateTime($row->start),
                    new DateTime($row->end),
                    (int) $row->days,
                    (int) $row->reason_id,
                    (string) $row->comment,
                    (string) $row->approval,
                    (string) $row->user,
                    new DateTime($row->timestamp)
            );
        }
        return $absence;
    }

    public static function getAllAbsenceObjectsInPeriod(\DateTime $startDateObject, \DateTime $endDateObject): \PDR\Roster\AbsenceCollection {
        $startDateSqlString = $startDateObject->format("Y-m-d");
        $endDateSqlString = $endDateObject->format("Y-m-d");
        $absenceCollection = new \PDR\Roster\AbsenceCollection();
        $query = "SELECT * FROM `absence` WHERE `start` <= :end AND `end` >= :start ORDER BY `start`";
        $result = database_wrapper::instance()->run($query, array('start' => $startDateSqlString, 'end' => $endDateSqlString));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $absence = new \PDR\Roster\Absence(
                    (int) $row->employee_key,
                    new DateTime($row->start),
                    new DateTime($row->end),
                    (int) $row->days,
                    (int) $row->reason_id,
                    (string) $row->comment,
                    (string) $row->approval,
                    (string) $row->user,
                    new DateTime($row->timestamp)
            );
            $absenceCollection->addAbsence($absence);
        }
        return $absenceCollection;
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
        $command = filter_input(INPUT_POST, 'command', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
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
            $employee_key = filter_input(INPUT_POST, 'employee_key', FILTER_VALIDATE_INT);
            $beginn = filter_input(INPUT_POST, 'beginn', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $ende = filter_input(INPUT_POST, 'ende', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $reason_id = filter_input(INPUT_POST, 'reason_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $approval = filter_input(INPUT_POST, 'approval', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            if (NULL === $employee_key or FALSE === $employee_key) {
                return FALSE;
            }
            if (empty($beginn)) {
                return FALSE;
            }
            if (empty($ende)) {
                return FALSE;
            }
            if (!in_array($reason_id, self::$List_of_absence_reasons)) {
                return FALSE;
            }
            if (!in_array($approval, self::$List_of_approval_states)) {
                return FALSE;
            }
            $workforce = new workforce();
            $employee_object = $workforce->List_of_employees[$employee_key];
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
        $employee_key = $employee_object->get_employee_key();

        $days = self::calculate_employee_absence_days(clone $date_start_object, clone $date_end_object, $employee_object);
        /*
         * TODO: externalize the following part out or get the $start_date_old_sql as a parameter?
         */
        if ('replace' === filter_input(INPUT_POST, 'command', FILTER_SANITIZE_FULL_SPECIAL_CHARS)) {
            database_wrapper::instance()->beginTransaction();
            $start_date_old_sql = filter_input(INPUT_POST, 'start_old', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            PDR\Database\AbsenceDatabaseHandler::deleteAbsence($employee_key, $start_date_old_sql);
            PDR\Database\AbsenceDatabaseHandler::insertAbsence($employee_key, $date_start_object->format('Y-m-d'), $date_end_object->format('Y-m-d'), $days, $reason_id, $comment, $approval, $_SESSION['user_object']->user_name);

            if (!database_wrapper::instance()->inTransaction()) {
                return false;
            }
            database_wrapper::instance()->commit();
            return true;
        }
        PDR\Database\AbsenceDatabaseHandler::insertAbsence($employee_key, $date_start_object->format('Y-m-d'), $date_end_object->format('Y-m-d'), $days, $reason_id, $comment, $approval, $_SESSION['user_object']->user_name);
        return true;
    }

    public static function set_approval(string $approval, int $employee_key, string $start_date) {
        if (!in_array($approval, self::$List_of_approval_states)) {
            throw new Exception('Ileagal approval state');
        }
        $query = "UPDATE `absence` SET `approval` = :approval "
                . " WHERE `employee_key` = :employee_key AND `start` = :start";
        database_wrapper::instance()->run($query, array('approval' => $approval, 'employee_key' => $employee_key, 'start' => $start_date));
    }

    /**
     * @todo Move this function directly to where it belongs.
     * @return Statement
     */
    private static function delete_absence_data() {
        $employee_key = filter_input(INPUT_POST, 'employee_key', FILTER_VALIDATE_INT);
        $start_date_sql = filter_input(INPUT_POST, 'beginn', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        return PDR\Database\AbsenceDatabaseHandler::deleteAbsence($employee_key, $start_date_sql);
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
                    $date_string = $date_object->format('d.m.Y'); // Format for time (hours, minutes, seconds)
                    $message = $date_string . " " . gettext('is a holiday') . " (" . $holiday . ") " . gettext('and will not be counted.');
                    $user_dialog->add_message($message, E_USER_NOTICE);
                } else {
                    /*
                     * Only days which are neither a holiday nor a weekend/non-working-days are counted
                     */
                    $days++;
                }
            } else {
                $date_string = $date_object->format('D d.m.Y'); // Format for abbreviated weekday, day, month, and year
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
        $Years[] = (int) (new DateTime())->format('Y');
        $Years[] = max($Years) + 1;
        sort($Years);
        return array_unique($Years);
    }

    public static function get_number_of_holidays_due($employee_key, $workforce, $year) {
        $first_day_of_this_year = new DateTime("01.01." . $year);
        $last_day_of_this_year = new DateTime("31.12." . $year);
        $months_worked_in_this_year = 0;

        $employee_object = $workforce->List_of_employees[$employee_key];
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
     * @param int $employee_key
     * @param int $year <p>
     * The actual year to which the holidays belong.
     * The query looks for 'remaining holiday' in the following year.</p>
     * @return int number of remaining holidays
     */
    public static function get_number_of_remaining_holidays_submitted($employee_key, $year) {
        $sql_query = "SELECT sum(`days`) FROM `absence` "
                . "WHERE `employee_key` = :employee_key AND "
                . " `reason_id` = :reason_remain and :year = YEAR(`start`)-1";

        $result = database_wrapper::instance()->run($sql_query, array('employee_key' => $employee_key, 'year' => $year, 'reason_remain' => self::REASON_REMAINING_VACATION));
        $number_of_remaining_holidays_submitted = (int) $result->fetch(PDO::FETCH_COLUMN);
        return $number_of_remaining_holidays_submitted;
    }

    /**
     *
     * @param type $employee_key
     * @param type $year
     * @return int
     */
    public static function get_number_of_holidays_taken($employee_key, $year) {
        $sql_query = "SELECT sum(`days`) FROM `absence` "
                . "WHERE `employee_key` = :employee_key AND "
                . "reason_id = :reason_vacation and :year = YEAR(`start`)";

        $result = database_wrapper::instance()->run($sql_query, array('employee_key' => $employee_key, 'year' => $year, 'reason_vacation' => self::REASON_VACATION));
        $number_of_holidays_taken = (int) $result->fetch(PDO::FETCH_COLUMN);
        return $number_of_holidays_taken;
    }
}
