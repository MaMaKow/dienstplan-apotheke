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

    public static function add_message($text, $type = E_USER_ERROR, $formated_input = FALSE) {
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
        if ($formated_input) {
            self::$Messages[] = array('text' => '<pre>' . $text . '</pre>', 'type' => $type_string);
            return TRUE;
        }
        self::$Messages[] = array('text' => htmlentities($text), 'type' => $type_string);
        return TRUE;
    }

}
