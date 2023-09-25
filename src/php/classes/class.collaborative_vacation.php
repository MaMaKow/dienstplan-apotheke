<?php

/*
  Copyright (C) 2017 Mandelkow

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU Affero General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU Affero General Public License for more details.

  You should have received a copy of the GNU Affero General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class collaborative_vacation {

    /**
     * Handle the user input
     *
     * @return void
     */
    public function handle_user_data_input($session) {
        if (!$session->user_has_privilege('request_own_absence') and !$session->user_has_privilege('create_absence')) {
            return FALSE;
        }

        /*
         * Work on user data:
         */
        if (filter_has_var(INPUT_POST, 'approve_absence')) {
            $this->approve_absence_to_database($session);
        }
        if (filter_has_var(INPUT_POST, 'command')) {
            $this->write_user_input_to_database($session);
        }
    }

    /**
     * Fill new entries into absence table or change, delete old entries.
     *
     * The approval status of new entries defaults to "not_yet_approved".
     * Old entries keep their approval state.
     *
     * @global type $user from default.php
     * @return void
     */
    private function write_user_input_to_database($session) {

        $employee_key = filter_input(INPUT_POST, 'employee_key', FILTER_SANITIZE_NUMBER_INT);
        $start_date_string = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $end_date_string = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $reason_id = filter_input(INPUT_POST, 'reason_id', FILTER_SANITIZE_NUMBER_INT);
        $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $command = filter_input(INPUT_POST, 'command', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $employee_key_old = filter_input(INPUT_POST, 'employee_key_old', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $start_date_old_string = filter_input(INPUT_POST, 'start_date_old', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($session->user_has_privilege('create_absence')) {
            /*
             * User is allowed to write any input to the database.
             */
            $approval = filter_input(INPUT_POST, 'approval', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        } elseif ($session->user_has_privilege('request_own_absence')) {
            /*
             * User is only allowed to ask for specific changes to the database.
             */
            if ($_SESSION['user_object']->employee_key !== $employee_key) {
                error_log("Permissions: Employee " . $_SESSION['user_object']->employee_key . " tried to request holidays for employee " . $employee_key);
                global $config;
                $recipient = $config['contact_email'];
                $subject = "Permission Error";
                $message = "Permissions: Employee " . $_SESSION['user_object']->employee_key . " tried to request holidays for employee " . $employee_key;
                $user_dialog_email->send_email($recipient, $subject, $message);
                throw new Exception(gettext('Permission error.') . ' ' . gettext('Please see the error log for details!'));
            }
            if ("" !== $employee_key_old and $_SESSION['user_object']->employee_key !== $employee_key_old) {
                error_log("Permissions: Employee " . $_SESSION['user_object']->employee_key . " tried to request holidays from employee " . $employee_key_old);
                $user_dialog_email = new user_dialog_email;
                global $config;
                $recipient = $config['contact_email'];
                $subject = "Permission Error";
                $message = "Permissions: Employee " . $_SESSION['user_object']->employee_key . " tried to request holidays from employee " . $employee_key_old;
                $user_dialog_email->send_email($recipient, $subject, $message);
                throw new Exception(gettext('Permission error.') . ' ' . gettext('Please see the error log for details!'));
            }
            $approval = "not_yet_approved";
            global $config;
            $recipient = $config['contact_email'];
            $subject = "An absence for " . $_SESSION['user_object']->user_name . " was changed.";
            $message = "Dear Admin,\n\n";
            $message = "An absence for " . $_SESSION['user_object']->user_name . " was inserted or changed.\n";
            $message .= "\nUser input:";
            $message .= "$employee_key  = $employee_key
        start_date_string = $start_date_string
        end_date_string = $end_date_string
        reason_id = $reason_id
        comment = $comment
        command = $command
        employee_key_old = $employee_key_old
        start_date_old_string = $start_date_old_string"; //TODO: Test this an then gettext.
            $user_dialog_email->send_email($recipient, $subject, $message);
        } else {
            /*
             * This point should never be reached.
             */
            error_log("Permissions: Employee " . $_SESSION['user_object']->employee_key . " seems to misuse collaborative vacation.");
            $user_dialog_email = new user_dialog_email;
            global $config;
            $recipient = $config['contact_email'];
            $subject = "Permission Error";
            $message = "Permissions: Employee " . $_SESSION['user_object']->employee_key . " seems to misuse collaborative vacation.";
            $user_dialog_email->send_email($recipient, $subject, $message);
            throw new Exception(gettext('Permission error.') . ' ' . gettext('Please see the error log for details!'));
        }


        database_wrapper::instance()->beginTransaction();
        /*
         * Delete old entries:
         */
        if (NULL !== $employee_key_old) {
            absence::delete_absence($employee_key_old, $start_date_old_string);
        }

        /*
         * Insert new entry data into the table absence:
         */
        if ("save" === $command) {
            $workforce = new \workforce();
            $employee_object = $workforce->get_employee_object($employee_key);
            $days = \absence::calculate_employee_absence_days(new DateTime($start_date_string), new DateTime($end_date_string), $employee_object);
            absence::insert_absence($employee_key, $start_date_string, $end_date_string, $days, $reason_id, $comment, $approval);
        }
        database_wrapper::instance()->commit();
    }

    /**
     * Approve entries in the database or set them to pending or disapproved.
     *
     * @param object $session session data from logged in user
     * @return void
     */
    private function approve_absence_to_database($session) {
        if (!$session->user_has_privilege('create_absence')) {
            /*
             * User is not allowed to create or edit absences.
             */
            return FALSE;
        }
        $approval = filter_input(INPUT_POST, 'approve_absence', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $employee_key_old = filter_input(INPUT_POST, 'employee_key_old', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $start_date_old_string = filter_input(INPUT_POST, 'start_date_old', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        absence::set_approval($approval, $employee_key_old, $start_date_old_string);
    }

    /**
     * Build the HTML code of the calendar.
     *
     * The calendar is a div of the year containing divs of months containing paragraphs of days.
     * Each day paragraph contains the day of week and day number.
     * It may contain spans with the name of a holiday or
     * spans with the employee representations of absent employees.
     * Absence is not shown on holidays and on weekends.
     * The absence spans are colored differently for different professions.
     *
     * @param int $year
     * @global array[string] $List_of_employee_professions Discriminate between professions e.g. "Pharmacist", "Pharmacy technician (PTA)"
     * @return string HTML div element containing a calendar with absences.
     */
    public function build_absence_year($year, $workforce) {
        $date_start_object = new \DateTime();
        $date_start_object->setDate($year, 1, 1);
        $date_end_object = new \DateTime();
        $date_end_object->setDate($year, 12, 31);
        $current_month = $date_start_object->format("n");
        $current_year = $date_start_object->format("Y");

        $Absences = absence::get_all_absence_data_in_period($date_start_object->format("Y-m-d"), $date_end_object->format("Y-m-d"));

        $year_container_html = "<div class=year_container>\n";
        $year_container_html .= \form_element_builder::build_html_select_year($current_year);
        $month_container_html = "<div class='year_quarter_container'>";
        $month_container_html .= "<div class=month_container>";
        $month_container_html .= $this->get_month_name($date_start_object) . "<br>\n";
        for ($date_object = clone $date_start_object; $date_object->format('Y-m-d') <= $date_end_object->format('Y-m-d'); $date_object->add(new \DateInterval('P1D'))) {

            if ($current_month != $date_object->format("n")) {
                /** begin a new month div */
                $current_month = $date_object->format("n");
                $month_container_html .= "</div>";
                //if (in_array($current_month, array(4, 7, 10))) {
                if (in_array($current_month, array(7))) {
                    $month_container_html .= "</div><!-- class='year_quarter_container'-->";
                    $month_container_html .= "<div class='year_quarter_container'>";
                }
                $month_container_html .= "<div class='month_container'>";
                $month_container_html .= $this->get_month_name($date_object) . "<br>\n";
            }
            $month_container_html .= $this->build_absence_month_paragraph($date_object, $date_object, $Absences, 'year');
        }
        $month_container_html .= "</div>\n";
        $month_container_html .= "</div><!-- class='year_quarter_container'-->\n";
        $year_container_html .= $month_container_html;
        $year_container_html .= "</div>\n";
        return $year_container_html;
    }

    /**
     * Build the HTML code of the calendar.
     *
     * The calendar is a div of the month with adjacend weeks containing rows of weeks containing columns of days.
     * Each day column contains the day of week and day number.
     * It may contain spans with the name of a holiday or
     * spans with the employee representing strings of absent employees.
     * Absence is not shown on holidays and on weekends.
     * The absence spans are colored differently for different professions.
     *
     *
     * @param int $year
     * @param int $month_number
     * @global array[string] $List_of_employee_professions Discriminate between professions e.g. "Pharmacist", "Pharmacy technician (PTA)"
     * @return string HTML div element containing a calendar with absences.
     */
    public function build_absence_month($year, $month_number) {
        $input_date_object = new \DateTime();
        $input_date_object->setDate($year, $month_number, 1);
        $start_date_object = clone $input_date_object;
        $start_date_object->modify('last Monday');
        //$start_date_object->setISODate($input_date_object->format('Y'), $input_date_object->format('W'), 1);
        $end_date_object = new \DateTime();
        $end_date_object->setDate($year, $month_number, $input_date_object->format('t'))->modify('this Sunday');

        $current_week = $input_date_object->format('W');
        $current_month_name = $this->get_month_name($input_date_object);
        $Absences = absence::get_all_absence_data_in_period($start_date_object->format("Y-m-d"), $end_date_object->format("Y-m-d"));

        $month_container_html = "";
        $month_container_html .= \form_element_builder::build_html_select_year($input_date_object->format('Y'));
        $month_container_html .= \form_element_builder::build_html_select_month($input_date_object->format('n'));

        $week_container_html = "<table class='month_container noselect'>"
                . "<tr class=week_container>";
        for ($date_object = clone $start_date_object; $date_object->format('Y-m-d') <= $end_date_object->format('Y-m-d'); $date_object->add(new \DateInterval('P1D'))) {
            if ($current_week != $date_object->format('W')) {
                /** begin a new month div */
                $current_week = $date_object->format('W');
                $current_month_name = $this->get_month_name($date_object);
                $week_container_html .= "</tr>";
                $week_container_html .= "<tr class=week_container>";
            }

            $week_container_html .= $this->build_absence_month_paragraph($date_object, $input_date_object, $Absences);
        }
        $week_container_html .= "</tr></table></div>\n";
        $month_container_html .= $week_container_html;

        return $month_container_html;
    }

    private function build_absence_month_paragraph($date_object, $input_date_object, $Absences, $mode = 'month') {
        $is_holiday = \holidays::is_holiday($date_object->format('U'));
        $html_class_list = $this->get_classes_of_day_paragraph($date_object, $is_holiday, $input_date_object);
        $paragraph = "<p class='$html_class_list'";
        $paragraph .= $this->build_absence_month_paragraph_javascript();
        $paragraph .= $this->build_absence_month_get_paragraph_attributes($date_object);
        $paragraph .= ">";
        $paragraph .= $this->build_absence_month_paragraph_content($date_object, $Absences, $is_holiday, $mode);
        $paragraph .= "</p>\n";
        return $paragraph;
    }

    private function build_absence_month_paragraph_content($date_object, $Absences, $is_holiday, $mode = 'month') {
        switch ($mode) {
            case 'year':
                $date_string = $date_object->format('d.m.');
                break;
            case 'month':
            default:
                $date_string = mb_substr(\localization::gettext($date_object->format('l')), 0, 3);
                $date_string .= ' ';
                $date_string .= $date_object->format('d.m.');

                break;
        }

        $paragraph_content = "<strong>" . $date_string . "</strong> ";
        $paragraph_content .= $this->build_absence_year_absent_employees_containers($date_object, $Absences, $is_holiday, $mode);
        if ($is_holiday) {
            $paragraph_content .= "<span class='holiday'>" . $is_holiday . "</span>\n";
        }
        $paragraph_content .= $this->build_absence_month_paragraph_add_emergency_service($date_object, $mode);

        return $paragraph_content;
    }

    private function build_absence_month_paragraph_add_emergency_service($date_object, $mode) {
        if (!\PDR\Roster\EmergencyService::is_our_service_day($date_object)) {
            return "";
        }
        $emergency_service = new \PDR\Roster\EmergencyService($date_object);
        $emergency_service_content = "";
        if ('month' === $mode) {
            $emergency_service_content .= "<span class='emergency_service'>"
                    . gettext("emergency service")
                    . ": "
                    . $emergency_service->get_branch_name_short()
                    . ", "
                    . $emergency_service->get_employee_short_descriptor()
                    . "</span>\n";
        } else {
            $title = gettext("emergency service")
                    . ": ";
            $title .= $emergency_service->get_branch_name_short()
                    . ", ";
            $title .= $emergency_service->get_employee_name();
            $emergency_service_content .= "<span class='emergency_service' title='$title'>"
                    . mb_substr(gettext('emergency service'), 0, 2)
                    . "<sub>"
                    . $emergency_service->get_employee_short_descriptor()
                    . "&rarr;"
                    . $emergency_service->get_branch_id()
                    . "</sub>"
                    . "</span>";
        }
        return $emergency_service_content;
    }

    private function build_absence_month_get_paragraph_attributes(\DateTime $date_object) {
        $paragraph_attributes = "";
        $paragraph_attributes .= " data-date_sql='" . $date_object->format('Y-m-d') . "'";
        $paragraph_attributes .= " data-date_unix='" . $date_object->format('U') . "'";
        return $paragraph_attributes;
    }

    private function build_absence_month_paragraph_javascript() {
        $paragraph_javascript = "";
        $paragraph_javascript .= " onmousedown='highlight_absence_create_start(event)' ";
        $paragraph_javascript .= " onmouseover='highlight_absence_create_intermediate(event)' ";
        $paragraph_javascript .= " onmouseup='highlight_absence_create_end(event)' ";
        return $paragraph_javascript;
    }

    private function build_absence_year_absent_employees_containers($date_object, $Absences, $is_holiday, $mode = 'year') {
        if ($date_object->format("N") >= 6) {
            return "";
        }
        if ($is_holiday) {
            return "";
        }
        $date_sql = $date_object->format("Y-m-d");
        $absent_employees_containers = '';
        foreach ($Absences as $Absence) {
            if ($Absence['start'] > $date_sql) {
                break;
            }
            if ($Absence['end'] < $date_sql) {
                continue;
            }

            $employee_key = $Absence['employee_key'];

            $workforce = new workforce($date_object->format('Y-m-d'));
            $employee_representation = $workforce->get_employee_short_descriptor($employee_key);
            if ($workforce->employee_exists($employee_key)) {
                $profession = $workforce->get_employee_profession($employee_key);
                $employee_exists = ""; //blank means existing.
            } else {
                $profession = "";
                $employee_exists = "non_existing_employee";
            }

            $span_class = "absent_employee_container";
            $span_class .= " " . $profession;
            $span_class .= " " . $employee_exists;
            $span_class .= " " . $Absence['approval'];
            $span_class .= " " . $mode;
            if ('month' == $mode) {
                /*
                 * In the year mode there is not enough space for the last names:
                 */
                $employee_representation = mb_substr($workforce->get_employee_last_name($employee_key), 0, 16);
            }

            $absent_employees_containers .= "<span "
                    . " class='" . $span_class . "' "
                    . " onclick='insert_form_div(\"edit\")' "
                    . " title='" . $this->build_absence_year_absent_employees_containers_title_text($workforce, $Absence) . "' "
                    . " data-absence_details='" . json_encode($Absence, JSON_UNESCAPED_UNICODE) . "' "
                    . ">";
            $absent_employees_containers .= $employee_representation;
            $absent_employees_containers .= "</span> \n";
        }
        return $absent_employees_containers;
    }

    /**
     *
     * @param workforce $workforce
     * @param array $Absence
     * @return string
     * @todo Perhaps build a real absence object from a real absence class.
     */
    private function build_absence_year_absent_employees_containers_title_text(workforce $workforce, $Absence) {
        $absence_title_text = $workforce->get_employee_last_name($Absence['employee_key']) . "\n";
        $absence_title_text .= absence::get_reason_string_localized($Absence['reason_id']) . "\n";
        $absence_title_text .= $Absence['comment'] . "\n";
        $dateObjectAbenceStart = new DateTime($Absence['start']);
        $dateObjectAbenceEnd = new DateTime($Absence['end']);
        $dateStringAbenceStart = $dateObjectAbenceStart->format('d.m.Y'); // Format for time (hours, minutes, seconds)
        $dateStringAbenceEnd = $dateObjectAbenceEnd->format('d.m.Y'); // Format for time (hours, minutes, seconds)

        $absence_title_text .= gettext('from') . ' ' . $dateStringAbenceStart . "\n";
        $absence_title_text .= gettext('to') . ' ' . $dateStringAbenceEnd . "\n";
        $absence_title_text .= sprintf(gettext('%1$s days taken'), $Absence['days']) . "\n";
        $absence_title_text .= localization::gettext($Absence['approval']) . "";
        return $absence_title_text;
    }

    private function get_classes_of_day_paragraph($date_object, $is_holiday, $input_date_object) {

        $Paragraph_class = array('day_paragraph');
        if ($date_object->format('N') < 6 and !$is_holiday) {
            $Paragraph_class[] = 'weekday';
        } else {
            $Paragraph_class[] = 'weekend';
        }
        if ($date_object->format('n') !== $input_date_object->format('n')) {
            $Paragraph_class[] = 'adjacent_month';
        }
        if ($date_object->format('Y-m-d') === date('Y-m-d', time())) {
            $Paragraph_class[] = 'today';
        }
        $html_class_list = implode(' ', $Paragraph_class);
        return $html_class_list;
    }

    /**
     * Returns the localized name of the month
     *
     * @param DateTime $date_object PHP DateTime object
     * @return string $month_name month name.
     */
    private function get_month_name($date_object) {
        $Month_names = localization::get_month_names();
        return $Month_names[$date_object->format("n")];
    }
}
