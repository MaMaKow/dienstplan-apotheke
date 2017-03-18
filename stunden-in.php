<?php
require 'default.php';
//Get a list of employees:
require 'db-lesen-mitarbeiter.php';
//Get a list of branches:
require 'db-lesen-mandant.php';
if (isset($_POST['auswahl_mitarbeiter'])) {
    $auswahl_mitarbeiter = $_POST['auswahl_mitarbeiter'];
} elseif (isset($_GET['auswahl_mitarbeiter'])) {
    $auswahl_mitarbeiter = $_GET['auswahl_mitarbeiter'];
} elseif (isset($_COOKIE['auswahl_mitarbeiter'])) {
    $auswahl_mitarbeiter = $_COOKIE['auswahl_mitarbeiter'];
} else {
    $auswahl_mitarbeiter = min(array_keys($Mitarbeiter));
}
if (isset($auswahl_mitarbeiter)) {
    create_cookie("auswahl_mitarbeiter", $auswahl_mitarbeiter, 30); //Diese Funktion wird von cookie-auswertung.php bereit gestellt. Sie muss vor dem ersten echo durchgeführt werden.
}

//Deleting rows of data:
if (isset($_POST['loeschen'])) {
    foreach ($_POST['loeschen'] as $vk => $Daten) {
        foreach ($Daten as $datum => $X) {
            $abfrage = "DELETE FROM `Stunden`
			WHERE `VK` = '$vk' AND `Datum` = '$datum'";
            $ergebnis = mysqli_query_verbose($abfrage);
        }
    }
    $auswahl_mitarbeiter = $vk;
}

//Wir fügen neue Datensätze ein, wenn ALLE Daten übermittelt werden. (Leere Daten klappen vielleicht auch.)
if (isset($_POST['submitStunden']) and isset($_POST['auswahl_mitarbeiter']) and isset($_POST['datum']) and isset($_POST['stunden']) and isset($_POST['saldo']) and isset($_POST['grund'])) {
    $abfrage = "INSERT INTO `Stunden`
						(VK, Datum, Stunden, Saldo, Grund)
						VALUES (" . $_POST['auswahl_mitarbeiter'] . ", '" . $_POST['datum'] . "', " . $_POST['stunden'] . ", " . $_POST['saldo'] . ", '" . $_POST['grund'] . "')";
    if (!($ergebnis = mysqli_query($verbindungi, $abfrage))) {
        $error_string = mysqli_error($verbindungi);
        if (strpos($error_string, 'Duplicate') !== false) {
            $Fehlermeldung[] = "<b>An diesem Datum existiert bereits ein Eintrag!</b>\n Die Daten wurden daher nicht in die Datenbank eingefügt.";
        } else {
            //Are there other errors, that we should handle?
            error_log("Error: $abfrage <br>" . mysqli_error($verbindungi));
            die("Error: $abfrage <br>" . mysqli_error($verbindungi));
        }
    }
}
$vk = $auswahl_mitarbeiter;
$abfrage = "SELECT * FROM `Stunden`
				WHERE `VK` = " . $vk . "
				ORDER BY `Aktualisierung` ASC
				";
$ergebnis = mysqli_query_verbose($abfrage);
$number_of_rows = mysqli_num_rows($ergebnis);
$tablebody = "\t\t\t<tbody>\n";
$i = 1;
while ($row = mysqli_fetch_object($ergebnis)) {
    $tablebody.= "\t\t\t<tr>\n";
    $tablebody.= "\t\t\t\t<td>\n";
    $tablebody.= "\t\t\t\t\t<form onsubmit='return confirmDelete()' method=POST id=delete_" . $row->Datum . ">\n";
    $tablebody.= "\t\t\t\t\t" . date('d.m.Y', strtotime($row->Datum)) . " <input class=no-print type=submit name=loeschen[$vk][$row->Datum] value='X' title='Diesen Datensatz löschen'>\n";
    $tablebody.= "\t\t\t\t\t</form>\n";
    $tablebody.= "\n\t\t\t\t</td>\n";
    $tablebody.= "\t\t\t\t<td>\n\t\t\t\t\t";
    $tablebody.= "$row->Grund";
    $tablebody.= "\n\t\t\t\t</td>\n";
    $tablebody.= "\t\t\t\t<td>\n\t\t\t\t\t";
    $tablebody.= "$row->Stunden";
    $tablebody.= "\n\t\t\t\t</td>\n";
    if ($i == $number_of_rows) { //Get the last row. //TODO: Perhaps the server should calculate on it's own again afterwards.
        $tablebody.= "\t\t\t\t<td id=saldoAlt>\n\t\t\t\t\t";
    } else {
        $tablebody.= "\t\t\t\t<td>\n\t\t\t\t\t";
    }
    $tablebody.= "$row->Saldo";
    $saldo = $row->Saldo; //Wir tragen den Saldo mit uns fort.
    $tablebody.= "\n\t\t\t\t</td>\n";
    $tablebody.= "\n\t\t\t</tr>\n";
    $i++;
}
$tablebody.= "\t\t\t</tbody>\n";

if (empty($saldo)) {
    $saldo = 0;
}


//Start of output:
require 'head.php';
require 'navigation.php';
require 'src/html/menu.html';
echo "<div id=main-area>\n";

require_once 'src/php/build-warning-messages.php';
echo build_warning_messages($Fehlermeldung, $Warnmeldung);

echo "\t\t<form method=POST>\n";
echo "\t\t\t<select name=auswahl_mitarbeiter class='no-print large' onChange='document.getElementById(\"submitAuswahlMitarbeiter\").click()'>\n";
foreach ($Mitarbeiter as $vk => $name) {
    if ($vk == $auswahl_mitarbeiter) {
        echo "\t\t\t\t<option value=$vk selected>" . $vk . " " . $Mitarbeiter[$vk] . "</option>\n";
    } else {
        echo "\t\t\t\t<option value=$vk>" . $vk . " " . $Mitarbeiter[$vk] . "</option>\n";
    }
}
echo "\t\t\t</select>\n";
$submit_button = "\t\t\t<input hidden type=submit value=Auswahl name='submitAuswahlMitarbeiter' id='submitAuswahlMitarbeiter' class=no-print>\n";
echo $submit_button; //name ist für die $_POST-Variable relevant. Die id wird für den onChange-Event im select benötigt.
echo "\t\t\t<H1 class=only-print>" . $Mitarbeiter[$auswahl_mitarbeiter] . "</H1>\n";
echo "\t\t</form>\n";
echo "<a class=no-print href='stunden-out.php?auswahl_mitarbeiter=$auswahl_mitarbeiter'>[Lesen]</a>\n";

echo "\t\t<table>\n";
//Heading
echo "\t\t\t<thead>\n";
echo "\t\t\t<tr>\n"
 . "\t\t\t\t<th>\n"
 . "\t\t\t\t\tDatum\n"
 . "\t\t\t\t</th>\n"
 . "\t\t\t\t<th>\n"
 . "\t\t\t\t\tGrund\n"
 . "\t\t\t\t</th>\n"
 . "\t\t\t\t<th>\n"
 . "\t\t\t\t\tStunden\n"
 . "\t\t\t\t</th>\n"
 . "\t\t\t\t<th>\n"
 . "\t\t\t\t\tSaldo\n"
 . "\t\t\t\t</th>\n"
 . "\t\t\t</tr>\n"
 . "\t\t\t</thead>\n";

//Ausgabe
echo "$tablebody";

//Eingabe. Der Saldo wird natürlich berechnet.
echo "\t\t\t<tfoot><tr>\n";
echo "\t\t\t\t<td>\n";
echo "\t\t\t\t\t<input type=date id=date_chooser_input class='datepicker' value=" . date('Y-m-d') . " name=datum form=insert_new_overtime>\n";
echo "\t\t\t\t</td>\n";
echo "\t\t\t\t<td>\n";
echo "\t\t\t\t\t<input type=text id=grund name=grund form=insert_new_overtime>\n";
echo "\t\t\t\t</td>\n";
echo "\t\t\t\t<td>\n";
echo "\t\t\t\t\t<input type=text onchange=updatesaldo() id=stunden name=stunden form=insert_new_overtime>\n";
echo "\t\t\t\t</td>\n";
echo "\t\t\t\t<td>\n";
echo "\t\t\t\t\t<input readonly type=text name=saldo id=saldoNeu value=" . $saldo . " form=insert_new_overtime>\n";
echo "\t\t\t\t</td>\n";
echo "\t\t\t</tr></tfoot>\n";
echo "\t\t</table>\n";
echo "\t\t<form method=POST id=insert_new_overtime>\n"
 . "\t\t\t<input class=no-print type=submit name=submitStunden value='Eintragen' form=insert_new_overtime>\n"
 . "\t\t\t<input hidden name=auswahl_mitarbeiter value=$auswahl_mitarbeiter form=insert_new_overtime>\n"
 . "\t\t</form>\n";
echo "\t</div>\n";
require 'contact-form.php';
?>
</body>
</html>
