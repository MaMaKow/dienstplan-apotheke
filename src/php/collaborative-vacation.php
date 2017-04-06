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
        <style>
            div{
                /*border: solid red thin;*/
            }
            div.year_container{
                width: 120em;
            }
            div.month_container{
                font-family: monospace;
                width: 12em;
                float: left;
                /*display: inline-block;*/
            }
            p.day_paragraph{
                border: solid black thin;
                margin-bottom: 0em;
                margin-top: 0em;
                /*-webkit-margin-before: 0em;*/
            }
            p.weekday{
                background-color: transparent;
            }
            p.weekend{
                background-color: lightgray;
            }
            span.holiday{
                font-size: xx-small;
            }
            span.absent_employee_container{
                /*border: solid red thin;*/
                float: right;
                width: 2em;
                background-color: #B4B4B4;
            }
            span.Apotheker{
                background-color: #73AC22;
            }
            span.PI{
                background-color: #73AC22;
            }
            span.PTA{
                background-color: #BDE682;
            }
            span.PKA{
                background-color: #B4B4B4;
            }
            div.input_box_div{
                position: absolute;
                background-color: inherit;
                padding: 1em;
                /*margin: 2em;*/
            }
        </style>
        <script type="text/javascript">
            "use strict";
            function insert_form_div(element_mouse_is_over) {
                var x = event.clientX;
                var y = event.clientY;
                var element_mouse_is_over = document.elementFromPoint(x, y);
                var existing_div = document.getElementById('input_box_div');
                if (existing_div) {
                    if (!is_descendant(existing_div, element_mouse_is_over) && !is_descendant(element_mouse_is_over, existing_div)) {
                        //console.log('Destroying ' + existing_div);
                        //console.log(element_mouse_is_over + ' is not a child of ' + existing_div);
                        existing_div.parentNode.removeChild(existing_div);
                    } else {
                        return false; //Do not remove and rebuild when clicking inside the form.
                    }
                }
                //var prototype = document.getElementById('input_box_prototype');
                var div = document.createElement('div');
                element_mouse_is_over.appendChild(div);
                var rect = element_mouse_is_over.getBoundingClientRect();
                div.style.left = rect.left;
                div.style.top = rect.top;
                div.style.position = 'absolute';
                div.style.backgroundColor = 'inherit';
                div.id = 'input_box_div';
                div.className = 'input_box_div'
                fill_input_box_from_prototype(div);
            }
            function prefill_input_box_form() {
                var input_box_div = document.getElementById('input_box_div');
                var absence_details = JSON.parse(input_box_div.parentNode.attributes.absence_details.nodeValue);
                var employee_id_select = document.getElementById('employee_id_select');
                var employee_id_options = employee_id_select.options;
                for (var i = 0; i < employee_id_options.length; i++) {
                    if (absence_details.employee_id == employee_id_options[i].value) {
                        employee_id_options[i].selected = true;
                    }
                }
                document.getElementById('input_box_form_start_date').value = absence_details.start;
                document.getElementById('input_box_form_end_date').value = absence_details.end;
                document.getElementById('input_box_form_reason').value = absence_details.reason;
                //In order to remove the old entry we need the former values
                document.getElementById('input_box_form_start_date_old').value = absence_details.start;
                document.getElementById('employee_id_old').value = absence_details.employee_id;
            }
            function is_descendant(parent, child) {
                var node = child.parentNode;
                while (node !== null) {
                    if (node === parent) {
                        return true;
                    }
                    node = node.parentNode;
                }
                return false;
            }

            function fill_input_box_from_prototype(div) {
                var secondary_element = document.getElementById(div.id);
                var filename = 'src/php/collaborative-vacation-input-box.php';
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function () {
                    if (this.readyState == 4 && this.status == 200) {
                        secondary_element.innerHTML = xmlhttp.responseText;
                    }
                };
                xmlhttp.open("GET", filename, true);
                xmlhttp.send();
            }

        </script>
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
                echo "<div class=month_container>";
                echo $current_month_name . "<br>\n";
            }
            $date_text = date("D d", $date_unix);
            $current_week_day_number = date("N", $date_unix);
            if ($current_week_day_number < 6 and ! $is_holiday) {
                $paragraph_weekday_class = "weekday";
                if (isset($Abwesende)) {
                    unset($absent_employees_containers);
                    foreach ($Abwesende as $employee_id => $reason) {
                        $Absence = get_absence_data_specific($date_sql, $employee_id);
                        //print_debug_variable($Absence);

                        $absent_employees_containers .= "<span class='absent_employee_container $Ausbildung_mitarbeiter[$employee_id]' onclick='insert_form_div()' absence_details='" . json_encode($Absence) . "'>";
                        $absent_employees_containers .= $employee_id;
                        $absent_employees_containers .= "</span>\n";
                    }
                } else {
                    $absent_employees_containers = "";
                }
                echo "<p class='day_paragraph "
                . $paragraph_weekday_class
                . "'>"
                . $date_text
                . " "
                . $absent_employees_containers
                . "</p>\n";
            } elseif ($is_holiday) {
                $paragraph_weekday_class = "weekend";
                echo "<p class='day_paragraph "
                . $paragraph_weekday_class
                . "'>"
                . $date_text
                . "\n"
                . "<span class='holiday'>" . $is_holiday . "</span>"
                . "\n"
                . "</p>\n";
            } else {
                $paragraph_weekday_class = "weekend";
                echo "<p class='day_paragraph "
                . $paragraph_weekday_class
                . "'>"
                . $date_text
                . "\n"
                . "</p>\n";
            }
        }
        echo "\n</div>\n";
        echo "</div>\n";
        ?>
    </body>
</html>
