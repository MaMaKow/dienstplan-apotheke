<?php
require 'default.php';
require 'db-lesen-abwesenheit.php';
require 'db-lesen-mandant.php';
require 'schreiben-ics.php'; //Dieses Script enthält eine Funktion zum schreiben von kleinen ICS Dateien, die mehrere VEVENTs enthalten können.
require "src/php/calculate-holidays.php";

//$datenübertragung="";
$dienstplanCSV = '';
$tage = 7;

$datum = date('Y-m-d'); //Dieser Wert wird überschrieben, wenn "$wochenauswahl und $woche per POST übergeben werden."

require 'cookie-auswertung.php'; //Auswerten der per GET übergebenen Daten.
require 'get-auswertung.php'; //Auswerten der per GET übergebenen Daten.
require 'post-auswertung.php'; //Auswerten der per POST übergebenen Daten.
if (filter_has_var(INPUT_POST, 'submitAuswahlMitarbeiter')) {
    $auswahl_mitarbeiter = filter_input(INPUT_POST, 'auswahl_mitarbeiter', FILTER_SANITIZE_NUMBER_INT);
    $Plan = filter_input(INPUT_POST, 'Dienstplan', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $datum = $Plan[0]['Datum'][0];
    //echo $datum;
} elseif (!isset($auswahl_mitarbeiter)) {
    $auswahl_mitarbeiter = 1;
}
if (isset($auswahl_mitarbeiter)) {
    create_cookie('auswahl_mitarbeiter', $auswahl_mitarbeiter, 30);
}
if (isset($datum)) {
    // Dies ist eine Wochenansicht. Wir beginnen daher immer mit dem Montag.
    $monday_difference = date('w', strtotime($datum)) - 1; //Wir wollen den Anfang der Woche
    $monday_differenceString = '-'.$monday_difference.' day';
    $datum = strtotime($monday_differenceString, strtotime($datum));
    $date_unix = $datum;
    $date_sql = date('Y-m-d', $date_unix);
    $datum = date('Y-m-d', $datum);
    $date_sql_start = $date_sql;

}
//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';
if (!isset($Mitarbeiter[$auswahl_mitarbeiter])) {
  //This happens if a coworker is not working with us anymore.
  //He can still be chosen within abwesenheit and stunden.
  //Therefore we will read his/her number in the cookie.
  //Now we just change it to someone, who is actually there:
  $auswahl_mitarbeiter=min(array_keys($Mitarbeiter));
  //die ("<H1>Mitarbeiter Nummer $auswahl_mitarbeiter ist nicht bekannt.</H1>");
}
//Lesen der in der Datenbank gespeicherten Daten.
require 'db-lesen-woche-mitarbeiter.php';

$VKcount = count($Mitarbeiter); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
//end($Mitarbeiter); $VKmax=key($Mitarbeiter); reset($Mitarbeiter); //Wir suchen nach der höchsten VK-Nummer VKmax.
$VKmax = max(array_keys($Mitarbeiter));
foreach ($Dienstplan as $key => $Dienstplantag) {
    $Plan_anzahl[] = (count($Dienstplantag['VK']));
}
$plan_anzahl = max($Plan_anzahl);

//Produce the output:
require 'head.php';
require 'navigation.php';
require 'src/php/pages/menu.php';
echo "<div id=main-area>\n";
echo "\t\t<a href='woche-out.php?datum=" . htmlentities($date_unix) . "'>Kalenderwoche " . strftime('%V', $date_unix) . "</a><br>\n";

echo build_select_employee($auswahl_mitarbeiter);

//Navigation between the weeks:
echo "<form method='POST' id=navigate_time>";
echo "\t\t\t<input type=hidden name=date value=".$date_sql." form='navigate_time'>\n";
echo "\t\t\t<input type=hidden name=selected_employee value=".$auswahl_mitarbeiter." form='navigate_time'>\n";
echo "$rückwärts_button_week_img";
echo "$vorwärts_button_week_img";
echo '</form>';
echo "\t\t\t<table>\n";
echo "\t\t\t\t<thead>\n";
echo "\t\t\t\t<tr>\n";
for ($tag = 0; $tag < count($Dienstplan); $tag++, $date_sql = date('Y-m-d', strtotime('+ 1 day', $date_unix))) {
    $date_unix = strtotime($date_sql);
    require 'db-lesen-notdienst.php';
    list($Abwesende, $Urlauber, $Kranke)=db_lesen_abwesenheit($date_sql);
    $zeile = '';
    echo "\t\t\t\t\t<td>";
    //Datum
    echo "<a href='tag-out.php?datum=".$Dienstplan[$tag]['Datum'][0]."'>";
    $zeile .= "<input type=hidden name=Dienstplan[".$tag."][Datum][0] value=".$Dienstplan[$tag]["Datum"][0]." form='select_employee'>";
    $zeile .= strftime('%d.%m.', strtotime($Dienstplan[$tag]['Datum'][0]));
    echo $zeile;
    $holiday = is_holiday($date_unix);
    if (FALSE !== $holiday) {
        echo ' '.$holiday.' ';
        if (!isset($bereinigte_Wochenstunden_Mitarbeiter[$auswahl_mitarbeiter]) and date('N', strtotime($date_sql)) < 6) {
            $bereinigte_Wochenstunden_Mitarbeiter[$auswahl_mitarbeiter] = $Stunden_mitarbeiter[$auswahl_mitarbeiter] - $Stunden_mitarbeiter[$auswahl_mitarbeiter] / 5;
        } elseif( date('N', strtotime($date_sql)) < 6) {
            $bereinigte_Wochenstunden_Mitarbeiter[$auswahl_mitarbeiter] = $bereinigte_Wochenstunden_Mitarbeiter[$auswahl_mitarbeiter] - $Stunden_mitarbeiter[$auswahl_mitarbeiter] / 5;
        }
    }
    if (isset($notdienst)) {
        echo ' NOTDIENST ';
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
    if (isset($Abwesende[$auswahl_mitarbeiter])) {
        echo '<br>'.$Abwesende[$auswahl_mitarbeiter];
        if (FALSE !== $holiday and date('N', strtotime($date_sql)) < 6) {
            //An Feiertagen whaben wir die Stunden bereits abgezogen. Keine weiteren Abwesenheitsgründe notwendig.
            if (!isset($bereinigte_Wochenstunden_Mitarbeiter[$auswahl_mitarbeiter])) {
                $bereinigte_Wochenstunden_Mitarbeiter[$auswahl_mitarbeiter] = $Stunden_mitarbeiter[$auswahl_mitarbeiter] - $Stunden_mitarbeiter[$auswahl_mitarbeiter] / 5;
            } else {
                $bereinigte_Wochenstunden_Mitarbeiter[$auswahl_mitarbeiter] = $bereinigte_Wochenstunden_Mitarbeiter[$auswahl_mitarbeiter] - $Stunden_mitarbeiter[$auswahl_mitarbeiter] / 5;
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
        $zeile = '';
        echo "<br>\n\t\t\t\t";
        if (isset($Dienstplan[$i]['VK'][$j]) and $Dienstplan[$i]['Mittagsbeginn'][$j] > 0) {
            $zeile .= ' Pause: ';
            $zeile .= strftime('%H:%M', strtotime($Dienstplan[$i]['Mittagsbeginn'][$j]));
        }
        if (isset($Dienstplan[$i]['VK'][$j]) and $Dienstplan[$i]['Mittagsbeginn'][$j] > 0) {
            $zeile .= ' bis ';
            $zeile .= strftime('%H:%M', strtotime($Dienstplan[$i]['Mittagsende'][$j]));
        }
        if (isset($Dienstplan[$i]['VK'][$j]) and $Dienstplan[$i]['Stunden'][$j] > 0) {
            $zeile .= "<br><a href='stunden-out.php?auswahl_mitarbeiter=".$Dienstplan[$i]["VK"][$j]."'>".$Dienstplan[$i]["Stunden"][$j]." Stunden</a>";
        }
        if (isset($Dienstplan[$i]["VK"][$j]) and isset($Dienstplan[$i]["Mandant"][$j])) {
            $zeile .= "<br>".$Kurz_mandant[$Dienstplan[$i]["Mandant"][$j]];
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
for ($tag = 0; $tag < 5; ++$tag) {
    // Wir wollen nicht wirklich die ganze Woche. Es zählen nur die "Arbeitswochenstunden".
    foreach ($Dienstplan[$tag]['Stunden'] as $key => $stunden) {
        $Stunden[$Dienstplan[$tag]['VK'][$key]][] = $stunden;
    }
}
echo 'Wochenstunden ';
ksort($Stunden);
$i = 1;$j = 1; //Zahler für den Stunden-Array (wir wollen nach je x Elementen einen Umbruch)
foreach ($Stunden as $mitarbeiter => $stunden) {
    echo round(array_sum($stunden), 1);
    echo ' / ';
    if (isset($bereinigte_Wochenstunden_Mitarbeiter[$mitarbeiter])) {
        echo round($bereinigte_Wochenstunden_Mitarbeiter[$mitarbeiter], 1);
    } else {
        echo round($Stunden_mitarbeiter[$mitarbeiter], 1);
    }
    if (isset($bereinigte_Wochenstunden_Mitarbeiter[$mitarbeiter])) {
        if (round($bereinigte_Wochenstunden_Mitarbeiter[$mitarbeiter], 1) != round(array_sum($stunden), 1)) {
            $differenz = round(array_sum($stunden), 1) - round($bereinigte_Wochenstunden_Mitarbeiter[$mitarbeiter], 1);
            echo ' <b>( '.$differenz.' )</b>';
        }
    } else {
        if (round($Stunden_mitarbeiter[$mitarbeiter], 1) != round(array_sum($stunden), 1)) {
            $differenz = round(array_sum($stunden), 1) - round($Stunden_mitarbeiter[$mitarbeiter], 1);
            echo ' <b>( '.$differenz.' )</b>';
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
            $vk = $auswahl_mitarbeiter;
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
            //Und jetzt schreiben wir die Daten noch in eine Datei, damit wir sie mit gnuplot darstellen können.
            if (empty($mittagsbeginn)) {
                $mittagsbeginn = '0:00';
            }
            if (empty($mittagsende)) {
                $mittagsende = '0:00';
            }
            //In der default.php wurde die Sprache für Zeitangaben auf Deutsch gestzt. Daher steht hier z.B. Montag statt Monday.
            $dienstplanCSV .= '" '.strftime('%A', strtotime($date_sql)).'"'.", $vk, ".strftime('%w', strtotime($date_sql));
            $dienstplanCSV .= ', '.$dienstbeginn;
            $dienstplanCSV .= ', '.$dienstende;
            $dienstplanCSV .= ', '.$mittagsbeginn;
            $dienstplanCSV .= ', '.$mittagsende;
            $dienstplanCSV .= '," '.$stunden." \"\n";
        }
    }
}
$filename = 'tmp/Mitarbeiter.csv';
$myfile = fopen($filename, 'w') or die( "Unable to open file $filename!");
fwrite($myfile, $dienstplanCSV);
fclose($myfile);
unset($dienstplanCSV);
$command = ('./Mitarbeiter_image.sh 2>&1 '.escapeshellcmd($Dienstplan[0]['Datum'][0]).'_'.escapeshellcmd($vk));
exec($command, $kommando_ergebnis);
if ( file_exists('images/mitarbeiter_'.$Dienstplan[0]['Datum'][0].'_'.$vk.'.png') )
{
  echo '<img class=worker-img src=images/mitarbeiter_'.$Dienstplan[0]['Datum'][0].'_'.$vk.'.png?'.filemtime('images/mitarbeiter_'.$Dienstplan[0]['Datum'][0].'_'.$vk.'.png').';><br>'; //Um das Bild immer neu zu laden, wenn es verändert wurde müssen wir das Cachen verhindern.
  }
echo "<button type=button style='float:left; height:74px; margin: 0 10px 0 10px' class=no-print " //TODO: Put this into style.css
    . "onclick='location=\"webdav.php?auswahl_mitarbeiter=$auswahl_mitarbeiter&datum=$date_sql_start\"' "
    . "title='Download ics Kalender Datei'>"
        . "<img src=img/download.png style='width:32px' alt='Download ics Kalender Datei'>"
        . "<br>ICS Datei"
    . "</button>\n";
echo "</div>\n";

require 'contact-form.php';

?>
	</BODY>
</HTML>
