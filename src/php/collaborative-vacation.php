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

/**
 * Handle the user input.
 *
 * @global int $year
 *
 * @return void
 */
function handle_user_data_input() {
    global $session;
    if (!$session->user_has_privilege('request_own_absence') and ! $session->user_has_privilege('create_absence')) {
        return FALSE;
    }

    //Work on user data:
    global $year;
    global $month_number;
    if (filter_has_var(INPUT_POST, "year")) {
        $year = filter_input(INPUT_POST, "year", FILTER_SANITIZE_NUMBER_INT);
    } elseif (filter_has_var(INPUT_COOKIE, "year")) {
        $year = filter_input(INPUT_COOKIE, "year", FILTER_SANITIZE_NUMBER_INT);
    } else {
        $year = date("Y");
    }
    if (filter_has_var(INPUT_POST, "month_number")) {
        $month_number = filter_input(INPUT_POST, "month_number", FILTER_SANITIZE_NUMBER_INT);
    } elseif (filter_has_var(INPUT_COOKIE, "month_number")) {
        $month_number = filter_input(INPUT_COOKIE, "month_number", FILTER_SANITIZE_NUMBER_INT);
    } else {
        $month_number = date("n");
    }
    create_cookie('month_number', $month_number, 1);
    create_cookie('year', $year, 1);
    if (filter_has_var(INPUT_POST, 'approve_absence')) {
        approve_absence_to_database();
    }
    if (filter_has_var(INPUT_POST, 'command')) {
        write_user_input_to_database();
    }
}

/**
 * Fill new entries into absence table or change, delete old entries.
 *
 * The approval status of new entries defaults to "not_yet_approved".
 * Old entries keep their approval state.
 * TODO: THere is no approval-tool for administrator users yet.
 *
 * @global type $user from default.php
 * @return void
 */
function write_user_input_to_database() {
    global $session;

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
        if ($_SESSION['user_employee_id'] !== $employee_id) {
            /*
             * TODO: Make this an email.
             * TODO: Build a contact class to handle this.
             */
            error_log("Permissions: Employee " . $_SESSION['user_employee_id'] . " tried to request holidays for employee " . $employee_id);
            return FALSE;
        }
        if ("" !== $employee_id_old and $_SESSION['user_employee_id'] !== $employee_id_old) {
            /*
             * TODO: Make this an email.
             * TODO: Build a contact class to handle this.
             */
            error_log("Permissions: Employee " . $_SESSION['user_employee_id'] . " tried to request holidays from employee " . $employee_id_old);
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
      $result = database_wrapper::instance()->run($query);
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
    $result = database_wrapper::instance()->run($query, array('employee_id' => $employee_id_old, 'start' => $start_date_old_string));

    /*
     * Insert new entry data into the table absence.
     */
    if ("save" === $command) {
        $workforce = new workforce();
        $employee_object = $workforce->List_of_employees[$employee_id];
        $days = absence::calculate_employee_absence_days($start_date_string, $end_date_string, $employee_object);
        $query = "INSERT INTO absence (`employee_id`, `start`, `end`, `days`, `reason`, `comment`, `approval`, `user`) "
                . "VALUES (:employee_id, :start, :end, :days, :reason, :comment, :approval, :user)";
        $result = database_wrapper::instance()->run($query, array(
            'employee_id' => $employee_id,
            'start' => $start_date_string,
            'end' => $end_date_string,
            'days' => $days,
            'reason' => $reason,
            'comment' => $comment,
            'approval' => $approval,
            'user' => $_SESSION['user_name']
        ));
    }
}

/**
 * Approve entries in the database or set them to pending or disapproved.
 *
 * @global object $session session data from logged in user
 * @return void
 */
function approve_absence_to_database() {
    global $session;
    if (!$session->user_has_privilege('create_absence')) {
        /*
         * User is allowed to write any input to the database.
         */
        return FALSE;
    }

    $approval = filter_input(INPUT_POST, 'approve_absence', FILTER_SANITIZE_STRING);
    $employee_id_old = filter_input(INPUT_POST, 'employee_id_old', FILTER_SANITIZE_STRING);
    $start_date_old_string = filter_input(INPUT_POST, 'start_date_old', FILTER_SANITIZE_STRING);

    $query = "UPDATE `absence` SET `approval` = :approval "
            . " WHERE `employee_id` = :employee_id AND `start` = :start";
    $result = database_wrapper::instance()->run($query, array('approval' => $approval, 'employee_id' => $employee_id_old, 'start' => $start_date_old_string));
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
 *
 * @param int $year
 * @global array[string] $List_of_employee_professions Discriminate between professions e.g. "Pharmacist", "Pharmacy technician (PTA)"
 * @return string HTML div element containing a calendar with absences.
 */
function build_absence_year($year) {
    global $List_of_employee_professions, $workforce;
    $start_date = mktime(0, 0, 0, 1, 1, $year);
    $current_month = date("n", $start_date);
    //$system_encoding = mb_detect_encoding(strftime("äöüÄÖÜß %B", 1490388361), "auto");
    //$current_month_name = mb_convert_encoding(strftime("%B", $date_unix), "UTF-8", 'Windows-1252');
    $current_month_name = get_utf8_month_name($start_date);
    $current_year = date("Y", $start_date);

    $year_container_html = "<div class=year_container>\n";


    $year_container_html .= absence::build_html_select_year($current_year);
    $month_container_html = "<div class='year_quarter_container'>";
    $month_container_html .= "<div class=month_container>";
    $month_container_html .= $current_month_name . "<br>\n";
    for ($date_unix = $start_date; $date_unix < strtotime("+ 1 year", $start_date); $date_unix += PDR_ONE_DAY_IN_SECONDS) {
        $date_sql = date('Y-m-d', $date_unix);
        $is_holiday = holidays::is_holiday($date_unix);
        $Abwesende = absence::read_absentees_from_database($date_sql);

        if ($current_month < date("n", $date_unix)) {
            /** begin a new month div */
            $current_month = date("n", $date_unix);
            $current_month_name = get_utf8_month_name($date_unix);
            $month_container_html .= "</div>";
            //if (in_array($current_month, array(4, 7, 10))) {
            if (in_array($current_month, array(7))) {
                $month_container_html .= "</div><!-- class='year_quarter_container'-->";
                $month_container_html .= "<div class='year_quarter_container'>";
            }
            $month_container_html .= "<div class='month_container'>";
            $month_container_html .= $current_month_name . "<br>\n";
        }
        $date_text = date("D d", $date_unix);
        $current_week_day_number = date("N", $date_unix);



        if (isset($Abwesende)) {
            $absent_employees_containers = "";
            foreach ($Abwesende as $employee_id => $reason) {
                $Absence = absence::get_absence_data_specific($date_sql, $employee_id);
                $absence_title_text = ""
                        . $workforce->List_of_employees[$Absence['employee_id']]->last_name . "\n"
                        . pdr_gettext($Absence['reason']) . "\n"
                        . $Absence['comment'] . "\n"
                        . gettext("from") . " " . strftime('%x', strtotime($Absence['start'])) . "\n"
                        . gettext("to") . " " . strftime('%x', strtotime($Absence['end'])) . "\n"
                        . pdr_gettext($Absence['approval']) . "";

                $absent_employees_containers .= "<span "
                        . "class='absent_employee_container " . $workforce->List_of_employees[$employee_id]->profession . " " . $Absence['approval'] . "' "
                        . "onclick='insert_form_div(\"edit\")' "
                        . "title='$absence_title_text'"
                        . "data-absence_details='" . json_encode($Absence) . "'>";
                $absent_employees_containers .= $employee_id;
                $absent_employees_containers .= "</span>\n";
            }
        } else {
            $absent_employees_containers = "";
        }
        $p_html = "<p class='day_paragraph ";
        if ($current_week_day_number < 6 and ! $is_holiday) {
            $paragraph_weekday_class = "weekday";
        } else {
            $paragraph_weekday_class = "weekend";
        }
        $p_html .= $paragraph_weekday_class . "'";
//                $p_html_javascript = "' onclick='insert_form_div(\"create\")'";
        $p_html_javascript = " onmousedown='highlight_absence_create_start(event)'";
        $p_html_javascript .= " onmouseover='highlight_absence_create_intermediate(event)'";
        $p_html_javascript .= " onmouseup='highlight_absence_create_end(event)'";
        $p_html_attributes = " date_sql='$date_sql'";
        $p_html_attributes .= " data-date_sql='$date_sql'";
        $p_html_attributes .= " date_unix='$date_unix'";
        $p_html_attributes .= " data-date_unix='$date_unix'";
        $p_html_attributes .= ">";
        /*
         * TODO: Use data-* attributes to store the data in a valid way:
         * https://developer.mozilla.org/en-US/docs/Learn/HTML/Howto/Use_data_attributes
         * Or store all the data in one array for javascript somewhere at the beginning of the page.
         * Perhaps even use that one data to reduce the amount of SQL calls in PHP
         */
        $p_html_content = $date_text . " ";
        if ($current_week_day_number < 6 and ! $is_holiday) {
            $p_html_content .= $absent_employees_containers;
        }
        if ($is_holiday) {
            $p_html_content .= "<span class='holiday'>" . $is_holiday . "</span>\n";
        }
        $p_html .= $p_html_javascript;
        $p_html .= $p_html_attributes;
        $p_html .= $p_html_content;
        $p_html .= "</p>\n";
        $month_container_html .= $p_html;
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
function build_absence_month($year, $month_number) {
    global $workforce, $List_of_employee_professions;
    $input_date = mktime(8, 0, 0, $month_number, 1, $year);
    $monday_difference = date('w', $input_date) - 1; //Get start of the week
    if (-1 === $monday_difference) {
        $extra_days = 7;
    } else {
        $extra_days = 0;
    }
    $start_date = $input_date - ($monday_difference + $extra_days) * PDR_ONE_DAY_IN_SECONDS;
    $end_date = $input_date + (7 * 5 - $monday_difference) * PDR_ONE_DAY_IN_SECONDS;
    $current_month = date("n", $input_date);
    $current_week = date("W", $input_date);
    $current_month_name = get_utf8_month_name($input_date);
    $current_year = date("Y", $input_date);

    $month_container_html = "";

    $month_container_html .= absence::build_html_select_year($current_year);
    $month_container_html .= absence::build_html_select_month($current_month);
    $table_header_of_weekdays = "<tr>";
    for ($date_unix = $start_date; $date_unix < $start_date + 7 * PDR_ONE_DAY_IN_SECONDS; $date_unix += PDR_ONE_DAY_IN_SECONDS) {
        $table_header_of_weekdays .= "<td class=day_column_head>" . strftime("%A", $date_unix) . "</td>";
    }
    $table_header_of_weekdays .= "</tr>";

    $week_container_html = "<table class='month_container noselect'>"
            . "$table_header_of_weekdays"
            . "<tr class=week_container>";
    //$week_container_html .= $current_month_name . "<br>\n";
    for ($date_unix = $start_date; $date_unix < $end_date; $date_unix += PDR_ONE_DAY_IN_SECONDS) {

        $date_sql = date('Y-m-d', $date_unix);
        $is_holiday = holidays::is_holiday($date_unix);
        $having_emergency_service = pharmacy_emergency_service::having_emergency_service($date_sql);

        $Abwesende = absence::read_absentees_from_database($date_sql);

        if ($current_week < date("W", $date_unix)) {
            /** begin a new month div */
            $current_week = date("W", $date_unix);
            $current_month_name = get_utf8_month_name($date_unix);
            $week_container_html .= "</tr>";
            $week_container_html .= "<tr class=week_container>";
            //$week_container_html .= $current_month_name . "<br>\n";
        }
        $date_text = date("d.m.", $date_unix);
        $current_week_day_number = date("N", $date_unix);



        $absent_employees_containers = "";
        if (isset($Abwesende)) {
            foreach ($Abwesende as $employee_id => $reason) {
                $Absence = absence::get_absence_data_specific($date_sql, $employee_id);
                $absence_title_text = ""
                        . $workforce->List_of_employees[$Absence['employee_id']]->last_name . "\n"
                        . pdr_gettext($Absence['reason']) . "\n"
                        . $Absence['comment'] . "\n"
                        . gettext("from") . " " . strftime('%x', strtotime($Absence['start'])) . "\n"
                        . gettext("to") . " " . strftime('%x', strtotime($Absence['end'])) . "\n"
                        . pdr_gettext($Absence['approval']) . "";

                $absent_employees_containers .= "<span "
                        . "class='absent_employee_container " . $workforce->List_of_employees[$employee_id]->profession . " " . $Absence['approval'] . "' "
                        . "onclick='insert_form_div(\"edit\")' "
                        . "title='$absence_title_text'"
                        . "data-absence_details='" . json_encode($Absence) . "'>";
                $absent_employees_containers .= $employee_id . " " . mb_substr($workforce->List_of_employees[$employee_id]->last_name, 0, 16);
                $absent_employees_containers .= "</span><br>\n";
            }
        }
        if ($current_week_day_number < 6 and ! $is_holiday) {
            $Paragraph_class[] = "weekday";
        } else {
            $Paragraph_class[] = "weekend";
        }
        if (date('n', $date_unix) !== date('n', $input_date)) {
            $Paragraph_class[] = "adjacent_month";
        }
        if (date('Y-m-d', $date_unix) === date('Y-m-d', time())) {
            $Paragraph_class[] = "today";
        }
        $html_class_list = get_classes_of_day_paragraph($current_week_day_number, $is_holiday, $date_unix, $input_date);
        $p_html = "<td class='$html_class_list'";
        unset($Paragraph_class);
        //$p_html .= $paragraph_weekday_class . " " . $paragraph_adjacent_month_class . "'";
        $p_html_javascript = " onmousedown='highlight_absence_create_start(event)'";
        $p_html_javascript .= " onmouseover='highlight_absence_create_intermediate(event)'";
        $p_html_javascript .= " onmouseup='highlight_absence_create_end(event)'";
        $p_html_attributes = " data-date_sql='$date_sql'";
        $p_html_attributes .= " date_unix='$date_unix'";
        $p_html_attributes .= " data-date_unix='$date_unix'";
        $p_html_attributes .= ">";
        /*
         * TODO: Use data-* attributes to store the data in a valid way:
         * https://developer.mozilla.org/en-US/docs/Learn/HTML/Howto/Use_data_attributes
          $p_html_attributes = " data-date_sql='$date_sql'";
          $p_html_attributes .= " data-date_unix='$date_unix'>";
         * Or store all the data in one array for javascript somewhere at the beginning of the page.
         * Perhaps even use that one data to reduce the amount of SQL calls in PHP
         */
        $p_html_content = "<strong>" . $date_text . "</strong><br> ";
        if ($current_week_day_number < 6 and ! $is_holiday) {
            $p_html_content .= $absent_employees_containers;
        }
        if ($is_holiday) {
            $p_html_content .= "<span class='holiday'>" . $is_holiday . "</span>\n";
        }

        if (FALSE !== $having_emergency_service) {
            $List_of_branch_objects = branch::read_branches_from_database();
            $p_html_content .= "<p class='emergency_service'>"
                    . gettext("emergency service")
                    . ":<br>"
                    . $List_of_branch_objects[$having_emergency_service["branch_id"]]->short_name
                    . ",<br>"
                    . $workforce->List_of_employees[$having_emergency_service["employee_id"]]->last_name
                    . "</p>\n";
        }
        $p_html .= $p_html_javascript;
        $p_html .= $p_html_attributes;
        $p_html .= $p_html_content;
        $p_html .= "</td>\n";
        $week_container_html .= $p_html;
    }
    $week_container_html .= "</tr></table></div>\n";
    $month_container_html .= $week_container_html;

    return $month_container_html;
}

function get_classes_of_day_paragraph($current_week_day_number, $is_holiday, $date_unix, $input_date) {
    $Paragraph_class = array('day_paragraph');
    if ($current_week_day_number < 6 and ! $is_holiday) {
        $Paragraph_class[] = 'weekday';
    } else {
        $Paragraph_class[] = 'weekend';
    }
    if (date('n', $date_unix) !== date('n', $input_date)) {
        $Paragraph_class[] = 'adjacent_month';
    }
    if (date('Y-m-d', $date_unix) === date('Y-m-d', time())) {
        $Paragraph_class[] = 'today';
    }
    $html_class_list = implode(' ', $Paragraph_class);
    return $html_class_list;
}

/**
 * Returns the localized name of the month correctly on Windows and *nix
 *
 * On windows systems the function strftime() will not use utf8 encoding.
 * It ignores setlocale().
 *
 * @param int $date_unix unix time.
 * @return string $month_name month name.
 */
function get_utf8_month_name($date_unix) {
    $month_name = strftime("%B", $date_unix);
    if (running_on_windows()) {
        return utf8_encode($month_name);
    }
    return $month_name;
}
