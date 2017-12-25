<?php
require 'default.php';
//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';
//Hole Informationen über die Mandanten
require 'db-lesen-mandant.php';
//$datenübertragung="";
$grundplanCSV = '';
$tage = 7;

require 'cookie-auswertung.php'; //Auswerten der per COOKIE übergebenen Daten.
require 'get-auswertung.php'; //Auswerten der per GET übergebenen Daten.
//require "post-auswertung.php"; //Auswerten der per POST übergebenen Daten.
if (filter_has_var(INPUT_POST, 'employee_id')) {
    $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
} elseif (!isset($employee_id)) {
    $employee_id = 1;
}

if (isset($employee_id)) {
    create_cookie('employee_id', $employee_id, 30);
}
if (filter_has_var(INPUT_POST, 'submit_roster')) {
    $Grundplan = filter_input(INPUT_POST, 'Grundplan', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
}
if (isset($Grundplan)) {
    unset($Sql_query_list);
    /*
     * Extract variables from the array:
     * @var $max_rows int number of rows in the principle roster array
     */
    foreach ($Grundplan as $wochentag => $Columns) {
        $row_number = count($Grundplan[$wochentag]['VK']);
        $max_rows = max($row_number, $max_rows);
    }
    for ($row = 0; $row <= $max_rows; ++$row) {
        foreach ($Grundplan as $wochentag => $Columns) {
            foreach ($Columns as $column => $Rows) {
                if (isset($Rows[$row])) {
                    ${$column} = $Rows[$row];
                }
            }
            //TODO: It seems that the $Stunden should be calculated here!
            /*
             * Test if all the variables were set:
             */
            if (isset($VK, $wochentag, $Dienstbeginn, $Dienstende, $Mittagsbeginn, $Mittagsende, $Kommentar, $Stunden, $Mandant)) {
                /*
                 * First, the old values are deleted.
                 */
                $sql_query = "DELETE FROM `Grundplan` WHERE Wochentag='$wochentag' AND VK='$VK'";
                $result = mysqli_query_verbose($sql_query);
                /*
                 * Second, new values are inserted.
                 */
                $Sql_query_list[] = "INSERT INTO `Grundplan` (VK, Wochentag, Dienstbeginn, Dienstende, Mittagsbeginn, Mittagsende, Kommentar, Stunden, Mandant)
					      VALUES ('$VK', '$wochentag', '$Dienstbeginn', '$Dienstende', '$Mittagsbeginn', '$Mittagsende', '$Kommentar', '$Stunden', '$Mandant')";
                unset($VK, $wochentag, $Dienstbeginn, $Dienstende, $Mittagsbeginn, $Mittagsende, $Kommentar, $Stunden, $Mandant);
            }
        }
    }
    foreach ($Sql_query_list as $sql_query) {
        //print_debug_variable($sql_query);
        $result = mysqli_query_verbose($sql_query);
    }
}

//Abruf der gespeicherten Daten aus der Datenbank
unset($Grundplan);
for ($wochentag = 1; $wochentag <= 5; ++$wochentag) {
    $sql_query = 'SELECT *
		FROM `Grundplan`
		WHERE `Wochentag` = "' . $wochentag . '"
			AND `VK`="' . $employee_id . '"
		;';
    $result = mysqli_query_verbose($sql_query);
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
            if ($sekunden - $List_of_employee_lunch_break_minutes[$employee_id] * 60 >= 6 * 3600) {
                $mittagspause = $List_of_employee_lunch_break_minutes[$employee_id] * 60;
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
    }
    //Wir füllen komplett leere Tage mit Werten, damit trotzdem eine Anzeige entsteht.
    if (!isset($Grundplan[$wochentag])) {
        $Grundplan[$wochentag]["Wochentag"][] = $wochentag;
        $Grundplan[$wochentag]["VK"][] = "$employee_id";
        $Grundplan[$wochentag]["Dienstbeginn"][] = null;
        $Grundplan[$wochentag]["Dienstende"][] = null;
        $Grundplan[$wochentag]["Mittagsbeginn"][] = null;
        $Grundplan[$wochentag]["Mittagsende"][] = null;
        $Grundplan[$wochentag]["Stunden"][] = null;
        $Grundplan[$wochentag]["Kommentar"][] = null;
    }
    //Wir machen aus den Nummern 1 bis 7 wieder Wochentage
    // Wir wollen den Anfang der Woche und von dort aus unseren Tag
    $pseudo_datum = strtotime('-' . (date('w') - 1) . ' day', time());
    $pseudo_datum = strtotime('+' . ($wochentag - 1) . ' day', $pseudo_datum);
    //In der default.php wurde die Sprache für Zeitangaben auf Deutsch gestzt. Daher steht hier z.B. Montag statt Monday.
    $Wochentag[$wochentag] = strftime('%A', $pseudo_datum);
}

$VKcount = count($List_of_employees); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
$VKmax = max(array_keys($List_of_employees));
foreach ($Grundplan as $key => $Grundplantag) {
    $Plan_anzahl[] = (count($Grundplantag['VK']));
}
$plan_anzahl = max($Plan_anzahl);

//Produziere die Ausgabe
require 'head.php';
?>
<a name=top></a>
<?php
require 'navigation.php';
require 'src/php/pages/menu.php';
if (!$session->user_has_privilege('create_roster')) {
    echo build_warning_messages("", ["Die notwendige Berechtigung zum Erstellen von Dienstplänen fehlt. Bitte wenden Sie sich an einen Administrator."]);
    //die("Die notwendige Berechtigung zum Erstellen von Dienstplänen fehlt. Bitte wenden Sie sich an einen Administrator.");
    die();
}
echo "<div id=main-area>\n";
echo build_select_employee($employee_id, $List_of_employees);

echo "<form method='POST' id='change_principle_roster_employee'>";
echo $submit_button_img; //name ist für die $_POST-Variable relevant. Die id wird für den onChange-Event im select benötigt.
echo "</form>";

echo "\t\t\t<table>\n";
echo "\t\t\t\t<thead>\n";
echo "\t\t\t\t<tr>\n";
foreach ($Grundplan as $wochentag => $Plan) {
    //Wochentag
    echo "\t\t\t\t\t<td width=14%>";
    echo $Wochentag[$wochentag];
    echo "</td>\n";
}
for ($j = 0; $j < $plan_anzahl; ++$j) {
    echo "\t\t\t\t</tr></thead><tr>\n";
    //for ($wochentag=1; $wochentag<=count($Grundplan); $wochentag++)
    foreach ($Grundplan as $wochentag => $Plan) {
        $zeile = "";
        echo "\t\t\t\t\t<td>&nbsp;";
        //Dienstbeginn
        if (isset($Grundplan[$wochentag]["VK"][$j])) {
            $zeile .= "<input type=time name='Grundplan[" . $wochentag . "][Dienstbeginn][$j]' value='";
            if (empty($Grundplan[$wochentag]["Dienstbeginn"][$j])) {
                $zeile .= "";
            } else {
                $zeile .= strftime("%H:%M", strtotime($Grundplan[$wochentag]["Dienstbeginn"][$j]));
            }
            $zeile .= "' form='change_principle_roster_employee'>";
        }
        //Dienstende
        if (isset($Grundplan[$wochentag]['VK'][$j])) {
            $zeile .= " bis <input type=time name='Grundplan[" . $wochentag . "][Dienstende][$j]' value='";
            if (empty($Grundplan[$wochentag]["Dienstende"][$j])) {
                $zeile .= "";
            } else {
                $zeile .= strftime("%H:%M", strtotime($Grundplan[$wochentag]["Dienstende"][$j]));
            }
            $zeile .= "' form=change_principle_roster_employee'>";
        }
        echo $zeile;

        //Mittagspause
        $zeile = '';
        echo "<br>\n\t\t\t\t";
        if (isset($Grundplan[$wochentag]['VK'][$j]) and $Grundplan[$wochentag]['Mittagsbeginn'][$j] > 0 and $Grundplan[$wochentag]['Mittagsende'][$j] > 0) {
            $zeile .= " " . gettext("break") . ": ";
            $zeile .= '<input type=time name=Grundplan[' . $wochentag . "][Mittagsbeginn][$j] value=";
            $zeile .= strftime('%H:%M', strtotime($Grundplan[$wochentag]['Mittagsbeginn'][$j]));
            $zeile .= " form='change_principle_roster_employee'>";
            $zeile .= ' bis <input type=time name=Grundplan[' . $wochentag . "][Mittagsende][$j] value=";
            $zeile .= strftime('%H:%M', strtotime($Grundplan[$wochentag]['Mittagsende'][$j]));
            $zeile .= " form='change_principle_roster_employee'>\n";
        } else {
            $zeile .= "<div class=mittags_ersatz>";
            if (!empty($Grundplan[$wochentag]['Pause'][$j])) {
                $zeile .= " " . gettext("break") . ": ";
                $zeile .= $Grundplan[$wochentag]['Pause'][$j];
                $zeile .= ' min';
            } else {
                //If there is no break specified and no beak is intended:
                $zeile .= gettext("No break");
            }
            $zeile .= ' <a href=#top onclick=unhide_mittag()>+</a></div>';
            $zeile .= '<div class=mittags_input style=display:none>';
            $zeile .= gettext("break") . ": <input type=time name=Grundplan[" . $wochentag . "][Mittagsbeginn][$j]  form='change_principle_roster_employee'> bis ";
            $zeile .= "<input type=time name=Grundplan[$wochentag][Mittagsende][$j]  form='change_principle_roster_employee'> <a href=#top onclick=rehide_mittag()>-</a></div>";
        }
        //Mittagsende
        if (isset($Grundplan[$wochentag]['VK'][$j]) and isset($Grundplan[$wochentag]['Mandant'][$j])) {
            $zeile .= "<br>\n";
            $zeile .= "<select name=Grundplan[$wochentag][Mandant][$j] form='change_principle_roster_employee'>\n";
            foreach ($Branch_short_name as $branch_id => $branch_short_name) {
                if ($branch_id != $Grundplan[$wochentag]['Mandant'][$j]) {
                    $zeile .= "\t\t\t\t\t<option value=" . $branch_id . '>' . $branch_short_name . "</option>\n";
                } else {
                    $zeile .= "\t\t\t\t\t<option value=" . $branch_id . ' selected>' . $branch_short_name . "</option>\n";
                }
            }
        }
        if (isset($Grundplan[$wochentag]['VK'][$j]) and isset($Grundplan[$wochentag]['Kommentar'][$j])) {
            $zeile .= "<input type=hidden name=Grundplan[$wochentag][Kommentar][$j] value='" . $Grundplan[$wochentag]["Kommentar"][$j] . "' form='change_principle_roster_employee'>\n";
        } else {
            $zeile .= "<input type=hidden name=Grundplan[$wochentag][Kommentar][$j] form='change_principle_roster_employee'>\n";
        }
        if (isset($Grundplan[$wochentag]['VK'][$j])) {
            $zeile .= "<input type=hidden name=Grundplan[$wochentag][VK][$j] value='" . $Grundplan[$wochentag]["VK"][$j] . "' form='change_principle_roster_employee'>\n";
        }
        if (isset($Grundplan[$wochentag]["VK"][$j]) and isset($Grundplan[$wochentag]["Stunden"][$j])) {
            $zeile .= "<input type=hidden name=Grundplan[$wochentag][Stunden][$j] value=" . $Grundplan[$wochentag]["Stunden"][$j] . " form='change_principle_roster_employee'>\n";
            $zeile .= " " . $Grundplan[$wochentag]["Stunden"][$j] . " Stunden";
        }
        $zeile .= "";

        echo $zeile;
        echo "</td>\n";
    }
}
echo "\t\t\t\t</tr>\n";
echo "\t\t\t\t<tfoot>\n";
echo "\t\t\t\t<tr>\n";
echo "\t\t\t\t\t<td colspan=$tage>\n";

//Das folgende wird wohl durch ${spalte} mit $spalte=Stunden ausgelöst, wenn $_POST ausgelesen wird. Dadurch wird $Stunden zum String.
unset($Stunden); //Aber ohne dieses Löschen versagt die folgende Schleife. Sie wird als String betrachtet.
foreach ($Grundplan as $wochentag => $value) {
    // Wir wollen nicht wirklich die ganze Woche. Es zählen nur die "Arbeitswochenstunden".
    if ($wochentag >= 6) {
        continue 1;
    }
    foreach ($Grundplan[$wochentag]["Stunden"] as $key => $stunden) {
        $Stunden[$employee_id][] = $stunden;
    }
}
echo "Wochenstunden ";
ksort($Stunden);
$i = 1;
$j = 1; //Zahler für den Stunden-Array (wir wollen nach je x Elementen einen Umbruch)
foreach ($Stunden as $mitarbeiter => $stunden) {
    echo array_sum($stunden);
    echo ' / ';
    echo $List_of_employee_working_week_hours[$mitarbeiter];
    if ($List_of_employee_working_week_hours[$mitarbeiter] != array_sum($stunden)) {
        $differenz = array_sum($stunden) - $List_of_employee_working_week_hours[$mitarbeiter];
        echo " <b>( " . $differenz . " )</b>";
    }
}
echo "\t\t\t\t\t</td>\n";
echo "\t\t\t\t</tr>\n";
echo "\t\t\t\t</tfoot>\n";
echo "\t\t\t</table>\n";

//$submit_button = "\t\t\t\t<input type=submit value=Absenden name=submitGrundplan>\n";echo "$submit_button";
//echo "\t\t</form>\n";
echo "</div>\n";

require_once 'image_dienstplan_vk.php';
$svg_image_dienstplan = draw_image_dienstplan_vk($Grundplan);
echo $svg_image_dienstplan;


require 'contact-form.php';
?>
</body>
</html>
