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

namespace PDR\Database;

/**
 * Class AbsenceDatabaseHandler
 * Handles database operations for absence-related data.
 *
 * @package PDR\Database
 * @author Mandelkow
 */
class AbsenceDatabaseHandler {

    /**
     * Inserts a new absence entry into the database.
     *
     * @param int $employeeKey
     * @param string $dateStartString
     * @param string $dateEndString
     * @param int $days
     * @param int $reasonId
     * @param string $comment
     * @param string|null $approval
     * @param string $userName
     */
    public static function insertAbsence(int $employeeKey, string $dateStartString, string $dateEndString, int $days, int $reasonId, string $comment, ?string $approval, string $userName): void {
        $sqlQuery = "INSERT INTO `absence` "
                . "(employee_key, start, end, days, reason_id, comment, user, approval) "
                . "VALUES (:employee_key, :start, :end, :days, :reason_id, :comment, :user, :approval)";
        try {
            \database_wrapper::instance()->run($sqlQuery, array(
                'employee_key' => $employeeKey,
                'start' => $dateStartString,
                'end' => $dateEndString,
                'days' => $days,
                'reason_id' => $reasonId,
                'comment' => $comment,
                'user' => $userName,
                'approval' => (!is_null($approval)) ? $approval : "not_yet_approved"
            ));
        } catch (\Exception $exception) { //function handle_exceptions(Exception $exception) may throw any kind of Exception at us.
            if (\database_wrapper::ERROR_MESSAGE_DUPLICATE_ENTRY_FOR_KEY === $exception->getMessage()) {
                $userDialog = new \user_dialog();
                $message = gettext("There is already an entry on this date. The data was therefore not inserted in the database.");
                $userDialog->add_message($message, \E_USER_ERROR);
            } elseif ('23000' == $exception->getCode() and 1062 === $exception->errorInfo[1]) {
                $userDialog = new \user_dialog();
                $message = gettext("There is already an entry on this date. The data was therefore not inserted in the database.");
                $userDialog->add_message($message, \E_USER_ERROR);
                $message = gettext("The transaction was rolled back.");
                $userDialog->add_message($message, \E_USER_NOTICE);
            } else {
                \PDR\Utility\GeneralUtility::printDebugVariable($exception);
                $message = gettext('There was an error while querying the database.')
                        . " " . gettext('Please see the error log for more details!');
                die("<p>$message</p>");
            }
        }
    }

    /**
     * Delete absence entries for a specific employee and start date.
     *
     * This method deletes records from the 'absence' table where the given employee key
     * and start date match. The absence records represent periods when the employee is
     * not available due to various reasons.
     *
     * @param int    $employeeKey   The unique identifier for the employee.
     * @param string $startDateSql  The start date of the absence period in SQL format.
     *
     * @return bool                 The boolean result of the delete operation.
     *                              Note: Exceptions may be thrown during the execution, and they are handled
     *                              by the database_wrapper::run method.
     */
    public static function deleteAbsence(int $employeeKey, string $startDateSql): bool {
        $query = "DELETE FROM absence WHERE `employee_key` = :employee_key AND `start` = :start";
        $result = \database_wrapper::instance()->run($query, array('employee_key' => $employeeKey, 'start' => $startDateSql));
        $success = '00000' === $result->errorCode();
        return $success;
    }

    /**
     *
     * @param string $dateSql
     * @return array $Absentees[$employee_key] = $reason_id;
     * @throws Exception
     * @throws UnexpectedValueException
     */
    public static function readAbsenteesOnDate(string $dateSql): \PDR\Roster\AbsenceCollection {

        $absenceCollection = new \PDR\Roster\AbsenceCollection();
        if (is_numeric($dateSql) && (int) $dateSql == $dateSql) {
            throw new \Exception("\$date_sql has to be a string! $dateSql given.");
        }
        /**
         * We define a list of still existing coworkers.
         *  There might be workers in the database, that do not work anymore, but still have vacations registered in the database.
         */
        $workforce = new \workforce($dateSql);
        if (!isset($workforce)) {
            throw new \UnexpectedValueException("\$workforce must be set but was '$workforce'. ");
        }
        /**
         * Employees, whose absence has started but not ended yet.
         */
        $sqlQuery = "SELECT * FROM `absence` WHERE `start` <= :start AND `end` >= :end;";
        $result = \database_wrapper::instance()->run($sqlQuery, array('start' => $dateSql, 'end' => $dateSql));
        while ($row = $result->fetch(\PDO::FETCH_OBJ)) {
            if (!$workforce->employee_exists($row->employee_key)) {
                /**
                 * <p lang=de>
                 * Es werden nur Mitarbeiter ausgegeben, die auch noch arbeiten.
                 *  Abwesenheiten von gekündigten Mitarbeiern werden ignoriert.</p>
                 */
                continue;
            }
            $absence = new \PDR\Roster\Absence(
                    (int) $row->employee_key,
                    new \DateTime($row->start),
                    new \DateTime($row->end),
                    (int) $row->days,
                    (int) $row->reason_id,
                    (string) $row->comment,
                    (string) $row->approval,
                    (string) $row->user,
                    new \DateTime($row->timestamp)
            );
            $absenceCollection->addAbsence($absence);
        }
        return $absenceCollection;
    }

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
        $result = \database_wrapper::instance()->run($query, array('start' => $dateSqlString, 'end' => $dateSqlString, 'employee_key' => $employeeKey));
        while ($row = $result->fetch(\PDO::FETCH_OBJ)) {
            $absence = new \PDR\Roster\Absence(
                    (int) $row->employee_key,
                    new \DateTime($row->start),
                    new \DateTime($row->end),
                    (int) $row->days,
                    (int) $row->reason_id,
                    (string) $row->comment,
                    (string) $row->approval,
                    (string) $row->user,
                    new \DateTime($row->timestamp)
            );
        }
        return $absence;
    }

    /**
     * Retrieve all Absence objects within a specified period.
     *
     * This static method queries the database to fetch all absence records
     * that fall within the given date range. It constructs an AbsenceCollection
     * containing Absence objects for each retrieved record, providing a convenient
     * way to manage and work with absences within the specified period.
     *
     * @param \DateTime $startDateObject The start date of the period.
     * @param \DateTime $endDateObject   The end date of the period.
     *
     * @return \PDR\Roster\AbsenceCollection An AbsenceCollection containing Absence objects
     *                                        within the specified date range.
     */
    public static function getAllAbsenceObjectsInPeriod(\DateTime $startDateObject, \DateTime $endDateObject): \PDR\Roster\AbsenceCollection {
        $startDateSqlString = $startDateObject->format("Y-m-d");
        $endDateSqlString = $endDateObject->format("Y-m-d");
        $absenceCollection = new \PDR\Roster\AbsenceCollection();
        $query = "SELECT * FROM `absence` WHERE `start` <= :end AND `end` >= :start ORDER BY `start`";
        $result = \database_wrapper::instance()->run($query, array('start' => $startDateSqlString, 'end' => $endDateSqlString));
        while ($row = $result->fetch(\PDO::FETCH_OBJ)) {
            $absence = new \PDR\Roster\Absence(
                    (int) $row->employee_key,
                    new \DateTime($row->start),
                    new \DateTime($row->end),
                    (int) $row->days,
                    (int) $row->reason_id,
                    (string) $row->comment,
                    (string) $row->approval,
                    (string) $row->user,
                    new \DateTime($row->timestamp)
            );
            $absenceCollection->addAbsence($absence);
        }
        return $absenceCollection;
    }

    /**
     * Set the approval state for a specific absence record.
     *
     * This static function updates the approval state of an absence record
     * identified by the given employee key and start date. It validates the
     * provided approval state against a predefined list of valid states before
     * executing the database update query.
     *
     * @param string $approval       The desired approval state ('approved', 'not_yet_approved', 'disapproved', 'changed_after_approval').
     * @param int    $employeeKey    The employee key associated with the absence record.
     * @param string $startDate      The start date of the absence record in 'Y-m-d' format.
     *
     * @throws \Exception If the provided approval state is not in the list of valid states.
     */
    public static function setApproval(string $approval, int $employeeKey, string $startDate): void {
        // Validate the provided approval state
        if (!in_array($approval, \PDR\Utility\AbsenceUtility::$ListOfApprovalStates)) {
            throw new Exception('Ileagal approval state');
        }
        // Update the approval state in the database
        $query = "UPDATE `absence` SET `approval` = :approval "
                . " WHERE `employee_key` = :employee_key AND `start` = :start";
        \database_wrapper::instance()->run($query, array('approval' => $approval, 'employee_key' => $employeeKey, 'start' => $startDate));
    }

    /**
     * Read the number of remaining holidays submitted for the following year from the database.
     *
     * This method retrieves the total number of remaining holidays that have been submitted
     * for an employee in the subsequent year based on the provided employee key and the
     * reference year (current year for which the holidays were submitted).
     *
     * @param int $employeeKey The unique identifier of the employee.
     * @param int $year        The reference year for which holidays were submitted.
     *                         The query searches for 'remaining holiday' in the following year.
     *
     * @return int The number of remaining holidays submitted for the following year.
     */
    public static function getNumberOfRemainingHolidaysSubmitted(int $employeeKey, int $year): int {
        // SQL query to sum the days of 'remaining holiday' for the following year
        $sqlQuery = "SELECT SUM(`days`) FROM `absence` "
                . "WHERE `employee_key` = :employee_key AND "
                . "      `reason_id` = :reason_remain AND "
                . "      :year = YEAR(`start`) - 1";

        // Execute the query and fetch the result
        $result = \database_wrapper::instance()->run($sqlQuery, ['employee_key' => $employeeKey, 'year' => $year, 'reason_remain' => \PDR\Utility\AbsenceUtility::REASON_REMAINING_VACATION]);

        // Extract the number of remaining holidays submitted
        $numberOfRemainingHolidaysSubmitted = (int) $result->fetch(\PDO::FETCH_COLUMN);

        return $numberOfRemainingHolidaysSubmitted;
    }

    /**
     * Get the total number of holidays taken by an employee in a specific year.
     *
     * This method retrieves the sum of days for all holiday absences taken by the specified
     * employee in the given year. It considers only absences with the reason_id indicating
     * standard vacation.
     *
     * @param int $employeeKey The unique identifier of the employee.
     * @param int $year        The target year for which holidays are considered.
     *
     * @return int The total number of holidays taken by the employee in the specified year.
     */
    public static function getNumberOfHolidaysTaken($employeeKey, $year): int {
        // SQL query to sum the days of standard vacation for the given year
        $sqlQuery = "SELECT SUM(`days`) FROM `absence` "
                . "WHERE `employee_key` = :employee_key AND "
                . "      `reason_id` = :reason_vacation AND "
                . "      :year = YEAR(`start`)";

        // Execute the query and fetch the result
        $result = \database_wrapper::instance()->run($sqlQuery, ['employee_key' => $employeeKey, 'year' => $year, 'reason_vacation' => \PDR\Utility\AbsenceUtility::REASON_VACATION]);

        // Extract the number of holidays taken
        $numberOfHolidaysTaken = (int) $result->fetch(\PDO::FETCH_COLUMN);

        return $numberOfHolidaysTaken;
    }

    public static function findOverlappingAbsences(int $employeeKey, string $startDate, string $endDate): \PDR\Roster\AbsenceCollection {
        $absenceCollection = new \PDR\Roster\AbsenceCollection();
        $selectQery = "SELECT employee_key, start, end, days, reason_id, comment, approval, user, timestamp "
                . "FROM absence "
                . "WHERE "
                . "    start <= :end AND end >= :start "
                . "    AND start <> :start2 "
                . "    AND employee_key = :employee_key "
                . "ORDER BY `start` DESC ";
        $result = \database_wrapper::instance()->run($selectQery, array(
            "employee_key" => $employeeKey,
            "start" => $startDate,
            "start2" => $startDate,
            "end" => $endDate,
        ));
        while ($row = $result->fetch(\PDO::FETCH_OBJ)) {
            $start = new \DateTime($row->start);
            $end = new \DateTime($row->end);
            $days = $row->days;
            $reasonId = $row->reason_id;
            $comment = $row->comment;
            $approval = $row->approval;
            $userName = $row->user;
            $timeStamp = new \DateTime($row->timestamp);
            $absence = new \PDR\Roster\Absence($employeeKey, $start, $end, $days, $reasonId, $comment, $approval, $userName, $timeStamp);
            $absenceCollection->addAbsence($absence);
        }
        return $absenceCollection;
    }
}
