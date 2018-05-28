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
$Fehlermeldung = array();
$Warnmeldung = array();

$date_sql = user_input::get_variable_from_any_input('datum', FILTER_SANITIZE_NUMBER_INT, date('Y-m-d'));
$date_unix = strtotime($date_sql);
create_cookie("datum", $date_sql, 0.5);

//The following lines check for the state of approval.
//Duty rosters have to be approved by the leader, before the staff can view them.
unset($approval);
$sql_query = "SELECT state FROM `approval` WHERE date='$date_sql' AND branch='$branch_id'";
$result = database_wrapper::instance()->run($sql_query);
while ($row = $result->fetch(PDO::FETCH_OBJ)) {
    $approval = $row->state;
}
if (isset($approval)) {
    if ($approval == "approved") {
        //Everything is fine.
    } elseif ($approval == "not_yet_approved") {
        $Warnmeldung[] = gettext("The roster has not been approved by the administration!");
    } elseif ($approval == "disapproved") {
        $Warnmeldung[] = gettext("The roster is still beeing revised!");
    }
} else {
    $approval = "not_yet_approved";
    $Warnmeldung[] = gettext("Missing data in table `approval`");
    // TODO: This is an Exception. It will occur when There is no approval, disapproval or other connected information in the approval table of the database.
    //That might espacially occur during the development stage of this feature.
}


//Get a list of all employees:
$workforce = new workforce($date_sql);
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/read_roster_array_from_db.php';
$Dienstplan = read_roster_array_from_db($date_sql, $number_of_days, $branch_id);
$Roster = roster::read_roster_from_database($branch_id, $date_sql);
foreach (array_keys($List_of_branch_objects) as $other_branch_id) {
    /*
     * The $Branch_roster contanins all the rosters from all branches, including the current branch.
     */
    $Branch_roster[$other_branch_id] = roster::read_branch_roster_from_database($branch_id, $other_branch_id, $date_sql, $date_sql);
}

$max_vk_count_in_rooster_days = 0;
foreach ($Roster as $Roster_day_array) {
    $max_vk_count_in_rooster_days = max($max_vk_count_in_rooster_days, count($Roster_day_array));
}
$VKmax = max(array_keys($workforce->List_of_employees)); //The highest given employee_id
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';


echo "<div id=main-area>\n";


echo build_warning_messages($Fehlermeldung, $Warnmeldung);
echo build_html_navigation_elements::build_select_branch($branch_id, $date_sql);
echo "<div id=navigation_form_div class=no-print>\n";
echo build_html_navigation_elements::build_button_day_backward($date_unix);
echo build_html_navigation_elements::build_button_day_forward($date_unix);
echo build_html_navigation_elements::build_button_open_edit_version('tag-in.php', $date_sql);
echo "<br><br>\n";
echo build_html_navigation_elements::build_input_date($date_sql);
echo "</div>\n";
echo "<div id=roster_table_div>\n";
echo "<table id=roster_table>\n";
echo "<tr>\n";
for ($i = 0; $i < count($Dienstplan); $i++) { //$i will be zero, beacause this is just one day.//Datum
    $date_unix = strtotime($Dienstplan[$i]["Datum"][0]);
    $zeile = "";
    echo "<td>\n";
    $zeile .= "<input type=hidden name=Dienstplan[" . $i . "][Datum][0] value=" . $Dienstplan[$i]["Datum"][0] . ">\n";
    $zeile .= "<input type=hidden name=mandant value=" . htmlentities($branch_id) . ">\n";
    $zeile .= strftime('%A, %d.%m. ', $date_unix);
    $zeile .= "<a href='" . PDR_HTTP_SERVER_APPLICATION_PATH . "src/php/pages/roster-week-table.php?datum=" . $date_sql . "'>" . gettext("calendar week") . strftime(' %V', strtotime($date_sql)) . "</a>\n";
    echo $zeile;
    $holiday = holidays::is_holiday($date_unix);
    if (FALSE !== $holiday) {
        echo "<p>" . $holiday . "</p>\n";
    }
    $Abwesende = absence::read_absentees_from_database($date_sql);
    $having_emergency_service = pharmacy_emergency_service::having_emergency_service($date_sql);
    if (FALSE !== $having_emergency_service) {
        echo "<br>NOTDIENST<br>";
        if (isset($workforce->List_of_employees[$having_emergency_service['employee_id']])) {
            echo $workforce->List_of_employees[$having_emergency_service['employee_id']]->last_name;
        } else {
            echo "???";
        }
        echo " / " . $List_of_branch_objects[$having_emergency_service['branch_id']]->name;
    }
    echo "</td>\n";
}
if ($approval == "approved" OR $config['hide_disapproved'] == false) {

    echo build_html_roster_views::build_roster_readonly_table($Roster, $branch_id);
    echo "<tr><td></td></tr>\n";
    echo build_html_roster_views::build_roster_readonly_branch_table_rows($Branch_roster, $branch_id, $date_sql, $date_sql);
    echo "<tr><td><br></td></tr>";
    if (isset($Abwesende)) {
        echo build_html_roster_views::build_absentees_row($Abwesende);
    }
}
echo "</table>\n";
echo "</div>\n";

if (($approval == "approved" OR $config['hide_disapproved'] !== TRUE) AND ! empty($Dienstplan[0]["Dienstbeginn"])) {
    echo "<div id=roster_image_div class=image>\n";
    $roster_image_bar_plot = new roster_image_bar_plot($Roster);
    echo $roster_image_bar_plot->svg_string;
    echo "<br>\n";
    echo "<br>\n";
    $examine_roster = new examine_roster($Roster, $date_unix, $branch_id);
    echo roster_image_histogramm::draw_image_histogramm($Roster, $branch_id, $examine_roster->Anwesende, $date_unix);
    echo "</div>\n";
}

echo "</div>\n";

require PDR_FILE_SYSTEM_APPLICATION_PATH . 'contact-form.php';
?>
</body>
</html>
