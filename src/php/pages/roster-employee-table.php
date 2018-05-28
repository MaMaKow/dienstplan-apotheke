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
require '../../../default.php';

$tage = 7;
$date_sql_user_input = user_input::get_variable_from_any_input('datum', FILTER_SANITIZE_NUMBER_INT, date('Y-m-d'));
$date_sql = general_calculations::get_first_day_of_week($date_sql_user_input);
$date_unix = strtotime($date_sql);
$date_sql_start = $date_sql;
$date_sql_end = date('Y-m-d', strtotime('+ ' . ($tage - 1) . ' days', $date_unix));
create_cookie('datum', $date_sql, 1);
$workforce = new workforce($date_sql);

$employee_id = (int) user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_employee_id']);
create_cookie('employee_id', $employee_id, 1);

/*
 * Get a list of employees:
 */
if (!isset($workforce->List_of_employees[$employee_id])) {
    /* This happens if a coworker is not working with us anymore.
     * He can still be chosen within abwesenheit and stunden.
     * Therefore we might get his/her id in the cookie.
     * Now we just change it to someone, who is actually there:
     */
    $employee_id = $_SESSION['user_employee_id'];
}

$Roster = roster::read_employee_roster_from_database($employee_id, $date_sql_start, $date_sql_end);
foreach (array_keys($List_of_branch_objects) as $other_branch_id) {
    /*
     * The $Branch_roster contanins all the rosters from all branches, including the current branch.
     */
    $Branch_roster[$other_branch_id] = roster::read_branch_roster_from_database($workforce->List_of_employees[$employee_id]->principle_branch_id, $other_branch_id, $date_sql_start, $date_sql_end);
}

//Produce the output:
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
echo "<div id=main-area>\n";
echo "<a href=" . PDR_HTTP_SERVER_APPLICATION_PATH . "src/php/pages/roster-week-table.php?datum=" . htmlentities(date('Y-m-d', $date_unix)) . "'> " . gettext("calendar week") . strftime(' %V', $date_unix) . "</a><br>\n";

echo build_html_navigation_elements::build_select_employee($employee_id, $workforce->List_of_employees);

//Navigation between the weeks:
echo build_html_navigation_elements::build_button_week_backward($date_sql);
echo build_html_navigation_elements::build_button_week_forward($date_sql);
echo build_html_navigation_elements::build_button_link_download_ics_file($date_sql, $employee_id);

echo "<table>\n";
echo build_html_roster_views::build_roster_read_only_table_head($Roster);
echo build_html_roster_views::build_roster_readonly_employee_table($Roster, $workforce->List_of_employees[$employee_id]->principle_branch_id);
$table_foot_html = "<tfoot>"
        //. "<tr class=page-break></tr>"
        . "\n<tr>\n";

/*
 * We are having a look into the absence data:
 */
foreach (array_keys($Roster) as $date_unix) {
    $date_sql = date('Y-m-d', $date_unix);
    $Absentees = absence::read_absentees_from_database($date_sql);

    /*
     * Now we build a row of absent employees in the foot of the table.
     */
    if (isset($Absentees)) {
        $table_foot_html .= build_html_roster_views::build_absentees_column($Absentees);
    } else {
        $table_foot_html .= "</td><td>";
    }
}
$table_foot_html .= "</tr>\n";
$table_foot_html .= "</tfoot>\n";
echo "$table_foot_html";
echo "</table>\n";


$Working_hours_week_have = roster::calculate_working_hours_weekly_from_branch_roster($Branch_roster);
$Working_hours_week_should = build_html_roster_views::calculate_working_hours_week_should($Roster);
echo build_html_roster_views::build_roster_working_hours_div($Working_hours_week_have, $Working_hours_week_should, array('employee_id' => $employee_id));
echo "</div>\n";

require PDR_FILE_SYSTEM_APPLICATION_PATH . 'contact-form.php';
?>
</BODY>
</HTML>
