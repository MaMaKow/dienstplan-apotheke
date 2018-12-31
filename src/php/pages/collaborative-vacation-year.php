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
$workforce = new workforce();
$employee_id = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_object']->employee_id);
$year = \user_input::get_variable_from_any_input('year', FILTER_SANITIZE_NUMBER_INT, date("Y"));
$month_number = \user_input::get_variable_from_any_input('month_number', FILTER_SANITIZE_NUMBER_INT, date("n"));
create_cookie('month_number', $month_number, 1);
create_cookie('year', $year, 1);
create_cookie('employee_id', $employee_id, 1);
$collaborative_vacation = new collaborative_vacation();
$collaborative_vacation->handle_user_data_input($session);
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
$session->exit_on_missing_privilege('request_own_absence');

echo "<div id='input_box_data_div'></div>";
echo "<script>var employee_id = " . json_encode($employee_id, JSON_HEX_TAG) . ";</script>\n";
echo $collaborative_vacation->build_absence_year($year, $workforce);
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php';
?>
</BODY>
</HTML>
