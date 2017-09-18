<?php

#Diese Seite wird den kompletten Dienstplan eines einzelnen Tages anzeigen.
require 'default.php';
$mandant = 1; //First branch is allways the default.
$tage = 7; //Dies ist eine Tagesansicht für einen einzelnen Tag.


$datum = date('Y-m-d'); //Dieser Wert wird überschrieben, wenn "$wochenauswahl und $woche per POST übergeben werden."



require 'cookie-auswertung.php'; //Auswerten der per COOKIE gespeicherten Daten.
require 'get-auswertung.php'; //Auswerten der per GET übergebenen Daten.
require 'post-auswertung.php'; //Auswerten der per POST übergebenen Daten.
if (isset($mandant)) {
    create_cookie("mandant", $mandant, 30);
}
$monday_difference = date("w", strtotime($datum)) - 1; //Wir wollen den Anfang der Woche
$monday_differenceString = "-" . $monday_difference . " day";
$datum = strtotime($monday_differenceString, strtotime($datum));
$datum = date('Y-m-d', $datum);
$date_sql = $datum;
if (isset($datum)) {
    create_cookie("datum", $datum, 0.5);
}

//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';
//Hole eine Liste aller Mandanten (Filialen)
require 'db-lesen-mandant.php';
require 'db-lesen-tage.php'; //Lesen der in der Datenbank gespeicherten Daten.
$Dienstplan = db_lesen_tage($tage, $mandant);


//Produziere die Ausgabe
require 'head.php';
require 'navigation.php';
require 'src/php/pages/menu.php';

//Hier beginnt die Normale Ausgabe.
echo "<div class='main-area no-print'>\n";
echo build_select_branch($mandant, $date_sql);
echo "\t\t<form id=myform method=post>\n";
echo "\t\t\t<div id=navigation_elements>";
echo "$backward_button_week_img";
echo "$forward_button_week_img";
echo "<input type=hidden size=2 name=Dienstplan[0][Datum][0] value=" . htmlentities($Dienstplan[0]["Datum"][0]) . ">";
echo "<br><br>\n";

echo "\t\t\t</div>\n";
echo "\t\t\t<div class=no-print id=wochenAuswahl>\n";
echo "\t\t\t\t<input name=date_sql type=date value=" . date('Y-m-d', strtotime($datum)) . ">\n";
echo "\t\t\t\t<input type=submit name=tagesAuswahl value=Anzeigen>\n";
echo "\t\t\t</div>\n";
echo "\t\t</form>\n";
echo "</div>";

//echo "<br><br><pre>"; var_export(array_column($Dienstplan, 'VK')); echo "</pre>";

if (!empty(array_column($Dienstplan, 'VK'))) {
    require_once 'image_dienstplan.php';
    $image_div_style = 'clear: left';
    $svg_width = 320;
    $svg_height = $svg_width / sqrt(2);
    $roster_plot_div_height = "calc($svg_height px + 2em)";
    foreach ($Dienstplan as $day => $Column) {
        echo "\t\t<div class=above-image style='$image_div_style'>\n";
        echo "\t\t\t<div class=roster_plot_div style='height:$roster_plot_div_height'>\n";
        echo "<a href='tag-out.php?datum=" . $Dienstplan[$day]["Datum"][0] . "'>";
        echo strftime('%A, %d.%m.%Y', strtotime($Dienstplan[$day]['Datum'][0])) . " </a><br>\n";
        if (empty(array_sum($Dienstplan[$day]['VK']))) {
            echo "<svg width='$svg_width px' height='$svg_height px' style='border: 1px solid #000000;'></svg>";
        } else {
            $Plan[0] = $Dienstplan[$day];
            echo draw_image_dienstplan($Plan, $svg_width, $svg_height);
        }
        echo "\t\t\t</div>\n";
        echo "\t\t</div>\n";
        $image_div_style = 'clear: none';
    }
}
//echo "<pre>";	var_export($Dienstplan);    	echo "</pre>";

require 'contact-form.php';

echo "\t</body>\n";
echo "</html>";
