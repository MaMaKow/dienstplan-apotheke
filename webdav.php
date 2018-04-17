<?php

/*
 * Wir erstellen eine umfassende Icalendar Datei (ICS). Diese kann dann von Kalenderprogrammen aboniert werden.
 */
require_once 'default.php';
if (!isset($_SESSION['user_employee_id'])) {
    require_once PDR_FILE_SYSTEM_APPLICATION_PATH . '/src/php/basic_access_authentication.php';
}
/*
 * @var $build_lunch_break_alert bool This variable is currently not used. In a future implementation, it should enable the user to request additional alerts (VALARM) to remind him on the beginning of the defined lunch break.
 */
$build_lunch_break_alert = user_input::get_variable_from_any_input('build_lunch_break_alert', FILTER_SANITIZE_NUMBER_INT, FALSE);
/*
 * @var $days_into_the_future int Number of days into the future. The roster for this number of consecutive days will be added to the iCalendar file.
 */
$days_into_the_future = user_input::get_variable_from_any_input('days_into_the_future', FILTER_SANITIZE_STRING, 30);
$date_sql = user_input::get_variable_from_any_input('datum', FILTER_SANITIZE_STRING, date('Y-m-d'));
$employee_id = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_employee_id']);
$workforce = new workforce($date_sql);
$date_sql_start = $date_sql;
$date_sql_end = date('Y-m-d', strtotime("+ $days_into_the_future days", strtotime($date_sql)));
$Roster = roster::read_employee_roster_from_database($employee_id, $date_sql_start, $date_sql_end);
header('Content-type: text/Calendar');
header('Content-Disposition: attachment; filename="Calendar.ics"');
echo iCalendar::build_ics_roster_employee($Roster);
