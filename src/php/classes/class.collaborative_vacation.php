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
        if (!$session->user_has_privilege('request_own_absence') and ! $session->user_has_privilege('create_absence')) {
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
     * TODO: There is no approval-tool for administrator users yet.
     *
     * @global type $user from default.php
     * @return void
     */
    private function write_user_input_to_database($session) {

        $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
        $start_date_string = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING);
        $end_date_string = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_STRING);
        $reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_STRING);
        $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING);
        $command = filter_input(INPUT_POST, 'command', FILTER_SANITIZE_STRING);
        $employee_id_old = filter_input(INPUT_POST, 'employee_id_old', FILTER_SANITIZE_STRING);
        $start_date_old_string = filter_input(INPUT_POST, 'start_date_old', FILTER_SANITIZE_STRING);

        if ($session->user_has_privilege('create_absence')) {
            /*
             * User is allowed to write any input to the database.
             * But still we will turn any input into a not_yet_approved state
             */
            $approval = "not_yet_approved";
        } elseif ($session->user_has_privilege('request_own_absence')) {
            /*
             * User is only allowed to ask for specific changes to the database.
             */
            if ($_SESSION['user_object']->employee_id !== $employee_id) {
                /*
                 * TODO: Make this an email.
                 * TODO: Build a contact class to handle this.
                 */
                error_log("Permissions: Employee " . $_SESSION['user_object']->employee_id . " tried to request holidays for employee " . $employee_id);
                return FALSE;
            }
            if ("" !== $employee_id_old and $_SESSION['user_object']->employee_id !== $employee_id_old) {
                /*
                 * TODO: Make this an email.
                 * TODO: Build a contact class to handle this.
                 */
                error_log("Permissions: Employee " . $_SESSION['user_object']->employee_id . " tried to request holidays from employee " . $employee_id_old);
                return FALSE;
            }
            $approval = "not_yet_approved";
        } else {
            /*
             * This point should never be reached.
             */
            return FALSE;
        }

        //Decide on $approval state
        /*
         * Every change is put back to "not_yet_approved".
         * Therefore we currently do not need the following block of code:
          $query = "SELECT `approval` FROM absence WHERE `employee_id` = '$employee_id_old' AND `start` = '$start_date_old_string'";
          $result = \database_wrapper::instance()->run($query);
          $row = $result->fetch(PDO::FETCH_OBJ);
          if (empty($approval) and empty($row->approval)) {
          $approval = "not_yet_approved";
          } elseif (empty($approval)) {
          $approval = $row->approval;
          }

         */

        /**
         * Delete old entries
         *
         * TODO: This probably should be solved with TRANSACTIONS
         * What happens, if the DELETE  is successfull but the INSERT fails?
         *
         * $employee_id_old and $start_date_old_string are NULL for new entries. Therefore there will be no deletions.
         */
        $query = "DELETE FROM absence WHERE `employee_id` = :employee_id AND `start` = :start";
        $result = \database_wrapper::instance()->run($query, array('employee_id' => $employee_id_old, 'start' => $start_date_old_string));

        /*
         * Insert new entry data into the table absence.
         */
        if ("save" === $command) {
            $workforce = new \workforce();
            $employee_object = $workforce->List_of_employees[$employee_id];
            $days = \absence::calculate_employee_absence_days($start_date_string, $end_date_string, $employee_object);
            $query = "INSERT INTO absence (`employee_id`, `start`, `end`, `days`, `reason`, `comment`, `approval`, `user`) "
                    . "VALUES (:employee_id, :start, :end, :days, :reason, :comment, :approval, :user)";
            $result = \database_wrapper::instance()->run($query, array(
                'employee_id' => $employee_id,
                'start' => $start_date_string,
                'end' => $end_date_string,
                'days' => $days,
                'reason' => $reason,
                'comment' => $comment,
                'approval' => $approval,
                'user' => $_SESSION['user_object']->user_name,
            ));
        }
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

        $approval = filter_input(INPUT_POST, 'approve_absence', FILTER_SANITIZE_STRING);
        $employee_id_old = filter_input(INPUT_POST, 'employee_id_old', FILTER_SANITIZE_STRING);
        $start_date_old_string = filter_input(INPUT_POST, 'start_date_old', FILTER_SANITIZE_STRING);

        $query = "UPDATE `absence` SET `approval` = :approval "
                . " WHERE `employee_id` = :employee_id AND `start` = :start";
        $result = \database_wrapper::instance()->run($query, array('approval' => $approval, 'employee_id' => $employee_id_old, 'start' => $start_date_old_string));
    }

    /**
     * Build the HTML code of the calendar.
     *
     * The calendar is a div of the year containing divs of months containing paragraphs of days.
     * Each day paragraph contains the day of week and day number.
     * It may contain spans with the name of a holiday or
     * spans with the employee_id numbers of absent employees.
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
        /*
         * TODO: Build a DateTime Object in \MaMaKow namespace.
         */
        $current_year = $date_start_object->format("Y");

        $year_container_html = "<div class=year_container>\n";
        $year_container_html .= \absence::build_html_select_year($current_year);
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
            $month_container_html .= $this->build_absence_month_paragraph($date_object, $date_object, $workforce, 'year');
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
     * spans with the employee_id numbers of absent employees.
     * Absence is not shown on holidays and on weekends.
     * The absence spans are colored differently for different professions.
     *
     *
     * @param int $year
     * @param int $month_number
     * @global array[string] $List_of_employee_professions Discriminate between professions e.g. "Pharmacist", "Pharmacy technician (PTA)"
     * @return string HTML div element containing a calendar with absences.
     */
    public function build_absence_month($year, $month_number, $workforce) {
        $input_date_object = new \DateTime();
        $input_date_object->setDate($year, $month_number, 1);
        $start_date_object = clone $input_date_object;
        $start_date_object->modify('last Monday');
        //$start_date_object->setISODate($input_date_object->format('Y'), $input_date_object->format('W'), 1);
        $end_date_object = new \DateTime();
        $end_date_object->setDate($year, $month_number, $input_date_object->format('t'))->modify('this Sunday');

        $current_week = $input_date_object->format('W');
        $current_month_name = $this->get_month_name($input_date_object);

        $month_container_html = "";
        $month_container_html .= \absence::build_html_select_year($input_date_object->format('Y'));
        $month_container_html .= \absence::build_html_select_month($input_date_object->format('n'));

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

            $week_container_html .= $this->build_absence_month_paragraph($date_object, $input_date_object, $workforce);
        }
        $week_container_html .= "</tr></table></div>\n";
        $month_container_html .= $week_container_html;

        return $month_container_html;
    }

    private function build_absence_month_paragraph($date_object, $input_date_object, $workforce, $mode = 'month') {
        $is_holiday = \holidays::is_holiday($date_object->format('U'));
        $html_class_list = $this->get_classes_of_day_paragraph($date_object, $is_holiday, $input_date_object);
        $paragraph = "<p class='$html_class_list'";
        $paragraph .= $this->build_absence_month_paragraph_javascript();
        $paragraph .= $this->build_absence_month_get_paragraph_attributes($date_object);
        $paragraph .= ">";
        $paragraph .= $this->build_absence_month_paragraph_content($date_object, $workforce, $is_holiday, $mode);
        $paragraph .= "</p>\n";
        return $paragraph;
    }

    private function build_absence_month_paragraph_content($date_object, $workforce, $is_holiday, $mode = 'month') {
        $Abwesende = \absence::read_absentees_from_database($date_object->format('Y-m-d'));
        switch ($mode) {
            case 'year':
                $date_string = $date_object->format('d.m.');
                break;
            case 'month':
            default:
                $date_string = mb_substr(\pdr_gettext($date_object->format('l')), 0, 3);
                $date_string .= ' ';
                $date_string .= $date_object->format('d.m.');

                break;
        }

        $paragraph_content = "<strong>" . $date_string . "</strong> ";
        $paragraph_content .= $this->build_absence_year_absent_employees_containers($date_object, $workforce, $Abwesende, $is_holiday, $mode);
        if ($is_holiday) {
            $paragraph_content .= "<span class='holiday'>" . $is_holiday . "</span>\n";
        }
        $paragraph_content .= $this->build_absence_month_paragraph_add_emergency_service($date_object, $workforce, $mode);

        return $paragraph_content;
    }

    private function build_absence_month_paragraph_add_emergency_service($date_object, $workforce, $mode) {
        $having_emergency_service = \pharmacy_emergency_service::having_emergency_service($date_object->format("Y-m-d"));
        if (FALSE === $having_emergency_service) {
            return "";
        }
        $List_of_branch_objects = \branch::read_branches_from_database();
        $emergency_service_content = "";
        if ('month' === $mode) {
            $emergency_service_content .= "<span class='emergency_service'>"
                    . gettext("emergency service")
                    . ": "
                    . $List_of_branch_objects[$having_emergency_service["branch_id"]]->short_name
                    . ", "
                    . $workforce->List_of_employees[$having_emergency_service["employee_id"]]->last_name
                    . "</span>\n";
        } else {
            $title = gettext("emergency service")
                    . ": "
                    . $List_of_branch_objects[$having_emergency_service["branch_id"]]->short_name
                    . ", "
                    . $workforce->List_of_employees[$having_emergency_service["employee_id"]]->last_name;
            $emergency_service_content .= "<span class='emergency_service' title='$title'>"
                    . mb_substr(gettext('emergency service'), 0, 2)
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

    private function build_absence_year_absent_employees_containers($date_object, $workforce, $Abwesende, $is_holiday, $mode = 'year') {
        if (!isset($Abwesende)) {
            return "";
        }
        if ($date_object->format("N") >= 6) {
            return "";
        }
        if ($is_holiday) {
            return "";
        }
        $absent_employees_containers = '';
        foreach (array_keys($Abwesende) as $employee_id) {
            $Absence = \absence::get_absence_data_specific($date_object->format('Y-m-d'), $employee_id);
            $employee_long_representation = " ";
            $span_class = "absent_employee_container"
                    . " " . $workforce->List_of_employees[$employee_id]->profession
                    . " " . $Absence['approval']
                    . " " . $mode;
            if ('month' == $mode) {
                /*
                 * In the year mode there is not enough space for the last names:
                 */
                $employee_long_representation = " " . mb_substr($workforce->List_of_employees[$employee_id]->last_name, 0, 16);
            }

            $absent_employees_containers .= "<span "
                    . " class='" . $span_class . "' "
                    . " onclick='insert_form_div(\"edit\")' "
                    . " title='" . $this->build_absence_year_absent_employees_containers_title_text($workforce, $Absence) . "' "
                    . " data-absence_details='" . json_encode($Absence) . "' "
                    . ">";
            $absent_employees_containers .= $employee_id;
            $absent_employees_containers .= $employee_long_representation;
            $absent_employees_containers .= "</span> \n";
        }
        return $absent_employees_containers;
    }

    private function build_absence_year_absent_employees_containers_title_text($workforce, $Absence) {
        $absence_title_text = $workforce->List_of_employees[$Absence['employee_id']]->last_name . "\n"
                . pdr_gettext($Absence['reason']) . "\n"
                . $Absence['comment'] . "\n"
                . gettext('from') . ' ' . strftime('%x', strtotime($Absence['start'])) . "\n"
                . gettext('to') . ' ' . strftime('%x', strtotime($Absence['end'])) . "\n"
                . sprintf(gettext('%1s days taken'), $Absence['days']) . "\n"
                . pdr_gettext($Absence['approval']) . "";
        return $absence_title_text;
    }

    private function get_classes_of_day_paragraph($date_object, $is_holiday, $input_date_object) {

        $Paragraph_class = array('day_paragraph');
        if ($date_object->format('N') < 6 and ! $is_holiday) {
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
     * @todo build a DateTime class inside the \MaMaKow\PDR namespace include this function.
     */
    private function get_month_name($date_object) {
        $Month_names = array(
            1 => gettext('January'),
            2 => gettext('February'),
            3 => gettext('March'),
            4 => gettext('April'),
            5 => gettext('May'),
            6 => gettext('June'),
            7 => gettext('July'),
            8 => gettext('August'),
            9 => gettext('September'),
            10 => gettext('October'),
            11 => gettext('November'),
            12 => gettext('December'),
        );
        return $Month_names[$date_object->format("n")];
    }

}
