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

    const TYPE_ERROR = 'error';
    const TYPE_WARNING = 'warning';
    const TYPE_NOTIFICATION = 'notification';

    public function __construct() {
        $this->Messages = array(
            1 => array(
                'type' => self::TYPE_ERROR,
                'text' => '__construct will never be called'
            ),
            2 => array(
                'type' => self::TYPE_NOTIFICATION,
                'text' => 'This is just an example of the array structure.'
            ),
        );
    }

    /**
     * Build the output of errors and warnings.
     *
     * We display the errors, which we collected in $Fehlermeldung and $Warnmeldung
     * The errors are assembled in a div "error_container".
     *
     * @param array $Fehlermeldung An array of strings of errors.
     * @param array $Warnmeldung An array of strings of warnings.
     *
     * @return string HTML code with error containers.
     */
    public static function build_messages() {
        $html_messages = "<div class='user_dialog_container'>\n";
        foreach (self::$Messages as $message_array) {
            $html_messages .= "<div class=" . $message_array['type'] . ">\n";
            $html_messages .= "<p>" . $message_array['text'] . "</p>\n";
            $html_messages .= "</div>\n";
        }
        $html_messages .= "</div>\n";
        return $html_messages;
    }

    public static function add_message($text, $type = self::TYPE_ERROR) {
        $allowed_types = array(self::TYPE_ERROR, self::TYPE_NOTIFICATION, self::TYPE_WARNING);
        if (!in_array($type, $allowed_types)) {
            /*
             * Only allow specific constants as message type:
             */
            throw new Exception('$type must be any of ' . var_export($allowed_types, TRUE) . ' but was: ' . $type);
            //return FALSE;
        }
        self::$Messages[] = array('text' => $text, 'type' => $type);
        return TRUE;
    }

}
