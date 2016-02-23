<?php
require 'default.php';
require 'db-verbindung.php';
$mandant = 1;    //Wir zeigen den Grundplan standardmäßig für die "Apotheke am Marienplatz"
$filiale = 2;    //Am unteren Rand werden auch unsere Mitarbeiter in dieser Filale angezeigt.
$tage = 1;    //Dies ist eine Tagesansicht für einen einzelnen Tag.

#Diese Seite wird den kompletten Grundplan eines einzelnen Wochentages anzeigen.

$datenübertragung = '';
$dienstplanCSV = '';

for ($wochentag = 1; $wochentag <= 5; ++$wochentag) {
    $pseudo_datum = strtotime('-'.(date('w') - 1).' day', time());
    $pseudo_datum = strtotime('+'.($wochentag - 1).' day', $pseudo_datum);
    //In der default.php wurde die Sprache für Zeitangaben auf Deutsch gestzt. Daher steht hier z.B. Montag statt Monday.
    $Wochentage[$wochentag] = strftime('%A', $pseudo_datum);
}

//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';
require 'cookie-auswertung.php'; //Auswerten der per COOKIE gespeicherten Daten.
require 'get-auswertung.php'; //Auswerten der per GET übergebenen Daten.
//echo "<pre>";    var_export($_POST);        echo "</pre>"; // Hier kann der aus der Datenbank gelesene Datensatz zu Debugging-Zwecken angesehen werden.
if (isset($_POST['submitGrundplan'])) {
    foreach ($_POST['Grundplan'] as $plan => $inhalt) {
        $Grundplan[$plan] = $inhalt;
    }

    foreach ($Grundplan as $wochentag => $value) {
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
                    if ($sekunden - $MittagMitarbeiter[$VK] * 60 >= 6 * 3600) {
                      $mittagspause = $MittagMitarbeiter[$VK] * 60;
                      $sekunden = $sekunden - $mittagspause;
                    } else {
                      //Keine Mittagspause
                    }
                    $stunden = round($sekunden / 3600, 1);
                }
                $abfrage = "REPLACE INTO `Grundplan` (VK, Wochentag, Dienstbeginn, Dienstende, Mittagsbeginn, Mittagsende, Stunden, Kommentar, Mandant)
			             VALUES ('$VK', '$wochentag', '$dienstbeginn', '$dienstende', '$mittagsbeginn', '$mittagsende', '$stunden', '$kommentar', '$mandant')";
                $ergebnis = mysqli_query($verbindungi, $abfrage) or die("Error: $abfrage <br>".mysqli_error($verbindungi));
                //echo "$abfrage<br>\n";
            }
        }
    }
}

if (isset($_POST['mandant'])) {
    $mandant = htmlspecialchars($_POST['mandant']);
}
if (isset($_POST['wochentag'])) {
    $wochentag = htmlspecialchars($_POST['wochentag']);
} elseif (!empty($Grundplan)) {
    list($wochentag) = array_keys($Grundplan);
} else {
    $wochentag = 1;
}

if (isset($mandant)) {
    create_cookie('mandant', $mandant);
}

//Hole erneut eine Liste aller Mitarbeiter debug DEBUG Post-Auswertung braucht dies und dies braucht POST-Auswertung!
require 'db-lesen-mitarbeiter.php';
//Hole eine Liste aller Mandanten (Filialen)
require 'db-lesen-mandant.php';

$abfrage = 'SELECT *
FROM `Grundplan`
WHERE `Wochentag` = "'.$wochentag.'"
	AND `Mandant`="'.$mandant.'"
	ORDER BY `Dienstbeginn`
;';
$ergebnis = mysqli_query($verbindungi, $abfrage) or die("Error: $abfrage <br>".mysqli_error($verbindungi));
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
                if ($sekunden - $MittagMitarbeiter[$row->VK] * 60 >= 6 * 3600) {
                    $mittagspause = $MittagMitarbeiter[$row->VK] * 60;
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

    //Und jetzt schreiben wir die Daten noch in eine Datei, damit wir sie mit gnuplot darstellen können.
    $dienstplanCSV .= $Mitarbeiter[$row->VK].", $row->VK, $wochentag";
    $dienstplanCSV .= ', '.$row->Dienstbeginn;
    $dienstplanCSV .= ', '.$row->Dienstende;
    $dienstplanCSV .= ', '.$row->Mittagsbeginn;
    $dienstplanCSV .= ', '.$row->Mittagsende;
    $dienstplanCSV .= ', '.$row->Stunden;
    $dienstplanCSV .= ', '.$row->Mandant."\n";
}
$filename = 'tmp/Dienstplan.csv';
$myfile = fopen($filename, 'w') or die('Unable to open file!');
fwrite($myfile, $dienstplanCSV);
fclose($myfile);
$dienstplanCSV = '';
$command = ('./Dienstplan_image.sh '.escapeshellcmd('m'.$mandant.'_'.$wochentag));
exec($command, $kommandoErgebnis);

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
$Dienstplan = $Grundplan;
$tag = $wochentag;
//Wir brauchen das pseudo_datum vom aktuellen Wochentag
$pseudo_datum = strtotime('-'.(date('w') - 1).' day', time());
$pseudo_datum = strtotime('+'.($wochentag - 1).' day', $pseudo_datum);
$datum = date('Y-m-d', $pseudo_datum);
require 'zeichne-histogramm.php';

//$Grundplan=db_lesen_tage($tage, $mandant);
/*Die Funktion schaut jetzt nach dem Arbeitsplan in der Helene. Die Daten werden bisher noch nicht verwendet. Das wird aber notwendig sein, denn wir wollen einen Mitarbeiter ja nicht aus versehen an zwei Orten gleichzeitig einsetzen.*/
//$Filialplan=db_lesen_tage($tage, $filiale, "[^".$filiale."]");

$VKcount = count($Mitarbeiter); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
//end($Mitarbeiter); $VKmax=key($Mitarbeiter); reset($Mitarbeiter); //Wir suchen nach der höchsten VK-Nummer VKmax.
$VKmax = max(array_keys($Mitarbeiter));

//Produziere die Ausgabe
?>
<html>
	<head>
		<meta charset=UTF-8>
		<link rel="stylesheet" type="text/css" href="style.css" media="all">
		<link rel="stylesheet" type="text/css" href="print.css" media="print">
	</head>
	<body>
<?php
require 'navigation.php';

//Hier beginnt die Normale Ausgabe.
echo "\t\t<div class=no-image>\n";
echo "\t\t\t<form id=mandantenformular method=post>\n";
//echo "\t\t\t\t<input type=hidden name=wochentag value=".$Grundplan[$wochentag]["Wochentag"][0].">\n";
echo "\t\t\t\t<select class=no-print style=font-size:150% name=mandant onchange=this.form.submit()>\n";
//echo "\t\t\t\t\t<option value=".$mandant.">".$Mandant[$mandant]."</option>\n";
foreach ($Mandant as $key => $value) {
    //wir verwenden nicht die Variablen $filiale oder Mandant, weil wir diese jetzt nicht verändern wollen!
    if ($key != $mandant) {
        echo "\t\t\t\t\t<option value=".$key.'>'.$value."</option>\n";
    } else {
        echo "\t\t\t\t\t<option value=".$key.' selected>'.$value."</option>\n";
    }
}
echo "\t\t\t\t</select>\n\t\t\t</form>\n";

//Auswahl des Wochentages
echo "\t\t\t<form id=wochentagformular method=post>\n";
//echo "\t\t\t\t<input type=hidden name=mandant value=".$Grundplan[$wochentag]["Mandant"][0].">\n";
echo "\t\t\t\t<select class=no-print style=font-size:150% name=wochentag onchange=this.form.submit()>\n";
//echo "\t\t\t\t\t<option value=".$wochentag.">".$Wochentage[$wochentag]."</option>\n";
foreach ($Wochentage as $key => $value) {
    //wir verwenden nicht die Variablen $filiale oder Mandant, weil wir diese jetzt nicht verändern wollen!
    if ($key != $wochentag) {
        echo "\t\t\t\t\t<option value=".$key.'>'.$value."</option>\n";
    } else {
        echo "\t\t\t\t\t<option value=".$key.' selected>'.$value."</option>\n";
    }
}
echo "\t\t\t\t</select>\n\t\t\t</form>\n";

echo "\t\t<form id=myform method=post>\n";
//echo "\t\t<form id=myform method=post action=test-post.php>\n";
echo "\t\t\t<div id=navigationsElemente>";
$submitButton = "\t\t\t\t<input type=submit value=Absenden name=submitGrundplan>\n";echo "$submitButton";
echo "\t\t\t\t<a href=grundplan-tag-out.php?wochentag=".$wochentag.">[Lesen]</a>\n";
echo "\t\t\t</div>\n";
echo "\t\t\t<table border=2>\n";
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
    echo "\t\t\t\t\t<td align=right>";
    $zeile .= '<select name=Grundplan['.$wochentag.'][VK]['.$j.'] tabindex='.(($wochentag * $VKcount * 5) + ($j * 5) + 1).'><option>';
    if (isset($Grundplan[$wochentag]['VK'][$j]) && isset($Mitarbeiter[$Grundplan[$wochentag]['VK'][$j]])) {
        $zeile .= $Grundplan[$wochentag]['VK'][$j].' '.$Mitarbeiter[$Grundplan[$wochentag]['VK'][$j]];
    }
    $zeile .= '</option>';
    for ($k = 1; $k < $VKmax + 1; ++$k) {
        if (isset($Grundplan[$wochentag]['VK'][$j])) {
            if (isset($Mitarbeiter[$k]) and $Grundplan[$wochentag]['VK'][$j] != $k) {
                //Dieser Ausdruck dient nur dazu, dass der vorgesehene  Mitarbeiter nicht zwei mal in der Liste auftaucht.

                    $zeile .= '<option>'.$k.' '.$Mitarbeiter[$k].'</option>,';
            } else {
                $zeile .= '<option></option>,'; // Es ist sinnvoll, auch eine leere Zeile zu besitzen, damit Mitarbeiter auch wieder gelöscht werden können.
            }
        } elseif (isset($Mitarbeiter[$k])) {
            $zeile .= '<option>'.$k.' '.$Mitarbeiter[$k].'</option>,';
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
    $zeile .= "\t\t\t\t\t\t<input type=time size=1 name=Grundplan[".$wochentag.'][Dienstbeginn]['.$j.'] tabindex='.($wochentag * $VKcount * 5 + $j * 5 + 2).' value=';
    if (isset($Grundplan[$wochentag]['Dienstbeginn'][$j]) and $Grundplan[$wochentag]['Dienstbeginn'][$j] > 0) {
        $zeile .= strftime('%H:%M', strtotime($Grundplan[$wochentag]['Dienstbeginn'][$j]));
    }
    $zeile .= '> bis <input type=time size=1 name=Grundplan['.$wochentag.'][Dienstende]['.$j.'] tabindex='.($wochentag * $VKcount * 5 + $j * 5 + 3).' value=';
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
    echo "\t\t\t\t\t<td align=right>";
    $zeile .= ' Pause: <input type=time size=1 name=Grundplan['.$wochentag.'][Mittagsbeginn]['.$j.'] tabindex='.($wochentag * $VKcount * 5 + $j * 5 + 4).' value=';
    if (isset($Grundplan[$wochentag]['VK'][$j]) and $Grundplan[$wochentag]['Mittagsbeginn'][$j] > 0) {
        $zeile .= strftime('%H:%M', strtotime($Grundplan[$wochentag]['Mittagsbeginn'][$j]));
    }
    $zeile .= '> bis <input type=time size=1 name=Grundplan['.$wochentag.'][Mittagsende]['.$j.'] tabindex='.($wochentag * $VKcount * 5 + $j * 5 + 5).' value=';
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
echo "$submitButton";
echo "\t\t</form>\n";
echo '</div>';
if (file_exists('images/dienstplan_m'.$mandant.'_'.$wochentag.'.png')) {
    echo '<div class=above-image>';
    echo '<div class=image>';
//echo "<td align=center valign=top rowspan=60>";
echo '<img src=images/dienstplan_m'.$mandant.'_'.$wochentag.'.png?'.filemtime('images/dienstplan_m'.$mandant.'_'.$wochentag.'.png').' style=width:100%;><br>';
//Um das Bild immer neu zu laden, wenn es verändert wurde müssen wir das Cachen verhindern.
//echo "</div>";
//echo "<div class=image>";
echo '<img src=images/histogramm_m'.$mandant.'_'.$datum.'.png?'.filemtime('images/histogramm_m'.$mandant.'_'.$datum.'.png').' style=width:100%;>';
    echo '</div>';
//echo "<td></td>";//Wir fügen hier eine Spalte ein, weil im IE9 die Tabelle über die Seite hinaus geht.
}
//	echo "<pre>";	var_export($MandantenMitarbeiter);    	echo "</pre>"; // Hier kann der aus der Datenbank gelesene Datensatz zu Debugging-Zwecken angesehen werden.
    //echo "<pre>";	var_export($Wochentage);    	echo "</pre>"; // Hier kann der aus der Datenbank gelesene Datensatz zu Debugging-Zwecken angesehen werden.

echo "\t</body>\n";
echo '</html>';
?>
