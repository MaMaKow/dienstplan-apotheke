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

$tage = 7; //One week
$user_dialog = new user_dialog();

$network_of_branch_offices = new \PDR\Pharmacy\NetworkOfBranchOffices;
$List_of_branch_objects = $network_of_branch_offices->get_list_of_branch_objects();
$branch_id = user_input::get_variable_from_any_input('mandant', FILTER_SANITIZE_NUMBER_INT, min(array_keys($List_of_branch_objects)));
$mandant = $branch_id;
create_cookie('mandant', $branch_id, 30);

$overlay_message_html = "";

$date_sql_user_input = user_input::get_variable_from_any_input('datum', FILTER_SANITIZE_NUMBER_INT, date('Y-m-d'));
$date_sql = general_calculations::get_first_day_of_week($date_sql_user_input);
$date_unix = strtotime($date_sql);
$date_sql_start = $date_sql;
$date_sql_end = date('Y-m-d', strtotime('+ ' . ($tage - 1) . ' days', $date_unix));
create_cookie('datum', $date_sql, 0.5);
$date_unix_start = $date_unix;
$date_start_object = new DateTime();
$date_start_object->setTimestamp($date_unix_start);

$date_unix_end = $date_unix_start + ($tage - 1) * PDR_ONE_DAY_IN_SECONDS;

for ($i = 0; $i < $tage; $i++) {
    $Week_dates_unix[] = strtotime(' +' . $i . ' days', $date_unix);
}

//Hole eine Liste aller Mitarbeiter
$workforce = new workforce($date_sql_start, $date_sql_end);
$Roster = roster::read_roster_from_database($branch_id, $date_sql_start, $date_sql_end);
foreach (array_keys($List_of_branch_objects) as $other_branch_id) {
    /*
     * The $Branch_roster contanins all the rosters from all branches, including the current branch.
     */
    $Branch_roster[$other_branch_id] = roster::read_branch_roster_from_database($branch_id, $other_branch_id, $date_sql_start, $date_sql_end);
}
/**
 * Build a div containing assignment of tasks:
 */
$weekly_rotation_div_html = task_rotation::task_rotation_main(array_keys($Roster), "Rezeptur", $branch_id);
$Working_week_hours_have = roster::calculate_working_weekly_hours_from_branch_roster($Branch_roster);
$duty_roster_working_week_hours_div = "";
if (array() !== $Roster and isset($Working_week_hours_have)) {
    $Working_week_hours_should = build_html_roster_views::calculate_working_week_hours_should($Roster, $workforce);
    $duty_roster_working_week_hours_div = build_html_roster_views::build_roster_working_week_hours_div($Working_week_hours_have, $Working_week_hours_should, $workforce);
}

//Produziere die Ausgabe
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
$main_div_html = "<div id='main-area'>\n";
$dateString = $date_start_object->format('W');
$date_info_line_html = "<div id=date_info_line class='no_print'>"
        . gettext("calendar week") . '&nbsp;'
        . $dateString
        . '&nbsp;' . alternating_week::get_human_readable_string(alternating_week::get_alternating_week_for_date($date_start_object))
        . "</div>\n";
$main_div_html .= $date_info_line_html;

//Support for various branch clients.
$main_div_html .= "<div class='no_print'>";
$main_div_html .= build_html_navigation_elements::build_select_branch($branch_id, $List_of_branch_objects, $date_sql);
$main_div_html .= "</div>";

$duty_roster_form_html = "";
$buttons_div_html = "";
$buttons_div_html .= "<div id=buttons_div class=no_print>";
$buttons_div_html .= build_html_navigation_elements::build_button_week_backward($date_sql);
$buttons_div_html .= build_html_navigation_elements::build_button_week_forward($date_sql);
$buttons_div_html .= build_html_navigation_elements::build_input_date($date_sql);
$buttons_div_html .= "</div>";
$duty_roster_form_html .= $buttons_div_html;

$table_html = "<table id=duty_roster_table>\n";
$table_html .= build_html_roster_views::build_roster_read_only_table_head($Roster);

$table_body_html = build_html_roster_views::build_roster_readonly_table($Roster, $branch_id, array('space_constraints' => 'narrow'));
if (isset($Overlay_message)) {
    $overlay_message_html .= "<div class='overlay no_print'>\n";
    $Overlay_message = array_unique($Overlay_message);
    foreach ($Overlay_message as $message) {
        $overlay_message_html .= "<H1>" . $message . "</H1>\n";
    }
    $overlay_message_html .= "</div>\n";
}
$table_html .= $table_body_html;
$table_html .= build_html_roster_views::build_roster_readonly_branch_table_rows($Branch_roster, $branch_id, $date_sql_start, $date_sql_end, array('space_constraints' => 'narrow'));
$table_html .= "";

//echo "</div>\n";
$table_foot_html = "<tfoot>"
        //. "<tr class=page-break></tr>"
        . "\n<tr>\n";

//Wir werfen einen Blick in den Urlaubsplan und schauen, ob alle da sind.
foreach (array_keys($Roster) as $date_unix) {
    $date_sql = date('Y-m-d', $date_unix);
    $Abwesende = absence::read_absentees_from_database($date_sql);

    //Jetzt notieren wir die Urlauber und die Kranken Mitarbeiter unten in der Tabelle.
    if (!empty($Abwesende)) {
        $table_foot_html .= build_html_roster_views::build_absentees_column($Abwesende);
    } else {
        $table_foot_html .= "<td><!-- Nobody is absent --></td>";
    }
}
$table_foot_html .= "</tr>\n";
$table_foot_html .= "</tfoot>\n";

$table_html .= $table_foot_html;
$table_html .= "</table><!--id=duty_roster_table-->\n";

$table_div_html = "<div id=table_overlay_area>";
$table_div_html .= $overlay_message_html;
$table_div_html .= $table_html;
$table_div_html .= "</div><!--id='main-area'-->\n";
$table_div_html .= "$weekly_rotation_div_html";

$duty_roster_form_html .= $table_div_html;

$main_div_html .= $duty_roster_form_html;
$main_div_html .= $duty_roster_working_week_hours_div;
//$main_div_html .= "</div>\n";

echo $user_dialog->build_messages();

echo '<div id="print_time_info" class="only_print"><p class="tiny">' . sprintf(gettext('Time of print: %1$s'), date('d.m.Y H:i:s')) . '</p></div>';
echo $main_div_html;
/* echo <<<EOF
  <p style="page-break-after: always;">&nbsp;</p>
  <p style="page-break-before: always;">&nbsp;</p>\n
  EOF;
 */
echo "</div><!--class='main-area no_print'-->\n";
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php';
?>
</BODY>
</HTML>
