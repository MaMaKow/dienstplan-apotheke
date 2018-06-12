<?php
/*
 * Copyright (C) 2017 Mandelkow
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
require '../../../default.php';
$Fehlermeldung = array();
$Warnmeldung = array();
$workforce = new workforce();
$employee_id = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_employee_id']);
create_cookie('employee_id', $employee_id, 1);

//Deleting rows of data:
if (filter_has_var(INPUT_POST, 'loeschen')) {
    $session->exit_on_missing_privilege('create_overtime');

    $Remove = filter_input(INPUT_POST, 'loeschen', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    foreach ($Remove as $employee_id => $Data) {
        $employee_id = intval($employee_id);
        foreach ($Data as $date_sql => $X) {
            $sql_query = "DELETE FROM `Stunden`
			WHERE `VK` = :employee_id AND `Datum` = :date";
            $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id, 'date' => $date_sql));
        }
    }
}

//Wir fügen neue Datensätze ein, wenn ALLE Daten übermittelt werden. (Leere Daten klappen vielleicht auch.)
if (filter_has_var(INPUT_POST, 'submitStunden') and filter_has_var(INPUT_POST, 'employee_id') and filter_has_var(INPUT_POST, 'datum') and filter_has_var(INPUT_POST, 'stunden') and filter_has_var(INPUT_POST, 'grund')) {
    $session->exit_on_missing_privilege('create_overtime');
    $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
    $overtime_hours_new = filter_input(INPUT_POST, 'stunden', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $sql_query = "SELECT * FROM `Stunden` WHERE `VK` = :employee_id ORDER BY `Aktualisierung` DESC LIMIT 1";
    $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id));
    $row = $result->fetch(PDO::FETCH_OBJ);
    $balance_old = $row->Saldo;
    $balance_new = $balance_old + $overtime_hours_new;
    /*
     * TODO: The following part has to be rewritten to database_wrapper::instance()->run
     */
    $sql_query = "INSERT INTO `Stunden` (VK, Datum, Stunden, Saldo, Grund)
        VALUES (:employee_id, :date, :overtime_hours, :balance, :reason)";
    try {
        $result = database_wrapper::instance()->run($sql_query, array(
            'employee_id' => $employee_id,
            'date' => filter_input(INPUT_POST, 'datum', FILTER_SANITIZE_STRING),
            'overtime_hours' => $overtime_hours_new,
            'balance' => $balance_new,
            'reason' => filter_input(INPUT_POST, 'grund', FILTER_SANITIZE_STRING)
        ));
    } catch (Exception $exception) {
        $error_string = $exception->getMessage();
        if (database_wrapper::ERROR_MESSAGE_DUPLICATE_ENTRY_FOR_KEY === $exception->getMessage()) {
            global $Fehlermeldung;
            $Fehlermeldung[] = "<b>An diesem Datum existiert bereits ein Eintrag!</b>\n Die Daten wurden daher nicht in die Datenbank eingefügt.";
        } else {
            print_debug_variable($exception);
            die("<p>There was an error while querying the database. Please see the error log for more details!</p>");
        }
    }
}
$sql_query = "SELECT * FROM `Stunden` WHERE `VK` = :employee_id ORDER BY `Aktualisierung` DESC";
$result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id));
$tablebody = "<tbody>\n";
$i = 1;
while ($row = $result->fetch(PDO::FETCH_OBJ)) {
    $tablebody .= "<tr>\n";
    $tablebody .= "<td>\n";
    $tablebody .= "<form accept-charset='utf-8' onsubmit='return confirmDelete()' method=POST id=delete_" . htmlentities($row->Datum) . ">\n";
    $tablebody .= "" . date('d.m.Y', strtotime($row->Datum)) . " <input class=no-print type=submit name=loeschen[" . htmlentities($employee_id) . "][" . htmlentities($row->Datum) . "] value='X' title='Diesen Datensatz löschen'>\n";
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
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
$session->exit_on_missing_privilege('create_overtime');

echo "<div id=main-area>\n";
echo build_warning_messages($Fehlermeldung, $Warnmeldung);
echo user_dialog::build_messages();

echo build_html_navigation_elements::build_select_employee($employee_id, $workforce->List_of_employees);
echo build_html_navigation_elements::build_button_open_readonly_version('src/php/pages/overtime-read.php', array('employee_id' => $employee_id));

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
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'contact-form.php';
?>
</body>
</html>
