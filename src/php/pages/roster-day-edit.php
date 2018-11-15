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
 * This page is meant to show the whole roster of one day for one branch.
 */
require '../../../default.php';

$branch_id = user_input::get_variable_from_any_input("mandant", FILTER_SANITIZE_NUMBER_INT, min(array_keys($List_of_branch_objects)));
create_cookie("mandant", $branch_id, 30);

$date_sql = user_input::get_variable_from_any_input("datum", FILTER_SANITIZE_STRING, date('Y-m-d'));
create_cookie("datum", $date_sql, 0.5);
$date_unix = strtotime($date_sql);
$workforce = new workforce($date_sql);
/*
 * User input:
 * Get new/changed rosters from user input and put them into the database.
 */
if (filter_has_var(INPUT_POST, 'Roster')) {
    $Roster = user_input::get_Roster_from_POST_secure();
    if (filter_has_var(INPUT_POST, 'submit_roster') && $session->user_has_privilege('create_roster')) {
        user_input::roster_write_user_input_to_database($Roster, $branch_id);
    }
}
/*
 * User input:
 * Approve or disapprove rosters
 */
if ((filter_has_var(INPUT_POST, 'submit_approval') or filter_has_var(INPUT_POST, 'submit_disapproval')) && count($Roster) > 0 && $session->user_has_privilege('approve_roster')) {
    user_input::old_write_approval_to_database($branch_id, $Roster);
}

$Absentees = absence::read_absentees_from_database($date_sql);
$holiday = holidays::is_holiday($date_unix);
$Roster = roster::read_roster_from_database($branch_id, $date_sql);
foreach (array_keys($List_of_branch_objects) as $other_branch_id) {
    /*
     * The $Branch_roster contanins all the rosters from all branches, including the current branch.
     * But it only contains the employees, who are normally in this $branch_id.
     */
    $Branch_roster[$other_branch_id] = roster::read_branch_roster_from_database($branch_id, $other_branch_id, $date_sql);
}

$Principle_roster = roster::read_principle_roster_from_database($branch_id, $date_sql, NULL, array(roster::OPTION_CONTINUE_ON_ABSENCE));
/*
 * In case there is no roster scheduled yet, create a suggestion:
 */
if (roster::is_empty($Roster) and FALSE === $holiday) { //No plans on holidays.
    if (!empty($Principle_roster)) {
        /*
         * Create roster from principle roster:
         */
        $message = gettext('There is no roster in the database.') . " " . gettext('This is a proposal.');
        user_dialog::add_message($message);
        $Roster = $Principle_roster;
    } elseif (6 == strftime('%u', $date_unix)) {
        try {
            $saturday_rotation = new saturday_rotation($branch_id);
            $saturday_rotation_team_id = $saturday_rotation->get_participation_team_id($date_sql);
            $Roster = $saturday_rotation->fill_roster($saturday_rotation_team_id);
            $message = gettext('There is no roster in the database.') . " " . gettext('This is a proposal.');
            user_dialog::add_message($message);
        } catch (Exception $exception) {
            error_log($exception->getMessage());
        }
    }
}
/*
 * Examine roster for errors and irregularities:
 */
if ("7" !== date('N', $date_unix) and ! holidays::is_holiday($date_unix)) {
    $examine_roster = new examine_roster($Roster, $date_unix, $branch_id, $workforce);
    $examine_roster->check_for_overlap($date_sql, $List_of_branch_objects, $workforce);
    $examine_roster->check_for_sufficient_employee_count();
    $examine_roster->check_for_sufficient_goods_receipt_count();
    $examine_roster->check_for_sufficient_qualified_pharmacist_count();
    examine_attendance::check_for_absent_employees($Roster, $Principle_roster, $Absentees, $date_unix);
}
/*
 * examine_attendance::check_for_attendant_absentees() should be done regardless of weekday and holiday:
 */
examine_attendance::check_for_attendant_absentees($Roster, $Absentees);

if (FALSE !== pharmacy_emergency_service::having_emergency_service($date_sql)) {
    $message = gettext('Beware the emergency service!');
    user_dialog::add_message($message, E_USER_WARNING);
}

/*
 * Output:
 */
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
$session->exit_on_missing_privilege('create_roster');
$html_text = "";
$html_text .= "<div id=main-area>\n";
$html_text .= "<div id=navigation_elements>";
$html_text .= build_html_navigation_elements::build_button_day_backward($date_unix);
$html_text .= build_html_navigation_elements::build_button_day_forward($date_unix);
if ($session->user_has_privilege('create_roster')) {
    $html_text .= build_html_navigation_elements::build_button_submit('roster_form');
}
if ($session->user_has_privilege('approve_roster')) {
    $approval = build_html_roster_views::get_approval_from_database($date_sql, $branch_id);

    $html_text .= build_html_navigation_elements::build_button_approval($approval);
    $html_text .= build_html_navigation_elements::build_button_disapproval($approval);
}
$html_text .= build_html_navigation_elements::build_button_open_readonly_version('src/php/pages/roster-day-read.php', array('datum' => $date_sql));
$html_text .= "</div><!-- id=navigation_elements -->\n";
$html_text .= build_html_navigation_elements::build_select_branch($branch_id, $date_sql);
$html_text .= build_html_navigation_elements::build_input_date($date_sql);
/*
 * Here we put the output of errors and warnings.
 */
$html_text .= user_dialog::build_messages();
$html_text .= "<form accept-charset='utf-8' id='roster_form' method=post>\n";
$html_text .= "<table>\n";
$html_text .= "<tr>\n";
$html_text .= "<td>";
$html_text .= "<input type=hidden name=datum value=" . $date_sql . ">";
$html_text .= "<input type=hidden name=mandant value=" . htmlentities($branch_id) . ">";
$html_text .= strftime('%d.%m. ', $date_unix);
/*
 * Weekday:
 */
$html_text .= strftime('%A ', $date_unix);
if (FALSE !== $holiday) {
    $html_text .= " " . $holiday . " ";
}
$html_text .= "<br>";
$html_text .= "" . strftime(gettext("calendar week") . ' %V', $date_unix);
$having_emergency_service = pharmacy_emergency_service::having_emergency_service($date_sql);
if (isset($having_emergency_service['branch_id'])) {
    if (isset($workforce->List_of_employees[$having_emergency_service['employee_id']])) {
        $html_text .= "<br>" . gettext("EMERGENCY SERVICE") . "<br>" . $workforce->List_of_employees[$having_emergency_service['employee_id']]->last_name . " / " . $List_of_branch_objects[$having_emergency_service['branch_id']]->name;
    } else {
        $html_text .= "<br>" . gettext("EMERGENCY SERVICE") . "<br>??? / " . $List_of_branch_objects[$having_emergency_service['branch_id']]->name;
    }
}
$html_text .= "</td>\n";
$html_text .= "</tr>\n";
$max_employee_count = roster::calculate_max_employee_count($Roster);
$day_iterator = $date_unix; //Just in case the loop does not define it for build_html_roster_views::build_roster_input_row_add_row
if (array() !== $Roster) {
    for ($table_input_row_iterator = 0; $table_input_row_iterator < $max_employee_count; $table_input_row_iterator++) {
        $html_text .= "<tr>\n";
        foreach (array_keys($Roster) as $day_iterator) {
            $html_text .= build_html_roster_views::build_roster_input_row($Roster, $day_iterator, $table_input_row_iterator, $max_employee_count, $branch_id, array('add_select_employee'));
        }
        $html_text .= "</tr>\n";
    }
} else {
    /*
     * Write an empty line in case the roster is empty:
     */
    $html_text .= "<tr>\n";
    $html_text .= build_html_roster_views::build_roster_input_row($Roster, $date_unix, 0, $max_employee_count, $branch_id, array('add_select_employee'));
    $html_text .= "</tr>\n";
    $html_text .= "<tr>\n";
    $html_text .= build_html_roster_views::build_roster_input_row($Roster, $date_unix, 1, $max_employee_count, $branch_id, array('add_select_employee'));
    $html_text .= "</tr>\n";
}
$html_text .= build_html_roster_views::build_roster_input_row_add_row($day_iterator, $table_input_row_iterator, $max_employee_count, $branch_id);

$html_text .= "<tr><td></td></tr>\n";
$html_text .= build_html_roster_views::build_roster_readonly_branch_table_rows($Branch_roster, $branch_id, $date_sql, $date_sql, array('space_constraints' => 'wide'));
$html_text .= "<tr><td></td></tr>\n";
/*
 * Make a list of absent people:
 */
$html_text .= build_html_roster_views::build_absentees_row($Absentees);
$html_text .= "</table>\n";
$html_text .= "</form>\n";


if (!empty($Roster)) {
    if (!isset($examine_roster)) {
        /*
         * we need $examine_roster->Anwesende for roster_image_histogramm::draw_image_histogramm()
         * $examine_roster should already be defined. This is just a precaution.
         */
        $examine_roster = new examine_roster($Roster, $date_unix, $branch_id, $workforce);
    }
    $html_text .= "<div class=image>\n";
    $roster_image_bar_plot = new roster_image_bar_plot($Roster);
    $html_text .= $roster_image_bar_plot->svg_string;
    $html_text .= "<br>\n";
    $html_text .= roster_image_histogramm::draw_image_histogramm($Roster, $branch_id, $examine_roster->Anwesende, $date_unix);
    $html_text .= "</div><!-- class=image -->\n";
}
$html_text .= task_rotation::build_html_task_rotation_select('Rezeptur', $date_sql, $branch_id);
$html_text .= "</div><!-- id=main-area -->";
echo "$html_text";

require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php';

echo "</body>\n";
echo "</html>";
?>
