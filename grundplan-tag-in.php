<?php

require 'default.php';
#Diese Seite wird den kompletten Grundplan eines einzelnen Wochentages anzeigen.

function get_weekday_names() {
    for ($wochentag = 1; $wochentag <= 7; ++$wochentag) {
        $pseudo_date = strtotime('-' . (date('w') - 1) . ' day', time());
        $pseudo_date = strtotime('+' . ($wochentag - 1) . ' day', $pseudo_date);
        $Wochentage[$wochentag] = strftime('%A', $pseudo_date);
    }
    return $Wochentage;
}

$Wochentage = get_weekday_names();

$employee_id = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_employee_id']);
$mandant = user_input::get_variable_from_any_input('mandant', FILTER_SANITIZE_NUMBER_INT, min($List_of_branch_objects));
create_cookie('mandant', $mandant, 30);

if (filter_has_var(INPUT_POST, 'submit_roster')) {
    //TODO: Test if this works:
    user_input::principle_roster_write_user_input_to_database();
}

if (filter_has_var(INPUT_POST, 'wochentag')) {
    $wochentag = filter_input(INPUT_POST, 'wochentag', FILTER_SANITIZE_NUMBER_INT);
} elseif (!empty($Grundplan)) {
    list($wochentag) = array_keys($Grundplan);
} elseif (filter_has_var(INPUT_POST, 'datum')) {
    $wochentag = date('w', strtotime(filter_input(INPUT_POST, 'datum', FILTER_SANITIZE_STRING)));
} else {
    $wochentag = 1;
}


//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';

$sql_query = 'SELECT *
FROM `Grundplan`
WHERE `Wochentag` = "' . $wochentag . '"
	AND `Mandant`="' . $mandant . '"
	ORDER BY `Dienstbeginn` + `Dienstende`, `Dienstbeginn`
;';
$result = mysqli_query_verbose($sql_query);
unset($Grundplan);
while ($row = mysqli_fetch_object($result)) {
    $Grundplan[$wochentag]['Wochentag'][] = $row->Wochentag;
    $Grundplan[$wochentag]['VK'][] = $row->VK;
    $Grundplan[$wochentag]['Dienstbeginn'][] = $row->Dienstbeginn;
    $Grundplan[$wochentag]['Dienstende'][] = $row->Dienstende;
    $Grundplan[$wochentag]['Mittagsbeginn'][] = $row->Mittagsbeginn;
    $Grundplan[$wochentag]['Mittagsende'][] = $row->Mittagsende;

    if (!empty($row->Mittagsbeginn) and ! empty($row->Mittagsende) and $row->Mittagsbeginn > 0 and $row->Mittagsende > 0) {
        $sekunden = strtotime($row->Dienstende) - strtotime($row->Dienstbeginn);
        $mittagspause = strtotime($row->Mittagsende) - strtotime($row->Mittagsbeginn);
        $sekunden = $sekunden - $mittagspause;
        $stunden = round($sekunden / 3600, 1);
    } else {
        $sekunden = strtotime($row->Dienstende) - strtotime($row->Dienstbeginn);
        //Wer länger als 6 Stunden Arbeitszeit hat, bekommt eine Mittagspause.
        if ($sekunden - $List_of_employee_lunch_break_minutes[$row->VK] * 60 >= 6 * 3600) {
            $mittagspause = $List_of_employee_lunch_break_minutes[$row->VK] * 60;
            $sekunden = $sekunden - $mittagspause;
        } else {
            $mittagspause = false;
        }
        $stunden = round($sekunden / 3600, 1);
    }
    $Grundplan[$wochentag]['Stunden'][] = $stunden;
    $Grundplan[$wochentag]['Pause'][] = $mittagspause / 60;
    $Grundplan[$wochentag]['Kommentar'][] = $row->Kommentar;
    $Grundplan[$wochentag]['Mandant'][] = $row->Mandant;

    if ($List_of_employee_professions[$row->VK] == "Apotheker") {
        $worker_style = 1;
    } elseif ($List_of_employee_professions[$row->VK] == "PI") {
        $worker_style = 1;
    } elseif ($List_of_employee_professions[$row->VK] == "PTA") {
        $worker_style = 2;
    } elseif ($List_of_employee_professions[$row->VK] == "PKA") {
        $worker_style = 3;
    } else {
        //anybody else
        $worker_style = 3;
    }
}
//Wir füllen komplett leere Tage mit Werten, damit trotzdem eine Anzeige entsteht.
if (!isset($Grundplan[$wochentag])) {
    $Grundplan[$wochentag]['Wochentag'][] = $wochentag;
    $Grundplan[$wochentag]['VK'][] = '';
    $Grundplan[$wochentag]['Dienstbeginn'][] = '-';
    $Grundplan[$wochentag]['Dienstende'][] = '-';
    $Grundplan[$wochentag]['Mittagsbeginn'][] = '-';
    $Grundplan[$wochentag]['Mittagsende'][] = '-';
    $Grundplan[$wochentag]['Stunden'][] = '-';
    $Grundplan[$wochentag]['Kommentar'][] = '-';
}

/*
 * Wir zeichnen eine Kurve der Anzahl der Mitarbeiter.
 * Dazu übersetzen wir unsere Variablen in die korrekten Namen für das übliche Histrogramm
 */
$Dienstplan[0] = $Grundplan[$wochentag]; //We will use $Dienstplan[0] for functions that are written for the use with single days as a workaround.
$tag = $wochentag;
//Wir brauchen das pseudo_datum vom aktuellen Wochentag
$pseudo_date = strtotime('-' . (date('w') - 1) . ' day', time());
$pseudo_date = strtotime('+' . ($wochentag - 1) . ' day', $pseudo_date);
$date_sql = date('Y-m-d', $pseudo_date);


$VKcount = count($List_of_employees); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
$VKmax = max(array_keys($List_of_employees));

//Produziere die Ausgabe
require 'head.php';
require 'navigation.php';
require 'src/php/pages/menu.php';
if (!$session->user_has_privilege('create_roster')) {
    echo build_warning_messages("", ["Die notwendige Berechtigung zum Erstellen von Dienstplänen fehlt. Bitte wenden Sie sich an einen Administrator."]);
    //die("Die notwendige Berechtigung zum Erstellen von Dienstplänen fehlt. Bitte wenden Sie sich an einen Administrator.");
    die();
}

//Hier beginnt die Normale Ausgabe.
echo "\t\t<H1>Grundplan Tagesansicht</H1>\n";
echo "\t\t<div id=main-area>\n";
echo build_select_branch($mandant, $date_sql);

//Auswahl des Wochentages
echo "\t\t\t<form id='week_day_form' method=post>\n";
echo "\t\t\t\t<input type=hidden name=mandant value=" . $mandant . ">\n";
echo "\t\t\t\t<select class='no-print large' name=wochentag onchange=this.form.submit()>\n";
//echo "\t\t\t\t\t<option value=".$wochentag.">".$Wochentage[$wochentag]."</option>\n";
foreach ($Wochentage as $temp_weekday => $value) {
    if ($temp_weekday != $wochentag) {
        echo "\t\t\t\t\t<option value=" . $temp_weekday . '>' . $value . "</option>\n";
    } else {
        echo "\t\t\t\t\t<option value=" . $temp_weekday . ' selected>' . $value . "</option>\n";
    }
}
echo "\t\t\t\t</select>\n\t\t\t</form>\n";

echo "\t\t\t<div id=navigation_elements>";
echo build_html_navigation_elements::build_button_submit('principle_roster_form');
echo "\t\t\t</div>\n";
echo "\t\t<form id=principle_roster_form method=post>\n";
echo "\t\t\t<table>\n";
echo "\t\t\t\t<tr>\n";
//Datum
$zeile = '';
$zeile .= '<input type=hidden name=Grundplan[' . $wochentag . '][Wochentag][0] value=' . htmlentities($Grundplan[$wochentag]['Wochentag'][0]) . '>';
$zeile .= '<input type=hidden name=mandant value=' . $mandant . '>';
echo $zeile;
//Wochentag

for ($j = 0; $j < $VKcount; ++$j) {
    echo "\t\t\t\t</tr><tr>\n";
//Mitarbeiter
    $zeile = '';
    echo "\t\t\t\t\t<td>";
    $zeile .= "<select name=Grundplan[" . $wochentag . "][VK][" . $j . "] tabindex=" . (($wochentag * $VKcount * 5) + ($j * 5) + 1) . "><option value=''>&nbsp;</option>";
    foreach ($List_of_employees as $k => $name) {
        if (isset($Grundplan[$wochentag]['VK'][$j])) {
            if ($Grundplan[$wochentag]['VK'][$j] != $k) {
                //Dieser Ausdruck dient nur dazu, dass der vorgesehene  Mitarbeiter nicht zwei mal in der Liste auftaucht.

                $zeile .= "<option value='$k'>" . $k . " " . $name . "</option>";
            } else {
                $zeile .= "<option value='$k' selected>" . $k . " " . $name . "</option>";
            }
        } else {
            $zeile .= "<option value='$k'>" . $k . " " . $name . "</option>";
        }
    }
    $zeile .= "</select>\n";
    //Dienstbeginn
    $zeile .= "\t\t\t\t\t\t<input type=hidden name=Grundplan[" . $wochentag . '][Wochentag][' . $j . '] value=';
    if (isset($Grundplan[$wochentag]['Wochentag'][$j])) {
        $zeile .= htmlentities($Grundplan[$wochentag]['Wochentag'][$j]);
    } else {
        $zeile .= $wochentag;
    }

    $zeile .= ">\n";
    $zeile .= "\t\t\t\t\t\t<input type=hidden name=Grundplan[" . $wochentag . '][Kommentar][' . $j . '] value="';
    if (isset($Grundplan[$wochentag]['Kommentar'][$j])) {
        $zeile .= htmlentities($Grundplan[$wochentag]['Kommentar'][$j]);
    }
    $zeile .= "\">\n";
    $zeile .= "\t\t\t\t\t\t<input type=time name=Grundplan[" . $wochentag . '][Dienstbeginn][' . $j . '] tabindex=' . ($wochentag * $VKcount * 5 + $j * 5 + 2) . ' value=';
    if (isset($Grundplan[$wochentag]['Dienstbeginn'][$j]) and $Grundplan[$wochentag]['Dienstbeginn'][$j] > 0) {
        $zeile .= strftime('%H:%M', strtotime($Grundplan[$wochentag]['Dienstbeginn'][$j]));
    }
    $zeile .= '> bis <input type=time name=Grundplan[' . $wochentag . '][Dienstende][' . $j . '] tabindex=' . ($wochentag * $VKcount * 5 + $j * 5 + 3) . ' value=';
    //Dienstende
    if (isset($Grundplan[$wochentag]['Dienstende'][$j]) and $Grundplan[$wochentag]['Dienstende'][$j] > 0) {
        $zeile .= strftime('%H:%M', strtotime($Grundplan[$wochentag]['Dienstende'][$j]));
    }
    $zeile .= '>';
    echo $zeile;

    echo "</td>\n";

    echo "\t\t\t\t</tr><tr>\n";
//Mittagspause
    $zeile = '';
    echo "\t\t\t\t\t<td>";
    $zeile .= gettext("break") . ": <input type=time name=Grundplan[" . $wochentag . "][Mittagsbeginn][" . $j . "] tabindex='" . ($wochentag * $VKcount * 5 + $j * 5 + 4) . "' value='";
    if (isset($Grundplan[$wochentag]['VK'][$j]) and $Grundplan[$wochentag]['Mittagsbeginn'][$j] > 0) {
        $zeile .= strftime('%H:%M', strtotime($Grundplan[$wochentag]['Mittagsbeginn'][$j]));
    }
    $zeile .= "'> bis <input type=time name=Grundplan[" . $wochentag . "][Mittagsende][" . $j . "] tabindex='" . ($wochentag * $VKcount * 5 + $j * 5 + 5) . "' value='";
    if (isset($Grundplan[$wochentag]['VK'][$j]) and $Grundplan[$wochentag]['Mittagsbeginn'][$j] > 0) {
        $zeile .= strftime('%H:%M', strtotime($Grundplan[$wochentag]['Mittagsende'][$j]));
    }
    $zeile .= "'>";

    echo $zeile;
    echo "</td>\n";
}
echo "\t\t\t\t</tr>";

echo "\t\t\t</table>\n";
echo "$submit_button";
echo "\t\t</form>\n";
if (!empty($Grundplan[$wochentag]["Dienstbeginn"])) {
    //TODO: This does not work yet. PLease check Dienstplan equals Grundplan?
    echo "\t\t<div class=above-image>\n";
    echo "\t\t\t<div class=image>\n";
    require_once 'image_dienstplan.php';
    $svg_image_dienstplan = draw_image_dienstplan($Dienstplan);
    echo $svg_image_dienstplan;
    require_once 'image_histogramm.php';
    $svg_image_histogramm = draw_image_histogramm($Dienstplan);
    echo "<br>\n";
    echo $svg_image_histogramm;
    echo "\t\t\t</div>\n";
    echo "\t\t</div>\n";
}
echo '</div>';

require 'contact-form.php';

echo "\t</body>\n";
echo '</html>';
