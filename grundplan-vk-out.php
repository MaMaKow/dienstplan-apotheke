<?php
require 'default.php';
require 'db-verbindung.php';

//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';

//$datenübertragung="";
$grundplanCSV = '';
$tage = 7;

require 'cookie-auswertung.php'; //Auswerten der per COOKIE übergebenen Daten.
require 'get-auswertung.php'; //Auswerten der per GET übergebenen Daten.
//require 'post-auswertung.php'; //Auswerten der per POST übergebenen Daten.
if (isset($_POST['auswahlMitarbeiter'])) {
    $auswahlMitarbeiter = $_POST['auswahlMitarbeiter'];
} elseif (!isset($auswahlMitarbeiter)) {
    $auswahlMitarbeiter = 1;
}

if (isset($auswahlMitarbeiter)) {
    create_cookie('auswahlMitarbeiter', $auswahlMitarbeiter);
}

//Abruf der gespeicherten Daten aus der Datenbank
for ($wochentag = 1; $wochentag <= 5; ++$wochentag) {
    $abfrage = 'SELECT *
		FROM `Grundplan`
		WHERE `Wochentag` = "'.$wochentag.'"
			AND `VK`="'.$auswahlMitarbeiter.'"
		;';
    $ergebnis = mysqli_query($verbindungi, $abfrage) or die("Error: $abfrage <br>".mysqli_error($verbindungi));
    while ($row = mysqli_fetch_object($ergebnis)) {
        $Grundplan[$wochentag]['Wochentag'][] = $row->Wochentag;
        $Grundplan[$wochentag]['VK'][] = $row->VK;
        $Grundplan[$wochentag]['Dienstbeginn'][] = $row->Dienstbeginn;
        $Grundplan[$wochentag]['Dienstende'][] = $row->Dienstende;
        $Grundplan[$wochentag]['Mittagsbeginn'][] = $row->Mittagsbeginn;
        $Grundplan[$wochentag]['Mittagsende'][] = $row->Mittagsende;

        if (!empty($row->Mittagsbeginn) && !empty($row->Mittagsende)) {
            $sekunden = strtotime($row->Dienstende) - strtotime($row->Dienstbeginn);
            $mittagspause = strtotime($row->Mittagsende) - strtotime($row->Mittagsbeginn);
            $sekunden = $sekunden - $mittagspause;
            $stunden = $sekunden / 3600;
        } else {
            $sekunden = strtotime($row->Dienstende) - strtotime($row->Dienstbeginn);
            //Wer länger als 6 Stunden Arbeitszeit hat, bekommt eine Mittagspause.
            if ($sekunden - $MittagMitarbeiter[$auswahlMitarbeiter] * 60 >= 6 * 3600) {
                $mittagspause = $MittagMitarbeiter[$auswahlMitarbeiter] * 60;
                $sekunden = $sekunden - $mittagspause;
            } else {
                $mittagspause = false;
            }
            $stunden = $sekunden / 3600;
        }
        $Grundplan[$wochentag]['Stunden'][] = $stunden;
        $Grundplan[$wochentag]['Pause'][] = $mittagspause / 60;
        $Grundplan[$wochentag]['Kommentar'][] = $row->Kommentar;
        $Grundplan[$wochentag]['Mandant'][] = $row->Mandant;
    }
    //Wir füllen komplett leere Tage mit Werten, damit trotzdem eine Anzeige entsteht.
    // if ( !isset($Grundplan[$wochentag]) )
    // {
    // 	$Grundplan[$wochentag]["Wochentag"][]=$wochentag;
    // 	$Grundplan[$wochentag]["VK"][]="$auswahlMitarbeiter";
    // 	$Grundplan[$wochentag]["Dienstbeginn"][]="-";
    // 	$Grundplan[$wochentag]["Dienstende"][]="-";
    // 	$Grundplan[$wochentag]["Mittagsbeginn"][]="-";
    // 	$Grundplan[$wochentag]["Mittagsende"][]="-";
    // 	$Grundplan[$wochentag]["Stunden"][]="-";
    // 	$Grundplan[$wochentag]["Kommentar"][]="-";
    // }
    //Wir machen aus den Nummern 1 bis 7 wieder Wochentage
    // Wir wollen den Anfang der Woche und von dort aus unseren Tag
    $pseudo_datum = strtotime('-'.(date('w') - 1).' day', time());
    $pseudo_datum = strtotime('+'.($wochentag - 1).' day', $pseudo_datum);
    //In der default.php wurde die Sprache für Zeitangaben auf Deutsch gestzt. Daher steht hier z.B. Montag statt Monday.
    $Wochentag[$wochentag] = strftime('%A', $pseudo_datum);
}
require 'db-lesen-woche-mitarbeiter.php'; //Lesen der in der Datenbank gespeicherten Daten.
require 'db-lesen-feiertag.php';

$VKcount = count($Mitarbeiter); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
//end($Mitarbeiter); $VKmax=key($Mitarbeiter); reset($Mitarbeiter); //Wir suchen nach der höchsten VK-Nummer VKmax.
$VKmax = max(array_keys($Mitarbeiter));
foreach ($Grundplan as $key => $Grundplantag) {
    $PlanAnzahl[] = (count($Grundplantag['VK']));
}
$planAnzahl = max($PlanAnzahl);

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
echo "<div class=no-image>\n";
//echo "\t\t<a href=woche-out.php?datum=".$datum.">Kalenderwoche ".strftime('%V', strtotime($datum))."</a><br>\n";
echo "\t\t<form id=myform method=post>\n";
//$RückwärtsButton="\t\t\t<input type=submit 	class=no-print	value='1 Woche Rückwärts'	name='submitWocheRückwärts'>\n";echo $RückwärtsButton;
//$VorwärtsButton="\t\t\t<input type=submit 	class=no-print	value='1 Woche Vorwärts'	name='submitWocheVorwärts'>\n";echo $VorwärtsButton;
//$zeile="<br>";
$zeile = "<select name=auswahlMitarbeiter class=no-print onChange=document.getElementById('submitAuswahlMitarbeiter').click()>";
$zeile .= "<option value=$auswahlMitarbeiter>".$auswahlMitarbeiter.' '.$Mitarbeiter[$auswahlMitarbeiter].'</option>,';
for ($vk = 1; $vk < $VKmax + 1; ++$vk) {
    if (isset($Mitarbeiter[$vk])) {
        $zeile .= "<option value=$vk>".$vk.' '.$Mitarbeiter[$vk].'</option>,';
    }
}
$zeile .= '</select>';
echo $zeile;
$submitButton = "\t<input type=submit value=Absenden name='submitAuswahlMitarbeiter' id='submitAuswahlMitarbeiter' class=no-print>\n"; echo $submitButton; //name ist für die $_POST-Variable relevant. Die id wird für den onChange-Event im select benötigt.
echo '<H1>'.$Mitarbeiter[$auswahlMitarbeiter].'</H1>';

echo "\t\t\t<table border=1>\n";
echo "\t\t\t\t<thead>\n";
echo "\t\t\t\t<tr>\n";
foreach ($Grundplan as $wochentag => $Plan) {
    //Wochentag
    echo "\t\t\t\t\t<td width=14%>";
    echo $Wochentag[$wochentag];
    echo "</td>\n";
}
for ($j = 0; $j < $planAnzahl; ++$j) {
    if (isset($feiertag) && !isset($notdienst)) {
        break 1;
    }
    echo "\t\t\t\t</tr></thead><tr>\n";
    //for ($wochentag=1; $wochentag<=count($Grundplan); $wochentag++)
    foreach ($Grundplan as $wochentag => $Plan) {
        $zeile = '';
        echo "\t\t\t\t\t<td align=right>&nbsp";
        //Dienstbeginn
        if (isset($Grundplan[$wochentag]['VK'][$j]) and $Grundplan[$wochentag]['Dienstbeginn'][$j] > 0) {
            $zeile .= strftime('%H:%M', strtotime($Grundplan[$wochentag]['Dienstbeginn'][$j]));
        }
        //Dienstende
        if (isset($Grundplan[$wochentag]['VK'][$j]) and $Grundplan[$wochentag]['Dienstende'][$j] > 0) {
            $zeile .= ' bis ';
            $zeile .= strftime('%H:%M', strtotime($Grundplan[$wochentag]['Dienstende'][$j]));
        }
        $zeile .= '';
        echo $zeile;

        //Mittagspause
        $zeile = '';
        echo "<br>\n\t\t\t\t";
        if (isset($Grundplan[$wochentag]['VK'][$j]) and $Grundplan[$wochentag]['Mittagsbeginn'][$j] > 0) {
            $zeile .= ' Pause: ';
            $zeile .= strftime('%H:%M', strtotime($Grundplan[$wochentag]['Mittagsbeginn'][$j]));
        } elseif (!empty($Grundplan[$wochentag]['Pause'][$j])) {
            $zeile .= ' Pause: ';
            $zeile .= $Grundplan[$wochentag]['Pause'][$j];
            $zeile .= ' min';
        }
        if (isset($Grundplan[$wochentag]['VK'][$j]) and $Grundplan[$wochentag]['Mittagsbeginn'][$j] > 0) {
            $zeile .= ' bis ';
            $zeile .= strftime('%H:%M', strtotime($Grundplan[$wochentag]['Mittagsende'][$j]));
        }
        if (isset($Grundplan[$wochentag]['VK'][$j]) and $Grundplan[$wochentag]['Stunden'][$j] > 0) {
            $zeile .= '<br><a href=stunden-out.php?auswahlMitarbeiter='.$Grundplan[$wochentag]['VK'][$j].'>'.$Grundplan[$wochentag]['Stunden'][$j].' Stunden';
        }
        $zeile .= '';

        echo $zeile;
        echo "</td>\n";
    }
}
echo "\t\t\t\t</tr>\n";
echo "\t\t\t\t<tfoot>\n";

echo "\t\t\t\t</tr>\n"; //debug DEBUG THis one seems to be bulshit. There is a </tr> a few lines above before the start of this <tfoot>
echo "\t\t\t\t<tr>\n";
echo "\t\t\t\t\t<td colspan=$tage>\n";
//for ($tag=0; $tag<count($Grundplan); $tag++)
for ($wochentag = 1; $wochentag <= 5; ++$wochentag) {// Wir wollen nicht wirklich die ganze Woche. Es zählen nur die "Arbeitswochenstunden".
    foreach ($Grundplan[$wochentag]['Stunden'] as $key => $stunden) {
        $Stunden[$Grundplan[$wochentag]['VK'][$key]][] = $stunden;
    }
}
echo 'Wochenstunden ';
ksort($Stunden);
$i = 1;$j = 1; //Zahler für den Stunden-Array (wir wollen nach je x Elementen einen Umbruch)
foreach ($Stunden as $mitarbeiter => $stunden) {
    echo array_sum($stunden);
    echo ' / ';
    echo $StundenMitarbeiter[$mitarbeiter];
    if ($StundenMitarbeiter[$mitarbeiter] != array_sum($stunden)) {
        $differenz = array_sum($stunden) - $StundenMitarbeiter[$mitarbeiter];
        echo ' <b>( '.$differenz.' )</b>';
    }
}
echo "\t\t\t\t\t</td>\n";
echo "\t\t\t\t</tr>\n";
echo "\t\t\t\t</tfoot>\n";
echo "\t\t\t</table>\n";
// echo $submitButton;
echo "\t\t</form>\n";
echo "</div>\n";

//Jetzt wird ein Bild gezeichnet, dass den Stundenplan des Mitarbeiters wiedergibt.
foreach (array_keys($Grundplan) as $wochentag) {
    foreach ($Grundplan[$wochentag]['VK'] as $key => $vk) {
        //Die einzelnen Zeilen im Grundplan

        if (!empty($vk)) {
            //Wir ignorieren die nicht ausgefüllten Felder

            $vk = $auswahlMitarbeiter;
            $dienstbeginn = $Grundplan[$wochentag]['Dienstbeginn'][$key];
            $dienstende = $Grundplan[$wochentag]['Dienstende'][$key];
            $mittagsbeginn = $Grundplan[$wochentag]['Mittagsbeginn'][$key]; //if(empty($Mittagsbeginn)){$Mittagsbeginn="0:00";}
            $mittagsende = $Grundplan[$wochentag]['Mittagsende'][$key]; //if(empty($Mittagsende)){$Mittagsende="0:00";}
//			$kommentar='Noch nicht eingebaut'
            $stunden = $Grundplan[$wochentag]['Stunden'][$key];    //Und jetzt schreiben wir die Daten noch in eine Datei, damit wir sie mit gnuplot darstellen können.
            if (empty($mittagsbeginn)) {
                $mittagsbeginn = '0:00';
            }
            if (empty($mittagsende)) {
                $mittagsende = '0:00';
            }
            $grundplanCSV .= '" '.$Wochentag[$wochentag]."\", $vk, $wochentag";
            $grundplanCSV .= ', '.$dienstbeginn;
            $grundplanCSV .= ', '.$dienstende;
            $grundplanCSV .= ', '.$mittagsbeginn;
            $grundplanCSV .= ', '.$mittagsende;
            $grundplanCSV .= ', "'.$stunden." \"\n";
        }
    }
}
$filename = 'tmp/Mitarbeiter.csv';
$myfile = fopen($filename, 'w') or die('Unable to open file!');
fwrite($myfile, $grundplanCSV);
fclose($myfile);
$grundplanCSV = '';
$command = ('./Mitarbeiter_image.sh '.escapeshellcmd($vk));
exec($command, $kommandoErgebnis);
echo '<img src=images/mitarbeiter_'.$vk.'.png?'.filemtime('images/mitarbeiter_'.$vk.'.png').' style=width:70%;><br>'; //Um das Bild immer neu zu laden, wenn es verändert wurde müssen wir das Cachen verhindern.

//echo "<pre>";	var_export($Grundplan);    	echo "</pre>";

?>
	</body>
<html>
