<?php
require "default.php";
require "db-verbindung.php";
$mandant = 1;    //Wir zeigen den Grundplan standardmäßig für die "Apotheke am Marienplatz"
$tage = 1;    //Dies ist eine Tagesansicht für einen einzelnen Tag.

#Diese Seite wird den kompletten Grundplan eines einzelnen Wochentages anzeigen.

$datenübertragung = "";
$dienstplanCSV = "";

for ($wochentag = 1; $wochentag <= 5; ++$wochentag) {
    $pseudo_datum = strtotime("-".(date("w") - 1)." day", time());
    $pseudo_datum = strtotime("+".($wochentag - 1)." day", $pseudo_datum);
    //In der default.php wurde die Sprache für Zeitangaben auf Deutsch gestzt. Daher steht hier z.B. Montag statt Monday.
    $Wochentage[$wochentag] = strftime("%A", $pseudo_datum);
}

require "cookie-auswertung.php"; //Auswerten der per COOKIE gespeicherten Daten.
require "get-auswertung.php"; //Auswerten der per GET übergebenen Daten.


if (isset($_POST['mandant'])) {
    $mandant = htmlspecialchars($_POST['mandant']);
}
if (isset($_POST['wochentag'])) {
    $wochentag = htmlspecialchars($_POST['wochentag']);
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
	ORDER BY `Dienstbeginn`
;';
$ergebnis = mysqli_query($verbindungi, $abfrage) or error_log("Error: $abfrage <br>".mysqli_error($verbindungi)) and die("Error: $abfrage <br>".mysqli_error($verbindungi));
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
                if ($sekunden - $Mittag_mitarbeiter[$auswahl_mitarbeiter] * 60 >= 6 * 3600) {
                    $mittagspause = $Mittag_mitarbeiter[$auswahl_mitarbeiter] * 60;
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
$myfile = fopen($filename, 'w') or error_log("Error: $abfrage <br>".mysqli_error($verbindungi)) and die( "Unable to open file $filename!");
fwrite($myfile, $dienstplanCSV);
fclose($myfile);
$dienstplanCSV = '';
$command = ('./Dienstplan_image.sh '.escapeshellcmd('m'.$mandant.'_'.$wochentag));
exec($command, $kommando_ergebnis);

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

$VKcount = count($Mitarbeiter); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
//end($Mitarbeiter); $VKmax=key($Mitarbeiter); reset($Mitarbeiter); //Wir suchen nach der höchsten VK-Nummer VKmax.
$VKmax = max(array_keys($Mitarbeiter));

//Produziere die Ausgabe
?>
<html>
<?php require 'head.php';?>
<body>
<?php
require 'navigation.php';

//Hier beginnt die Normale Ausgabe.
echo "\t\t<div class=main-area>\n";
echo "\t\t\t<form id=mandantenformular method=post>\n";
//echo "\t\t\t\t<input type=hidden name=wochentag value=".$Grundplan[$wochentag]["Wochentag"][0].">\n";
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
//echo "\t\t\t\t<input type=hidden name=mandant value=".$Grundplan[$wochentag]["Mandant"][0].">\n";
echo "\t\t\t\t<select class='no-print large' name=wochentag onchange=this.form.submit()>\n";
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
echo "\t\t\t\t<a href=grundplan-tag-in.php?wochentag=".$wochentag.">[Bearbeiten]</a>\n";
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
if (!isset($Grundplan[$wochentag]["Dienstbeginn"][$j]) or !($Grundplan[$wochentag]["Dienstbeginn"][$j] > 0)) {
  continue 1;
}
    echo "\t\t\t\t</tr><tr>\n";
//Mitarbeiter
        $zeile = '';
    echo "\t\t\t\t\t<td align=right><b>";
    $zeile .= $Mitarbeiter[$Grundplan[$wochentag]["VK"][$j]]."</b> ";
    //Dienstbeginn
    if (isset($Grundplan[$wochentag]["Dienstbeginn"][$j]) and $Grundplan[$wochentag]["Dienstbeginn"][$j] > 0) {
        $zeile .= strftime("%H:%M", strtotime($Grundplan[$wochentag]["Dienstbeginn"][$j]));
    }
    $zeile .= " bis ";
        //Dienstende
        if (isset($Grundplan[$wochentag]["Dienstende"][$j]) and $Grundplan[$wochentag]["Dienstende"][$j] > 0) {
            $zeile .= strftime("%H:%M", strtotime($Grundplan[$wochentag]["Dienstende"][$j]));
        }
    echo $zeile;

    //echo "</td>\n";

    echo "\t\t\t\t<br>\n";
//Mittagspause
        $zeile = "";
    //echo "\t\t\t\t\t<td align=right>";
    if (isset($Grundplan[$wochentag]["VK"][$j]) and $Grundplan[$wochentag]["Mittagsbeginn"][$j] > 0) {
      $zeile .= " Pause: ";
        $zeile .= strftime("%H:%M", strtotime($Grundplan[$wochentag]["Mittagsbeginn"][$j]));
    $zeile .= " bis ";
    $zeile .= strftime("%H:%M", strtotime($Grundplan[$wochentag]["Mittagsende"][$j]));
  }
    echo $zeile;
    echo "</td>\n";
}
echo "\t\t\t\t</tr>";
echo "\t\t\t</table>\n";
echo "\t\t</form>\n";
echo "</div>";
if (file_exists("images/dienstplan_m".$mandant."_".$wochentag.".png")) {
    echo "<div class=above-image>";
    echo "<div class=image>";
echo "<img src=images/dienstplan_m".$mandant."_".$wochentag.".png?".filemtime("images/dienstplan_m".$mandant."_".$wochentag.".png")." style=width:100%;><br>";
//Um das Bild immer neu zu laden, wenn es verändert wurde müssen wir das Cachen verhindern. Therefore we use the file modification time.
echo "<img src=images/histogramm_m".$mandant."_".$datum.".png?".filemtime("images/histogramm_m".$mandant."_".$datum.".png")." style=width:100%;>";
    echo "</div>";
}

require 'contact-form.php';

echo "\t</body>\n";
echo "</html>";
?>
