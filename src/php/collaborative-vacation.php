<!DOCTYPE html>
<!--
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
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
        <script>
            var employee_id = <?php echo json_encode($employee_id, JSON_HEX_TAG); ?>;
        </script>
        <SCRIPT type="text/javascript" src="js/collaborative-vacation.js" ></SCRIPT>
        <LINK rel="stylesheet" type="text/css" href="css/collaborative-vacation.css" media="all">
    </head>
    <body>
        <?php
        if (!function_exists('is_holiday')) {
            require "src/php/calculate-holidays.php";
        }
        //Build a datalist with common reasons fo absence:
        $query = "SELECT `reason` FROM `absence` GROUP BY `reason` HAVING COUNT(*) > 3 ORDER BY `reason` ASC";
        $result = mysqli_query_verbose($query);
        $datalist = "<datalist id='reasons'>\n";
        while ($row = mysqli_fetch_object($result)) {
            $datalist .= "\t<option value='$row->reason'>\n";
        }
        $datalist .= "</datalist>\n";
        echo "$datalist";

        //Work on user data:
        if (isset($_POST["year"])) {
            $year = filter_input(INPUT_POST, "year", FILTER_SANITIZE_NUMBER_INT);
        } else {
            $year = date("Y");
        }

        $start_date = mktime(0, 0, 0, 1, 1, $year);
        $current_month = date("n", $start_date);
        $current_month_name = date("F", $start_date);
        $current_year = date("Y", $start_date);

        function write_user_input_to_database() {
            global $user;
            $employee_id = filter_input(INPUT_POST, employee_id, FILTER_SANITIZE_NUMBER_INT);
            $start_date_string = filter_input(INPUT_POST, start_date, FILTER_SANITIZE_STRING);
            $end_date_string = filter_input(INPUT_POST, end_date, FILTER_SANITIZE_STRING);
            $reason = filter_input(INPUT_POST, reason, FILTER_SANITIZE_STRING);
            $command = filter_input(INPUT_POST, command, FILTER_SANITIZE_STRING);
            $employee_id_old = filter_input(INPUT_POST, employee_id_old, FILTER_SANITIZE_STRING);
            $start_date_old_string = filter_input(INPUT_POST, start_date_old, FILTER_SANITIZE_STRING);
            $query = "SELECT `approval` FROM absence WHERE `employee_id` = '$employee_id_old' AND `start` = '$start_date_old_string'";
            $result = mysqli_query_verbose($query);
            $row = mysqli_fetch_object($result);
            if (empty($row->approval)) {
                $approval = "not_yet_approved";
            } else {
                $approval = $row->approval;
            }
            $query = "DELETE FROM absence WHERE `employee_id` = '$employee_id_old' AND `start` = '$start_date_old_string'";
            $result = mysqli_query_verbose($query);
            if ("save" === $command) {
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

        if (isset($_POST['command'])) {
            write_user_input_to_database();
        }

        echo "<div class=year_container>\n";
        echo $current_year . "<br>\n";
        echo "<div class=month_container>";
        echo $current_month_name . "<br>\n";
        for ($date_unix = $start_date; $date_unix < strtotime("+ 1 year", $start_date); $date_unix += 24 * 60 * 60) {
            $date_sql = date('Y-m-d', $date_unix);
            $is_holiday = is_holiday($date_unix);
            list($Abwesende, $Urlauber, $Kranke) = db_lesen_abwesenheit($date_unix);

            if ($current_month < date("n", $date_unix)) {
                $current_month = date("n", $date_unix);
                $current_month_name = date("F", $date_unix);
                echo "</div>";
                echo "<div class='month_container'>";
                echo $current_month_name . "<br>\n";
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
            $p_html = "<p class='day_paragraph noselect ";
            if ($current_week_day_number < 6 and ! $is_holiday) {
                $paragraph_weekday_class = "weekday";
            } else {
                $paragraph_weekday_class = "weekend";
            }
            $p_html .= $paragraph_weekday_class;
//                $p_html_javascript = "' onclick='insert_form_div(\"create\")'";
            $p_html_javascript .= " onmousedown='highlight_absence_create_start()'";
            $p_html_javascript .= " onmouseover='highlight_absence_create_intermediate()'";
            $p_html_javascript .= " onmouseup='highlight_absence_create_end()'";
            $p_html_attributes = " date_sql='$date_sql'";
            $p_html_attributes .= " date_unix='$date_unix'>";
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
            echo $p_html;
        }
        echo "\t</div>\n";
        echo "</div>\n";
        ?>
    </body>
</html>
