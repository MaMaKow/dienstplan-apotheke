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
$tage = 7;
$network_of_branch_offices = new \PDR\Pharmacy\NetworkOfBranchOffices;
$List_of_branch_objects = $network_of_branch_offices->get_list_of_branch_objects();
$branch_id = user_input::get_variable_from_any_input('mandant', FILTER_SANITIZE_NUMBER_INT, min(array_keys($List_of_branch_objects)));
$mandant = $branch_id;
create_cookie('mandant', $mandant, 30);



$date_sql_user_input = user_input::get_variable_from_any_input('datum', FILTER_SANITIZE_NUMBER_INT, date('Y-m-d'));
$date_sql = general_calculations::get_first_day_of_week($date_sql_user_input);
$date_unix_start = strtotime($date_sql);
create_cookie("datum", $date_sql, 0.5);
$date_unix_end = $date_unix_start + ($tage - 1) * PDR_ONE_DAY_IN_SECONDS;

$workforce = new workforce();
/*
 * Start of output:
 */
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';

echo "<div class='main-area'>\n";
echo "<div id=navigation_elements class='no_print'>";
echo build_html_navigation_elements::build_select_branch($mandant, $List_of_branch_objects, $date_sql);
echo build_html_navigation_elements::build_button_week_backward($date_sql);
echo build_html_navigation_elements::build_button_week_forward($date_sql);
echo build_html_navigation_elements::build_input_date($date_sql);
echo "</div>\n";

echo "<div id=roster_week_image_div class=image>\n";
for ($date_unix = $date_unix_start; $date_unix <= $date_unix_end; $date_unix += PDR_ONE_DAY_IN_SECONDS) {
    $date_sql = date('Y-m-d', $date_unix);
    $Roster = roster::read_roster_from_database($branch_id, $date_sql);
    $roster_image_bar_plot = new roster_image_bar_plot($Roster, 300, 200);
    echo "<div class=image_part>\n";
    echo "<p>" . strftime('%A %x', $date_unix) . "</p>";
    echo $roster_image_bar_plot->svg_string;
    echo "</div>\n";
}
echo "</div><!--id=roster_image_div-->\n";
echo "</div><!--class='main-area no_print'-->\n";


require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php';

echo "</body>\n";
echo "</html>";
