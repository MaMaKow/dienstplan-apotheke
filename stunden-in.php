<?php
require 'default.php';
//Get a list of employees:
require 'db-lesen-mitarbeiter.php';
//Get a list of branches:
require 'db-lesen-mandant.php';
if (filter_has_var(INPUT_POST, 'auswahl_mitarbeiter')) {
    $auswahl_mitarbeiter = filter_input(INPUT_POST, 'auswahl_mitarbeiter', FILTER_SANITIZE_NUMBER_INT);
} elseif (filter_has_var(INPUT_GET, 'auswahl_mitarbeiter')) {
    $auswahl_mitarbeiter = filter_input(INPUT_GET, 'auswahl_mitarbeiter', FILTER_SANITIZE_NUMBER_INT);
} elseif (filter_has_var(INPUT_COOKIE, 'auswahl_mitarbeiter')) {
    $auswahl_mitarbeiter = filter_input(INPUT_COOKIE, 'auswahl_mitarbeiter', FILTER_SANITIZE_NUMBER_INT);
} else {
    $auswahl_mitarbeiter = min(array_keys($Mitarbeiter));
}
if (isset($auswahl_mitarbeiter)) {
    create_cookie("auswahl_mitarbeiter", $auswahl_mitarbeiter, 30); //Diese Funktion wird von cookie-auswertung.php bereit gestellt. Sie muss vor dem ersten echo durchgeführt werden.
}

//Deleting rows of data:
if (filter_has_var(INPUT_POST, 'loeschen')) {
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
if (filter_has_var(INPUT_POST, 'submitStunden') and filter_has_var(INPUT_POST, 'auswahl_mitarbeiter') and filter_has_var(INPUT_POST, 'datum') and filter_has_var(INPUT_POST, 'stunden') and filter_has_var(INPUT_POST, 'saldo') and filter_has_var(INPUT_POST, 'grund')) {
    $abfrage = "INSERT INTO `Stunden`
        (VK, Datum, Stunden, Saldo, Grund)
        VALUES (" . filter_input(INPUT_POST, 'auswahl_mitarbeiter', FILTER_SANITIZE_NUMBER_INT) 
            . ", '" 
            . filter_input(INPUT_POST, 'datum', FILTER_SANITIZE_STRING) 
            . "', " 
            . filter_input(INPUT_POST, 'stunden', FILTER_SANITIZE_NUMBER_FLOAT) 
            . ", " 
            . filter_input(INPUT_POST, 'saldo', FILTER_SANITIZE_NUMBER_FLOAT) 
            . ", '" 
            . filter_input(INPUT_POST, 'grund', FILTER_SANITIZE_STRING)
            . "')";
    if (!($ergebnis = mysqli_query($verbindungi, $abfrage))) {
        $error_string = mysqli_error($verbindungi);
        if (strpos($error_string, 'Duplicate') !== false) {
            $Fehlermeldung[] = "<b>An diesem Datum existiert bereits ein Eintrag!</b>\n Die Daten wurden daher nicht in die Datenbank eingefügt.";
        } else {
            //Are there other errors, that we should handle?
            error_log("Error: $abfrage <br>" . mysqli_error($verbindungi));
            die("<p>There was an error while querying the database. Please see the error log for more details!</p>");
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
require 'src/php/pages/menu.php';
if(!$session->user_has_privilege('create_roster')){
    echo build_warning_messages("",["Die notwendige Berechtigung zum Erstellen von Arbeitszeitverlagerungen fehlt. Bitte wenden Sie sich an einen Administrator."]);
    die();
}

echo "<div id=main-area>\n";
echo build_warning_messages($Fehlermeldung, $Warnmeldung);

echo build_select_employee($auswahl_mitarbeiter);
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
