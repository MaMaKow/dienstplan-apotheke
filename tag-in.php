<?php

#Diese Seite wird den kompletten Dienstplan eines einzelnen Tages anzeigen.
require 'default.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . "/src/php/classes/build_html_roster_views.php";

require "src/php/calculate-holidays.php";

$mandant = min(array_keys($List_of_branch_objects)); //First branch is allways the default.
$tage = 1; //Dies ist eine Tagesansicht für einen einzelnen Tag.
$tag = 0;

$datum = date('Y-m-d'); //Dieser Wert wird überschrieben, wenn "$wochenauswahl und $woche per POST übergeben werden."



require 'cookie-auswertung.php'; //Auswerten der per COOKIE gespeicherten Daten.
require 'get-auswertung.php'; //Auswerten der per GET übergebenen Daten.
require 'post-auswertung.php'; //Auswerten der per POST übergebenen Daten.
$date_unix = strtotime($datum);
$branch_id = $mandant;
if (isset($mandant)) {
    create_cookie("mandant", $mandant, 30);
}
if (isset($datum)) {
    create_cookie("datum", $datum, 0.5);
}
$date_sql = $datum;
$date_unix = strtotime($date_sql);
//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/read_roster_array_from_db.php';
$Dienstplan = read_roster_array_from_db($datum, $tage, $mandant);
try {
    $Roster = roster::read_roster_from_database($branch_id, $date_sql);
} catch (PDRRosterLogicException $pdr_roster_logic_exception) {
    $Fehlermeldung[] = $pdr_roster_logic_exception->getMessage();
}
require_once 'db-lesen-abwesenheit.php';
$Abwesende = db_lesen_abwesenheit($datum);
$holiday = is_holiday($date_unix);
require_once 'plane-tag-grundplan.php';
$Principle_roster = get_principle_roster($datum, $mandant, $tag, $tage);
if (array_sum($Dienstplan[0]['VK']) <= 1 AND empty($Dienstplan[0]['VK'][0]) AND NULL !== $Principle_roster AND FALSE === $holiday) { //No plans on Saturday, SUnday and holidays.
    //Wir wollen eine automatische Dienstplanfindung beginnen.
    //Mal sehen, wie viel die Maschine selbst gestalten kann.
    $Fehlermeldung[] = "Kein Plan in der Datenbank, dies ist ein Vorschlag!";
    //sort_roster_array($Principle_roster);
    $Dienstplan = determine_lunch_breaks($Principle_roster, $tag);
}
if ((array_sum($Dienstplan[0]['VK']) > 1 OR ! empty($Dienstplan[0]['VK'][0]))
        and "7" !== date('N', strtotime($datum))
        and ! is_holiday(strtotime($datum))) {
    require 'pruefe-dienstplan.php';
    examine_duty_roster();
}
$roster_first_key = min(array_keys($Dienstplan[$tag]['Datum']));

require 'db-lesen-notdienst.php';
if (isset($notdienst['mandant'])) {
    $Warnmeldung[] = "An den Notdienst denken!";
}




//Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
//$VKcount=count($List_of_employees);
$VKcount = calculate_VKcount($Dienstplan);

//end($List_of_employees); $VKmax=key($List_of_employees); reset($List_of_employees); //Wir suchen nach der höchsten VK-Nummer VKmax.
$VKmax = max(array_keys($List_of_employees));

//Wir schauen, on alle Anwesenden anwesend sind und alle Kranken und Siechenden im Urlaub.
require 'pruefe-abwesenheit.php';




//Produziere die Ausgabe
require 'head.php';
require 'navigation.php';
require 'src/php/pages/menu.php';
$session->exit_on_missing_privilege('create_roster');

//Hier beginnt die Normale Ausgabe.
echo "<div id=main-area>\n";

//Here we put the output of errors and warnings. We display the errors, which we collected in $Fehlermeldung and $Warnmeldung:
echo build_warning_messages($Fehlermeldung, $Warnmeldung);

echo "\t\t" . strftime(gettext("calendar week") . ' %V', strtotime($datum)) . "<br>";
echo "<div class=only-print><b>" . $List_of_branch_objects[$mandant]->name . "</b></div><br>\n";
echo build_select_branch($mandant, $date_sql);


echo "\t\t<form id=myform method=post>\n";
echo "\t\t\t<div id=navigation_elements>";
echo "$backward_button_img";
echo "$forward_button_img";
echo "$submit_button_img";
echo "<br>\n";
if ($session->user_has_privilege('approve_roster')) {
    echo "$submit_approval_button_img";
    echo "$submit_disapproval_button_img";
    echo "<br>\n";
}

echo "\t\t\t\t<a href='tag-out.php?datum=" . $datum . "'>[" . gettext("Read") . "]</a>\n";
echo "\t\t\t</div>\n";
echo "\t\t\t<div id=wochenAuswahl>\n";
echo "\t\t\t\t<input name=date_sql type=date id=date_chooser_input class='datepicker' value=" . date('Y-m-d', strtotime($datum)) . ">\n";
echo "\t\t\t\t<input type=submit name=tagesAuswahl value=Anzeigen>\n";
echo "\t\t\t</div>\n";
echo "\t\t\t<table>\n";
echo "\t\t\t\t<tr>\n";
for ($i = 0; $i < count($Dienstplan); $i++) {//Datum
    //TODO: This loop probably is not necessary. Is there any case where $i ist not 0?
    $zeile = "";
    echo "\t\t\t\t\t<td>";
    $zeile .= "<input type=hidden name=Dienstplan[" . $i . "][Datum][0] value=" . $Dienstplan[$i]["Datum"][$roster_first_key] . ">";
    $zeile .= "<input type=hidden name=mandant value=" . htmlentities($mandant) . ">";
    $zeile .= strftime('%d.%m. ', strtotime($Dienstplan[$i]["Datum"][$roster_first_key]));
    echo $zeile;
//Wochentag
    $zeile = "";
    $zeile .= strftime('%A ', strtotime($Dienstplan[$i]["Datum"][$roster_first_key]));
    echo $zeile;
    if (FALSE !== $holiday) {
        echo " " . $holiday . " ";
    }
    require 'db-lesen-notdienst.php';
    if (isset($notdienst['mandant'])) {
        if (isset($List_of_employees[$notdienst['vk']])) {
            echo "<br>NOTDIENST<br>" . $List_of_employees[$notdienst['vk']] . " / " . $List_of_branch_objects[$notdienst['mandant']]->name;
        } else {
            echo "<br>NOTDIENST<br>??? / " . $List_of_branch_objects[$notdienst['mandant']]->name;
        }
    }
    echo "</td>\n";
}
for ($j = 0; $j < $VKcount; $j++) {
    echo "\t\t\t\t</tr><tr>\n";
    for ($i = 0; $i < count($Dienstplan); $i++) {//Mitarbeiter
        $zeile = "";
        echo "\t\t\t\t\t<td>";
        $zeile .= "<select name=Dienstplan[" . $i . "][VK][" . $j . "] tabindex=" . (($i * $VKcount * 5) + ($j * 5) + 1) . ">";
        $zeile .= "<option value=''>&nbsp;</option>";

        for ($k = 1; $k < $VKmax + 1; $k++) { //k=1 means that we will ignore any worker with a number smaller than one. Specific people like the cleaning lady will not be visible in the plan. But their holiday can still be organized with the holiday module.
            if (isset($Dienstplan[$i]["VK"][$j])) {
                if (isset($List_of_employees[$k]) and $Dienstplan[$i]["VK"][$j] != $k) { //Dieser Ausdruck dient nur dazu, dass der vorgesehene  Mitarbeiter nicht zwei mal in der Liste auftaucht.
                    $zeile .= "<option value=$k>" . $k . " " . $List_of_employees[$k] . "</option>";
                } elseif (isset($List_of_employees[$k])) {
                    $zeile .= "<option value=$k selected>" . $k . " " . $List_of_employees[$k] . "</option>"; // Es ist sinnvoll, auch eine leere Zeile zu besitzen, damit Mitarbeiter auch wieder gelöscht werden können.
                }
            } elseif (isset($List_of_employees[$k])) {
                $zeile .= "<option value=$k>" . $k . " " . $List_of_employees[$k] . "</option>";
            }
        }
        $zeile .= "</select>\n";
        //Dienstbeginn
        $zeile .= "\t\t\t\t\t\t<input type=hidden name=Dienstplan[" . $i . "][Datum][" . $j . "] value=" . htmlentities($Dienstplan[0]["Datum"][$roster_first_key]) . ">\n";
        $zeile .= "\t\t\t\t\t\t<input type=time size=5 class=Dienstplan_Dienstbeginn name=Dienstplan[" . $i . "][Dienstbeginn][" . $j . "] id=Dienstplan[" . $i . "][Dienstbeginn][" . $j . "] tabindex=" . ($i * $VKcount * 5 + $j * 5 + 2 ) . " value='";
        if (isset($Dienstplan[$i]["VK"][$j])) {
            $zeile .= roster::get_duty_start_from_roster($Roster, $date_unix, $j);
        }
        $zeile .= "'> bis <input type=time size=5 class=Dienstplan_Dienstende name=Dienstplan[" . $i . "][Dienstende][" . $j . "] id=Dienstplan[" . $i . "][Dienstende][" . $j . "] tabindex=" . ($i * $VKcount * 5 + $j * 5 + 3 ) . " value='";
        //Dienstende
        if (isset($Dienstplan[$i]["VK"][$j])) {
            $zeile .= strftime('%H:%M', strtotime($Dienstplan[$i]["Dienstende"][$j]));
        }
        $zeile .= "'>";
        echo $zeile;

        echo "</td>\n";
    }
    echo "\t\t\t\t</tr><tr>\n";
    for ($i = 0; $i < count($Dienstplan); $i++) {//Mittagspause
        $zeile = "";
        echo "\t\t\t\t\t<td>";
        $zeile .= "<div class='no-print kommentar_ersatz' style=display:inline><a onclick=unhide_kommentar() title='Kommentar anzeigen'>K+</a></div>";
        $zeile .= "<div class='no-print kommentar_input' style=display:none><a onclick=rehide_kommentar() title='Kommentar ausblenden'>K-</a></div>";
        $zeile .= " " . gettext("break") . ": <input type=time size=5 class=Dienstplan_Mittagbeginn name=Dienstplan[" . $i . "][Mittagsbeginn][" . $j . "] id=Dienstplan[" . $i . "][Mittagsbeginn][" . $j . "] tabindex=" . ($i * $VKcount * 5 + $j * 5 + 4 ) . " value='";
        if (isset($Dienstplan[$i]["VK"][$j]) and $Dienstplan[$i]["Mittagsbeginn"][$j] > 0) {
            $zeile .= strftime('%H:%M', strtotime($Dienstplan[$i]["Mittagsbeginn"][$j]));
        }
        $zeile .= "'> bis <input type=time size=5 class=Dienstplan_Mittagsende name=Dienstplan[" . $i . "][Mittagsende][" . $j . "] id=Dienstplan[" . $i . "][Mittagsende][" . $j . "] tabindex=" . ($i * $VKcount * 5 + $j * 5 + 5 ) . " value='";
        if (isset($Dienstplan[$i]["VK"][$j]) and $Dienstplan[$i]["Mittagsbeginn"][$j] > 0) {
            $zeile .= strftime('%H:%M', strtotime($Dienstplan[$i]["Mittagsende"][$j]));
        }
        $zeile .= "'>";
        $zeile .= "<div class=kommentar_input style=display:none><br>Kommentar: <input type=text name=Dienstplan[" . $i . "][Kommentar][" . $j . "] value=\"";
        if (isset($Dienstplan[$i]["Kommentar"][$j])) {
            $zeile .= $Dienstplan[$i]["Kommentar"][$j];
        }
        $zeile .= "\"></div>";
        echo $zeile;
        echo "</td>\n";
    }
}
echo "\t\t\t\t</tr>";


//Wir werfen einen Blick in den Urlaubsplan und schauen, ob alle da sind.
if (isset($Abwesende)) {
    echo build_absentees_row($Abwesende);
}
echo "\t\t\t</table>\n";
echo "\t\t</form>\n";


if (!empty($Dienstplan[0]["Dienstbeginn"])) {
    echo "\t\t\t<div class=image>\n";
    require_once 'image_dienstplan.php';
    $svg_image_dienstplan = draw_image_dienstplan($Dienstplan);
    echo $svg_image_dienstplan;
    echo "<br>\n";
    require_once 'image_histogramm.php';
    $svg_image_histogramm = draw_image_histogramm($Dienstplan);
    echo $svg_image_histogramm;
    echo "\t\t\t</div>\n";
}
echo "</div>";

require 'contact-form.php';

echo "\t</body>\n";
echo "</html>";
?>
