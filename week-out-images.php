<?php

#Diese Seite wird den kompletten Dienstplan eines einzelnen Tages anzeigen.
require 'default.php';
$tage = 7; //Dies ist eine Tagesansicht für einen einzelnen Tag.
$branch_id = user_input::get_variable_from_any_input('mandant', FILTER_SANITIZE_NUMBER_INT, min(array_keys($List_of_branch_objects)));
$mandant = $branch_id;
create_cookie('mandant', $mandant, 30);



$date_sql_user_input = user_input::get_variable_from_any_input('datum', FILTER_SANITIZE_NUMBER_INT, date('Y-m-d'));
$date_sql = general_calculations::get_first_day_of_week($date_sql_user_input);
$date_unix_start = strtotime($date_sql);
create_cookie("datum", $date_sql, 0.5);
$date_unix_end = $date_unix_start + ($tage - 1) * PDR_ONE_DAY_IN_SECONDS;

//Hole eine Liste aller Mitarbeiter
$workforce = new workforce();
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/read_roster_array_from_db.php';


//Produziere die Ausgabe
require 'head.php';
require 'src/php/pages/menu.php';

//Hier beginnt die Normale Ausgabe.
echo "<div class='main-area'>\n";
echo "<div id=navigation_elements class='no-print'>";
echo build_html_navigation_elements::build_select_branch($mandant, $date_sql);
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
echo "</div><!--class='main-area no-print'-->\n";


require 'contact-form.php';

echo "</body>\n";
echo "</html>";
