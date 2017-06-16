<?php
require 'default.php';
$mandant = 1;    //First branch is allways the default.
$tage = 1;    //Dies ist eine Tagesansicht für einen einzelnen Tag.

#Diese Seite wird den kompletten Grundplan eines einzelnen Wochentages anzeigen.

$datenübertragung = '';
$dienstplanCSV = '';

for ($wochentag = 1; $wochentag <= 5; ++$wochentag) {
    $pseudo_datum = strtotime('-'.(date('w') - 1).' day', time());
    $pseudo_datum = strtotime('+'.($wochentag - 1).' day', $pseudo_datum);
    //In der default.php wurde die Sprache für Zeitangaben auf Deutsch gesetzt. Daher steht hier z.B. Montag statt Monday.
    $Wochentage[$wochentag] = strftime('%A', $pseudo_datum);
}

require 'cookie-auswertung.php'; //Auswerten der per COOKIE gespeicherten Daten.
require 'get-auswertung.php'; //Auswerten der per GET übergebenen Daten.
if (filter_has_var(INPUT_POST, 'submitDienstplan')) {
    foreach ($_POST['Grundplan'] as $plan => $inhalt) {
        $Grundplan[filter_var($plan, FILTER_SANITIZE_STRING)] = filter_var($inhalt, FILTER_SANITIZE_STRING);
    }

    foreach ($Grundplan as $wochentag => $value) {
        //First, the old values are deleted.
        $abfrage = "DELETE FROM `Grundplan` WHERE Wochentag='$wochentag' AND Mandant='$mandant'";
        $ergebnis = mysqli_query_verbose($abfrage);
        //New values are composed from the Grundplan from $_POST.
        foreach ($Grundplan[$wochentag]['VK'] as $key => $VK) {
            //Die einzelnen Zeilen im Grundplan
            if (!empty($VK)) {
                //Wir ignorieren die nicht ausgefüllten Felder
                list($VK) = explode(' ', $VK); //Wir brauchen nur die VK Nummer. Die steht vor dem Leerzeichen.
                $dienstbeginn = $Grundplan[$wochentag]['Dienstbeginn'][$key];
                $dienstende = $Grundplan[$wochentag]['Dienstende'][$key];
                $mittagsbeginn = $Grundplan[$wochentag]['Mittagsbeginn'][$key];
                $mittagsende = $Grundplan[$wochentag]['Mittagsende'][$key];
                $kommentar = $Grundplan[$wochentag]['Kommentar'][$key];
                if (!empty($mittagsbeginn) && !empty($mittagsende)) {
                    $sekunden = strtotime($dienstende) - strtotime($dienstbeginn);
                    $mittagspause = strtotime($mittagsende) - strtotime($mittagsbeginn);
                    $sekunden = $sekunden - $mittagspause;
                    $stunden = round($sekunden / 3600, 1);
                } else {
                    $sekunden = strtotime($dienstende) - strtotime($dienstbeginn);
                    //Wer länger als 6 Stunden Arbeitszeit hat, bekommt eine Mittagspause.
                    if (!isset($Mitarbeiter)) {
                      require 'db-lesen-mitarbeiter.php';
                    }

                    if ($sekunden - $Mittag_mitarbeiter[$VK] * 60 >= 6 * 3600) {
                      $mittagspause = $Mittag_mitarbeiter[$VK] * 60;
                      $sekunden = $sekunden - $mittagspause;
                    } else {
                      //Keine Mittagspause
                    }
                    $stunden = round($sekunden / 3600, 1);
                }
                //The new values are stored inside the database.
                $abfrage = "REPLACE INTO `Grundplan` (VK, Wochentag, Dienstbeginn, Dienstende, Mittagsbeginn, Mittagsende, Stunden, Kommentar, Mandant)
			             VALUES ('$VK', '$wochentag', '$dienstbeginn', '$dienstende', '$mittagsbeginn', '$mittagsende', '$stunden', '$kommentar', '$mandant')";
                $ergebnis = mysqli_query_verbose($abfrage);
            }
        }
    }
}

if (filter_has_var(INPUT_POST, 'mandant')) {
    $mandant = filter_input(INPUT_POST, 'mandant', FILTER_SANITIZE_NUMBER_INT);
}
if (filter_has_var(INPUT_POST, 'wochentag')) {
    $mandant = filter_input(INPUT_POST, 'wochentag', FILTER_SANITIZE_NUMBER_INT);
} elseif (!empty($Grundplan)) {
    list($wochentag) = array_keys($Grundplan);
} else {
    $wochentag = 1;
}

if (isset($mandant)) {
    create_cookie('mandant', $mandant, 30);
}

//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';
//Hole eine Liste aller Mandanten (Filialen)
require 'db-lesen-mandant.php';

$abfrage = 'SELECT *
FROM `Grundplan`
WHERE `Wochentag` = "'.$wochentag.'"
	AND `Mandant`="'.$mandant.'"
	ORDER BY `Dienstbeginn` + `Dienstende`, `Dienstbeginn`
;';
$ergebnis = mysqli_query_verbose($abfrage);
unset($Grundplan);
while ($row = mysqli_fetch_object($ergebnis)) {
    $Grundplan[$wochentag]['Wochentag'][] = $row->Wochentag;
    $Grundplan[$wochentag]['VK'][] = $row->VK;
    $Grundplan[$wochentag]['Dienstbeginn'][] = $row->Dienstbeginn;
    $Grundplan[$wochentag]['Dienstende'][] = $row->Dienstende;
    $Grundplan[$wochentag]['Mittagsbeginn'][] = $row->Mittagsbeginn;
    $Grundplan[$wochentag]['Mittagsende'][] = $row->Mittagsende;

    if (!empty($row->Mittagsbeginn) and !empty($row->Mittagsende) and $row->Mittagsbeginn > 0 and $row->Mittagsende > 0) {
        $sekunden = strtotime($row->Dienstende) - strtotime($row->Dienstbeginn);
        $mittagspause = strtotime($row->Mittagsende) - strtotime($row->Mittagsbeginn);
        $sekunden = $sekunden - $mittagspause;
        $stunden = round($sekunden / 3600, 1);
    } else {
        $sekunden = strtotime($row->Dienstende) - strtotime($row->Dienstbeginn);
                //Wer länger als 6 Stunden Arbeitszeit hat, bekommt eine Mittagspause.
                if ($sekunden - $Mittag_mitarbeiter[$row->VK] * 60 >= 6 * 3600) {
                    $mittagspause = $Mittag_mitarbeiter[$row->VK] * 60;
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

	if($Ausbildung_mitarbeiter[$row->VK] == "Apotheker"){
		$worker_style = 1;
	} elseif ($Ausbildung_mitarbeiter[$row->VK] == "PI"){
		$worker_style = 1;
	} elseif ($Ausbildung_mitarbeiter[$row->VK] == "PTA"){
		$worker_style = 2;
	} elseif ($Ausbildung_mitarbeiter[$row->VK] == "PKA"){
		$worker_style = 3;
	} else{
		//anybody else
		$worker_style = 3;
	}
    //Und jetzt schreiben wir die Daten noch in eine Datei, damit wir sie mit gnuplot darstellen können.
    $dienstplanCSV .= $Mitarbeiter[$row->VK].", $row->VK, $wochentag";
    $dienstplanCSV .= ', '.$row->Dienstbeginn;
    $dienstplanCSV .= ', '.$row->Dienstende;
    $dienstplanCSV .= ', '.$row->Mittagsbeginn;
    $dienstplanCSV .= ', '.$row->Mittagsende;
    $dienstplanCSV .= ', '.$row->Stunden;
    $dienstplanCSV .= ', '.$row->Mandant;;
	$dienstplanCSV.=", ".$worker_style."\n";
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

//

 //Wir zeichnen eine Kurve der Anzahl der Mitarbeiter.
//Dazu übersetzen wir unsere Variablen in die korrekten Namen für das übliche Histrogramm
$Dienstplan[0] = $Grundplan[$wochentag]; //We will use $Dienstplan[0] for functions that are written for the use with single days as a workaround.
$tag = $wochentag;
//Wir brauchen das pseudo_datum vom aktuellen Wochentag
$pseudo_datum = strtotime('-'.(date('w') - 1).' day', time());
$pseudo_datum = strtotime('+'.($wochentag - 1).' day', $pseudo_datum);
$datum = date('Y-m-d', $pseudo_datum);

//$Grundplan=db_lesen_tage($tage, $mandant);
/*Die Funktion schaut jetzt nach dem Arbeitsplan in der Helene. Die Daten werden bisher noch nicht verwendet. Das wird aber notwendig sein, denn wir wollen einen Mitarbeiter ja nicht aus versehen an zwei Orten gleichzeitig einsetzen.*/
//foreach ($Mandant as $filiale => $name) {
  //$Filialplan[$filiale]=db_lesen_tage($tage, $filiale, "[^".$filiale."]");
//}

$VKcount = count($Mitarbeiter); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
//end($Mitarbeiter); $VKmax=key($Mitarbeiter); reset($Mitarbeiter); //Wir suchen nach der höchsten VK-Nummer VKmax.
$VKmax = max(array_keys($Mitarbeiter));

//Produziere die Ausgabe
require 'head.php';
require 'navigation.php';
require 'src/html/menu.html';

//Hier beginnt die Normale Ausgabe.
echo "\t\t<H1>Grundplan Tagesansicht</H1>\n";
echo "\t\t<div id=main-area>\n";
echo "\t\t\t<form id=mandantenformular method=post>\n";
echo "\t\t\t\t<input type=hidden name=wochentag value=".$Grundplan[$wochentag]["Wochentag"][0].">\n";
echo "\t\t\t\t<select class='no-print large' name=mandant onchange=this.form.submit()>\n";
//echo "\t\t\t\t\t<option value=".$mandant.">".$Mandant[$mandant]."</option>\n";
foreach ($Mandant as $filiale => $name) {
    if ($filiale != $mandant) {
        echo "\t\t\t\t\t<option value=".$filiale.'>'.$name."</option>\n";
    } else {
        echo "\t\t\t\t\t<option value=".$filiale.' selected>'.$name."</option>\n";
    }
}
echo "\t\t\t\t</select>\n\t\t\t</form>\n";

//Auswahl des Wochentages
echo "\t\t\t<form id=wochentagformular method=post>\n";
echo "\t\t\t\t<input type=hidden name=mandant value=".$Grundplan[$wochentag]["Mandant"][0].">\n";
echo "\t\t\t\t<select class='no-print large' name=wochentag onchange=this.form.submit()>\n";
//echo "\t\t\t\t\t<option value=".$wochentag.">".$Wochentage[$wochentag]."</option>\n";
foreach ($Wochentage as $temp_weekday => $value) {
    if ($temp_weekday != $wochentag) {
        echo "\t\t\t\t\t<option value=".$temp_weekday.'>'.$value."</option>\n";
    } else {
        echo "\t\t\t\t\t<option value=".$temp_weekday.' selected>'.$value."</option>\n";
    }
}
echo "\t\t\t\t</select>\n\t\t\t</form>\n";

echo "\t\t<form id=myform method=post>\n";
//echo "\t\t<form id=myform method=post action=test-post.php>\n";
echo "\t\t\t<div id=navigationsElemente>";
//$submit_button = "\t\t\t\t<input type=submit value=Absenden name=submitGrundplan>\n";
echo "$submit_button_img";
echo "<br>";
echo "<br>";
echo "\t\t\t\t<a href=grundplan-tag-out.php?wochentag=".$wochentag.">[Lesen]</a>\n";
echo "\t\t\t</div>\n";
echo "\t\t\t<table>\n";
echo "\t\t\t\t<tr>\n";
//Datum
    $zeile = '';
    $zeile .= '<input type=hidden name=Grundplan['.$wochentag.'][Wochentag][0] value='.$Grundplan[$wochentag]['Wochentag'][0].'>';
    $zeile .= '<input type=hidden name=mandant value='.$mandant.'>';
    echo $zeile;
//Wochentag

for ($j = 0; $j < $VKcount; ++$j) {
    echo "\t\t\t\t</tr><tr>\n";
//Mitarbeiter
        $zeile = '';
    echo "\t\t\t\t\t<td>";
    $zeile .= "<select name=Grundplan[".$wochentag."][VK][".$j."] tabindex=".(($wochentag * $VKcount * 5) + ($j * 5) + 1)."><option value=''>&nbsp;</option>";
    foreach ($Mitarbeiter as $k => $name) {
        if (isset($Grundplan[$wochentag]['VK'][$j])) {
            if ( $Grundplan[$wochentag]['VK'][$j] != $k) {
                //Dieser Ausdruck dient nur dazu, dass der vorgesehene  Mitarbeiter nicht zwei mal in der Liste auftaucht.

                    $zeile .= "<option value='$k'>".$k." ".$name."</option>";
            } else {
              $zeile .= "<option value='$k' selected>".$k." ".$name."</option>";
            }
        } else {
            $zeile .= "<option value='$k'>".$k." ".$name."</option>";
        }
    }
    $zeile .= "</select>\n";
    //Dienstbeginn
    $zeile .= "\t\t\t\t\t\t<input type=hidden name=Grundplan[".$wochentag.'][Wochentag]['.$j.'] value=';
    if (isset($Grundplan[$wochentag]['Wochentag'][$j])) {
        $zeile .= $Grundplan[$wochentag]['Wochentag'][$j];
    } else {
        $zeile .= $wochentag;
    }

    $zeile .=    ">\n";
    $zeile .= "\t\t\t\t\t\t<input type=hidden name=Grundplan[".$wochentag.'][Kommentar]['.$j.'] value="';
    if (isset($Grundplan[$wochentag]['Kommentar'][$j])) {
        $zeile .= $Grundplan[$wochentag]['Kommentar'][$j];
    }
    $zeile .= "\">\n";
    $zeile .= "\t\t\t\t\t\t<input type=time name=Grundplan[".$wochentag.'][Dienstbeginn]['.$j.'] tabindex='.($wochentag * $VKcount * 5 + $j * 5 + 2).' value=';
    if (isset($Grundplan[$wochentag]['Dienstbeginn'][$j]) and $Grundplan[$wochentag]['Dienstbeginn'][$j] > 0) {
        $zeile .= strftime('%H:%M', strtotime($Grundplan[$wochentag]['Dienstbeginn'][$j]));
    }
    $zeile .= '> bis <input type=time name=Grundplan['.$wochentag.'][Dienstende]['.$j.'] tabindex='.($wochentag * $VKcount * 5 + $j * 5 + 3).' value=';
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
    $zeile .= ' Pause: <input type=time name=Grundplan['.$wochentag.'][Mittagsbeginn]['.$j.'] tabindex='.($wochentag * $VKcount * 5 + $j * 5 + 4).' value=';
    if (isset($Grundplan[$wochentag]['VK'][$j]) and $Grundplan[$wochentag]['Mittagsbeginn'][$j] > 0) {
        $zeile .= strftime('%H:%M', strtotime($Grundplan[$wochentag]['Mittagsbeginn'][$j]));
    }
    $zeile .= '> bis <input type=time name=Grundplan['.$wochentag.'][Mittagsende]['.$j.'] tabindex='.($wochentag * $VKcount * 5 + $j * 5 + 5).' value=';
    if (isset($Grundplan[$wochentag]['VK'][$j]) and $Grundplan[$wochentag]['Mittagsbeginn'][$j] > 0) {
        $zeile .= strftime('%H:%M', strtotime($Grundplan[$wochentag]['Mittagsende'][$j]));
    }
    $zeile .= '>';

    echo $zeile;
    echo "</td>\n";
}
echo "\t\t\t\t</tr>";

//Wir werfen einen Blick in den Urlaubsplan und schauen, ob alle da sind.
if (isset($Urlauber)) {
    echo "\t\t<tr><td><b>Urlaub</b><br>";
    foreach ($Urlauber as $value) {
        echo $Mitarbeiter[$value].'<br>';
    };
    echo "</td></tr>\n";
}
if (isset($Kranke)) {
    echo "\t\t<tr><td><b>Krank</b><br>";
    foreach ($Kranke as $value) {
        echo $Mitarbeiter[$value].'<br>';
    };
    echo "</td></tr>\n";
}
echo "\t\t\t</table>\n";
echo "$submit_button";
echo "\t\t</form>\n";
if (!empty($Grundplan[$wochentag]["Dienstbeginn"]))
{
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
