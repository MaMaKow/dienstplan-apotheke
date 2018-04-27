<?php

#Diese Seite wird den kompletten Dienstplan eines einzelnen Tages anzeigen.
require 'default.php';
$tage = 7; //Dies ist eine Tagesansicht für einen einzelnen Tag.
$mandant = user_input::get_variable_from_any_input('mandant', FILTER_SANITIZE_NUMBER_INT, min(array_keys($List_of_branch_objects)));
create_cookie('mandant', $mandant, 30);



$date_sql_user_input = user_input::get_variable_from_any_input('datum', FILTER_SANITIZE_NUMBER_INT, date('Y-m-d'));
$datum = general_calculations::get_first_day_of_week($date_sql_user_input);
$date_sql = $datum;
create_cookie("datum", $datum, 0.5);

//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/read_roster_array_from_db.php';
$Dienstplan = read_roster_array_from_db($datum, $tage, $mandant);


//Produziere die Ausgabe
require 'head.php';
require 'navigation.php';
require 'src/php/pages/menu.php';

//Hier beginnt die Normale Ausgabe.
echo "<div class='main-area no-print'>\n";
echo build_select_branch($mandant, $date_sql);
echo "<form id=navigation_form method=post>\n";
echo "<div id=navigation_elements>";
echo "$backward_button_week_img";
echo "$forward_button_week_img";
echo "<input type=hidden size=2 name=Dienstplan[0][Datum][0] value=" . htmlentities($Dienstplan[0]["Datum"][0]) . ">";
echo "<br><br>\n";

echo "</div>\n";
echo "<div class=no-print id=wochenAuswahl>\n";
echo "<input name=date_sql type=date value=" . date('Y-m-d', strtotime($datum)) . ">\n";
echo "<input type=submit name=tagesAuswahl value=Anzeigen>\n";
echo "</div>\n";
echo "</form>\n";
echo "</div>";

//echo "<br><br><pre>"; var_export(array_column($Dienstplan, 'VK')); echo "</pre>";

if (!empty(array_column($Dienstplan, 'VK'))) {
    require_once 'image_dienstplan.php';
    $image_div_style = 'clear: left';
    $svg_width = 320;
    $svg_height = $svg_width / sqrt(2);
    $roster_plot_div_height = "calc($svg_height px + 2em)";
    foreach ($Dienstplan as $day => $Column) {
        echo "<div class=above-image style='$image_div_style'>\n";
        echo "<div class=roster_plot_div style='height:$roster_plot_div_height'>\n";
        echo "<a href='tag-out.php?datum=" . $Dienstplan[$day]["Datum"][0] . "'>";
        echo strftime('%A, %d.%m.%Y', strtotime($Dienstplan[$day]['Datum'][0])) . " </a><br>\n";
        if (empty(array_sum($Dienstplan[$day]['VK']))) {
            echo "<svg width='$svg_width px' height='$svg_height px' style='border: 1px solid #000000;'></svg>";
        } else {
            $Plan[0] = $Dienstplan[$day];
            echo draw_image_dienstplan($Plan, $svg_width, $svg_height);
        }
        echo "</div>\n";
        echo "</div>\n";
        $image_div_style = 'clear: none';
    }
}
//echo "<pre>";	var_export($Dienstplan);    	echo "</pre>";

require 'contact-form.php';

echo "</body>\n";
echo "</html>";
