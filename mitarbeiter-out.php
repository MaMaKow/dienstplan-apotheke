<?php
require 'default.php';

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

//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';
if (!isset($workforce->List_of_employees[$employee_id])) {
    /* This happens if a coworker is not working with us anymore.
     * He can still be chosen within abwesenheit and stunden.
     * Therefore we might get his/her id in the cookie.
     * Now we just change it to someone, who is actually there:
     */
    $employee_id = $_SESSION['user_employee_id'];
}

//require 'db-lesen-woche-mitarbeiter.php';
$Roster = roster::read_employee_roster_from_database($employee_id, $date_sql_start, $date_sql_end);
foreach (array_keys($List_of_branch_objects) as $other_branch_id) {
    /*
     * The $Branch_roster contanins all the rosters from all branches, including the current branch.
     */
    $Branch_roster[$other_branch_id] = roster::read_branch_roster_from_database($workforce->List_of_employees[$employee_id]->principle_branch_id, $other_branch_id, $date_sql_start, $date_sql_end);
}

//$VKcount = count($List_of_employees); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
//end($List_of_employees); $VKmax=key($List_of_employees); reset($List_of_employees); //Wir suchen nach der höchsten VK-Nummer VKmax.
//$VKmax = max(array_keys($List_of_employees));
//Produce the output:
require 'head.php';
require 'src/php/pages/menu.php';
echo "<div id=main-area>\n";
echo "<a href='woche-out.php?datum=" . htmlentities($date_unix) . "'> " . gettext("calendar week") . strftime(' %V', $date_unix) . "</a><br>\n";

echo build_html_navigation_elements::build_select_employee($employee_id, $List_of_employees);

//Navigation between the weeks:
echo build_html_navigation_elements::build_button_week_backward($date_sql);
echo build_html_navigation_elements::build_button_week_forward($date_sql);
echo build_html_navigation_elements::build_button_link_download_ics_file($date_sql, $employee_id);

echo "<table>\n";
echo build_html_roster_views::build_roster_read_only_table_head($Roster);

foreach ($Roster as $Roster_day_array) {
    $Row_count[] = count($Roster_day_array);
}
$row_count = max($Row_count);
echo build_html_roster_views::build_roster_readonly_employee_table($Roster, $workforce->List_of_employees[$employee_id]->principle_branch_id);
echo "</table>\n";


$Working_hours_week_have = roster::calculate_working_hours_weekly_from_branch_roster($Branch_roster);
$Working_hours_week_should = build_html_roster_views::calculate_working_hours_week_should($Roster);
echo build_html_roster_views::build_roster_working_hours_div($Working_hours_week_have, $Working_hours_week_should, array('employee_id' => $employee_id));
echo "</div>\n";

require 'contact-form.php';
?>
</BODY>
</HTML>
