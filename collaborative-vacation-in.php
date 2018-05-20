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
require_once "default.php";
$workforce = new workforce();
$employee_id = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_employee_id']);
create_cookie('employee_id', $employee_id, 1);
require_once "src/php/collaborative-vacation.php";
handle_user_data_input();
require "head.php";
require 'src/php/pages/menu.php';
if (!$session->user_has_privilege('request_own_absence') and ! $session->user_has_privilege('create_absence')) {
    echo build_warning_messages("", ["Die notwendige Berechtigung zum Beantragen von Abwesenheiten fehlt. Bitte wenden Sie sich an einen Administrator."]);
    die();
}

echo "<div id='input_box_data_div'></div>";
echo build_datalist();
echo "<script>var employee_id = " . json_encode($employee_id, JSON_HEX_TAG) . ";</script>\n";
echo build_absence_year($year);
require 'contact-form.php';
?>
</BODY>
</HTML>
