<?php

/*
 * Copyright (C) 2023 Mandelkow
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

namespace PDR\Utility;

/**
 * Utility class for handling absences, absence reasons, and user input related to absences.
 *
 * Class AbsenceUtility
 * @package PDR\Utility
 *
 * <p lang=de>
 * TODO: Finde Überlappungen zwischen Abwesenheiten der gleichen Person. Zeige dies als Warnung oder Fehler an.
 * </p>
 * @author Mandelkow
 */
class AbsenceUtility {

    // Constants representing absence reasons
    const REASON_VACATION = 1;
    const REASON_REMAINING_VACATION = 2;
    const REASON_SICKNESS = 3;
    const REASON_SICKNESS_OF_CHILD = 4;
    const REASON_TAKEN_OVERTIME = 5;
    const REASON_PAID_LEAVE_OF_ABSENCE = 6;
    const REASON_MATERNITY_LEAVE = 7;
    const REASON_PARENTAL_LEAVE = 8;

    /**
     * @var array $ListOfAbsenceReasons
     */
    public static $ListOfAbsenceReasons = array(
        self::REASON_VACATION,
        self::REASON_REMAINING_VACATION,
        self::REASON_SICKNESS,
        self::REASON_SICKNESS_OF_CHILD,
        self::REASON_TAKEN_OVERTIME,
        self::REASON_PAID_LEAVE_OF_ABSENCE,
        self::REASON_MATERNITY_LEAVE,
        self::REASON_PARENTAL_LEAVE,
    );

    /**
     * Get localized string for a given absence reason.
     *
     * @param int $reasonId The ID of the absence reason.
     * @return string Localized reason string.
     * @throws \Exception if the reason is not defined.
     */
    public static function getReasonStringLocalized(int $reasonId): string {
        switch ($reasonId) {
            case self::REASON_VACATION: return gettext('vacation');
            case self::REASON_REMAINING_VACATION: return gettext('remaining vacation');
            case self::REASON_SICKNESS: return gettext('sickness');
            case self::REASON_SICKNESS_OF_CHILD: return gettext('sickness of child');
            case self::REASON_TAKEN_OVERTIME: return gettext('taken overtime');
            case self::REASON_PAID_LEAVE_OF_ABSENCE: return gettext('paid leave of absence');
            case self::REASON_MATERNITY_LEAVE: return gettext('maternity leave');
            case self::REASON_PARENTAL_LEAVE: return gettext('parental leave');
            default:
                if (isset(self::$ListOfAbsenceReasons[$reasonId])) {
                    throw new \Exception('The given reason is defined within PHP class absence, but has no human translation yet.');
                }
                throw new \OutOfRangeException('The reason with the given id is not defined: ' . $reasonId);
        }
    }

    public static $ListOfApprovalStates = array(
        'approved',
        'not_yet_approved',
        'disapproved',
        'changed_after_approval',
    );

    /**
     * Handle user input for creating, editing, or deleting absence entries.
     *
     * @global type $session
     * @return boolean Returns true if the operation is successful, otherwise false.
     * @throws Exception Throws an exception if an unknown command is encountered.
     */
    public static function handleUserInput(): bool {
        global $session;

        // Check if the user has the privilege to create absence entries.
        if (!$session->user_has_privilege('create_absence')) {
            return false;
        }

        // Get the command from the POST data.
        $command = filter_input(INPUT_POST, 'command', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (empty($command)) {
            return false;
        }

        // Handling different commands.
        if ('delete' === $command) {
            // Delete an existing absence entry.
            $employeeKey = filter_input(INPUT_POST, 'employee_key', FILTER_VALIDATE_INT);
            $startDateSql = filter_input(INPUT_POST, 'beginn', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            return \PDR\Database\AbsenceDatabaseHandler::deleteAbsence($employeeKey, $startDateSql);
        }

        // Create new entries or edit existing ones.
        if ('insert_new' === $command || 'replace' === $command) {
            $employeeKey = filter_input(INPUT_POST, 'employee_key', FILTER_VALIDATE_INT);
            $beginn = filter_input(INPUT_POST, 'beginn', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $ende = filter_input(INPUT_POST, 'ende', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $reasonId = filter_input(INPUT_POST, 'reason_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $approval = filter_input(INPUT_POST, 'approval', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            // Check for required values.
            if (null === $employeeKey || false === $employeeKey || empty($beginn) || empty($ende) || !in_array($reasonId, self::$ListOfAbsenceReasons) || !in_array($approval, self::$ListOfApprovalStates)) {
                return false;
            }

            // Instantiate the workforce and get the employee object.
            $workforce = new \workforce();
            $employeeObject = $workforce->List_of_employees[$employeeKey];

            // Call the method to write absence data to the database.
            return self::writeAbsenceDataToDatabase($employeeObject, $beginn, $ende, $reasonId, $comment, $approval);
        }

        // Throw an exception for unknown commands.
        throw new \Exception("Unknown command " . htmlentities($command) . " in handleUserInput()");
    }

    /**
     * Write absence data to the database for a given employee.
     *
     * @param \employee $employeeObject The employee for whom to write absence data.
     * @param string $beginn The start date of the absence period.
     * @param string $ende The end date of the absence period.
     * @param int $reasonId The reason code for the absence.
     * @param string|null $comment An optional comment for the absence.
     * @param string $approval The approval status for the absence (default: 'approved').
     * @return bool Returns true if the operation is successful, otherwise false.
     */
    private static function writeAbsenceDataToDatabase(\employee $employeeObject, string $beginn, string $ende, int $reasonId, string $comment = null, string $approval = 'approved'): bool {
        // Create DateTime objects for the start and end dates of the absence.
        $dateStartObject = new \DateTime($beginn);
        $dateEndObject = new \DateTime($ende);

        // Get the employee key.
        $employeeKey = $employeeObject->get_employee_key();

        // Calculate the number of absence days using the provided method.
        $days = self::calculateEmployeeAbsenceDays(clone $dateStartObject, clone $dateEndObject, $employeeObject);

        // Check if the operation is a replacement of an existing absence.
        if ('replace' === filter_input(INPUT_POST, 'command', FILTER_SANITIZE_FULL_SPECIAL_CHARS)) {
            // Begin a database transaction.
            \database_wrapper::instance()->beginTransaction();

            // Get the old start date from the input parameters.
            $startDateOldSql = filter_input(INPUT_POST, 'start_old', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            // Delete the existing absence record.
            \PDR\Database\AbsenceDatabaseHandler::deleteAbsence($employeeKey, $startDateOldSql);

            // Insert the new absence record.
            \PDR\Database\AbsenceDatabaseHandler::insertAbsence(
                    $employeeKey,
                    $dateStartObject->format('Y-m-d'),
                    $dateEndObject->format('Y-m-d'),
                    $days,
                    $reasonId,
                    $comment,
                    $approval,
                    $_SESSION['user_object']->user_name
            );

            // Check if the transaction is successful.
            if (!\database_wrapper::instance()->inTransaction()) {
                return false;
            }

            // Commit the transaction.
            \database_wrapper::instance()->commit();
            return true;
        }

        // If not a replacement, directly insert the absence record.
        \PDR\Database\AbsenceDatabaseHandler::insertAbsence(
                $employeeKey,
                $dateStartObject->format('Y-m-d'),
                $dateEndObject->format('Y-m-d'),
                $days,
                $reasonId,
                $comment,
                $approval,
                $_SESSION['user_object']->user_name
        );

        // Return true to indicate a successful operation.
        return true;
    }

    /**
     * Calculate the number of absence days for an employee within a specified date range.
     *
     * @param DateTime $dateStartObject The start date of the absence period.
     * @param DateTime $dateEndObject The end date of the absence period.
     * @param employee $employeeObject The employee for whom to calculate absence days.
     * @return int The total number of absence days within the specified range.
     */
    public static function calculateEmployeeAbsenceDays(\DateTime $dateStartObject, \DateTime $dateEndObject, \employee $employeeObject): int {
        // Create a user dialog instance to handle messages.
        $userDialog = new \user_dialog();

        // Initialize the count of absence days.
        $days = 0;

        // Loop through each day in the specified date range.
        for ($dateObject = clone $dateStartObject; $dateObject <= $dateEndObject; $dateObject->add(new \DateInterval('P1D'))) {
            // Get the weekday number (1 for Monday, 7 for Sunday).
            $currentWeekDayNumber = $dateObject->format('N');

            // Check if the employee normally works on the current day.
            if (!\roster::is_empty_roster_day_array($employeeObject->get_principle_roster_on_date($dateObject))
                    or (0 === $employeeObject->working_week_days and $currentWeekDayNumber < 6)) {
                /*
                 * The employee normally does not work on this day.
                 * This might be Saturdays and Sundays, or a specific non-working day.
                 */

                // Check if the current day is a holiday.
                $holiday = \holidays::is_holiday($dateObject);
                if (FALSE !== $holiday) {
                    /*
                     * Holidays are not counted.
                     * Inform the user about not counting those days.
                     */
                    $dateString = $dateObject->format('d.m.Y');
                    $message = $dateString . " " . gettext('is a holiday') . " (" . $holiday . ") " . gettext('and will not be counted.');
                    $userDialog->add_message($message, E_USER_NOTICE);
                } else {
                    /*
                     * Only days which are neither a holiday nor a weekend/non-working-days are counted.
                     */
                    $days++;
                }
            } else {
                // The current day is a working day for the employee.
                $dateString = $dateObject->format('D d.m.Y');
                $message = sprintf(gettext('%1$s is not a working day for %2$s and will not be counted.'), $dateString, $employeeObject->full_name);
                $userDialog->add_message($message, E_USER_NOTICE);
            }
        }

        // Return the total count of absence days.
        return $days;
    }

    /**
     * @todo Move this somewhere else. Or make it use only absence data. The roster class could use its own version.
     * @return array $Years <p>An array containing all the years, that are stored with at least one day in the `Dienstplan`table.
     * </p>
     */
    public static function getRosteringYears(): array {
        $Years = array();
        $sqlQueryDienstplan = "SELECT DISTINCT YEAR(`Datum`) AS `year` FROM `Dienstplan` ORDER BY `Datum`";
        $resultRoster = \database_wrapper::instance()->run($sqlQueryDienstplan);
        while ($row = $resultRoster->fetch(\PDO::FETCH_OBJ)) {
            $Years[] = $row->year;
        }
        $sqlQueryHours = "SELECT DISTINCT YEAR(`Datum`) AS `year` FROM `Stunden` ORDER BY `Datum`";
        $resultHours = \database_wrapper::instance()->run($sqlQueryHours);
        while ($row = $resultHours->fetch(\PDO::FETCH_OBJ)) {
            $Years[] = $row->year;
        }
        $sqlQueryAbsence = "SELECT DISTINCT YEAR(`start`) AS `year` FROM `absence` ORDER BY `start`";
        $resultAbsence = \database_wrapper::instance()->run($sqlQueryAbsence);
        while ($row = $resultAbsence->fetch(\PDO::FETCH_OBJ)) {
            $Years[] = $row->year;
        }
        $Years[] = (int) (new \DateTime())->format('Y');
        $Years[] = max($Years) + 1;
        sort($Years);
        return array_unique($Years);
    }

    /**
     * Calculate the number of holidays due for an employee in a given year.
     *
     * @param int $employeeKey The unique identifier for the employee.
     * @param Workforce $workforce The workforce object containing employee data.
     * @param int $year The year for which to calculate holidays.
     * @return int The number of holidays due.
     */
    public static function getNumberOfHolidaysDue($employeeKey, $workforce, $year): int {
        $firstDayOfThisYear = new \DateTime("01.01." . $year);
        $lastDayOfThisYear = new \DateTime("31.12." . $year);
        $monthsWorkedInThisYear = 0;

        $employeeObject = $workforce->List_of_employees[$employeeKey];
        $numberOfHolidaysPrinciple = $employeeObject->holidays;
        $numberOfWorkingWeekDays = $employeeObject->working_week_days;
        $numberOfHolidaysDue = $numberOfHolidaysPrinciple;
        if (NULL !== $employeeObject->start_of_employment) {
            $startOfEmployment = new \DateTime($employeeObject->start_of_employment);
        } else {
            $startOfEmployment = $firstDayOfThisYear;
        }
        if (NULL !== $employeeObject->end_of_employment) {
            $endOfEmployment = new \DateTime($employeeObject->end_of_employment);
        } else {
            $endOfEmployment = $lastDayOfThisYear;
        }

        $interval = new \DateInterval('P1M');
        for ($startOfMonth = $firstDayOfThisYear; $startOfMonth <= $lastDayOfThisYear; $startOfMonth->add($interval)) {
            $endOfMonth = (new \DateTime($startOfMonth->format('Y-m-d')))->modify('last day of');
            /*
             * Bundesrahmentarifvertrag für Apothekenmitarbeiter
             * gültig ab 1. Januar 2015
             * § 11 Erholungsurlaub
             * Für jeden vollen Monat der Betriebszugehörigkeit hat der Mitarbeiter Anspruch auf 1/12 des tariflichen Jahresurlaubs.
             * Besteht das Arbeitsverhältnis länger als sechs Monate, darf der gesetzliche Mindesturlaub von 24 Werktagen nicht unterschritten werden.
             */
            if ($startOfEmployment > $startOfMonth or $endOfEmployment < $endOfMonth) {
                $numberOfHolidaysDue -= $numberOfHolidaysPrinciple / 12;
            } else {
                $monthsWorkedInThisYear++;
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
        if ($monthsWorkedInThisYear >= 6) {
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
            $legalMinimumHolidays = 24 * ($numberOfWorkingWeekDays / 6);
            $numberOfHolidaysDue = max($legalMinimumHolidays, $numberOfHolidaysDue);
        }
        /*
         * Mindesturlaubsgesetz für Arbeitnehmer (Bundesurlaubsgesetz)
         * § 5 Teilurlaub
         * (2) Bruchteile von Urlaubstagen, die mindestens einen halben Tag ergeben, sind auf volle Urlaubstage aufzurunden.
         */
        return round($numberOfHolidaysDue, 0);
    }
}
