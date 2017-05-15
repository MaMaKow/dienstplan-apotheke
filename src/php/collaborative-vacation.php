<?php

/*
  Copyright (C) 2017 Mandelkow

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Build a datalist for easy input of absence entries.
 * 
 * The list contains reasons of absence (like [de_DE] "Urlaub" or "Krank").
 * Only reasons that have been used at least 4 times are shown. (HAVING COUNT(*) > 3)
 * 
 * TODO: This function could also be used by abwesenheit-in.php.
 * 
 * @return string $datalist HTML datalist element.
 */
function build_datalist() {
    //Build a datalist with common reasons fo absence:
    $query = "SELECT `reason` FROM `absence` GROUP BY `reason` HAVING COUNT(*) > 3 ORDER BY `reason` ASC";
    $result = mysqli_query_verbose($query);
    $datalist = "<datalist id='reasons'>\n";
    while ($row = mysqli_fetch_object($result)) {
        $datalist .= "\t<option value='$row->reason'>\n";
    }
    $datalist .= "</datalist>\n";
    return $datalist;
}

/**
 * Handle the user input.
 * 
 * @global int $year
 * 
 * @return void
 */
function handle_user_data_input() {
    //Work on user data:
    global $year;
    if (isset($_POST["year"])) {
        $year = filter_input(INPUT_POST, "year", FILTER_SANITIZE_NUMBER_INT);
    } else {
        $year = date("Y");
    }
    if (isset($_POST['command'])) {
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
    global $user;
    $employee_id = filter_input(INPUT_POST, employee_id, FILTER_SANITIZE_NUMBER_INT);
    $start_date_string = filter_input(INPUT_POST, start_date, FILTER_SANITIZE_STRING);
    $end_date_string = filter_input(INPUT_POST, end_date, FILTER_SANITIZE_STRING);
    $reason = filter_input(INPUT_POST, reason, FILTER_SANITIZE_STRING);
    $command = filter_input(INPUT_POST, command, FILTER_SANITIZE_STRING);
    $employee_id_old = filter_input(INPUT_POST, employee_id_old, FILTER_SANITIZE_STRING);
    $start_date_old_string = filter_input(INPUT_POST, start_date_old, FILTER_SANITIZE_STRING);

    //Decide on $approval state
    $query = "SELECT `approval` FROM absence WHERE `employee_id` = '$employee_id_old' AND `start` = '$start_date_old_string'";
    $result = mysqli_query_verbose($query);
    $row = mysqli_fetch_object($result);
    if (empty($row->approval)) {
        $approval = "not_yet_approved";
    } else {
        $approval = $row->approval;
    }

    /**
     * Delete old entries
     * $employee_id_old and $start_date_old_string are NULL for new entries. Therefore there will be no deletions.
     */
    $query = "DELETE FROM absence WHERE `employee_id` = '$employee_id_old' AND `start` = '$start_date_old_string'";
    $result = mysqli_query_verbose($query);

    /*
     * Insert new entry data into the table absence.
     */
    if ("save" === $command) {
        /*
         * The function calculate_absence_days() currenty is defined within "db-lesen-abwesenheit.php".
         * TODO: Maybe there should be a common library/class for all the (common) absence functions.
         */
        $days = calculate_absence_days($start_date_string, $end_date_string);
        $query = "INSERT INTO absence ("
                . " `employee_id`,"
                . " `start`,"
                . " `end`,"
                . " `days`,"
                . " `reason`,"
                . " `approval`,"
                . " `user`"
                . ") VALUES ("
                . " '$employee_id',"
                . " '$start_date_string',"
                . " '$end_date_string',"
                . " '$days',"
                . " '$reason',"
                . " '$approval',"
                . " '$user'"
                . ")";
        $result = mysqli_query_verbose($query);
    }
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
 * @global array[string] $Ausbildung_mitarbeiter Discriminate between professions e.g. "Pharmacist", "Pharmacy technician (PTA)"
 * @return string HTML div element containing a calendar with absences.
 */
function build_absence_year($year) {
    global $Ausbildung_mitarbeiter;

    $start_date = mktime(0, 0, 0, 1, 1, $year);
    $current_month = date("n", $start_date);
    //print_debug_variable(strftime("äöüÄÖÜß %B", 1490388361));
    //$system_encoding = mb_detect_encoding(strftime("äöüÄÖÜß %B", 1490388361), "auto");
    //print_debug_variable("\$system_encoding", $system_encoding);
    //$current_month_name = mb_convert_encoding(strftime("%B", $date_unix), "UTF-8", 'Windows-1252');
    $current_month_name = get_utf8_month_name($date_unix);
    $current_year = date("Y", $start_date);

    $year_container_html = "<div class=year_container>\n";

    //The following lines for the year select are common code with anwesenheitsliste-out.php
    $Years = array();
    $abfrage = "SELECT DISTINCT YEAR(`Datum`) AS `year` FROM `Dienstplan`";
    $ergebnis = mysqli_query_verbose($abfrage);
    while ($row = mysqli_fetch_object($ergebnis)) {
        $Years[] = $row->year;
    }
    $year_input_select = "<form id='select_year'><select name=year onchange=this.form.submit()>";
    foreach ($Years as $year_number) {
        $year_input_select .= "<option value=$year_number";
        if ($year_number == $current_year) {
            $year_input_select .= " SELECTED ";
        }
        $year_input_select .= ">$year_number</option>\n";
    }
    $year_input_select .= "</select></form>";

    $year_container_html .= $year_input_select;
    $month_container_html = "<div class=month_container>";
    $month_container_html .= $current_month_name . "<br>\n";
    $one_day_in_seconds = 24 * 60 * 60;
    for ($date_unix = $start_date; $date_unix < strtotime("+ 1 year", $start_date); $date_unix += $one_day_in_seconds) {
        $date_sql = date('Y-m-d', $date_unix);
        $is_holiday = is_holiday($date_unix);
        list($Abwesende,, ) = db_lesen_abwesenheit($date_unix);

        if ($current_month < date("n", $date_unix)) {
            /** begin a new month div */
            $current_month = date("n", $date_unix);
            $current_month_name = get_utf8_month_name($date_unix);
            $month_container_html .= "</div>";
            $month_container_html .= "<div class='month_container'>";
            $month_container_html .= $current_month_name . "<br>\n";
        }
        $date_text = date("D d", $date_unix);
        $current_week_day_number = date("N", $date_unix);



        if (isset($Abwesende)) {
            unset($absent_employees_containers);
            foreach ($Abwesende as $employee_id => $reason) {
                $Absence = get_absence_data_specific($date_sql, $employee_id);
                //print_debug_variable($Absence);

                $absent_employees_containers .= "<span class='absent_employee_container $Ausbildung_mitarbeiter[$employee_id]' onclick='insert_form_div(\"edit\")' absence_details='" . json_encode($Absence) . "'>";
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
        $p_html_attributes .= " date_unix='$date_unix'>";
        /*
         * TODO: Use data-* attributes to store the data in a valid way:
         * https://developer.mozilla.org/en-US/docs/Learn/HTML/Howto/Use_data_attributes
          $p_html_attributes = " data-date_sql='$date_sql'";
          $p_html_attributes .= " data-date_unix='$date_unix'>";
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
    $month_container_html .= "\t</div>\n";
    $year_container_html .= $month_container_html;
    $year_container_html .= "</div>\n";
    return $year_container_html;
}
