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

/*
 * One roster_item holds one set of information of one employee on one day.
 *
 * @author Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */

class roster_item {

    public $date_sql;
    public $date_unix;
    public $employee_id;
    public $branch_id;
    public $comment;
    protected $duty_start_int;
    protected $duty_start_sql;
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
            throw new Exception($variable_name . " is private and not allowed to be called by " . __METHOD__);
        }
    }

    public function __get($variable_name) {
        if (in_array($variable_name, self::$List_of_allowed_variables)) {
            return $this->$variable_name;
        } else {
            throw new Exception($variable_name . " is private and not allowed to be called by " . __METHOD__);
        }
    }

    public function __construct($date_sql, int $employee_id = NULL, $branch_id, $duty_start, $duty_end, $break_start, $break_end, $comment = NULL) {
        $this->date_sql = $this->format_time_string_correct($date_sql, '%Y-%m-%d');
        $this->date_unix = strtotime($date_sql);
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

    protected static function format_time_string_correct($time_string, $format = '%H:%M') {
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

    public static function format_date_unix_to_string($date_unix, $format = 'Y-m-d') {
        if (NULL === $date_unix) {
            return NULL;
        }
        return gmdate($format, $date_unix);
    }

    public static function format_time_integer_to_string($time_seconds, $format = 'H:i') {
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

    public static function convert_time_to_seconds($time_string) {
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

    private function check_roster_item_sequence() {
        //TODO: Move this validation into the submiting of the form. That is the place to block wrong entries.
        try {
            if ($this->break_end_int > $this->duty_end_int) {
                throw new Exception('The break starts, before it ends.<br>' . ' Employee id: ' . $this->employee_id . '<br> Start of duty: ' . $this->duty_start_sql);
            }
            if (!empty($this->break_start_int) and $this->break_start_int < $this->duty_start_int) {
                echo "Exception" . $this->employee_id . "<br>\n";
                throw new Exception('The break starts, before duty begins.<br>' . ' Employee id: ' . $this->employee_id . '<br> Start of duty: ' . $this->duty_start_sql);
            }
            if ($this->break_end_int < $this->break_start_int) {
                throw new Exception('The break ends, after duty ends.<br>' . ' Employee id: ' . $this->employee_id . '<br> Start of duty: ' . $this->duty_start_sql);
            }
            if ($this->duty_end_int < $this->duty_start_int) {
                throw new Exception('The duty starts, after it ends.<br>' . ' Employee id: ' . $this->employee_id . '<br> Start of duty: ' . $this->duty_start_sql);
            }
        } catch (Exception $exception) {
            error_log('Message: ' . $exception->getMessage());
            throw new PDRRosterLogicException($exception->getMessage());
        }
    }

    public function to_email_message_string($context_string) {
        global $List_of_branch_objects, $workforce;
        $message = "";
        /*
         * The following part is added upon aggregation:
         * $message = sprintf(gettext("Dear %1s,"), $workforce->List_of_employees[$this->employee_id]->full_name) . PHP_EOL . PHP_EOL;
         */
        $message .= gettext('Date');
        $message .= ":";
        $message .= PHP_EOL;
        $message .= strftime('%x', $this->date_unix) . PHP_EOL;
        $message .= $context_string . PHP_EOL;
        $message .= gettext('You work at the following times:') . PHP_EOL;
        $message .= $List_of_branch_objects[$this->branch_id]->name . PHP_EOL;
        $message .= gettext('Start and end of duty');
        $message .= ":";
        $message .= PHP_EOL;
        $message .= sprintf(gettext("From %1s to %2s"), $this->duty_start_sql, $this->duty_end_sql);
        $message .= PHP_EOL;
        if (!empty($this->break_start_sql) and ! empty($this->break_end_sql)) {
            $message .= gettext('Start and end of lunch break');
            $message .= ":";
            $message .= PHP_EOL;
            $message .= sprintf(gettext("From %1s to %2s"), $this->break_start_sql, $this->break_end_sql);
            $message .= PHP_EOL;
        }
        /*
         * The following part is added upon aggregation:
         * $message .= PHP_EOL . gettext('Sincerely yours,') . PHP_EOL . PHP_EOL . gettext('the friendly roster robot') . PHP_EOL;
         */

        return $message;
    }

}
