<?php
require 'default.php';
$Fehlermeldung = array();
$Warnmeldung = array();
$workforce = new workforce();
$employee_id = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_employee_id']);
create_cookie('employee_id', $employee_id, 1);

//Deleting rows of data:
if (filter_has_var(INPUT_POST, 'loeschen')) {
    if (!$session->user_has_privilege('create_roster') and ! $session->user_has_privilege('create_overtime') and ! $session->user_has_privilege('administration')) {
        echo build_warning_messages("", ["Die notwendige Berechtigung zum Entfernen von Arbeitszeitverlagerungen fehlt. Bitte wenden Sie sich an einen Administrator."]);
        die();
    }

    $Remove = filter_input(INPUT_POST, 'loeschen', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    foreach ($Remove as $employee_id => $Data) {
        $employee_id = intval($employee_id);
        foreach ($Data as $date_sql => $X) {
            $sql_query = "DELETE FROM `Stunden`
			WHERE `VK` = '$employee_id' AND `Datum` = '$date_sql'";
            $result = mysqli_query_verbose($sql_query);
        }
    }
}

//Wir fügen neue Datensätze ein, wenn ALLE Daten übermittelt werden. (Leere Daten klappen vielleicht auch.)
if (filter_has_var(INPUT_POST, 'submitStunden') and filter_has_var(INPUT_POST, 'employee_id') and filter_has_var(INPUT_POST, 'datum') and filter_has_var(INPUT_POST, 'stunden') and filter_has_var(INPUT_POST, 'grund')) {
    if (!$session->user_has_privilege('create_roster') and ! $session->user_has_privilege('create_overtime') and ! $session->user_has_privilege('administration')) {
        echo build_warning_messages("", ["Die notwendige Berechtigung zum Erstellen von Arbeitszeitverlagerungen fehlt. Bitte wenden Sie sich an einen Administrator."]);
        die();
    }
    $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
    $overtime_hours_new = filter_input(INPUT_POST, 'stunden', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $sql_query = "SELECT * FROM `Stunden`
				WHERE `VK` = " . $employee_id . "
				ORDER BY `Aktualisierung` DESC
                                LIMIT 1
				";
    $result = mysqli_query_verbose($sql_query);
    $row = mysqli_fetch_object($result);
    $balance_old = $row->Saldo;
    $balance_new = $balance_old + $overtime_hours_new;

    $sql_query = "INSERT INTO `Stunden`
        (VK, Datum, Stunden, Saldo, Grund)
        VALUES ("
            . $employee_id
            . ", '"
            . filter_input(INPUT_POST, 'datum', FILTER_SANITIZE_STRING)
            . "', "
            . $overtime_hours_new
            . ", "
            . $balance_new
            . ", '"
            . filter_input(INPUT_POST, 'grund', FILTER_SANITIZE_STRING)
            . "')";
    if (!($result = mysqli_query($database_connection_mysqli, $sql_query))) {
        $error_string = mysqli_error($database_connection_mysqli);
        if (strpos($error_string, 'Duplicate') !== false) {
            $Fehlermeldung[] = "<b>An diesem Datum existiert bereits ein Eintrag!</b>\n Die Daten wurden daher nicht in die Datenbank eingefügt.";
        } else {
            //Are there other errors, that we should handle?
            error_log("Error: $sql_query <br>" . mysqli_error($database_connection_mysqli));
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
$tablebody = "<tbody>\n";
$i = 1;
while ($row = mysqli_fetch_object($result)) {
    $tablebody .= "<tr>\n";
    $tablebody .= "<td>\n";
    $tablebody .= "<form accept-charset='utf-8' onsubmit='return confirmDelete()' method=POST id=delete_" . htmlentities($row->Datum) . ">\n";
    $tablebody .= "" . date('d.m.Y', strtotime($row->Datum)) . " <input class=no-print type=submit name=loeschen[" . htmlentities($vk) . "][" . htmlentities($row->Datum) . "] value='X' title='Diesen Datensatz löschen'>\n";
    $tablebody .= "</form>\n";
    $tablebody .= "\n</td>\n";
    $tablebody .= "<td>\n";
    $tablebody .= htmlentities($row->Stunden);
    $tablebody .= "\n</td>\n";
    if ($i === 1) { //Get the last row. //TODO: Perhaps the server should calculate on it's own again afterwards.
        $tablebody .= "<td id=saldoAlt>\n";
        $saldo = $row->Saldo;
    } else {
        $tablebody .= "<td>\n";
    }
    $tablebody .= htmlentities($row->Saldo);
    $tablebody .= "\n</td>\n";
    $tablebody .= "<td>\n";
    $tablebody .= htmlentities($row->Grund);
    $tablebody .= "\n</td>\n";
    $tablebody .= "\n</tr>\n";
    $i++;
}
$tablebody .= "</tbody>\n";

if (empty($saldo)) {
    $saldo = 0;
}


//Start of output:
require 'head.php';
require 'src/php/pages/menu.php';
if (!$session->user_has_privilege('create_roster') and ! $session->user_has_privilege('create_overtime') and ! $session->user_has_privilege('administration')) {
    echo build_warning_messages("", ["Die notwendige Berechtigung zum Erstellen von Arbeitszeitverlagerungen fehlt. Bitte wenden Sie sich an einen Administrator."]);
    die();
}

echo "<div id=main-area>\n";
echo build_warning_messages($Fehlermeldung, $Warnmeldung);

echo build_html_navigation_elements::build_select_employee($employee_id, $workforce->List_of_employees);
echo "<a class=no-print href='stunden-out.php?employee_id=" . htmlentities($employee_id) . "'>[" . gettext("Read") . "]</a>\n";

echo "<table>\n";
//Heading
echo "<thead>\n";
echo "<tr>\n"
 . "<th>\n"
 . "Datum\n"
 . "</th>\n"
 . "<th>\n"
 . "Stunden\n"
 . "</th>\n"
 . "<th>\n"
 . "Saldo\n"
 . "</th>\n"
 . "<th>\n"
 . "Grund\n"
 . "</th>\n"
 . "</tr>\n"
 . "</thead>\n";

//Eingabe. Der Saldo wird natürlich berechnet.
echo "<tr>\n";
echo "<td>\n";
echo "<input type=date id=date_chooser_input class='datepicker' value=" . date('Y-m-d') . " name=datum form=insert_new_overtime>\n";
echo "</td>\n";
echo "<td>\n";
echo "<input type=text onchange=updatesaldo() id=stunden name=stunden form=insert_new_overtime>\n";
echo "</td>\n";
echo "<td>\n";
echo "<p id=saldoNeu>" . htmlentities($saldo) . " </p>\n";
echo "</td>\n";
echo "<td>\n";
echo "<input type=text id=grund name=grund form=insert_new_overtime>\n";
echo "</td>\n";
echo "<td>";
echo "<input class=no-print type=submit name=submitStunden value='Eintragen' form=insert_new_overtime></td>\n";
echo "</tr>\n";
//Ausgabe
echo "$tablebody";
echo "</table>\n";
echo "</div>\n";
echo "<form accept-charset='utf-8' method=POST id=insert_new_overtime>\n"
 . "<input hidden name=employee_id value=" . htmlentities($employee_id) . " form=insert_new_overtime>\n"
 . "</form>\n";
require 'contact-form.php';
?>
</body>
</html>
