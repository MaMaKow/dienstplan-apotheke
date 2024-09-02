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
 * Description of class
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class overtime {

    public static function handle_user_input_insert() {
        $user_dialog = new user_dialog();
        $employee_key = filter_input(INPUT_POST, 'employee_key', FILTER_SANITIZE_NUMBER_INT);
        $date = filter_input(INPUT_POST, 'datum', FILTER_SANITIZE_SPECIAL_CHARS);
        $overtime_hours_new = filter_input(INPUT_POST, 'stunden', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        list($balance_old, $date_old) = overtime::get_current_balance($employee_key);
        $first_balance_row = overtime::get_first_balance($employee_key);
        /**
         * In case the user inserts a date, that is before the last inserted date, a warning is shown.
         * If the user still wishes to enter the data, the flag user_has_been_warned_about_date_sequence is set to 1.
         * We cancel the execution if that warning has not been approved.
         */
        $user_has_been_warned_about_date_sequence = filter_input(INPUT_POST, 'user_has_been_warned_about_date_sequence', FILTER_SANITIZE_SPECIAL_CHARS);
        if (strtotime($date) < strtotime($date_old) and 'true' !== $user_has_been_warned_about_date_sequence) {
            $message = gettext('An error has occurred while inserting the overtime data.');
            $user_dialog->add_message($message, E_USER_ERROR);
            $message = gettext('The input date lies before the last existent date.');
            $user_dialog->add_message($message, E_USER_WARNING);
            $message = gettext('Please enable JavaScript in order to allow PDR to handle this case.');
            $user_dialog->add_message($message, E_USER_WARNING);
            return FALSE;
        }
        $balance_new = $balance_old + $overtime_hours_new;

        if (FALSE !== $first_balance_row and $first_balance_row->Datum > $date) {
            /*
             * The new entry lies before the very first entry.
             * This is a special case.
             * In this case we calculate the balance given on a date that lies in the future, in regard to the new data.
             */
            $balance_new = $first_balance_row->Saldo - $first_balance_row->Stunden;
        }

        $sql_query = "INSERT INTO `Stunden` (`employee_key`, Datum, Stunden, Saldo, Grund)
        VALUES (:employee_key, :date, :overtime_hours, :balance, :reason)";
        try {
            $result = database_wrapper::instance()->run($sql_query, array(
                'employee_key' => $employee_key,
                'date' => $date,
                'overtime_hours' => $overtime_hours_new,
                'balance' => $balance_new,
                'reason' => filter_input(INPUT_POST, 'grund', FILTER_SANITIZE_SPECIAL_CHARS)
            ));
        } catch (Exception $exception) {
            if (database_wrapper::ERROR_MESSAGE_DUPLICATE_ENTRY_FOR_KEY === $exception->getMessage()) {
                $user_dialog->add_message(gettext('There is already an entry on this date.'), E_USER_ERROR);
                $user_dialog->add_message(gettext('The data was therefore not inserted in the database.'), E_USER_WARNING);
            } else {
                \PDR\Utility\GeneralUtility::printDebugVariable($exception);
                $message = gettext('There was an error while querying the database.')
                        . " " . gettext('Please see the error log for more details!');
                die("<p>$message</p>");
            }
        }

        overtime::recalculate_balances($employee_key);
    }

    /**
     * Handles the deletion of an overtime entry based on user input.
     *
     * This function performs the following steps:
     * 1. Retrieves and sanitizes the employee key and date from the POST request.
     * 2. Validates the input to ensure that both the employee key and date are provided.
     *    - If either input is invalid or missing, an error message is added to the user dialog, and the function exits.
     * 3. Deletes the overtime entry from the database using the sanitized input.
     * 4. Checks if the current user lacks the privilege to manage the roster.
     *    - If so, prepares and sends an email notification about the deletion.
     *
     * @param \sessions $session The current user session object used to check user privileges and handle user dialogs.
     * @return void This function does not return any value.
     */
    private static function handleUserInputDelete($session): void {
        $deletionEmployeeKey = filter_input(INPUT_POST, 'deletionEmployeeKey', FILTER_SANITIZE_NUMBER_INT);
        $deletionDate = filter_input(INPUT_POST, 'deletionDate', FILTER_SANITIZE_SPECIAL_CHARS);

        if (!$deletionEmployeeKey || !$deletionDate) {
            // Handle invalid input
            /**
             * @todo I am not sure if the error message would survive POST/REDIRECT/GET.
             */
            $userDialog = new \user_dialog();
            $userDialog->add_message("Error while trying to delete overtime entry.", E_USER_ERROR);
            return;
        }

        // Step 1: Delete the overtime entry
        self::deleteOvertimeEntry($deletionEmployeeKey, $deletionDate);

        // Step 2: Check if we should notify the admin
        if (self::shouldNotifyAdmin($session)) {
            // Step 3: Prepare and send notification
            self::sendDeletionNotification($session, $deletionEmployeeKey, $deletionDate);
        }
    }

    /**
     * Deletes an overtime entry from the database based on employee key and date.
     *
     * This function executes a SQL DELETE query to remove an overtime entry from the
     * `Stunden` table where the `employee_key` and `Datum` match the provided parameters.
     * It uses a prepared statement to prevent SQL injection and ensure secure database interactions.
     *
     * @param int $employeeKey The unique identifier of the employee whose overtime entry is to be deleted.
     * @param string $date The date of the overtime entry to be deleted, formatted as a string.
     * @return void This function does not return any value.
     */
    private static function deleteOvertimeEntry(int $employeeKey, string $date): void {
        $sqlQuery = "DELETE FROM `Stunden` WHERE `employee_key` = :employee_key AND `Datum` = :date";
        database_wrapper::instance()->run($sqlQuery, array('employee_key' => $employeeKey, 'date' => $date));
    }

    /**
     * Determines whether an email notification should be sent to the administrator.
     *
     * This function checks if the current user session lacks the privilege to manage the roster.
     * If the user does not have the \sessions::PRIVILEGE_CREATE_ROSTER privilege, it returns true,
     * indicating that an email notification should be sent. Otherwise, it returns false.
     *
     * @param \sessions $session The current user session object used to check user privileges.
     * @return bool True if the user lacks the required privilege and an email notification should be sent; false otherwise.
     */
    private static function shouldNotifyAdmin($session): bool {
        return !$session->user_has_privilege(\sessions::PRIVILEGE_CREATE_ROSTER);
    }

    /**
     * Formats a date string into a locale-aware short date format.
     *
     * This function converts a date string into a `DateTime` object and then formats it
     * according to the specified locale using a short date format. The `IntlDateFormatter`
     * class is used to ensure that the date is formatted in a way that is appropriate
     * for the provided locale settings.
     *
     * @param string $deletionDate The date to be formatted, provided as a string. It should be in a format
     *                              that is recognized by the `DateTime` constructor.
     * @param string $locale The locale identifier used to format the date, e.g., 'en_US' or 'de_DE'.
     *                       This determines how the date is presented based on regional conventions.
     * @return string The formatted date string, which represents the original date in a short format
     *                according to the specified locale.
     */
    private static function formatDeletionDate(string $deletionDate, string $locale): string {
        $deletionDateObject = new DateTime($deletionDate);
        $formatter = new IntlDateFormatter($locale, IntlDateFormatter::SHORT, IntlDateFormatter::NONE);
        return $formatter->format($deletionDateObject);
    }

    /**
     * Prepares and sends an email notification about the deletion of an overtime entry.
     *
     * This function constructs an email message to notify the administrator about the deletion
     * of an overtime entry. It formats the deletion date according to the locale settings,
     * and includes details such as the name of the user who performed the deletion, the employee
     * whose entry was deleted, and the date of the deletion.
     *
     * Steps:
     * 1. Retrieves configuration settings and employee details.
     * 2. Formats the date according to the locale.
     * 3. Prepares the subject and body of the email using localized strings and formatted data.
     * 4. Sends the email to the contact address specified in the configuration.
     *
     * @param \sessions $session The current user session object, used to get the name of the user who performed the deletion.
     * @param int $employeeKey The unique identifier of the employee whose overtime entry was deleted.
     * @param string $deletionDate The date of the deleted overtime entry, formatted as a string.
     * @return void This function does not return any value.
     */
    private static function sendDeletionNotification($session, int $employeeKey, string $deletionDate): void {
        $configuration = new \PDR\Application\configuration();
        $workforce = new workforce();
        $employeeName = $workforce->getEmployeeFullName($employeeKey);
        $locale = $configuration->getLC_TIME();
        $dateString = self::formatDeletionDate($deletionDate, $locale);

        // Prepare Email
        $subject = gettext("PDR: An overtime entry has been deleted.");
        $messageTemplate = gettext('The user %1$s has deleted the following overtime entry:\nEmployee: %2$s\nDate: %3$s');
        $message = sprintf($messageTemplate, $session->getUserName(), $employeeName, $dateString);

        // Send Email
        $userDialogEmail = new \user_dialog_email();
        $userDialogEmail->send_email(
                $configuration->getContactEmail(),
                $subject,
                $message
        );
    }

    public static function handle_user_input($session, $employee_key) {
        if (!$session->user_has_privilege('create_overtime')) {
            return FALSE;
        }
        /*
         * Deleting rows of data:
         */
        if (filter_has_var(INPUT_POST, 'loeschen')) {
            self::handleUserInputDelete($session);
        }

        /*
         * Insert new data:
         */
        if (filter_has_var(INPUT_POST, 'submitStunden') and filter_has_var(INPUT_POST, 'employee_key') and filter_has_var(INPUT_POST, 'datum') and filter_has_var(INPUT_POST, 'stunden') and filter_has_var(INPUT_POST, 'grund')) {
            self::handle_user_input_insert();
        }
        /*
         * Sorting and recalculating the entries:
         */
        overtime::recalculate_balances($employee_key);
    }

    public static function recalculate_balances($employee_key) {
        $Overtime_list = array();
        $sql_query = "SELECT * FROM `Stunden` WHERE `employee_key` = :employee_key ORDER BY `Datum` ASC";
        $result = database_wrapper::instance()->run($sql_query, array('employee_key' => $employee_key));
        $first_loop = TRUE;
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            if ($first_loop === TRUE) {
                $balance = $row->Saldo - $row->Stunden;
                $first_loop = FALSE;
            }
            $date_unix = strtotime($row->Datum);
            $Overtime_list[$date_unix] = $row;
        }
        ksort($Overtime_list);
        foreach ($Overtime_list as $overtime_entry) {
            $balance += $overtime_entry->Stunden;
            $sql_query = "UPDATE `Stunden` SET `Saldo` = :balance WHERE `employee_key` = :employee_key and `Datum` = :date";
            database_wrapper::instance()->run($sql_query, array('employee_key' => $overtime_entry->employee_key, 'date' => $overtime_entry->Datum, 'balance' => $balance));
        }
        return TRUE;
    }

    /**
     * <p>The last balance stored in the database for a given employee. Current means, that the date (`Datum`) of the entry is the highest.</p>
     *
     * @param int $employee_key
     * @return array [$balance, $date]
     */
    public static function get_current_balance($employee_key) {
        $sql_query = "SELECT * FROM `Stunden` WHERE `employee_key` = :employee_key ORDER BY `Datum` DESC LIMIT 1";
        $result = database_wrapper::instance()->run($sql_query, array('employee_key' => $employee_key));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            /*
             * We cast the result to float,
             * so in case there is no balance yet, we just set it to 0.
             */
            $balance = (float) $row->Saldo;
            $date = $row->Datum;
            return [$balance, $date];
        }
        return [0, (new DateTime())->format('Y-m-d')];
    }

    /**
     * <p>
     * The first balance stored in the database for a given employee.
     * First means, that the date (`Datum`) of the entry is the lowest.
     * </p>
     *
     * @param int $employee_key
     * @return object <p>A standard PHP object representing a single row of data.</p>
     */
    public static function get_first_balance($employee_key) {
        $sql_query = "SELECT * FROM `Stunden` WHERE `employee_key` = :employee_key ORDER BY `Datum` ASC LIMIT 1";
        $result = database_wrapper::instance()->run($sql_query, array('employee_key' => $employee_key));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            return $row;
        }
        return FALSE;
    }

    public static function build_overview_table() {
        $table_head = overtime::build_overview_table_head();
        $table_body = overtime::build_overview_table_body();
        $table = "<table id='overtimeOverviewTable'>" . $table_head . $table_body . "</table>\n";
        return $table;
    }

    private static function build_overview_table_head() {
        $table_head = "<thead>";
        $table_head .= "<th>" . gettext('Employee') . "</th>";
        $table_head .= "<th>" . gettext('Balance') . "</th>";
        $table_head .= "<th>" . gettext('Date') . "</th>";
        $table_head .= "</thead>\n";
        return $table_head;
    }

    private static function build_overview_table_body() {
        $startDateObject = new DateTime("October last year");
        $endDateObject = new DateTime("last day of December this year");
        $workforce = new workforce($startDateObject->format("Y-m-d"), $endDateObject->format("Y-m-d"));
        $table_rows = "<tbody>";
        // Create a DateTime object for the current date
        $currentDate = new DateTime();

        // Calculate the date three months ago
        $threeMonthsAgo = clone $currentDate; // Create a copy of the current date
        $threeMonthsAgo->modify('-3 months'); // Subtract three months
        foreach (array_keys($workforce->List_of_employees) as $employee_key) {
            $sql_query = "SELECT * FROM `Stunden` WHERE `employee_key` = :employee_key ORDER BY `Datum` DESC LIMIT 1";
            $result = database_wrapper::instance()->run($sql_query, array('employee_key' => $employee_key));
            while ($row = $result->fetch(PDO::FETCH_OBJ)) {
                $date_object = new DateTime($row->Datum);
                switch (TRUE) {
                    case 40 < $row->Saldo:
                        $class = "positive-very-high";
                        break;
                    case 20 < $row->Saldo:
                        $class = "positive-high";
                        break;
                    case 0 == $row->Saldo:
                        $class = "zero";
                        break;
                    case 0 > $row->Saldo:
                        $class = "negative";
                        break;
                    default:
                        $class = "positive";
                        break;
                }

                if ($date_object < $threeMonthsAgo) {
                    $class .= " " . "not-updated";
                }
                $table_rows .= "<tr class='$class'>";
                $table_rows .= "<td>" . $row->employee_key . " " . $workforce->List_of_employees[$row->employee_key]->last_name . "</td>";
                $table_rows .= "<td>" . $row->Saldo . "</td>";
                $date_string = $date_object->format('d.m.Y');
                $table_rows .= "<td>" . $date_string . "</td>";
                $table_rows .= "</tr>\n";
            }
        }
        $table_rows .= "</tbody>\n";
        return $table_rows;
    }
}
