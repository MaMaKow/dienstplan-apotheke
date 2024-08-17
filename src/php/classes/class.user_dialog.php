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
 * This class is thought to be used for messages adressed to the end user.
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 * @todo no static methods
 */
class user_dialog {

    /**
     *
     * @var array $Messages <p>static array containing the error messages and their types
     *   $this->Messages = array(
     *      1 => array(
     *          'type' => E_USER_ERROR,
     *          'text' => 'This is an example error'
     *      ),
     *      2 => array(
     *          'type' => E_USER_NOTICE,
     *          'text' => 'This is just an example of the array structure.'
     *      ),
     *  );
     *      </p>
     * @todo I made this variable private. Test the behaviour!
     */
    private static $Messages = array();

    public function __construct() {

    }

    /**
     * Build the output of errors and warnings.
     *
     * The errors are assembled in a div "error_container".
     *
     * @return string HTML code with error containers.
     */
    public function build_messages() {
        $html_messages = "<div class='user-dialog-container'>\n";
        foreach (self::$Messages as $message_array) {
            $html_messages .= "<div class=" . htmlspecialchars($message_array['type']) . ">\n";
            $html_messages .= "<span>" . $message_array['text'] . "</span>\n";
            $html_messages .= "</div>\n";
        }
        $html_messages .= "</div>\n";
        return $html_messages;
    }

    public function storeMessagesInSession() {
        if (array() !== self::$Messages) {
            $_SESSION['userDialogMessages'] = self::$Messages;
        }
    }

    public function readMessagesFromSession() {
        if (!empty($_SESSION['userDialogMessages'])) {
            self::$Messages = $_SESSION['userDialogMessages'];
            unset($_SESSION['userDialogMessages']);
        }
    }

    /**
     * Build the output of errors and warnings.
     *
     * The errors are assembled in a text.
     * Oriented on markdown style
     *
     * @return string text with errors.
     */
    public function build_messages_for_cli() {
        if (empty(self::$Messages)) {
            return '';
        }
        $text_messages = "# Messages" . PHP_EOL;
        foreach (self::$Messages as $message_array) {
            $text_messages .= "## " . htmlspecialchars($message_array['type']) . PHP_EOL;
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
     * @param bool $allow_formatted_input If set to TRUE, the $text is not parsed by htmlspecialchars($text), which allows it to contain HTML text formatting.
     */
    public function add_message(string $text, int $type = E_USER_ERROR, bool $allow_formatted_input = FALSE) {
        switch ($type) {
            case E_USER_ERROR:
                $type_string = 'error';
                break;
            case E_USER_WARNING:
                $type_string = 'warning';
                break;
            case E_USER_NOTICE:
            case E_USER_DEPRECATED:
                $type_string = 'notification';
                break;
            default :
                throw new Exception('$type must be E_USER_ERROR, E_USER_NOTICE or E_USER_WARNING but was: ' . $type);
        }
        if (TRUE === $allow_formatted_input) {
            self::$Messages[] = array('text' => $text, 'type' => $type_string);
            return TRUE;
        }
        self::$Messages[] = array('text' => htmlspecialchars($text), 'type' => $type_string);
        return TRUE;
    }

    public function build_contact_form() {
        $form_html = "
        <div id='userDialogContactFormDiv'>
            <a title='" . gettext("Close") . "' href='#' onclick='hide_contact_form()'>
            <span id='removeFormDivSpan'>
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

    public function contact_form_send_mail() {
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
            $this->add_message($message, E_USER_NOTICE);
        } else {
            error_log(var_export(error_get_last(), TRUE));
            $message = gettext("An error occured while sending the mail. I am sorry.");
            $this->add_message($message, E_USER_ERROR);
        }
        return $mail_result;
    }

    private function contact_form_send_mail_build_message() {
        if (!filter_has_var(INPUT_POST, 'message')) {
            return FALSE;
        }

        global $workforce;
        if (!isset($workforce)) {
            $workforce = new workforce();
        }
        $paragraph_separator = "\n\n\n\n";
        $message = "";
        $message .= "________ " . gettext('Message') . " ________\n";
        $message .= filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS);
        $message .= $paragraph_separator;

        $message .= "________ " . gettext('Sender') . " ________\n";
        $message .= $_SESSION['user_object']->get_user_name();
        $message .= $paragraph_separator;

        $message .= "________ " . gettext('File') . " ________\n";
        $message .= filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_SPECIAL_CHARS);
        $message .= $paragraph_separator;

        return $message;
    }

    private function contact_form_send_mail_build_header() {
        $header = "";
        $header .= 'From: ' . $_SESSION['user_object']->email . "\r\n";
        $header .= 'Reply-To: ' . $_SESSION['user_object']->email . "\r\n";
        $header .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
        $header .= "Content-type: text/plain; charset=UTF-8;\r\n";
        return $header;
    }
}
