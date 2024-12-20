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
 *
 * Send an email to a user about a changed roster.
 *
 * <p> The email should be send if:
 *     - The user wishes emails
 *     - A change is less than 14 days ahead
 *     - The change is not in the past/today
 *     - No other email has been sent within 24 hours
 * </p>
 * <p> The email should contain:
 *     - a specific comment
 *     - the new roster
 *     - one ICS file
 * </p>
 * @todo Notifications can also be directly printed to the user upon login.
 * @todo Make this email thing a new class. It is big enough.
 * @todo <p>The aggregated string is not helpfull enough.
 *    The difference should be visible. And also multiple roster items on the same day should be displayed together.</p>
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class user_dialog_email {
//    use PHPMailer\PHPMailer\PHPMailer;
//    use PHPMailer\PHPMailer\Exception;

    /**
     *
     * @var int <p>Maximum days in the future to send an information about.
     *     If the roster is first planned, there is no need to email everybody about it.
     *     Also changes in the far future are not relevant now.
     *     Therefore we define a maximum of future days to react upon.
     *     </p>
     */
    private $maximum_future_days;

    public function __construct() {
        $this->maximum_future_days = 14;
    }

    /**
     *
     * Create a human readable text about a changed roster together with an iCalendar file
     *
     * <p>The class takes the new roster and the information about specific changes (inserted, changed, deleted)
     *     and composes and stores human readable text about the change
     *     as well as an iCalendar file with the new roster in the database.
     *     The texts can then be sent via email to the user.
     *     This usually happens once a day during the background_maintenance.
     * </p>
     *
     * @param array $Roster the new roster
     * @param array $Roster_old obsolete
     * @param array $Inserted_roster_employee_key_list An array of days, each with an array of employee_keys who were inserted into the Roster
     * @param array $Changed_roster_employee_key_list An array of days, each with an array of employee_keys whose existing Roster was changed
     * @param array $Deleted_roster_employee_key_list An array of days, each with an array of employee_keys who were deleted from the Roster
     * @return void
     */
    public function create_notification_about_changed_roster_to_employees($Roster, $Roster_old, $Inserted_roster_employee_key_list, $Changed_roster_employee_key_list, $Deleted_roster_employee_key_list) {
        foreach ($Roster as $date_unix => $Roster_day_array) {
            if (strtotime('+' . $this->maximum_future_days . ' days', time()) <= $date_unix) {
                continue;
            }
            if (time() >= $date_unix) {
                continue;
            }
            foreach ($Roster_day_array as $roster_item_object) {
                if (NULL === $roster_item_object->employee_key) {
                    continue;
                }
                if (!empty($Inserted_roster_employee_key_list[$date_unix]) and in_array($roster_item_object->employee_key, $Inserted_roster_employee_key_list[$date_unix])) {
                    $context_string = gettext("You have been added to the roster.");
                    $message = $roster_item_object->to_email_message_string($context_string);
                    $Single_employee_roster = array($date_unix => array(0 => $roster_item_object));
                    $ics_file = iCalendar::build_ics_roster_employee($Single_employee_roster);
                    self::save_notification_about_changed_roster_to_database(user::guess_user_key_by_employee_key($roster_item_object->employee_key), $roster_item_object->date_sql, $message, $ics_file);
                }
                if (!empty($Changed_roster_employee_key_list[$date_unix]) and in_array($roster_item_object->employee_key, $Changed_roster_employee_key_list[$date_unix])) {
                    $context_string = gettext("Your roster has changed.");
                    $message = $roster_item_object->to_email_message_string($context_string);
                    $Single_employee_roster = array($date_unix => array(0 => $roster_item_object));
                    $ics_file = iCalendar::build_ics_roster_employee($Single_employee_roster);
                    self::save_notification_about_changed_roster_to_database(user::guess_user_key_by_employee_key($roster_item_object->employee_key), $roster_item_object->date_sql, $message, $ics_file);
                }
            }
        }
        /*
         * TODO: Build the foreach loop only on the $Deleted_roster_employee_key_list.
         * We do not need the $Roster_old information.
         */
        foreach ($Roster_old as $date_unix => $Roster_day_array) {
            if (strtotime('+' . $this->maximum_future_days . ' days', time()) <= $date_unix) {
                continue;
            }
            foreach ($Roster_day_array as $roster_item_object) {
                if (NULL === $roster_item_object->employee_key) {
                    continue;
                }

                if (!empty($Deleted_roster_employee_key_list[$date_unix]) and in_array($roster_item_object->employee_key, $Deleted_roster_employee_key_list[$date_unix])) {
                    $dateString = $roster_item_object->date_object->format("d.m.Y");
                    $message = sprintf(gettext('You are not in the roster anymore on %1$s.'), $dateString) . PHP_EOL;
                    $ics_file = ""; // TODO: Right now iCalendar can not handle events with the STATUS:CANCELED
                    self::save_notification_about_changed_roster_to_database(user::guess_user_key_by_employee_key($roster_item_object->employee_key), $roster_item_object->date_sql, $message, $ics_file);
                }
            }
        }
    }

    private static function save_notification_about_changed_roster_to_database(int $user_key, string $date_sql, string $message, string $ics_file = "") {
        if (NULL === $user_key) {
            return FALSE;
        }
        /*
         * TODO: Do not send mail directly.
         *     Save it to the database, aggregate it, check it for plausibility, send it later.
         */
        /**
         * Remove old entries about this day if existent for this employee:
         */
        $sql_query = "DELETE FROM `user_email_notification_cache` WHERE "
                . " `user_key` = :user_key and "
                . " `date` = :date;"
        ;
        database_wrapper::instance()->run($sql_query, array(
            'user_key' => $user_key,
            'date' => $date_sql
        ));
        /**
         * Insert the new enries:
         */
        $sql_query = "INSERT INTO `user_email_notification_cache` SET "
                . " `user_key` = :user_key, "
                . " `date` = :date, "
                . " `notification_text` = :notification_text, "
                . " `notification_ics_file` = :notification_ics_file "
        ;
        database_wrapper::instance()->run($sql_query, array(
            'user_key' => $user_key,
            'date' => $date_sql,
            'notification_text' => $message,
            'notification_ics_file' => $ics_file,
        ));
    }

    public function aggregate_messages_about_changed_roster_to_employees($workforce) {
        $sql_query = "SELECT DISTINCT `user_key` "
                . " FROM `user_email_notification_cache`;";
        $result = database_wrapper::instance()->run($sql_query);
        while ($user_row = $result->fetch(PDO::FETCH_OBJ)) {
            $user_key = $user_row->user_key;

            $aggregated_message = sprintf(gettext('Dear %1$s,'), $workforce->List_of_employees[$user_key]->full_name) . PHP_EOL . PHP_EOL;
            $aggregated_ics_file = (string) "";
            $notifications_exist = FALSE;

            $sql_query = "SELECT `notification_id`, `user_key`, `date`, `notification_text`, `notification_ics_file` "
                    . " FROM `user_email_notification_cache` "
                    . " WHERE `user_key` = :user_key and `date` >= NOW();";
            $result_notification_employee = database_wrapper::instance()->run($sql_query, array(
                'user_key' => $user_key,
            ));
            while ($row = $result_notification_employee->fetch(PDO::FETCH_OBJ)) {
                $List_of_deletable_notifications[] = $row->notification_id;
                $notifications_exist = TRUE;
                $aggregated_message .= $row->notification_text . PHP_EOL;
                $aggregated_ics_file .= $row->notification_ics_file . "\r\n";
            }

            if ($notifications_exist) {
                $aggregated_message .= PHP_EOL . gettext('Sincerely yours,') . PHP_EOL . PHP_EOL . gettext('the friendly roster robot') . PHP_EOL;
                $mail_result = $this->send_email_about_changed_roster_to_employees($user_key, $aggregated_message, $aggregated_ics_file);
                if (TRUE === $mail_result) {
                    $sql_query = "DELETE FROM `user_email_notification_cache` WHERE `notification_id` = :notification_id";
                    $statement = database_wrapper::instance()->prepare($sql_query);
                    foreach ($List_of_deletable_notifications as $deletable_notification) {
                        $statement->execute(array('notification_id' => $deletable_notification));
                    }
                }
            }
        }
    }

    public function clean_up_user_email_notification_cache() {
        $sql_query = "DELETE FROM `user_email_notification_cache` "
                . " WHERE `date` < NOW();";
        database_wrapper::instance()->run($sql_query);
        /*
         * We start a transaction in order not to allow the TRUNCATE to delete an entry,
         * which was created in exactly that moment
         * between the SELECT search and the TRUNCATE deletion.
         */
        try {
            database_wrapper::instance()->beginTransaction();
            $sql_query = "SELECT `notification_id` "
                    . " FROM `user_email_notification_cache`;";
            $result = database_wrapper::instance()->run($sql_query);
            $table_is_empty = TRUE;
            while ($row = $result->fetch(PDO::FETCH_OBJ)) {
                $table_is_empty = FALSE;
                break;
            }
            if ($table_is_empty) {
                /*
                 * TRUNCATE the table if it is empty.
                 * This will reset the AUTO_INCREMENT value of `notification_id`
                 * TRUNCATE will implicitly commit all transactions.
                 */
                $sql_query = "TRUNCATE TABLE `user_email_notification_cache`;";
                database_wrapper::instance()->run($sql_query);
            }
            if (database_wrapper::instance()->inTransaction()) {
                database_wrapper::instance()->commit();
            }
        } catch (Exception $exception) {
            error_log($exception->getMessage());
            error_log($exception->getTraceAsString());
        }
        if ($table_is_empty) {
            /*
             * TRUNCATE the table if it is empty.
             * This will reset the AUTO_INCREMENT value of `notification_id`
             */
            $sql_query = "TRUNCATE TABLE `user_email_notification_cache`;";
            database_wrapper::instance()->run($sql_query);
            if (database_wrapper::instance()->inTransaction()) {
                database_wrapper::instance()->commit();
            }
        }
    }

    private function send_email_about_changed_roster_to_employees($user_key, $message, $ics_file_string) {
        if (NULL === $user_key) {
            return FALSE;
        }
        $user = new user($user_key);
        if (!$user->exists()) {
            return FALSE;
        }

        if (FALSE == $user->wants_emails_on_changed_roster()) {
            /*
             * The user does not want to be informed about roster changes via email.
             */
            return FALSE;
        }
        $mail_success = $this->send_email($user->email, gettext('Your roster has changed.'), $message, $ics_file_string, 'iCalendar.ics');
        return $mail_success;
    }

    public function send_email($recipient, $subject, $message, $attachment_string = NULL, $attachment_filename = NULL) {
        global $config;
        require_once PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/3rdparty/PHPMailer/PHPMailer.php';
        require_once PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/3rdparty/PHPMailer/SMTP.php';
        require_once PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/3rdparty/PHPMailer/Exception.php';

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        //$mail->SMTPDebug = 2; // Set to 2 for more detailed debug output
        $mail->SMTPDebug = 3; // 3 = 2 plus more information about the initial connection - this level can help diagnose STARTTLS failures.
        //$mail->SMTPDebug = 4; // 4 = Detaied Low-level data output.
        $mail->Debugoutput = function ($str, $level) {
            PDR\Utility\GeneralUtility::printDebugVariable($str);
        };
        try {
            /*
             * Server settings
             */
            switch ($config['email_method']) {
                case 'smtp':
                    if (!isset($config['email_smtp_host'], $config['email_smtp_port'], $config['email_smtp_username'], $config['email_smtp_password'])) {
                        \PDR\Utility\GeneralUtility::printDebugVariable('Error while sending mail: SMTP not correctly configured');
                        return FALSE;
                    }
                    $mail->isSMTP();
                    $mail->SMTPAuth = true;
                    $mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
                    $mail->Host = $config['email_smtp_host'];
                    $mail->Port = $config['email_smtp_port']; // TCP port to connect to (587 for TLS)
                    $mail->Username = $config['email_smtp_username'];
                    $mail->Password = $config['email_smtp_password'];
                    if ("localhost" === $mail->Host and "1025" == $mail->Port) {
                        /**
                         * For the purpose of testing mails with mailhog, TLS and STARTTLS have to be disabled.
                         */
                        $mail->SMTPAuth = false; // No authentication required for MailHog
                        $mail->SMTPSecure = ''; // Disable TLS/SSL
                        $mail->SMTPAutoTLS = false; // Disable automatic TLS negotiation
                    }
                    break;
                case 'sendmail':
                    $mail->isSendmail();
                    break;
                case 'qmail':
                    $mail->isQmail();
                    break;
                case 'mail':
                default:
                    $mail->isMail();
                    break;
            }
            /*
             * Recipients
             */
            $mail->setFrom($config['contact_email'], $config['application_name'] . ' Mailer');
            $mail->addAddress($recipient);
            /*
             * Attachments
             */
            if (NULL !== $attachment_string and !empty($attachment_string) and !empty($attachment_filename)) {
                $mail->addStringAttachment($attachment_string, $attachment_filename);
            }
            /*
             * Content
             */
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->isHTML(FALSE);
            $mail->Subject = $config['application_name'] . ": " . $subject;
            $mail->Body = $message;
            //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail_success = $mail->send();
            return $mail_success;
        } catch (Exception $exception) {
            \PDR\Utility\GeneralUtility::printDebugVariable('Email Message could not be sent. Mailer Error: ', $mail->ErrorInfo, $exception);
            $user_dialog = new user_dialog;
            $user_dialog->add_message(gettext('Error while trying to send email.') . ' ' . gettext('Please see the error log for details!'));
            return FALSE;
        }
    }
}
