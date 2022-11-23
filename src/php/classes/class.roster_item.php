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

/*
 * One roster_item holds one set of information of one employee on one day.
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */

class roster_item implements \JsonSerializable {

    public $date_sql;
    public $date_unix;
    public $date_object;
    public $employee_id;
    public $branch_id;
    public $comment;
    protected $duty_start_int;
    protected $duty_start_sql;
    protected $dutyStartDateTime;
    protected $dutyEndDateTime;
    protected $duty_end_int;
    protected $duty_end_sql;
    protected $break_start_int;
    protected $break_start_sql;
    protected $break_end_int;
    protected $break_end_sql;
    public $working_hours;
    public $break_duration;
    public $duty_duration;
    public $working_seconds;
    public $weekday;
    protected static $List_of_allowed_variables = array(
        'duty_start_int',
        'duty_start_sql',
        'duty_end_int',
        'duty_end_sql',
        'break_start_int',
        'break_start_sql',
        'break_end_int',
        'break_end_sql',
    );

    public function __set($variable_name, $variable_value) {
        if (in_array($variable_name, self::$List_of_allowed_variables)) {
            $this->$variable_name = $variable_value;
            $this->calculate_durations();
        } else {
            throw new Exception($variable_name . " is private and not allowed to be changed by " . __METHOD__);
        }
    }

    public function __get($variable_name) {
        /*
         * All variables are allowed to be read. Just the writing is prohibited to some.
         */
        return $this->$variable_name;
    }

    public function get_date_sql() {
        return $this->date_sql;
    }

    public function get_date_unix() {
        return $this->date_unix;
    }

    public function get_date_object() {
        return $this->date_object;
    }

    public function get_employee_id() {
        return $this->employee_id;
    }

    public function get_branch_id() {
        return $this->branch_id;
    }

    public function get_comment() {
        return $this->comment;
    }

    public function get_duty_start_sql() {
        return $this->duty_start_sql;
    }

    public function get_dutyStartDateTime() {
        return $this->dutyStartDateTime;
    }

    public function get_dutyEndDateTime() {
        return $this->dutyEndDateTime;
    }

    public function get_duty_end_sql() {
        return $this->duty_end_sql;
    }

    public function get_break_start_sql() {
        return $this->break_start_sql;
    }

    public function get_break_end_sql() {
        return $this->break_end_sql;
    }

    public function get_duty_start_int() {
        return $this->duty_start_int;
    }

    public function get_duty_end_int() {
        return $this->duty_end_int;
    }

    public function get_break_start_int() {
        return $this->break_start_int;
    }

    public function get_break_end_int() {
        return $this->break_end_int;
    }

    public function get_working_hours() {
        return $this->working_hours;
    }

    public function get_break_duration() {
        return $this->break_duration;
    }

    public function get_duty_duration() {
        return $this->duty_duration;
    }

    public function get_working_seconds() {
        return $this->working_seconds;
    }

    public function get_weekday() {
        return $this->weekday;
    }

    public function __construct(string $date_sql, int $employee_id = NULL, int $branch_id, string $duty_start, string $duty_end, string $break_start = NULL, string $break_end = NULL, string $comment = NULL) {
        $this->date_sql = $this->format_time_string_correct($date_sql, '%Y-%m-%d');
        $this->date_object = new DateTime($date_sql);
        $this->date_unix = $this->date_object->getTimestamp();
        $this->employee_id = $employee_id;
        $this->branch_id = (int) $branch_id;
        $this->duty_start_sql = $this->format_time_string_correct($duty_start);
        $this->duty_start_int = $this->convert_time_to_seconds($duty_start);
        $this->duty_end_sql = $this->format_time_string_correct($duty_end);
        $this->duty_end_int = $this->convert_time_to_seconds($duty_end);
        $this->break_start_sql = $this->format_time_string_correct($break_start);
        $this->break_start_int = $this->convert_time_to_seconds($break_start);
        $this->break_end_sql = $this->format_time_string_correct($break_end);
        $this->break_end_int = $this->convert_time_to_seconds($break_end);
        $this->comment = $comment;
        $this->weekday = date("N", $this->date_unix);
        /*
         * TODO: Make the TimeZone a configuration variable!
         */
        $this->dutyStartDateTime = DateTime::createFromFormat("Y-m-d H:i:s", $date_sql . " " . $duty_start, new DateTimeZone('Europe/Berlin'));
        $this->dutyEndDateTime = DateTime::createFromFormat("Y-m-d H:i:s", $date_sql . " " . $duty_end, new DateTimeZone('Europe/Berlin'));
        /*
         * TODO: This might be a good place to issue an error, if the break times are not within the working times.
         * Is it possible to define a roster_logic_exception and throw it here to be catched by the page-rendering-script?
         */
//$this->check_roster_item_sequence();
        $this->calculate_durations();
    }

    private function calculate_durations() {
        /*
         * TODO: This does not take into account, that emergency service is not calculated as full hours.
         * Emergeny service calculation might differ between states, federal states, or even employees with different contracts.
         */
        $this->duty_duration = $this->duty_end_int - $this->duty_start_int;
        $this->break_duration = $this->break_end_int - $this->break_start_int;
        $this->working_seconds = ($this->duty_duration - $this->break_duration);
        $this->working_hours = round($this->working_seconds / 3600, 2);
    }

    public static function format_time_string_correct(string $time_string = NULL, string $format = '%H:%M') {
        /*
         * TODO: This could be part of a namespaced DateTime class.
         */
        $time_int = strtotime($time_string);
        if (FALSE === $time_int) {
            return $time_string;
        }
        return strftime($format, $time_int);
    }

    /*
     * @param $date_unix int A unix timestamp
     * @param $format string A valid format for the date() function
     * @return string A string representing the unix date in a given format.
     */

    public static function format_date_unix_to_string(int $date_unix = NULL, string $format = 'Y-m-d') {
        if (NULL === $date_unix) {
            return NULL;
        }
        return gmdate($format, $date_unix);
    }

    public static function format_time_integer_to_string(int $time_seconds = NULL, string $format = 'H:i') {
        if ($time_seconds > PDR_ONE_DAY_IN_SECONDS) {
            throw new Exception('The time in seconds must be below 1 day (' . PDR_ONE_DAY_IN_SECONDS . ')');
        }
        if (NULL === $time_seconds) {
            return NULL;
        }
        if ('' === $time_seconds) {
            return '';
        }
        /*
         * TODO: find out why we have to use gmdate here.
         * Is it possible to configure the date environment?
         * Also have a look at format_date_unix_to_string
         */
        return gmdate($format, $time_seconds);
    }

    public static function convert_time_to_seconds(string $time_string = NULL) {
        if (NULL === $time_string) {
            return NULL;
        }
        /*
         * array_pad is used to ensure that input in the format HH:MM i.e. 9:30 is treated as HH:MM:SS i.e. 9:30:00
         * array_pad just puts a 0 to the missing seconds and or minutes.
         */
        list($hours, $mins, $secs) = array_pad(explode(':', $time_string), 3, 0);
        return ($hours * 3600 ) + ($mins * 60 ) + $secs;
    }

    public function check_roster_item_sequence() {
        $user_dialog = new user_dialog();
        if ($this->break_end_int > $this->duty_end_int) {
            $error_message = sprintf(gettext('The break starts, before it ends.<br>Employee id: %1$s<br>Start of duty: %2$s'), $this->employee_id, $this->duty_start_sql);
            $user_dialog->add_message($error_message, E_USER_ERROR, TRUE);
        }
        if (!empty($this->break_start_int) and $this->break_start_int < $this->duty_start_int) {
            $error_message = sprintf(gettext('The break starts, before duty begins.<br>Employee id: %1$s<br>Start of duty: %2$s'), $this->employee_id, $this->duty_start_sql);
            $user_dialog->add_message($error_message, E_USER_ERROR, TRUE);
        }
        if ($this->break_end_int < $this->break_start_int) {
            $error_message = sprintf(gettext('The break ends, after duty ends.<br>Employee id: %1$s<br>Start of duty: %2$s'), $this->employee_id, $this->duty_start_sql);
            $user_dialog->add_message($error_message, E_USER_ERROR, TRUE);
        }
        if ($this->duty_end_int < $this->duty_start_int) {
            $error_message = sprintf(gettext('The duty starts, after it ends.<br>Employee id: %1$s<br>Start of duty: %2$s'), $this->employee_id, $this->duty_start_sql);
            $user_dialog->add_message($error_message, E_USER_ERROR, TRUE);
        }
    }

    public function to_email_message_string($context_string) {

        $message = "";
        /*
         * The following part is added upon aggregation:
         */
        $message .= gettext('Date');
        $message .= ":";
        $message .= PHP_EOL;
        $message .= strftime('%x', $this->date_unix) . PHP_EOL;
        $message .= $context_string . PHP_EOL;
        $message .= gettext('You work at the following times:') . PHP_EOL;
        $network_of_branch_offices = new \PDR\Pharmacy\NetworkOfBranchOffices;
        $List_of_branch_objects = $network_of_branch_offices->get_list_of_branch_objects();
        $message .= $List_of_branch_objects[$this->branch_id]->name . PHP_EOL;
        $message .= gettext('Start and end of duty');
        $message .= ":";
        $message .= PHP_EOL;
        $message .= sprintf(gettext('From %1$s to %2$s'), $this->duty_start_sql, $this->duty_end_sql);
        $message .= PHP_EOL;
        if (!empty($this->break_start_sql) and!empty($this->break_end_sql)) {
            $message .= gettext('Start and end of lunch break');
            $message .= ":";
            $message .= PHP_EOL;
            $message .= sprintf(gettext('From %1$s to %2$s'), $this->break_start_sql, $this->break_end_sql);
            $message .= PHP_EOL;
        }
        /*
         * The following part is added upon aggregation:
         * $message .= PHP_EOL . gettext('Sincerely yours,') . PHP_EOL . PHP_EOL . gettext('the friendly roster robot') . PHP_EOL;
         */

        return $message;
    }

    public function jsonSerialize() {
        return get_object_vars($this);
    }

}
