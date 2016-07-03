<?php
require 'default.php';
require 'db-verbindung.php';
require 'schreiben-ics.php'; //Dieses Script enthält eine Funktion zum schreiben von kleinen ICS Dateien, die mehrere VEVENTs enthalten können.

//$datenübertragung="";
$dienstplanCSV = '';
$tage = 7;

$datum = date('Y-m-d'); //Dieser Wert wird überschrieben, wenn "$wochenauswahl und $woche per POST übergeben werden."
// $montags_differenz=date("w", strtotime($datum))-1; //Wir wollen den Anfang der Woche
// $montags_differenzString="-".$montags_differenz." day";
// $datum=strtotime($montags_differenzString, strtotime($datum));
// $datum=date('Y-m-d', $datum);

require 'cookie-auswertung.php'; //Auswerten der per GET übergebenen Daten.
require 'get-auswertung.php'; //Auswerten der per GET übergebenen Daten.
require 'post-auswertung.php'; //Auswerten der per POST übergebenen Daten.
if (isset($_POST['submitAuswahlMitarbeiter'])) {
    $auswahl_mitarbeiter = $_POST['auswahl_mitarbeiter'];
    $Plan = $_POST['Dienstplan'];
    $datum = $Plan[0]['Datum'][0];
    //echo $datum;
} elseif (isset($_POST['submitWocheRückwärts']) or isset($_POST['submitWocheVorwärts'])) {
    $auswahl_mitarbeiter = $_POST['auswahl_mitarbeiter'];
} elseif (!isset($auswahl_mitarbeiter)) {
    $auswahl_mitarbeiter = 1;
}
if (isset($auswahl_mitarbeiter)) {
    create_cookie('auswahl_mitarbeiter', $auswahl_mitarbeiter);
}

if (isset($datum)) {
    // Dies ist eine Wochenansicht. Wir beginnen daher immer mit dem Montag.
    $montags_differenz = date('w', strtotime($datum)) - 1; //Wir wollen den Anfang der Woche
    $montags_differenzString = '-'.$montags_differenz.' day';
    $datum = strtotime($montags_differenzString, strtotime($datum));
    $datum = date('Y-m-d', $datum);
}
//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';
//Lesen der in der Datenbank gespeicherten Daten.
require 'db-lesen-woche-mitarbeiter.php';
require 'db-lesen-feiertag.php';

$VKcount = count($Mitarbeiter); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
//end($Mitarbeiter); $VKmax=key($Mitarbeiter); reset($Mitarbeiter); //Wir suchen nach der höchsten VK-Nummer VKmax.
$VKmax = max(array_keys($Mitarbeiter));
foreach ($Dienstplan as $key => $Dienstplantag) {
    $Plan_anzahl[] = (count($Dienstplantag['VK']));
}
$plan_anzahl = max($Plan_anzahl);

//Produziere die Ausgabe
?>
<html>
	<head>
		<meta charset=UTF-8>
    <script type="text/javascript" src="javascript.js" ></script>
		<link rel="stylesheet" type="text/css" href="style.css" media="all">
		<link rel="stylesheet" type="text/css" href="print.css" media="print">
	</head>
	<body>
<?php
require 'navigation.php';
echo "<div class=no-image>\n";
echo "\t\t<a href=woche-out.php?datum=".$datum.'>Kalenderwoche '.strftime('%V', strtotime($datum))."</a><br>\n";
//echo "\t\tKalenderwoche ".strftime('%V', strtotime($datum))."<br>\n";
echo "\t\t<form id=myform method=post>\n";
$Rückwärts_button = "\t\t\t<input type=submit 	class=no-print	value='1 Woche Rückwärts'	name='submitWocheRückwärts'>\n";echo $Rückwärts_button;
$Vorwärts_button = "\t\t\t<input type=submit 	class=no-print	value='1 Woche Vorwärts'	name='submitWocheVorwärts'>\n";echo $Vorwärts_button;
$zeile = '<br>';
//$zeile.="<select name=auswahl_mitarbeiter class=no-print onChange=this.form.submit()>";
$zeile .= "<select name=auswahl_mitarbeiter class=no-print onChange=document.getElementById('submitAuswahlMitarbeiter').click()>";
//$zeile .= "<option value=$auswahl_mitarbeiter>".$auswahl_mitarbeiter.' '.$Mitarbeiter[$auswahl_mitarbeiter].'</option>,';
for ($vk = 1; $vk < $VKmax + 1; ++$vk) {
    if (isset($Mitarbeiter[$vk])) {
        if ($vk == $auswahl_mitarbeiter) {
            $zeile .= "<option value=$vk selected>".$vk.' '.$Mitarbeiter[$vk].'</option>,';
        } else {
            $zeile .= "<option value=$vk>".$vk.' '.$Mitarbeiter[$vk].'</option>,';
        }
    }
}
$zeile .= '</select>';
echo $zeile;
$submit_button = "\t<input type=submit value=Absenden name='submitAuswahlMitarbeiter' id='submitAuswahlMitarbeiter' class=no-print>\n"; echo $submit_button; //name ist für die $_POST-Variable relevant. Die id wird für den onChange-Event im select benötigt.
echo '<H1>'.$Mitarbeiter[$auswahl_mitarbeiter].'</H1>';

//echo "\t\t\t<table border=0 rules=groups style=width:99%>\n";
echo "\t\t\t<table border=1>\n";
echo "\t\t\t\t<thead>\n";
echo "\t\t\t\t<tr>\n";
for ($tag = 0; $tag < count($Dienstplan); $tag++, $datum = date('Y-m-d', strtotime('+ 1 day', strtotime($datum)))) {
    //Datum
    require 'db-lesen-feiertag.php';
    require 'db-lesen-notdienst.php';
    require 'db-lesen-abwesenheit.php';
    $zeile = '';
    echo "\t\t\t\t\t<td width=".floor(100 / $tage).'%>';
    echo '<a href=tag-out.php?datum='.$Dienstplan[$tag]['Datum'][0].'>';
    $zeile .= '<input type=hidden size=2 name=Dienstplan['.$tag.'][Datum][0] value='.$Dienstplan[$tag]['Datum'][0].'>';
    $zeile .= strftime('%d.%m.', strtotime($Dienstplan[$tag]['Datum'][0]));
    echo $zeile;
    if (isset($feiertag)) {
        echo ' '.$feiertag.' ';
        if (!isset($bereinigte_Wochenstunden_Mitarbeiter[$auswahl_mitarbeiter]) and date('N', strtotime($datum)) < 6) {
            $bereinigte_Wochenstunden_Mitarbeiter[$auswahl_mitarbeiter] = $Stunden_mitarbeiter[$auswahl_mitarbeiter] - $Stunden_mitarbeiter[$auswahl_mitarbeiter] / 5;
        } elseif( date('N', strtotime($datum)) < 6) {
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
    if (array_search($auswahl_mitarbeiter, $Abwesende) !== false) {
        echo '<br>'.$Abwesenheits_grund[$auswahl_mitarbeiter];
        if (!isset($feiertag) and date('N', strtotime($datum)) < 6) {
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
        echo "\t\t\t\t\t<td align=right>&nbsp";
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
            $zeile .= '<br><a href=stunden-out.php?auswahl_mitarbeiter='.$Dienstplan[$i]['VK'][$j].'>'.$Dienstplan[$i]['Stunden'][$j].' Stunden';
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
// echo $submit_button;
echo "\t\t</form>\n";
echo "</div>\n";

//Jetzt wird ein Bild gezeichnet, dass den Stundenplan des Mitarbeiters wiedergibt.
foreach (array_keys($Dienstplan) as $tag) {
    $datum = $Dienstplan[$tag]['Datum'][0];
    foreach ($Dienstplan[$tag]['VK'] as $key => $vk) {
        //Die einzelnen Zeilen im Dienstplan

        if (!empty($vk) and $Dienstplan[$tag]['Dienstbeginn'][$key] != '-') {
            //Wir ignorieren die nicht ausgefüllten Felder

        //	list($vk)=explode(' ', $vk); //Wir brauchen nur die VK Nummer. Die steht vor dem Leerzeichen.
            $vk = $auswahl_mitarbeiter;
            $dienstbeginn = $Dienstplan[$tag]['Dienstbeginn'][$key];
            $dienstende = $Dienstplan[$tag]['Dienstende'][$key];
            $mittagsbeginn = $Dienstplan[$tag]['Mittagsbeginn'][$key]; //if(empty($Mittagsbeginn)){$Mittagsbeginn="0:00";}
            $mittagsende = $Dienstplan[$tag]['Mittagsende'][$key]; //if(empty($Mittagsende)){$Mittagsende="0:00";}
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
            $dienstplanCSV .= '" '.strftime('%A', strtotime($datum)).'"'.", $vk, ".strftime('%w', strtotime($datum));
            $dienstplanCSV .= ', '.$dienstbeginn;
            $dienstplanCSV .= ', '.$dienstende;
            $dienstplanCSV .= ', '.$mittagsbeginn;
            $dienstplanCSV .= ', '.$mittagsende;
            $dienstplanCSV .= '," '.$stunden." \"\n";
        }
    }
}
$filename = 'tmp/Mitarbeiter.csv';
$myfile = fopen($filename, 'w') or die('Unable to open file!');
fwrite($myfile, $dienstplanCSV);
fclose($myfile);
$dienstplanCSV = '';
$command = ('./Mitarbeiter_image.sh '.escapeshellcmd($Dienstplan[0]['Datum'][0]).'_'.escapeshellcmd($vk));
exec($command, $kommando_ergebnis);
if ( file_exists('images/mitarbeiter_'.$Dienstplan[0]['Datum'][0].'_'.$vk.'.png') )
{
  echo '<img src=images/mitarbeiter_'.$Dienstplan[0]['Datum'][0].'_'.$vk.'.png?'.filemtime('images/mitarbeiter_'.$Dienstplan[0]['Datum'][0].'_'.$vk.'.png').' style=width:70%;><br>'; //Um das Bild immer neu zu laden, wenn es verändert wurde müssen wir das Cachen verhindern.
  schreiben_ics($Dienstplan); //Schreibt die Daten aus dem Dienstplan (alle Tage, ohne Pause) in eine ics Datei. Fügt einen Download-button für die Datei ein.
}


//echo "<pre>";	var_export($_POST);    	echo "</pre>";

require 'contact-form.php';

?>
	</body>
<html>
