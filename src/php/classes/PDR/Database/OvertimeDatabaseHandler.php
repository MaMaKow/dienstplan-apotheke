<?php

/*
 * Copyright (C) 2024 Mandelkow
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
 * Handle calls to overtime database table
 *
 * @author Mandelkow
 */
class OvertimeDatabaseHandler {

    public static function insertOvertimeToDatabase(int $employeeKey, \DateTime $dateObject, float $overtimeHours, String $overtimeReasonString): void {
        $userDialog = new \user_dialog();
        $dateString = $dateObject->format("Y-m-d");
        $currentOvertime = self::getCurrentOvertime($employeeKey);
        $balanceOld = $currentOvertime->getBalance();
        $firstOvertime = self::getFirstOvertime($employeeKey);
        $balanceNew = $balanceOld + $overtimeHours;

        if (null !== $firstOvertime and $firstOvertime->getDate() > $dateObject) {
            /*
             * The new entry lies before the very first entry.
             * This is a special case.
             * In this case we calculate the balance given on a date that lies in the future, in regard to the new data.
             */
            $balanceNew = $firstOvertime->getBalance() - $firstOvertime->getHours();
        }
        /**
         * Replace multiple spaces (including tabs and newlines) with a single space.
         * Also trim whitespace at the beginning and the end.
         */
        $overtimeReasonTrimmed = trim(preg_replace('/\s+/', ' ', $overtimeReasonString));

        $sql_query = "INSERT INTO `Stunden` (`employee_key`, Datum, Stunden, Saldo, Grund)
        VALUES (:employee_key, :date, :overtime_hours, :balance, :reason)";
        try {
            $result = \database_wrapper::instance()->run($sql_query, array(
                'employee_key' => $employeeKey,
                'date' => $dateString,
                'overtime_hours' => $overtimeHours,
                'balance' => $balanceNew,
                'reason' => $overtimeReasonTrimmed
            ));
            if (false !== $result) {
                $userDialog = new \user_dialog();
                $userDialog->add_message(gettext("The overtime data has been successfully saved."), E_USER_NOTICE);
            }
        } catch (\Exception $exception) {
            if (\database_wrapper::ERROR_MESSAGE_DUPLICATE_ENTRY_FOR_KEY === $exception->getMessage()) {
                $userDialog->add_message(gettext('There is already an entry on this date.'), E_USER_ERROR);
                $userDialog->add_message(gettext('The data was therefore not inserted in the database.'), E_USER_WARNING);
            } else {
                \PDR\Utility\GeneralUtility::printDebugVariable($exception);
                $messageDatabaseError = gettext('There was an error while querying the database.')
                        . " " . gettext('Please see the error log for more details!');
                die("<p>$messageDatabaseError</p>");
            }
        }
        \PDR\Database\OvertimeDatabaseHandler::recalculateBalances($employeeKey);
    }

    public static function updateOvertimeInDatabase(int $employeeKey, \DateTime $dateOld, \DateTime $dateNew, float $overtimeHoursNew, float $balanceNew, String $overtimeReasonString): bool {
        $sqlQuery = "UPDATE `Stunden` "
                . " SET Datum=:date_new, Stunden=:overtime_hours_new, Saldo=:balance_new, Grund=:reason_new "
                . " WHERE `employee_key` = :employee_key_old AND Datum = :date_old";
        $result = \database_wrapper::instance()->run($sqlQuery, array(
            'employee_key_old' => $employeeKey,
            'date_new' => $dateNew->format("Y-m-d"),
            'date_old' => $dateOld->format("Y-m-d"),
            'overtime_hours_new' => $overtimeHoursNew,
            'balance_new' => $balanceNew,
            'reason_new' => $overtimeReasonString
        ));
        if (false === $result) {
            $userDialog = new \user_dialog();
            $messageDatabaseError = gettext("An error has occured when trying to write overtime data to the database.");
            $userDialog->add_message($messageDatabaseError, E_USER_ERROR);
            $messageSeeLog = gettext("Please see the error log for details!");
            $userDialog->add_message($messageSeeLog, E_USER_ERROR);
            return false;
        }
        \PDR\Database\OvertimeDatabaseHandler::recalculateBalances($employeeKey);
        return true;
    }

    /**
     * <p>The last balance stored in the database for a given employee. Current means, that the date (`Datum`) of the entry is the highest.</p>
     *
     * @param int $employeeKey
     * @return array [$balance, $date]
     */
    public static function getCurrentOvertime($employeeKey): \PDR\Roster\Overtime {
        $sqlQueryGetBalance = "SELECT * FROM `Stunden` WHERE `employee_key` = :employee_key ORDER BY `Datum` DESC LIMIT 1";
        $result = \database_wrapper::instance()->run($sqlQueryGetBalance, array('employee_key' => $employeeKey));
        while ($row = $result->fetch(\PDO::FETCH_OBJ)) {
            /*
             * We cast the result to float,
             * so in case there is no balance yet, we just set it to 0.
             */
            $employeeKey = (int) $row->employee_key;
            $balance = (float) $row->Saldo;
            $hours = (float) $row->Stunden;
            $dateObject = new \DateTime($row->Datum);
            $overtime = new \PDR\Roster\Overtime($employeeKey, $dateObject, $hours, $balance);
            return $overtime;
        }
        $overtimeBlank = new \PDR\Roster\Overtime($employeeKey, new \DateTime(), 0, 0);
        return $overtimeBlank;
    }

    /**
     * <p>
     * The first balance stored in the database for a given employee.
     * First means, that the date (`Datum`) of the entry is the lowest.
     * </p>
     *
     * @param int $employeeKey
     * @return Overtime object
     */
    public static function getFirstOvertime($employeeKey): ?\PDR\Roster\Overtime {
        $sqlQueryGetOvertime = "SELECT * FROM `Stunden` WHERE `employee_key` = :employee_key ORDER BY `Datum` ASC LIMIT 1";
        $result = \database_wrapper::instance()->run($sqlQueryGetOvertime, array('employee_key' => $employeeKey));
        while ($row = $result->fetch(\PDO::FETCH_OBJ)) {
            $employeeKey = (int) $row->employee_key;
            $balance = (float) $row->Saldo;
            $hours = (float) $row->Stunden;
            $dateObject = new \DateTime($row->Datum);
            $overtime = new \PDR\Roster\Overtime($employeeKey, $dateObject, $hours, $balance);
            return $overtime;
        }
        return null;
    }

    public static function recalculateBalances(int $employeeKey) {
        $OvertimeList = array();
        /**
         *  @var \PDR\Roster\OvertimeCollection<\PDR\Roster\Overtime> $OvertimeCollection
         */
        $OvertimeCollection = new \PDR\Roster\OvertimeCollection();
        $sqlQueryGetEmployeeOvertimes = "SELECT * FROM `Stunden` WHERE `employee_key` = :employee_key ORDER BY `Datum` ASC";
        $result = \database_wrapper::instance()->run($sqlQueryGetEmployeeOvertimes, array('employee_key' => $employeeKey));
        $firstLoop = TRUE;
        while ($row = $result->fetch(\PDO::FETCH_OBJ)) {
            $balance = $row->Saldo;
            if ($firstLoop === TRUE) {
                $balance = $row->Saldo - $row->Stunden;
                $firstLoop = FALSE;
            }
            $dateUnix = strtotime($row->Datum);
            $dateObject = new \DateTime($row->Datum);
            $OvertimeList[$dateUnix] = $row;
            $overtime = new \PDR\Roster\Overtime($employeeKey, $dateObject, $row->Stunden, $balance);
            $OvertimeCollection->addOvertime($overtime);
        }
        ksort($OvertimeList);
        $currentBalance = 0;
        /**
         *  @var \PDR\Roster\Overtime $overtimeObject
         */
        foreach ($OvertimeCollection as $overtimeObject) {
            $currentBalance += $overtimeObject->getHours();
            $sqlQueryUpdateBalances = "UPDATE `Stunden` SET `Saldo` = :balance WHERE `employee_key` = :employee_key and `Datum` = :date";
            \database_wrapper::instance()->run($sqlQueryUpdateBalances, array('employee_key' => $overtimeObject->getEmployeeKey(), 'date' => $overtimeObject->getDate()->format("Y-m-d"), 'balance' => $currentBalance));
        }
        return TRUE;
    }

    /**
     * Deletes an overtime entry from the database based on employee key and date.
     *
     * @param int $employeeKey The unique identifier of the employee whose overtime entry is to be deleted.
     * @param string $date The date of the overtime entry to be deleted, formatted as a string.
     * @return void This function does not return any value.
     */
    public static function deleteOvertimeEntry(int $employeeKey, string $date): void {
        $sqlQuery = "DELETE FROM `Stunden` WHERE `employee_key` = :employee_key AND `Datum` = :date";
        \database_wrapper::instance()->run($sqlQuery, array('employee_key' => $employeeKey, 'date' => $date));
    }
}
