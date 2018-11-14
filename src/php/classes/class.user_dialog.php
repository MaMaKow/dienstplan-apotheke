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
 * This class is thought to be used for messages adressed to the end user.
 *
 * @author Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
abstract class user_dialog {

    static $Messages = array();

    public function __construct() {
        $this->Messages = array(
            1 => array(
                'type' => E_USER_ERROR,
                'text' => '__construct will never be called'
            ),
            2 => array(
                'type' => E_USER_NOTICE,
                'text' => 'This is just an example of the array structure.'
            ),
        );
    }

    /**
     * Build the output of errors and warnings.
     *
     * The errors are assembled in a div "error_container".
     *
     * @return string HTML code with error containers.
     */
    public static function build_messages() {
        $html_messages = "<div class='user_dialog_container'>\n";
        foreach (self::$Messages as $message_array) {
            $html_messages .= "<div class=" . htmlentities($message_array['type']) . ">\n";
            $html_messages .= "<p>" . $message_array['text'] . "</p>\n";
            $html_messages .= "</div>\n";
        }
        $html_messages .= "</div>\n";
        return $html_messages;
    }

    /**
     * Build the output of errors and warnings.
     *
     * The errors are assembled in a text.
     * Oriented on markdown style
     *
     * @return string text with errors.
     */
    public static function build_messages_for_cli() {
        if (empty(self::$Messages)) {
            return '';
        }
        $text_messages = "# Messages" . PHP_EOL;
        foreach (self::$Messages as $message_array) {
            $text_messages .= "## " . htmlentities($message_array['type']) . PHP_EOL;
            $text_messages .= "- " . $message_array['text'] . PHP_EOL;
            $text_messages .= PHP_EOL;
        }
        return $text_messages;
    }

    /**
     * Add a message to the static array user_dialog::$Messages
     *
     * @param string $text The error/warning/information text to display.
     * @param int $type <p>A predefined constant:
     * 256 = E_USER_ERROR
     * 512 = E_USER_WARNING
     * 1024 = E_USER_NOTICE
     * </p>
     * @param bool $allow_formatted_input If set to TRUE, the $text is not parsed by htmlentities($text), which allows it to contain HTML text formatting.
     */
    public static function add_message($text, $type = E_USER_ERROR, $allow_formatted_input = FALSE) {
        switch ($type) {
            case E_USER_ERROR:
                $type_string = 'error';
                break;
            case E_USER_WARNING:
                $type_string = 'warning';
                break;
            case E_USER_NOTICE:
                $type_string = 'notification';
                break;
            default :
                throw new Exception('$type must be E_USER_ERROR, E_USER_NOTICE or E_USER_WARNING but was: ' . $type);
        }
        if ($allow_formatted_input) {
            self::$Messages[] = array('text' => '<pre>' . $text . '</pre>', 'type' => $type_string);
            return TRUE;
        }
        self::$Messages[] = array('text' => htmlentities($text), 'type' => $type_string);
        return TRUE;
    }

    public static function build_contact_form() {
        $form_html = "
        <div id='user_dialog_contact_form_div'>
            <a title='" . gettext("Close") . "' href='#' onclick='hide_contact_form()'>
            <span id='remove_form_div_span'>
                x
            </span>
            </a>
            <form accept-charset='utf-8' id='contact_form' method=POST>
                <p>
                    " . gettext('Message') . "<br>
                    <textarea name=message rows=15></textarea>
                </p>
                <p>
                <input type='submit' name=submit_contact_form>
                </p>
            </form>
        </div>

";
        return $form_html;
    }

    public static function contact_form_send_mail() {
        if (!filter_has_var(INPUT_POST, 'submit_contact_form')) {
            return FALSE;
        }
        global $config;
        $application_name = $config['application_name'];
        $recipient = $config['contact_email'];
        $subject = $application_name . " " . gettext('has a comment');

        $message = self::contact_form_send_mail_build_message();
        $header = self::contact_form_send_mail_build_header();

        $mail_result = mail($recipient, $subject, $message, $header);
        if ($mail_result) {
            $message = gettext("The mail was successfully sent. Thank you!");
            user_dialog::add_message($message, E_USER_NOTICE);
        } else {
            error_log(var_export(error_get_last(), TRUE));
            $message = gettext("Error while sending the mail. I am sorry.");
            user_dialog::add_message($message, E_USER_ERROR);
        }
    }

    public static function contact_form_send_mail_build_message() {
        if (!filter_has_var(INPUT_POST, 'message')) {
            return FALSE;
        }

        global $workforce;
        if (!isset($workforce)) {
            $workforce = new workforce();
        }
        $trace = debug_backtrace();
        $paragraph_separator = "\n\n\n\n";
        $message = "";
        $message .= "________ " . gettext('Message') . " ________\n";
        $message .= filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
        $message .= $paragraph_separator;

        $message .= "________ " . gettext('Sender') . " ________\n";
        $message .= $workforce->List_of_employees[$_SESSION['user_employee_id']]->full_name;
        $message .= $paragraph_separator;

        $message .= "________ " . gettext('File') . " ________\n";
        $message .= $trace[1]['file'];
        $message .= $paragraph_separator;

        /* $message .= "________ " . gettext('Trace') . " ________\n";
         * $message .= "TRACE DEACTIVATED";
         * //$message .= htmlentities(var_export($trace, TRUE));
         * $message .= $paragraph_separator;
         */
        return $message;
    }

    public static function contact_form_send_mail_build_header() {
        $header = "";
        $header .= 'From: ' . $_SESSION['user_email'] . "\r\n";
        $header .= 'Reply-To: ' . $_SESSION['user_email'] . "\r\n";
        $header .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
        $header .= "Content-type: text/plain; charset=UTF-8;\r\n";
        return $header;
    }

}
