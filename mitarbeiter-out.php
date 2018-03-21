<?php
require 'default.php';
require 'db-lesen-abwesenheit.php';
require 'schreiben-ics.php'; //Dieses Script enthält eine Funktion zum schreiben von kleinen ICS Dateien, die mehrere VEVENTs enthalten können.

$tage = 7;
$date_sql_user_input = user_input::get_variable_from_any_input('datum', FILTER_SANITIZE_NUMBER_INT, date('Y-m-d'));
$datum = general_calculations::get_first_day_of_week($date_sql_user_input);
create_cookie('datum', $datum, 1);

$employee_id = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_employee_id']);
create_cookie('employee_id', $employee_id, 1);

//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';
if (!isset($List_of_employees[$employee_id])) {
//This happens if a coworker is not working with us anymore.
//He can still be chosen within abwesenheit and stunden.
//Therefore we will read his/her number in the cookie.
//Now we just change it to someone, who is actually there:
    $employee_id = min(array_keys($List_of_employees));
//die ("<H1>Mitarbeiter Nummer $employee_id ist nicht bekannt.</H1>");
}

require 'db-lesen-woche-mitarbeiter.php';

$VKcount = count($List_of_employees); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
//end($List_of_employees); $VKmax=key($List_of_employees); reset($List_of_employees); //Wir suchen nach der höchsten VK-Nummer VKmax.
$VKmax = max(array_keys($List_of_employees));
foreach ($Dienstplan as $key => $Dienstplantag) {
    $Plan_anzahl[] = (count($Dienstplantag['VK']));
}
$plan_anzahl = max($Plan_anzahl);

//Produce the output:
require 'head.php';
require 'navigation.php';
require 'src/php/pages/menu.php';
echo "<div id=main-area>\n";
echo "\t\t<a href='woche-out.php?datum=" . htmlentities($date_unix) . "'> " . gettext("calendar week") . strftime(' %V', $date_unix) . "</a><br>\n";

echo build_select_employee($employee_id, $List_of_employees);

//Navigation between the weeks:
echo build_html_navigation_elements::build_button_week_backward($date_sql);
echo build_html_navigation_elements::build_button_week_forward($date_sql);
echo "\t\t\t<table>\n";
echo "\t\t\t\t<thead>\n";
echo "\t\t\t\t<tr>\n";
for ($tag = 0; $tag < count($Dienstplan); $tag++, $date_sql = date('Y-m-d', strtotime('+ 1 day', $date_unix))) {
    $date_unix = strtotime($date_sql);
    $holiday = holidays::is_holiday($date_unix);
    $having_emergency_service = pharmacy_emergency_service::having_emergency_service($date_sql);
    $Abwesende = db_lesen_abwesenheit($date_sql);
    $zeile = '';
    echo "\t\t\t\t\t<td>";
//Datum
    echo "<a href='tag-out.php?datum=" . $Dienstplan[$tag]['Datum'][0] . "'";
    if (FALSE !== $having_emergency_service) {
        echo " title='" . $List_of_employees[$having_emergency_service["employee_id"]] . gettext(" is having emergency service at ") . $List_of_branch_objects[$having_emergency_service["branch_id"]]->name . "'";
    }
    echo ">";
    $zeile .= "<input type=hidden name=Dienstplan[" . $tag . "][Datum][0] value=" . $Dienstplan[$tag]["Datum"][0] . " form='select_employee'>";
    $zeile .= strftime('%d.%m.', strtotime($Dienstplan[$tag]['Datum'][0]));
    echo $zeile;
    if (FALSE !== $holiday) {
        echo " <br><strong>" . $holiday . "</strong> ";
        if (!isset($bereinigte_Wochenstunden_Mitarbeiter[$employee_id]) and date('N', strtotime($date_sql)) < 6) {
            $bereinigte_Wochenstunden_Mitarbeiter[$employee_id] = $List_of_employee_working_week_hours[$employee_id] - $List_of_employee_working_week_hours[$employee_id] / 5;
        } elseif (date('N', strtotime($date_sql)) < 6) {
            $bereinigte_Wochenstunden_Mitarbeiter[$employee_id] = $bereinigte_Wochenstunden_Mitarbeiter[$employee_id] - $List_of_employee_working_week_hours[$employee_id] / 5;
        }
    }
    if (FALSE !== $having_emergency_service) {
        echo " <br><strong>NOTDIENST</strong>"
        . "<br>"
        . $List_of_employees[$having_emergency_service["employee_id"]]
        . "<br>"
        . $List_of_branch_objects[$having_emergency_service["branch_id"]]->name;
    }
//	echo "</td>\n";
//}
//echo "\t\t\t\t</tr><tr>\n";
    echo "\t\t\t\t<br>\n";
//for ($tag=0; $tag<count($Dienstplan); $tag++)
//{//Wochentag
    $zeile = '';
//	echo "\t\t\t\t\t<td style=width:20%>";
//	echo "\t\t\t\t\t<td>";
    $zeile .= strftime('%A', strtotime($Dienstplan[$tag]['Datum'][0]));
    echo $zeile;
    echo '</a>';
    if (isset($Abwesende[$employee_id])) {
        echo '<br>' . $Abwesende[$employee_id];
        if (FALSE !== $holiday and date('N', strtotime($date_sql)) < 6) {
//An Feiertagen whaben wir die Stunden bereits abgezogen. Keine weiteren Abwesenheitsgründe notwendig.
            if (!isset($bereinigte_Wochenstunden_Mitarbeiter[$employee_id])) {
                $bereinigte_Wochenstunden_Mitarbeiter[$employee_id] = $List_of_employee_working_week_hours[$employee_id] - $List_of_employee_working_week_hours[$employee_id] / 5;
            } else {
                $bereinigte_Wochenstunden_Mitarbeiter[$employee_id] = $bereinigte_Wochenstunden_Mitarbeiter[$employee_id] - $List_of_employee_working_week_hours[$employee_id] / 5;
            }
        }
    }
    echo '</td>';
}
for ($j = 0; $j < $plan_anzahl; ++$j) {
    echo "\t\t\t\t</tr></thead><tr>\n";
    for ($i = 0; $i < count($Dienstplan); ++$i) {
        $zeile = '';
        echo "\t\t\t\t\t<td>";
//Dienstbeginn
        if (isset($Dienstplan[$i]['VK'][$j]) and $Dienstplan[$i]['Dienstbeginn'][$j] > 0) {
            $zeile .= strftime('%H:%M', strtotime($Dienstplan[$i]['Dienstbeginn'][$j]));
        }
//Dienstende
        if (isset($Dienstplan[$i]['VK'][$j]) and $Dienstplan[$i]['Dienstende'][$j] > 0) {
            $zeile .= ' bis ';
            $zeile .= strftime('%H:%M', strtotime($Dienstplan[$i]['Dienstende'][$j]));
        }
        $zeile .= '';
        echo $zeile;

//Mittagspause
        $zeile = "<br>\n\t\t\t\t";
        if (isset($Dienstplan[$i]['VK'][$j]) and $Dienstplan[$i]['Mittagsbeginn'][$j] > 0) {
            $zeile .= " " . gettext("break") . ": ";
            $zeile .= strftime('%H:%M', strtotime($Dienstplan[$i]['Mittagsbeginn'][$j]));
        }
        if (isset($Dienstplan[$i]['VK'][$j]) and $Dienstplan[$i]['Mittagsbeginn'][$j] > 0) {
            $zeile .= ' bis ';
            $zeile .= strftime('%H:%M', strtotime($Dienstplan[$i]['Mittagsende'][$j]));
        }
        if (isset($Dienstplan[$i]['VK'][$j]) and $Dienstplan[$i]['Stunden'][$j] > 0) {
            $zeile .= "<br><a href='stunden-out.php?employee_id=" . $Dienstplan[$i]["VK"][$j] . "'>" . $Dienstplan[$i]["Stunden"][$j] . " Stunden</a>";
        }
        if (isset($Dienstplan[$i]["VK"][$j]) and isset($Dienstplan[$i]["Mandant"][$j])) {
            $zeile .= "<br>" . $List_of_branch_objects[$Dienstplan[$i]["Mandant"][$j]]->short_name;
        }
        if (isset($Dienstplan[$i]["VK"][$j]) and ! empty($Dienstplan[$i]["Kommentar"][$j])) {
            $zeile .= "<br>" . $Dienstplan[$i]["Kommentar"][$j];
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
//for ($tag=0; $tag<count($Dienstplan); $tag++)
foreach (array_keys($Dienstplan) as $day_number) {
    foreach ($Dienstplan[$day_number]['Stunden'] as $key => $stunden) {
        $Stunden[$Dienstplan[$day_number]['VK'][$key]][] = $stunden;
    }
}
echo 'Wochenstunden ';
ksort($Stunden);
$i = 1;
$j = 1; //Zahler für den Stunden-Array (wir wollen nach je x Elementen einen Umbruch)
foreach ($Stunden as $mitarbeiter => $stunden) {
    echo round(array_sum($stunden), 1);
    echo ' / ';
    if (isset($bereinigte_Wochenstunden_Mitarbeiter[$mitarbeiter])) {
        echo round($bereinigte_Wochenstunden_Mitarbeiter[$mitarbeiter], 1);
    } else {
        echo round($List_of_employee_working_week_hours[$mitarbeiter], 1);
    }
    if (isset($bereinigte_Wochenstunden_Mitarbeiter[$mitarbeiter])) {
        if (round($bereinigte_Wochenstunden_Mitarbeiter[$mitarbeiter], 1) != round(array_sum($stunden), 1)) {
            $differenz = round(array_sum($stunden), 1) - round($bereinigte_Wochenstunden_Mitarbeiter[$mitarbeiter], 1);
            echo ' <b>( ' . $differenz . ' )</b>';
        }
    } else {
        if (round($List_of_employee_working_week_hours[$mitarbeiter], 1) != round(array_sum($stunden), 1)) {
            $differenz = round(array_sum($stunden), 1) - round($List_of_employee_working_week_hours[$mitarbeiter], 1);
            echo ' <b>( ' . $differenz . ' )</b>';
        }
    }
}
echo "\t\t\t\t\t</td>\n";
echo "\t\t\t\t</tr>\n";
echo "\t\t\t\t</tfoot>\n";
echo "\t\t\t</table>\n";

//Jetzt wird ein Bild gezeichnet, dass den Stundenplan des Mitarbeiters wiedergibt.
foreach (array_keys($Dienstplan) as $tag) {
    $date_sql = $Dienstplan[$tag]['Datum'][0];
    foreach ($Dienstplan[$tag]['VK'] as $key => $vk) {
//Die einzelnen Zeilen im Dienstplan

        if (!empty($vk) and $Dienstplan[$tag]['Dienstbeginn'][$key] != '-') {
//Wir ignorieren die nicht ausgefüllten Felder
//	list($vk)=explode(' ', $vk); //Wir brauchen nur die VK Nummer. Die steht vor dem Leerzeichen.
            $vk = $employee_id;
            $dienstbeginn = $Dienstplan[$tag]['Dienstbeginn'][$key];
            $dienstende = $Dienstplan[$tag]['Dienstende'][$key];
            $mittagsbeginn = $Dienstplan[$tag]['Mittagsbeginn'][$key];
            $mittagsende = $Dienstplan[$tag]['Mittagsende'][$key];
//			$kommentar='Noch nicht eingebaut'
            if (isset($mittagsbeginn) && isset($mittagsende)) {
                $sekunden = strtotime($dienstende) - strtotime($dienstbeginn);
                $mittagspause = strtotime($mittagsende) - strtotime($mittagsbeginn);
                $sekunden = $sekunden - $mittagspause;
                $stunden = $sekunden / 3600;
            } else {
                $sekunden = strtotime($dienstende) - strtotime($dienstbeginn);
                $stunden = $sekunden / 3600;
            }
        }
    }
}
echo "<button type=button class='btn-primary no-print' "
 . "onclick='location=\"webdav.php?employee_id=$employee_id&datum=$datum\"' "
 . "title='Download ics Kalender Datei'>"
 . "<img src=img/download.png style='width:32px' alt='Download ics Kalender Datei'>"
 . "<br>ICS Datei"
 . "</button>\n";
echo "</div>\n";

require 'contact-form.php';
?>
</BODY>
</HTML>
