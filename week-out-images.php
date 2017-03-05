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
$montags_differenz = date("w", strtotime($datum)) - 1; //Wir wollen den Anfang der Woche
$montags_differenzString = "-" . $montags_differenz . " day";
$datum = strtotime($montags_differenzString, strtotime($datum));
$datum = date('Y-m-d', $datum);
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

//Hier beginnt die Normale Ausgabe.
        echo "<div class='main-area no-print'>\n";
        echo "\t\t<form id=mandantenformular method=post>\n";
        echo "\t\t\t<input type=hidden name=datum value=" . $Dienstplan[0]["Datum"][0] . ">\n";
        echo "\t\t\t<select class='no-print large' name=mandant onchange=this.form.submit()>\n";
//echo "\t\t\t\t<option value=".$mandant.">".$Mandant[$mandant]."</option>\n";
        foreach ($Mandant as $filiale => $name) {
            if ($filiale != $mandant) {
                echo "\t\t\t\t<option value=" . $filiale . ">" . $name . "</option>\n";
            } else {
                echo "\t\t\t\t<option value=" . $filiale . " selected>" . $name . "</option>\n";
            }
        }
        echo "\t\t\t</select>\n\t\t</form>\n";
        echo "\t\t<form id=myform method=post>\n";
        echo "\t\t\t<div id=navigationsElemente>";
        echo "$rückwärts_button_week_img";
        echo "$vorwärts_button_week_img";
        echo "<input type=hidden size=2 name=Dienstplan[0][Datum][0] value=" . $Dienstplan[0]["Datum"][0] . ">";
        echo "<br><br>\n";

        echo "\t\t\t</div>\n";
        echo "\t\t\t<div class=no-print id=wochenAuswahl>\n";
        echo "\t\t\t\t<input name=tag type=date value=" . date('Y-m-d', strtotime($datum)) . ">\n";
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
            foreach ($Dienstplan as $day => $Column) {
                echo "\t\t<div class=above-image style='$image_div_style'>\n";
                echo "\t\t\t<div class=image>\n";
                echo "<a href='tag-out.php?datum=" . $Dienstplan[$day]["Datum"][0] . "'>";
                echo strftime('%A, %d.%m.%Y', strtotime($Dienstplan[$day]['Datum'][0])) . " </a><br>\n";
                if (empty(array_sum($Dienstplan[$day]['VK']))) {
                    echo "<svg width='$svg_width' height='$svg_height' style='border: 1px solid #000000;'></svg>";
                } else {
                    $Plan[0] = $Dienstplan[$day];
                    $svg_image_dienstplan = draw_image_dienstplan($Plan, $svg_width, $svg_height);
                    echo $svg_image_dienstplan;
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
        ?>
