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
 * Description of AbsenceDatabaseHandler
 *
 * @author Mandelkow
 */
class AbsenceDatabaseHandler {

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
        } catch (\PDOException $exception) {
            if (\database_wrapper::ERROR_MESSAGE_DUPLICATE_ENTRY_FOR_KEY === $exception->getMessage()) {
                $message = gettext("There is already an entry on this date. The data was therefore not inserted in the database.");
                $userDialog = new \user_dialog();
                $userDialog->add_message($message, \E_USER_ERROR);
            } elseif ('23000' == $exception->getCode() and 1062 === $exception->errorInfo[1]) {
                $userDialog = new \user_dialog();
                $message = gettext("There is already an entry on this date. The data was therefore not inserted in the database.");
                $userDialog->add_message($message, \E_USER_ERROR);
                $message = gettext("The transaction was rolled back.");
                $userDialog->add_message($message, \E_USER_NOTICE);
            } else {
                print_debug_variable($exception);
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
     * @return PDOStatement         The PDOStatement object representing the result of the delete operation.
     *                              Note: Exceptions may be thrown during the execution, and they are handled
     *                              by the database_wrapper::run method.
     */
    public static function deleteAbsence(int $employeeKey, string $startDateSql): \PDOStatement {
        $query = "DELETE FROM absence WHERE `employee_key` = :employee_key AND `start` = :start";
        $result = \database_wrapper::instance()->run($query, array('employee_key' => $employeeKey, 'start' => $startDateSql));
        return $result;
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
}
