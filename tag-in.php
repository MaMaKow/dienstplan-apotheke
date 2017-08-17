<?php

#Diese Seite wird den kompletten Dienstplan eines einzelnen Tages anzeigen.
require 'default.php';
require "src/php/calculate-holidays.php";

$mandant = 1; //First branch is allways the default.
$tage = 1; //Dies ist eine Tagesansicht für einen einzelnen Tag.
$tag = 0;
$datenübertragung = "";
$dienstplanCSV = "";


$datum = date('Y-m-d'); //Dieser Wert wird überschrieben, wenn "$wochenauswahl und $woche per POST übergeben werden."



require 'cookie-auswertung.php'; //Auswerten der per COOKIE gespeicherten Daten.
require 'get-auswertung.php'; //Auswerten der per GET übergebenen Daten.
require 'post-auswertung.php'; //Auswerten der per POST übergebenen Daten.
$date_unix = strtotime($datum);
if (isset($mandant)) {
    create_cookie("mandant", $mandant, 30);
}
if (isset($datum)) {
    create_cookie("datum", $datum, 0.5);
}
//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';
//Hole eine Liste aller Mandanten (Filialen)
require 'db-lesen-mandant.php';
require 'db-lesen-tage.php'; //Lesen der in der Datenbank gespeicherten Daten.
$Dienstplan = db_lesen_tage($tage, $mandant);
//echo "<pre>\$Dienstplan:\n";	var_export($Dienstplan);    	echo "</pre>";die;
/* Die Funktion schaut jetzt nach dem Arbeitsplan in der Helene. Die Daten werden bisher noch nicht verwendet. Das wird aber notwendig sein, denn wir wollen einen Mitarbeiter ja nicht aus versehen an zwei Orten gleichzeitig einsetzen. */
//$Filialplan=db_lesen_tage($tage, $filiale, '[^'.$filiale.']');
require_once 'db-lesen-abwesenheit.php';
list($Abwesende, $Urlauber, $Kranke) = db_lesen_abwesenheit($datum);
$holiday = is_holiday($date_unix);
require_once 'plane-tag-grundplan.php';
$Principle_roster = get_principle_roster($datum, $mandant, $tag);
if (array_sum($Dienstplan[0]['VK']) <= 1 AND empty($Dienstplan[0]['VK'][0]) AND NULL !== $Principle_roster AND FALSE === $holiday) { //No plans on Saturday, SUnday and holidays.
    //Wir wollen eine automatische Dienstplanfindung beginnen.
    //Mal sehen, wie viel die Maschine selbst gestalten kann.
    $Fehlermeldung[] = "Kein Plan in der Datenbank, dies ist ein Vorschlag!";
    //$Roster_sorted_without_lunch = sort_roster_array($Principle_roster);
    $Dienstplan = determine_lunch_breaks($Principle_roster, $tag);
}
if (array_sum($Dienstplan[0]['VK']) > 1 OR ! empty($Dienstplan[0]['VK'][0])) {
    require 'pruefe-dienstplan.php';
}
$roster_first_key = min(array_keys($Dienstplan[$tag]['Datum']));

require 'db-lesen-notdienst.php';
if (isset($notdienst['mandant'])) {
    $Warnmeldung[] = "An den Notdienst denken!";
}




//Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
//$VKcount=count($Mitarbeiter);
$VKcount = calculate_VKcount($Dienstplan);

//end($Mitarbeiter); $VKmax=key($Mitarbeiter); reset($Mitarbeiter); //Wir suchen nach der höchsten VK-Nummer VKmax.
$VKmax = max(array_keys($Mitarbeiter));

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

echo "\t\tKalenderwoche " . strftime('%V', strtotime($datum)) . "<br><div class=only-print><b>" . $Mandant[$mandant] . "</b></div><br>\n";
echo "\t\t<form id=mandantenformular method=post>\n";
echo "\t\t\t<input type=hidden name=datum value=" . htmlentities($Dienstplan[0]["Datum"][$roster_first_key]) . ">\n";
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
//echo "\t\t<form id=myform method=post action=test-post.php>\n";
echo "\t\t\t<div id=navigationsElemente>";
//$rückwärts_button="\t\t\t\t<input type=submit 	value='1 Tag Rückwärts'	name='submitRückwärts'>\n";
//$vorwärts_button="\t\t\t\t<input type=submit 	value='1 Tag Vorwärts'	name='submitVorwärts'><br>\n";
echo "$rückwärts_button_img";
echo "$vorwärts_button_img";
echo "$submit_button_img";
echo "<br><br>\n";
if($session->user_has_privilege('approve_roster')){
echo "$submit_approval_button_img";
echo "$submit_disapproval_button_img";
echo "<br><br>\n";
    
}

echo "\t\t\t\t<a href='tag-out.php?datum=" . $datum . "'>[Lesen]</a>\n";
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
    $zeile.="<input type=hidden name=Dienstplan[" . $i . "][Datum][0] value=" . $Dienstplan[$i]["Datum"][$roster_first_key] . ">";
    $zeile.="<input type=hidden name=mandant value=" . htmlentities($mandant) . ">";
    $zeile.=strftime('%d.%m. ', strtotime($Dienstplan[$i]["Datum"][$roster_first_key]));
    echo $zeile;
//Wochentag
    $zeile = "";
    $zeile.=strftime('%A ', strtotime($Dienstplan[$i]["Datum"][$roster_first_key]));
    echo $zeile;
    if (FALSE !== $holiday) {
        echo " " . $holiday . " ";
    }
    require 'db-lesen-notdienst.php';
    if (isset($notdienst['mandant'])) {
        if (isset($Mitarbeiter[$notdienst['vk']])) {
            echo "<br>NOTDIENST<br>" . $Mitarbeiter[$notdienst['vk']] . " / " . $Mandant[$notdienst['mandant']];
        } else {
            echo "<br>NOTDIENST<br>??? / " . $Mandant[$notdienst['mandant']];
        }
    }
    echo "</td>\n";
}
for ($j = 0; $j < $VKcount; $j++) {
    echo "\t\t\t\t</tr><tr>\n";
    for ($i = 0; $i < count($Dienstplan); $i++) {//Mitarbeiter
        $zeile = "";
        echo "\t\t\t\t\t<td>";
        $zeile.="<select name=Dienstplan[" . $i . "][VK][" . $j . "] tabindex=" . (($i * $VKcount * 5) + ($j * 5) + 1) . ">";
        $zeile.="<option value=''>&nbsp;</option>";

        for ($k = 1; $k < $VKmax + 1; $k++) { //k=1 means that we will ignore any worker with a number smaller than one. Specific people like the cleaning lady will not be visible in the plan. But their holiday can still be organized with the holiday module.
            if (isset($Dienstplan[$i]["VK"][$j])) {
                if (isset($Mitarbeiter[$k]) and $Dienstplan[$i]["VK"][$j] != $k) { //Dieser Ausdruck dient nur dazu, dass der vorgesehene  Mitarbeiter nicht zwei mal in der Liste auftaucht.
                    $zeile.="<option value=$k>" . $k . " " . $Mitarbeiter[$k] . "</option>";
                } elseif (isset($Mitarbeiter[$k])) {
                    $zeile.="<option value=$k selected>" . $k . " " . $Mitarbeiter[$k] . "</option>"; // Es ist sinnvoll, auch eine leere Zeile zu besitzen, damit Mitarbeiter auch wieder gelöscht werden können.
                }
            } elseif (isset($Mitarbeiter[$k])) {
                $zeile.="<option value=$k>" . $k . " " . $Mitarbeiter[$k] . "</option>";
            }
        }
        $zeile.="</select>\n";
        //Dienstbeginn
        $zeile.="\t\t\t\t\t\t<input type=hidden name=Dienstplan[" . $i . "][Datum][" . $j . "] value=" . htmlentities($Dienstplan[0]["Datum"][$roster_first_key]) . ">\n";
        $zeile.="\t\t\t\t\t\t<input type=time size=5 class=Dienstplan_Dienstbeginn name=Dienstplan[" . $i . "][Dienstbeginn][" . $j . "] id=Dienstplan[" . $i . "][Dienstbeginn][" . $j . "] tabindex=" . ($i * $VKcount * 5 + $j * 5 + 2 ) . " value='";
        if (isset($Dienstplan[$i]["VK"][$j])) {
            $zeile.=strftime('%H:%M', strtotime($Dienstplan[$i]["Dienstbeginn"][$j]));
        }
        $zeile.="'> bis <input type=time size=5 class=Dienstplan_Dienstende name=Dienstplan[" . $i . "][Dienstende][" . $j . "] id=Dienstplan[" . $i . "][Dienstende][" . $j . "] tabindex=" . ($i * $VKcount * 5 + $j * 5 + 3 ) . " value='";
        //Dienstende
        if (isset($Dienstplan[$i]["VK"][$j])) {
            $zeile.=strftime('%H:%M', strtotime($Dienstplan[$i]["Dienstende"][$j]));
        }
        $zeile.="'>";
        echo $zeile;

        echo "</td>\n";
    }
    echo "\t\t\t\t</tr><tr>\n";
    for ($i = 0; $i < count($Dienstplan); $i++) {//Mittagspause
        $zeile = "";
        echo "\t\t\t\t\t<td>";
        $zeile.="<div class='no-print kommentar_ersatz' style=display:inline><a onclick=unhide_kommentar() title='Kommentar anzeigen'>K+</a></div>";
        $zeile.="<div class='no-print kommentar_input' style=display:none><a onclick=rehide_kommentar() title='Kommentar ausblenden'>K-</a></div>";
        $zeile.=" Pause: <input type=time size=5 class=Dienstplan_Mittagbeginn name=Dienstplan[" . $i . "][Mittagsbeginn][" . $j . "] id=Dienstplan[" . $i . "][Mittagsbeginn][" . $j . "] tabindex=" . ($i * $VKcount * 5 + $j * 5 + 4 ) . " value='";
        if (isset($Dienstplan[$i]["VK"][$j]) and $Dienstplan[$i]["Mittagsbeginn"][$j] > 0) {
            $zeile.= strftime('%H:%M', strtotime($Dienstplan[$i]["Mittagsbeginn"][$j]));
        }
        $zeile.="'> bis <input type=time size=5 class=Dienstplan_Mittagsende name=Dienstplan[" . $i . "][Mittagsende][" . $j . "] id=Dienstplan[" . $i . "][Mittagsende][" . $j . "] tabindex=" . ($i * $VKcount * 5 + $j * 5 + 5 ) . " value='";
        if (isset($Dienstplan[$i]["VK"][$j]) and $Dienstplan[$i]["Mittagsbeginn"][$j] > 0) {
            $zeile.= strftime('%H:%M', strtotime($Dienstplan[$i]["Mittagsende"][$j]));
        }
        $zeile.="'>";
        $zeile.="<div class=kommentar_input style=display:none><br>Kommentar: <input type=text name=Dienstplan[" . $i . "][Kommentar][" . $j . "] value=\"";
        if (isset($Dienstplan[$i]["Kommentar"][$j])) {
            $zeile.= $Dienstplan[$i]["Kommentar"][$j];
        }
        $zeile.="\"></div>";
        echo $zeile;
        echo "</td>\n";
    }
}
echo "\t\t\t\t</tr>";

//Wir werfen einen Blick in den Urlaubsplan und schauen, ob alle da sind.
if (isset($Urlauber)) {
    echo "\t\t<tr><td><b>Urlaub</b><br>";
    foreach ($Urlauber as $value) {
        echo $Mitarbeiter[$value] . "<br>";
    };
    echo "</td></tr>\n";
}
if (isset($Kranke)) {
    echo "\t\t<tr><td><b>Krank</b><br>";
    foreach ($Kranke as $value) {
        echo $Mitarbeiter[$value] . "<br>";
    };
    echo "</td></tr>\n";
}
echo "\t\t\t</table>\n";
echo "\t\t</form>\n";


if (!empty($Dienstplan[0]["Dienstbeginn"])) {
    echo "\t\t\t<div class=image>\n";
    require_once 'image_dienstplan.php';
    $svg_image_dienstplan = draw_image_dienstplan($Dienstplan);
    echo $svg_image_dienstplan;
    require_once 'image_histogramm.php';
    $svg_image_histogramm = draw_image_histogramm($Dienstplan);
    echo "<br>\n";
    echo $svg_image_histogramm;
    echo "\t\t\t</div>\n";
}
echo "</div>";
//echo "<pre>";	var_export($Dienstplan);    	echo "</pre>";

require 'contact-form.php';

echo "\t</body>\n";
echo "</html>";
?>
