<?php
require 'default.php';
//Get a list of employees:
require 'db-lesen-mitarbeiter.php';
//Get a list of branches:
require 'db-lesen-mandant.php';
if (filter_has_var(INPUT_POST, 'employee_id')) {
    $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
} elseif (filter_has_var(INPUT_GET, 'employee_id')) {
    $employee_id = filter_input(INPUT_GET, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
} elseif (filter_has_var(INPUT_COOKIE, 'employee_id')) {
    $employee_id = filter_input(INPUT_COOKIE, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
} else {
    $employee_id = min(array_keys($List_of_employees));
}
if (isset($employee_id)) {
    create_cookie("employee_id", $employee_id, 30); //Diese Funktion wird von cookie-auswertung.php bereit gestellt. Sie muss vor dem ersten echo durchgeführt werden.
}

//Deleting rows of data:
if (filter_has_var(INPUT_POST, 'loeschen')) {
    $Remove = filter_input(INPUT_POST, 'loeschen', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    foreach ($Remove as $employee_id => $Data) {
        $employee_id = intval($employee_id);
        foreach ($Data as $date_sql => $X) {
            $sql_query = "DELETE FROM `Stunden`
			WHERE `VK` = '$employee_id' AND `Datum` = '$date_sql'";
            $result = mysqli_query_verbose($sql_query);
        }
    }
    $employee_id = $employee_id;
}

//Wir fügen neue Datensätze ein, wenn ALLE Daten übermittelt werden. (Leere Daten klappen vielleicht auch.)
if (filter_has_var(INPUT_POST, 'submitStunden') and filter_has_var(INPUT_POST, 'employee_id') and filter_has_var(INPUT_POST, 'datum') and filter_has_var(INPUT_POST, 'stunden') and filter_has_var(INPUT_POST, 'saldo') and filter_has_var(INPUT_POST, 'grund')) {
    $sql_query = "INSERT INTO `Stunden`
        (VK, Datum, Stunden, Saldo, Grund)
        VALUES (" . filter_input(INPUT_POST, 'employee_id', FILTER_SANITIZE_NUMBER_INT)
            . ", '"
            . filter_input(INPUT_POST, 'datum', FILTER_SANITIZE_STRING)
            . "', "
            . filter_input(INPUT_POST, 'stunden', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)
            . ", "
            . filter_input(INPUT_POST, 'saldo', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)
            . ", '"
            . filter_input(INPUT_POST, 'grund', FILTER_SANITIZE_STRING)
            . "')";
    if (!($result = mysqli_query($verbindungi, $sql_query))) {
        $error_string = mysqli_error($verbindungi);
        if (strpos($error_string, 'Duplicate') !== false) {
            $Fehlermeldung[] = "<b>An diesem Datum existiert bereits ein Eintrag!</b>\n Die Daten wurden daher nicht in die Datenbank eingefügt.";
        } else {
            //Are there other errors, that we should handle?
            error_log("Error: $sql_query <br>" . mysqli_error($verbindungi));
            die("<p>There was an error while querying the database. Please see the error log for more details!</p>");
        }
    }
}
$vk = $employee_id;
$sql_query = "SELECT * FROM `Stunden`
				WHERE `VK` = " . $vk . "
				ORDER BY `Aktualisierung` DESC
				";
$result = mysqli_query_verbose($sql_query);
$tablebody = "\t\t\t<tbody>\n";
$i = 1;
while ($row = mysqli_fetch_object($result)) {
    $tablebody.= "\t\t\t<tr>\n";
    $tablebody.= "\t\t\t\t<td>\n";
    $tablebody.= "\t\t\t\t\t<form onsubmit='return confirmDelete()' method=POST id=delete_" . htmlentities($row->Datum) . ">\n";
    $tablebody.= "\t\t\t\t\t" . date('d.m.Y', strtotime($row->Datum)) . " <input class=no-print type=submit name=loeschen[" . htmlentities($vk) . "][" . htmlentities($row->Datum) . "] value='X' title='Diesen Datensatz löschen'>\n";
    $tablebody.= "\t\t\t\t\t</form>\n";
    $tablebody.= "\n\t\t\t\t</td>\n";
    $tablebody.= "\t\t\t\t<td>\n\t\t\t\t\t";
    $tablebody.= htmlentities($row->Grund);
    $tablebody.= "\n\t\t\t\t</td>\n";
    $tablebody.= "\t\t\t\t<td>\n\t\t\t\t\t";
    $tablebody.= htmlentities($row->Stunden);
    $tablebody.= "\n\t\t\t\t</td>\n";
    if ($i === 1) { //Get the last row. //TODO: Perhaps the server should calculate on it's own again afterwards.
        $tablebody.= "\t\t\t\t<td id=saldoAlt>\n\t\t\t\t\t";
        $saldo = $row->Saldo;
    } else {
        $tablebody.= "\t\t\t\t<td>\n\t\t\t\t\t";
    }
    $tablebody.= htmlentities($row->Saldo);
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
if (!$session->user_has_privilege('create_roster')) {
    echo build_warning_messages("", ["Die notwendige Berechtigung zum Erstellen von Arbeitszeitverlagerungen fehlt. Bitte wenden Sie sich an einen Administrator."]);
    die();
}

echo "<div id=main-area>\n";
echo build_warning_messages($Fehlermeldung, $Warnmeldung);

echo build_select_employee($employee_id, $List_of_employees);
echo "<a class=no-print href='stunden-out.php?employee_id=" . htmlentities($employee_id) . "'>[" . gettext("Read") . "]</a>\n";

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

//Eingabe. Der Saldo wird natürlich berechnet.
echo "\t\t\t<tr>\n";
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
echo "\t\t\t\t\t<input readonly type=text name=saldo id=saldoNeu value=" . htmlentities($saldo) . " form=insert_new_overtime>\n";
echo "\t\t\t\t</td>\n";
echo "\t\t\t<td><input class=no-print type=submit name=submitStunden value='Eintragen' form=insert_new_overtime></td>\n";
echo "\t\t\t</tr>\n";
//Ausgabe
echo "$tablebody";
echo "\t\t</table>\n";
echo "\t</div>\n";
echo "<form method=POST id=insert_new_overtime>\n"
 . "\t\t\t<input hidden name=employee_id value=" . htmlentities($employee_id) . " form=insert_new_overtime>\n"
 . "\t\t</form>\n";
require 'contact-form.php';
?>
</body>
</html>
