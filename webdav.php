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

/*
 * We are creating an iCalendar file (*.ics). This file can be read by calendaring applications.
 */

require_once 'default.php';
if (!isset($_SESSION['user_object']->employee_id)) {
    require_once PDR_FILE_SYSTEM_APPLICATION_PATH . '/src/php/basic_access_authentication.php';
}
/*
 * @var $days_into_the_future int Number of days into the future. The roster for this number of consecutive days will be added to the iCalendar file.
 */
$days_into_the_future = user_input::get_variable_from_any_input('days_into_the_future', FILTER_SANITIZE_STRING, 30);
$date_string = user_input::get_variable_from_any_input('date_string', FILTER_SANITIZE_STRING, date('Y-m-d'));
$date_start_object = new \DateTime($date_string);
$employee_id = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_object']->employee_id);
$create_valarm = user_input::get_variable_from_any_input('create_valarm', FILTER_SANITIZE_NUMBER_INT, 0);
$date_end_object = clone $date_start_object;
$date_end_object->add(new \DateInterval('P' . $days_into_the_future . 'D'));
$roster_object = new roster($date_start_object, $date_end_object, $employee_id, NULL);
$Roster = $roster_object->array_of_days_of_roster_items;
header('Content-type: text/Calendar');
header('Content-Disposition: attachment; filename="Calendar.ics"');
echo iCalendar::build_ics_roster_employee($Roster, $create_valarm);
