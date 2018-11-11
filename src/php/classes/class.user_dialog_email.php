<?php

/*
 * Copyright (C) 2018 Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
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
 * Send an email to an employee about a changed roster.
 *
 * <p> The email should be send if:
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
 *
 * @author Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class user_dialog_email {

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
     * @param array $Inserted_roster_employee_id_list An array of days, each with an array of employee_ids who were inserted into the Roster
     * @param array $Changed_roster_employee_id_list An array of days, each with an array of employee_ids whose existing Roster was changed
     * @param array $Deleted_roster_employee_id_list An array of days, each with an array of employee_ids who were deleted from the Roster
     * @return void
     */
    public function create_email_about_changed_roster_to_employees($Roster, $Roster_old, $Inserted_roster_employee_id_list, $Changed_roster_employee_id_list, $Deleted_roster_employee_id_list) {
        foreach ($Roster as $date_unix => $Roster_day_array) {
            if (strtotime('+' . $this->maximum_future_days . ' days', time()) <= $date_unix) {
                continue;
            }
            if (time() >= $date_unix) {
                continue;
            }
            foreach ($Roster_day_array as $roster_item_object) {
                if (!empty($Inserted_roster_employee_id_list[$date_unix]) and in_array($roster_item_object->employee_id, $Inserted_roster_employee_id_list[$date_unix])) {
                    $context_string = gettext("You have been added to the roster.");
                    $message = $roster_item_object->to_email_message_string($context_string);
                    $Single_employee_roster = array($date_unix => array(0 => $roster_item_object));
                    $ics_file = iCalendar::build_ics_roster_employee($Single_employee_roster);
                    self::save_email_about_changed_roster_to_database($roster_item_object->employee_id, $roster_item_object->date_sql, $message, $ics_file);
                }
                if (!empty($Changed_roster_employee_id_list[$date_unix]) and in_array($roster_item_object->employee_id, $Changed_roster_employee_id_list[$date_unix])) {
                    $context_string = gettext("Your roster has changed.");
                    $message = $roster_item_object->to_email_message_string($context_string);
                    $Single_employee_roster = array($date_unix => array(0 => $roster_item_object));
                    $ics_file = iCalendar::build_ics_roster_employee($Single_employee_roster);
                    self::save_email_about_changed_roster_to_database($roster_item_object->employee_id, $roster_item_object->date_sql, $message, $ics_file);
                }
            }
        }
        /*
         * TODO: Build the foreach loop only on the $Deleted_roster_employee_id_list.
         * We do not need the $Roster_old information.
         */
        foreach ($Roster_old as $date_unix => $Roster_day_array) {
            if (strtotime('+' . $this->maximum_future_days . ' days', time()) <= $date_unix) {
                continue;
            }
            foreach ($Roster_day_array as $roster_item_object) {
                if (!empty($Deleted_roster_employee_id_list[$date_unix]) and in_array($roster_item_object->employee_id, $Deleted_roster_employee_id_list[$date_unix])) {
                    $message .= sprintf(gettext('You are not in the roster anymore on %1s.'), strftime('%x', $roster_item_object->date_unix)) . PHP_EOL;
                    $ics_file = iCalendar::build_ics_roster_cancelled($roster_item_object);
                    self::save_email_about_changed_roster_to_database($roster_item_object->employee_id, $roster_item_object->date_sql, $message, $ics_file);
                }
            }
        }
    }

    private static function save_email_about_changed_roster_to_database(int $employee_id, string $date_sql, string $message, string $ics_file) {
        /*
         * TODO: Do not send mail directly.
         *     Save it to the database, aggregate it, check it for plausibility, send it later.
         */
        /**
         * Remove old entries about this day if existent for this employee:
         */
        $sql_query = "DELETE FROM `user_email_notification_cache` WHERE "
                . " `employee_id` = :employee_id and "
                . " `date` = :date;"
        ;
        database_wrapper::instance()->run($sql_query, array(
            'employee_id' => $employee_id,
            'date' => $date_sql
        ));
        /**
         * Insert the new enries:
         */
        $sql_query = "INSERT INTO `user_email_notification_cache` SET "
                . " `employee_id` = :employee_id, "
                . " `date` = :date, "
                . " `notification_text` = :notification_text, "
                . " `notification_ics_file` = :notification_ics_file "
        ;
        database_wrapper::instance()->run($sql_query, array(
            'employee_id' => $employee_id,
            'date' => $date_sql,
            'notification_text' => $message,
            'notification_ics_file' => $ics_file,
        ));
    }

    public function aggregate_messages_about_changed_roster_to_employees($workforce) {
        $sql_query = "SELECT DISTINCT `employee_id` "
                . " FROM `user_email_notification_cache`;";
        $result = database_wrapper::instance()->run($sql_query);
        while ($employee_row = $result->fetch(PDO::FETCH_OBJ)) {
            $employee_id = $employee_row->employee_id;

            $aggregated_message = sprintf(gettext("Dear %1s,"), $workforce->List_of_employees[$employee_id]->full_name) . PHP_EOL . PHP_EOL;
            $aggregated_ics_file = (string) "";
            $notifications_exist = FALSE;

            $sql_query = "SELECT `notification_id`, `employee_id`, `date`, `notification_text`, `notification_ics_file` "
                    . " FROM `user_email_notification_cache` "
                    . " WHERE `employee_id` = :employee_id and `date` >= NOW();";
            $result = database_wrapper::instance()->run($sql_query, array(
                'employee_id' => $employee_id,
            ));
            while ($row = $result->fetch(PDO::FETCH_OBJ)) {
                $List_of_deletable_notifications[] = $row->notification_id;
                $notifications_exist = TRUE;
                $aggregated_message .= $row->notification_text . PHP_EOL;
                $aggregated_ics_file .= $row->notification_ics_file . "\r\n";
            }

            if ($notifications_exist) {
                $aggregated_message .= PHP_EOL . gettext('Sincerely yours,') . PHP_EOL . PHP_EOL . gettext('the friendly roster robot') . PHP_EOL;
                $mail_result = self::send_email_about_changed_roster_to_employees($employee_id, $aggregated_message, $aggregated_ics_file);

                if (TRUE === $mail_result) {
                    list($IN_placeholder, $IN_list_array) = database_wrapper::create_placeholder_for_mysql_IN_function($List_of_deletable_notifications);
                    $sql_query = "DELETE FROM `user_email_notification_cache` WHERE `notification_id` IN ($IN_placeholder)";
                    database_wrapper::instance()->run($sql_query, $IN_list_array);
                }
            }
        }
    }

    public function clean_up_user_email_notification_cache() {
        $sql_query = "DELETE FROM `user_email_notification_cache` "
                . " WHERE `date` < NOW();";
        database_wrapper::instance()->run($sql_query);
        /*
         * TODO: TRUNCATE the table if it is empty. This will reset the AUTO_INCREMENT value of `notification_id`
         */
        $sql_query = "SELECT `notification_id` "
                . " FROM `user_email_notification_cache`;";
        $result = database_wrapper::instance()->run($sql_query);
        $table_is_empty = TRUE;
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $table_is_empty = FALSE;
            break;
        }
        if ($table_is_empty) {
            $sql_query = "TRUNCATE TABLE `user_email_notification_cache`;";
            database_wrapper::instance()->run($sql_query);
        }
    }

    private static function send_email_about_changed_roster_to_employees($employee_id, $message, $ics_file) {
        global $config;
        /*
         * Define header:
         */
        $header = "";
        $header .= 'From: ' . $config['contact_email'] . "\r\n";
        $header .= 'Reply-To: ' . $config['contact_email'] . "\r\n";
        $header .= "MIME-Version: 1.0" . "\r\n";
        $header .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
        //$header .= "Content-type: text/plain; charset=UTF-8;" . "\r\n";
        $random_hash = md5(time());
        $attachment_filename = "calendar.ics";
        $attachment_content = chunk_split(base64_encode($ics_file));
        $header .= "Content-Type: multipart/mixed; boundary=\"" . $random_hash . "\"\r\n\r\n";

        $nmessage = "--" . $random_hash . "\r\n";
        $nmessage .= "Content-type:text/plain; charset=iso-8859-1\r\n";
        $nmessage .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $nmessage .= $message . "\r\n\r\n";
        $nmessage .= "--" . $random_hash . "\r\n";
        $nmessage .= "Content-Type: application/octet-stream; name=\"" . $attachment_filename . "\"\r\n";
        $nmessage .= "Content-Transfer-Encoding: base64\r\n";
        $nmessage .= "Content-Disposition: attachment; filename=\"" . $attachment_filename . "\"\r\n\r\n";
        $nmessage .= $attachment_content . "\r\n\r\n";
        $nmessage .= "--" . $random_hash . "--";

        $recipient = $config['contact_email']; //TODO: This will be the users mail address.
        $subject = $config['application_name'] . ": " . gettext('Your roster has changed.');
        /*
         * error_log(PHP_EOL . $message);
         */
        $mail_result = mail($recipient, $subject, $nmessage, $header);
        return $mail_result;
        /*
         * https://stackoverflow.com/questions/12301358/send-attachments-with-php-mail
         */
    }

}
