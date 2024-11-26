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

namespace PDR\Input;

/**
 * Handle user input for overtime data
 *
 * @author Mandelkow
 */
class OvertimeInputHandler {

    public static function handleUserInput($session, $employeeKey): bool {
        $userDialog = new \user_dialog();
        if (!filter_has_var(INPUT_POST, 'deleteRow')
                and !filter_has_var(INPUT_POST, 'submitStunden')
                and !filter_has_var(INPUT_POST, 'editDateNew')) {
            /**
             * No data has been sent.
             */
            return false;
        }
        /**
         * Deleting rows of data:
         */
        if (filter_has_var(INPUT_POST, 'deleteRow')) {
            $result = self::handleUserInputDelete($session);
            if (false === $result) {
                return false;
            }
        }

        /**
         * Insert new data:
         */
        if (filter_has_var(INPUT_POST, 'submitStunden') and filter_has_var(INPUT_POST, 'employee_key') and filter_has_var(INPUT_POST, 'datum') and filter_has_var(INPUT_POST, 'stunden') and filter_has_var(INPUT_POST, 'grund')) {
            $result = \PDR\Input\OvertimeInputHandler::handleUserInputInsert();
            if (false === $result) {
                return false;
            }
        }
        /**
         * Update changed data:
         */
        if (filter_has_var(INPUT_POST, 'editDateNew')) {
            $result = self::handleUserInputUpdate($session);
            if (false === $result) {
                return false;
            }
        }
        /**
         * Sorting and recalculating the entries:
         */
        \PDR\Database\OvertimeDatabaseHandler::recalculateBalances($employeeKey);
        if (!$session->user_has_privilege('create_overtime')) {
            /**
             * In the future everyone will be able to create overtime.
             */
            $userDialog = new \user_dialog();
            $message = gettext("Your overtime changes have been logged and will be sent to the administrator for review.");
            $userDialog->add_message($message, E_USER_NOTICE);
        }
        return true;
    }

    private static function handleUserInputUpdate($session) {
        $employeeKey = filter_input(INPUT_POST, 'editEmployeeKey', FILTER_SANITIZE_NUMBER_INT);
        $dateOldString = filter_input(INPUT_POST, 'editDateOld', FILTER_SANITIZE_SPECIAL_CHARS);
        $dateOld = new \DateTime($dateOldString);
        $dateNewString = filter_input(INPUT_POST, 'editDateNew', FILTER_SANITIZE_SPECIAL_CHARS);
        $dateNew = new \DateTime($dateNewString);
        $overtimeHoursOld = filter_input(INPUT_POST, 'editHoursOld', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $overtimeHoursNew = filter_input(INPUT_POST, 'editHoursNew', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $overtimeReasonOld = filter_input(INPUT_POST, 'editReasonOld', FILTER_SANITIZE_SPECIAL_CHARS);
        $overtimeReasonNew = filter_input(INPUT_POST, 'editReasonNew', FILTER_SANITIZE_SPECIAL_CHARS);
        if ("" === $overtimeHoursNew) {
            /**
             * No data sent:
             */
            return false;
        }
        $currentOvertime = \PDR\Database\OvertimeDatabaseHandler::getCurrentOvertime($employeeKey);
        $firstOvertime = \PDR\Database\OvertimeDatabaseHandler::getFirstOvertime($employeeKey);
        $balanceNew = $currentOvertime->getBalance() + $overtimeHoursNew;

        if (null !== $firstOvertime and $firstOvertime->getDate() > $currentOvertime->getDate()) {
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
        $overtimeReasonTrimmed = trim(preg_replace('/\s+/', ' ', $overtimeReasonNew));
        /**
         * @todo Move database calls to database class.
         */
        \PDR\Database\OvertimeDatabaseHandler::updateOvertimeInDatabase($employeeKey, $dateOld, $dateNew, $overtimeHoursNew, $balanceNew, $overtimeReasonTrimmed);
        try {
            self::sendChangeNotification($session, $employeeKey,
                    $dateOld, $dateNew,
                    $overtimeHoursOld, $overtimeHoursNew,
                    $overtimeReasonOld, $overtimeReasonNew
            );
        } catch (Exception $mailException) {
            \PDR\Utility\GeneralUtility::printDebugVariable($mailException->getMessage());
            $userDialog = new \user_dialog;
            $userDialog->add_message(gettext("There was an error when trying to send a notification."), E_USER_ERROR);
        }
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
    private static function handleUserInputDelete($session): bool {
        $deletionEmployeeKey = filter_input(INPUT_POST, 'deletionEmployeeKey', FILTER_SANITIZE_NUMBER_INT);
        $deletionDate = filter_input(INPUT_POST, 'deletionDate', FILTER_SANITIZE_SPECIAL_CHARS);
        $deletionHours = filter_input(INPUT_POST, 'deletionHours', FILTER_SANITIZE_SPECIAL_CHARS);

        if (!$deletionEmployeeKey || !$deletionDate) {
            /**
             * Handle invalid input
             * Using storeMessagesInSession() to make error message survive POST/REDIRECT/GET.
             */
            $userDialog = new \user_dialog();
            $userDialog->add_message("Error while trying to delete overtime entry.", E_USER_ERROR);
            return false;
        }

        // Step 1: Delete the overtime entry
        \PDR\Database\OvertimeDatabaseHandler::deleteOvertimeEntry($deletionEmployeeKey, $deletionDate);

        // Step 2: Check if we should notify the admin
        if (self::shouldNotifyAdmin($session)) {
            // Step 3: Prepare and send notification
            self::sendDeletionNotification($session, $deletionEmployeeKey, $deletionDate, $deletionHours);
        }
        return true; // Return true to indicate successful completion
    }

    /**
     * Prepares and sends an email notification about the deletion of an overtime entry.
     *
     * This function constructs an email message to notify the administrator about the deletion
     * of an overtime entry. It formats the deletion date according to the locale settings,
     * and includes details such as the name of the user who performed the deletion, the employee
     * whose entry was deleted, and the date of the deletion.
     *
     * @param \sessions $session The current user session object, used to get the name of the user who performed the deletion.
     * @param int $employeeKey The unique identifier of the employee whose overtime entry was deleted.
     * @param string $deletionDate The date of the deleted overtime entry, formatted as a string.
     * @return void This function does not return any value.
     */
    private static function sendDeletionNotification($session, int $employeeKey, string $deletionDate, string $deletionHours): void {
        $configuration = new \PDR\Application\configuration();
        $workforce = new \workforce();
        $employeeName = $workforce->getEmployeeFullName($employeeKey);
        $locale = $configuration->getLC_TIME();
        $dateString = self::formatReadableDate($deletionDate, $locale);

        // Prepare Email
        $subject = gettext("PDR: An overtime entry has been deleted.");
        $messageTemplate = gettext('The user %1$s has deleted the following overtime entry:\r\nEmployee: %2$s\r\nDate: %3$s\r\nHours:%4$s');
        $message = sprintf($messageTemplate, $session->getUserName(), $employeeName, $dateString, $deletionHours);

        // Send Email
        $userDialogEmail = new \user_dialog_email();
        $userDialogEmail->send_email(
                $configuration->getContactEmail(),
                $subject,
                $message
        );
    }

    private static function sendChangeNotification(\sessions $session, int $employeeKey,
            \DateTime $dateOld, \DateTime $dateNew,
            float $overtimeHoursOld, float $overtimeHoursNew,
            string $overtimeReasonOld, string $overtimeReasonNew): void {
        $configuration = new \PDR\Application\configuration();
        $workforce = new \workforce();
        $employeeName = $workforce->getEmployeeFullName($employeeKey);
        $dateStringOld = \PDR\DateTime\DateTimeUtility::formatReadableDateObject($dateOld);
        $dateStringNew = \PDR\DateTime\DateTimeUtility::formatReadableDateObject($dateNew);

        // Prepare Email
        $subject = gettext("PDR: An overtime entry has been changed.");
        $message = sprintf(gettext('The user %1$s has changed the following overtime entry:'), $session->getUserName()) . "\r\n"
                . gettext('Employee') . ": " . $employeeName . "\r\n"
                . gettext('Date') . ": " . $dateStringOld . "\r\n"
                . gettext('Hours') . ": " . $overtimeHoursOld . "\r\n"
                . gettext('Reason') . ": " . $overtimeReasonOld . "\r\n"
                . "\r\n"
                . gettext('to the new values:') . "\r\n"
                . gettext('Date') . ": " . $dateStringNew . "\r\n"
                . gettext('Hours') . ": " . $overtimeHoursNew . "\r\n"
                . gettext('Reason') . ": " . $overtimeReasonNew . "\r\n";
        // Send Email
        $userDialogEmail = new \user_dialog_email();
        $userDialogEmail->send_email(
                $configuration->getContactEmail(),
                $subject,
                $message
        );
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
    private static function shouldNotifyAdmin(\sessions $session): bool {
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
     * @param string $dateString The date to be formatted, provided as a string. It should be in a format
     *                              that is recognized by the `DateTime` constructor.
     * @param string $locale The locale identifier used to format the date, e.g., 'en_US' or 'de_DE'.
     *                       This determines how the date is presented based on regional conventions.
     * @return string The formatted date string, which represents the original date in a short format
     *                according to the specified locale.
     */
    private static function formatReadableDate(string $dateString, string $locale): string {
        $dateObject = new \DateTime($dateString);
        return \PDR\DateTime\DateTimeUtility::formatReadableDateObject($dateObject);
    }

    public static function handleUserInputInsert() {
        $userDialog = new \user_dialog();
        $employeeKey = filter_input(INPUT_POST, 'employee_key', FILTER_SANITIZE_NUMBER_INT);
        $date = filter_input(INPUT_POST, 'datum', FILTER_SANITIZE_SPECIAL_CHARS);
        $dateObject = new \DateTime($date);
        $overtimeHoursNew = filter_input(INPUT_POST, 'stunden', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        if ("" === $overtimeHoursNew) {
            /**
             * No data sent:
             */
            return false;
        }
        $currentOvertime = \PDR\Database\OvertimeDatabaseHandler::getCurrentOvertime($employeeKey);
        $firstOvertime = \PDR\Database\OvertimeDatabaseHandler::getFirstOvertime($employeeKey);
        /**
         * In case the user inserts a date, that is before the last inserted date, a warning is shown.
         * If the user still wishes to enter the data, the flag user_has_been_warned_about_date_sequence is set to 1.
         * We cancel the execution if that warning has not been approved.
         */
        $userHasBeenWarnedAboutDateSequence = filter_input(INPUT_POST, 'user_has_been_warned_about_date_sequence', FILTER_SANITIZE_SPECIAL_CHARS);
        if ($dateObject < $currentOvertime->getDate() and 'true' !== $userHasBeenWarnedAboutDateSequence) {
            $messageInputError = gettext('An error has occurred while inserting the overtime data.');
            $userDialog->add_message($messageInputError, E_USER_ERROR);
            $messageDateWarning = gettext('The input date lies before the last existent date.');
            $userDialog->add_message($messageDateWarning, E_USER_WARNING);
            $messageJSWarning = gettext('Please enable JavaScript in order to allow PDR to handle this case.');
            $userDialog->add_message($messageJSWarning, E_USER_WARNING);
            return FALSE;
        }
        $balanceNew = $currentOvertime->getBalance() + $overtimeHoursNew;

        if (null !== $firstOvertime and $firstOvertime->getDate() > $dateObject) {
            /*
             * The new entry lies before the very first entry.
             * This is a special case.
             * In this case we calculate the balance given on a date that lies in the future, in regard to the new data.
             */
            $balanceNew = $firstOvertime->getBalance() - $firstOvertime->getHours();
        }
        $overtimeReason = filter_input(INPUT_POST, 'grund', FILTER_SANITIZE_SPECIAL_CHARS);
        \PDR\Database\OvertimeDatabaseHandler::insertOvertimeToDatabase($employeeKey, $dateObject, $overtimeHoursNew, $overtimeReason);
    }

    /**
     *
     * @return void
     * @throws \Exception
     */
    public static function handleOvertimeInputFromRoster(): void {
        $employeeKey = (int) filter_input(INPUT_POST, 'employeeKey', FILTER_SANITIZE_NUMBER_INT);
        $dateString = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS);
        if (null === $dateString) {
            return;
        }
        $dateObject = new \DateTime($dateString);
        $workingHoursHave = (float) filter_input(INPUT_POST, 'workingHoursHave', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $workingHoursShould = (float) filter_input(INPUT_POST, 'workingHoursShould', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $difference = (float) filter_input(INPUT_POST, 'difference', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $overtimeHours = $workingHoursHave - $workingHoursShould;
        if ($difference !== $overtimeHours) {
            PDR\Utility\GeneralUtility::printDebugVariable($difference);
            PDR\Utility\GeneralUtility::printDebugVariable($overtimeHours);
            throw new \Exception("Error while calculating the overtime.");
        }
        /**
         * @var String <p>$reasonString is a short string made of:
         * H: $workingHoursHave,
         * S: $workingHoursShould,
         * O: $overtimeHours</p>
         */
        $reasonString = \sprintf(\gettext('H:%1$s S:%2$s O:%3$s'), $workingHoursHave, $workingHoursShould, $overtimeHours);
        \PDR\Database\OvertimeDatabaseHandler::insertOvertimeToDatabase($employeeKey, $dateObject, $overtimeHours, $reasonString);
    }
}
