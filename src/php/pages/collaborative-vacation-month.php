<?php
/*
 * Copyright (C) 2017 Mandelkow
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
require_once "../../../default.php";
$year = \user_input::get_variable_from_any_input('year', FILTER_SANITIZE_NUMBER_INT, date("Y"));
$month_number = \user_input::get_variable_from_any_input('month_number', FILTER_SANITIZE_NUMBER_INT, date("n"));
$dateStartObject = new DateTime("$year-01-01");
$dateEndObject = new DateTime("$year-12-31");
$workforce = new workforce($dateStartObject->format("Y-m-d"), $dateEndObject->format("Y-m-d"));
$employee_key = user_input::get_variable_from_any_input('employee_key', FILTER_SANITIZE_NUMBER_INT, $workforce->get_default_employee_key());
create_cookie('month_number', $month_number, 1);
create_cookie('year', $year, 1);
create_cookie("employee_key", $employee_key, 30);
//require_once PDR_FILE_SYSTEM_APPLICATION_PATH . "src/php/collaborative-vacation.php";
$collaborative_vacation = new collaborative_vacation();
$collaborative_vacation->handle_user_data_input($session);
if (isset($_POST) && !empty($_POST)) {
    // POST data has been submitted
    $location = PDR_HTTP_SERVER_APPLICATION_PATH . 'src/php/pages/collaborative-vacation-month.php' . "?year=$year&month_number=$month_number&employee_key=$employee_key";
    header('Location:' . $location);
    die("<p>Redirect to: <a href=$location>$location</a></p>");
}
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
$session->exit_on_missing_privilege('request_own_absence');

echo "<div id='input_box_data_div'></div>";
echo "<script>var employee_key = " . json_encode($employee_key, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) . ";</script>\n";
echo $collaborative_vacation->build_absence_month($year, $month_number);
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php';
?>
</BODY>
</HTML>
