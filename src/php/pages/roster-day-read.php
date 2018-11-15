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
require_once '../../../default.php';
/*
 * @var $mandant int the id of the active branch.
 * CAVE: Be aware, that the PEP part has its own branch id, coming from the cash register program
 */
$branch_id = user_input::get_variable_from_any_input('mandant', FILTER_SANITIZE_NUMBER_INT, min(array_keys($List_of_branch_objects)));
$mandant = $branch_id; //TODO: Make sure, that $mandant can be removed savely!
create_cookie('mandant', $branch_id, 30);
/*
 * @var $number_of_days int Number of days to show.
 * This page will show the roster of one single day.
 */
$number_of_days = 1;

$date_sql = user_input::get_variable_from_any_input('datum', FILTER_SANITIZE_NUMBER_INT, date('Y-m-d'));
$date_unix = strtotime($date_sql);
create_cookie("datum", $date_sql, 0.5);

$Roster = roster::read_roster_from_database($branch_id, $date_sql);

//The following lines check for the state of approval.
//Duty rosters have to be approved by the leader, before the staff can view them.
unset($approval);
$sql_query = "SELECT state FROM `approval` WHERE date=:date AND branch=:branch_id";
$result = database_wrapper::instance()->run($sql_query, array('date' => $date_sql, 'branch_id' => $branch_id));
while ($row = $result->fetch(PDO::FETCH_OBJ)) {
    $approval = $row->state;
}
if (isset($approval)) {
    if ($approval == 'approved') {
        //Everything is fine.
    } elseif ($approval == 'not_yet_approved') {
        $message = gettext('The roster has not been approved by the administration!');
        user_dialog::add_message($message, E_USER_NOTICE);
    } elseif ($approval == 'disapproved') {
        $message = gettext('The roster is still beeing revised!');
        user_dialog::add_message($message, E_USER_WARNING);
    }
} else {
    $approval = "not_yet_approved";
    if (!roster::is_empty($Roster)) {
        $message = gettext('Missing data in table `approval`');
        user_dialog::add_message($message, E_USER_NOTICE);
        /*
         * TODO: This is an Exception.
         * It will occur when there is no approval, disapproval or other connected information in the approval table of the database.
         * That might espacially occur during the development stage of this feature.
         */
    }
}


/*
 * Get a list of all employees:
 */
$workforce = new workforce($date_sql);
foreach (array_keys($List_of_branch_objects) as $other_branch_id) {
    /*
     * The $Branch_roster contanins all the rosters from all branches, including the current branch.
     */
    $Branch_roster[$other_branch_id] = roster::read_branch_roster_from_database($branch_id, $other_branch_id, $date_sql, $date_sql);
}

$max_vk_count_in_roster_days = 0;
foreach ($Roster as $Roster_day_array) {
    $max_vk_count_in_roster_days = max($max_vk_count_in_roster_days, count($Roster_day_array));
}
$VKmax = max(array_keys($workforce->List_of_employees)); //The highest given employee_id
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';


echo "<div id=main-area>\n";

echo user_dialog::build_messages();
echo build_html_navigation_elements::build_select_branch($branch_id, $date_sql);
echo "<div id=navigation_form_div class=no_print>\n";
echo build_html_navigation_elements::build_button_day_backward($date_unix);
echo build_html_navigation_elements::build_button_day_forward($date_unix);
echo build_html_navigation_elements::build_button_open_edit_version('src/php/pages/roster-day-edit.php', array('datum' => $date_sql));
echo "<br><br>\n";
echo build_html_navigation_elements::build_input_date($date_sql);
echo "</div>\n";
echo "<div id=duty_roster_table_div>\n";
echo "<table id=duty_roster_table>\n";
echo build_html_roster_views::build_roster_read_only_table_head($Roster, array(build_html_roster_views::OPTION_SHOW_EMERGENCY_SERVICE_NAME, build_html_roster_views::OPTION_SHOW_CALENDAR_WEEK));
if ($approval == "approved" OR $config['hide_disapproved'] == false) {

    echo build_html_roster_views::build_roster_readonly_table($Roster, $branch_id, array('space_constraints' => 'wide'));
    echo "<tr><td></td></tr>\n";
    echo build_html_roster_views::build_roster_readonly_branch_table_rows($Branch_roster, $branch_id, $date_sql, $date_sql, array('space_constraints' => 'wide'));
    echo "<tr><td></td></tr>\n";
    $Abwesende = absence::read_absentees_from_database($date_sql);
    if (isset($Abwesende)) {
        echo build_html_roster_views::build_absentees_row($Abwesende);
    }
}
echo "</table>\n";
echo "</div>\n";

if (($approval == "approved" OR $config['hide_disapproved'] !== TRUE)) {
    echo "<div id=roster_image_div class=image>\n";
    $roster_image_bar_plot = new roster_image_bar_plot($Roster);
    echo $roster_image_bar_plot->svg_string;
    echo "<br>\n";
    echo "<br>\n";
    $examine_roster = new examine_roster($Roster, $date_unix, $branch_id, $workforce);
    echo roster_image_histogramm::draw_image_histogramm($Roster, $branch_id, $examine_roster->Anwesende, $date_unix);
    echo "</div>\n";
}

echo "</div>\n";

require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php';
?>
</body>
</html>
