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
require 'default.php';
$Fehlermeldung = array();
$Warnmeldung = array();
$workforce = new workforce();
$employee_id = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_employee_id']);
create_cookie('employee_id', $employee_id, 30);

//Wir löschen Datensätze, wenn dies befohlen wird.
if ($command = filter_input(INPUT_POST, 'command', FILTER_SANITIZE_STRING) and 'delete' === $command) {
    delete_absence_data();
}

function delete_absence_data() {
    $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
    $start = filter_input(INPUT_POST, 'beginn', FILTER_SANITIZE_STRING);
    $sql_query = "DELETE FROM `absence` WHERE `employee_id` = :employee_id AND `start` = :start";
    $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id, 'start' => $start));
    return $result;
}

//We create new entries or edit old entries. (Empty values are not accepted.)
if ((filter_has_var(INPUT_POST, 'submitStunden') or ( filter_has_var(INPUT_POST, 'command') and 'replace' === filter_input(INPUT_POST, 'command', FILTER_SANITIZE_STRING)))
        and $beginn = filter_input(INPUT_POST, 'beginn', FILTER_SANITIZE_STRING)
        and $ende = filter_input(INPUT_POST, 'ende', FILTER_SANITIZE_STRING)
        and $grund = filter_input(INPUT_POST, 'grund', FILTER_SANITIZE_STRING)
) {
    write_absence_data_to_database($beginn, $ende, $grund);
}

function write_absence_data_to_database($beginn, $ende, $grund) {
    $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
    if ($employee_id === FALSE) {
        return FALSE;
    }
    $tage = 0;
    for ($date_unix = strtotime($beginn); $date_unix <= strtotime($ende); $date_unix = strtotime('+1 day', $date_unix)) {
        $date_string = strftime('%x', $date_unix);
        if (date('w', $date_unix) < 6 and date('w', $date_unix) > 0) {
            $tage++;
        }

        //Now the holidays which are not on a weekend are substracted.
        $holiday = holidays::is_holiday($date_unix);

        if (FALSE !== $holiday and date('w', $date_unix) < 6 and date('w', $date_unix) > 0) {
            global $Feiertagsmeldung; //TODO: This might better be handled via exceptions.
            $Feiertagsmeldung[] = htmlentities("$holiday ($date_string)\n");
            $tage--;
        }
    }
    //var_export($tage);
    if ('replace' === filter_input(INPUT_POST, 'command', FILTER_SANITIZE_STRING)) {
        $start_old = filter_input(INPUT_POST, 'start_old', FILTER_SANITIZE_STRING);
        $sql_query = "DELETE FROM `absence` WHERE `employee_id` = :employee_id AND `start` = :start";
        //echo "$sql_query<br>\n";
        database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id, 'start' => $start_old));
    }
    $approval = "approved"; //TODO: There will be a time to handle cases of non-approved holidays!
    $sql_query = "INSERT INTO `absence` "
            . "(employee_id, start, end, days, reason, user, approval) "
            . "VALUES (:employee_id, :start, :end, :days, :reason, :user, :approval)";
    //TODO: There must be a much better way to solve this!
    try {
        $result = database_wrapper::instance()->run($sql_query, array(
            'employee_id' => $employee_id,
            'start' => $beginn,
            'end' => $ende,
            'days' => $tage,
            'reason' => $grund,
            'user' => $_SESSION['user_name'],
            'approval' => $grund
        ));
    } catch (Exception $exception) {
        $error_string = $exception->getMessage();
        if ('Duplicate entry for key' === $exception->getMessage()) {
            global $Fehlermeldung;
            $Fehlermeldung[] = "<b>An diesem Datum existiert bereits ein Eintrag!</b>\n Die Daten wurden daher nicht in die Datenbank eingefügt.";
        } else {
            print_debug_variable($exception);
            die("<p>There was an error while querying the database. Please see the error log for more details!</p>");
        }
    }
}

$sql_query = 'SELECT * FROM `absence` WHERE `employee_id` = :employee_id ORDER BY `start` DESC
				';
$result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id));
$tablebody = '';
$i = 1;
while ($row = $result->fetch(PDO::FETCH_OBJ)) {
    $tablebody .= "<tr class='absence_row' data-approval='$row->approval' style='height: 1em;'>"
            . "<form accept-charset='utf-8' method=POST id='change_absence_entry_" . $row->start . "'>"
            . "\n";
    $tablebody .= "<td><div id=beginn_out_" . $row->start . ">";
    $tablebody .= date('d.m.Y', strtotime($row->start)) . "</div>";
    $tablebody .= "<input id=beginn_in_" . $row->start . " style='display: none;' type=date name='beginn' value=" . date('Y-m-d', strtotime($row->start)) . " form='change_absence_entry_" . $row->start . "'> ";
    $tablebody .= "<input id=beginn_in_old_" . $row->start . " style='display: none;' type=date name='start_old' value=" . date('Y-m-d', strtotime($row->start)) . " form='change_absence_entry_" . $row->start . "'> ";
    $tablebody .= "</td>\n";
    $tablebody .= "<td><div id=ende_out_" . $row->start . " form='change_absence_entry_" . $row->start . "'>";
    $tablebody .= date('d.m.Y', strtotime($row->end)) . "</div>";
    $tablebody .= "<input id=ende_in_" . $row->start . " style='display: none;' type=date name='ende' value=" . date('Y-m-d', strtotime($row->end)) . " form='change_absence_entry_" . $row->start . "'> ";
    $tablebody .= "</td>\n";
    if ($i == 1) {
        $tablebody .= "<td id=letzterGrund><div id=grund_out_" . $row->start . ">\n";
    } else {
        $tablebody .= "<td><div id=grund_out_" . $row->start . ">";
    }
    $tablebody .= "$row->reason" . "</div>";
    $tablebody .= "<input id=grund_in_" . $row->start . " style='display: none;' list='reasons' type=text name='grund' value='" . $row->reason . "' form='change_absence_entry_" . $row->start . "'> ";
    $tablebody .= "</td>\n";
    $tablebody .= "<td>\n";
    $tablebody .= "$row->days";
    $tablebody .= "</td>\n";
    $tablebody .= "<td>\n";
    $tablebody .= "$row->approval";
    $tablebody .= "</td>\n";
    $tablebody .= "<td style='font-size: 1em; height: 1em'>\n"
            . "<input hidden name='employee_id' value='$employee_id' form='change_absence_entry_" . $row->start . "'>\n"
            . "<button type=submit id=delete_" . $row->start . " class='button_small delete_button' title='Diese Zeile löschen' name=command value=delete onclick='return confirmDelete()'>\n"
            . "<img src='img/delete.png' alt='Diese Zeile löschen'>\n"
            . "</button>\n"
            . "<button type=button id=cancel_" . $row->start . " class='button_small' title='Bearbeitung abbrechen' onclick='return cancelEdit(\"" . $row->start . "\")' style='display: none; border-radius: 32px; background-color: transparent;'>\n"
            . "<img src='img/delete.png' alt='Bearbeitung abbrechen'>\n"
            . "</button>\n"
            . "<button type=button id=edit_" . $row->start . " class='button_small edit_button' title='Diese Zeile bearbeiten' name=command onclick='showEdit(\"" . $row->start . "\")'>\n"
            . "<img src='img/pencil-pictogram.svg' alt='Diese Zeile bearbeiten'>\n"
            . "</button>\n"
            . "<button type='submit' id='save_" . $row->start . "' class='button_small' title='Veränderungen dieser Zeile speichern' name='command' value='replace' style='display: none; border-radius: 32px;'>\n"
            . "<img src='img/save.png' alt='Veränderungen dieser Zeile speichern'>\n"
            . "</button>\n"
            . "";
    $tablebody .= "</td>\n";
    $tablebody .= "</form>\n"
            . "</tr>\n";
    ++$i;
}
$sql_query = "SELECT `reason` FROM `absence`  GROUP BY `reason` HAVING COUNT(*) > 3 ORDER BY `reason` ASC";
$result = database_wrapper::instance()->run($sql_query);
$datalist = "<datalist id='reasons'>\n";
while ($row = $result->fetch(PDO::FETCH_OBJ)) {
    $datalist .= "<option value='$row->reason'>\n";
}
$datalist .= "</datalist>\n";

//Here beginns the output:
require 'head.php';
require 'src/php/pages/menu.php';
$session->exit_on_missing_privilege('create_absence');

echo "<div id=main-area>\n";

echo build_warning_messages($Fehlermeldung, $Warnmeldung);

if (isset($Feiertagsmeldung)) {
    echo "<div class=error_container>\n";
    echo "<div class=warningmsg><H3>Die folgenden Feiertage werden nicht auf die Abwesenheit angerechnet:</H3>";
    foreach ($Feiertagsmeldung as $holiday) {
        echo "<p>" . $holiday . "</p>\n";
    }
    echo "</div>\n";
    echo "</div>\n";
}
echo build_html_navigation_elements::build_select_employee($employee_id, $workforce->List_of_employees);

echo "<a class=no-print href='abwesenheit-out.php?employee_id=$employee_id'>[" . gettext("Read") . "]</a>";
echo "\n";
echo "<table id=absence_table>\n";
/*
 * Head
 */
echo "<thead>\n";
echo "<tr><th>Beginn</th><th>Ende</th><th>Grund</th><th>Tage</th><th></th></tr>\n";
/*
 * Input with calculation of the saldo via javascript.
 */
echo "<tr class=no-print id=input_line_new>\n"
 . "<form accept-charset='utf-8' method=POST id='new_absence_entry'>\n";
echo "<td>\n"
 . "<input type=hidden name=employee_id value=$employee_id form='new_absence_entry'>\n";
echo "<input type=date class=datepicker onchange=updateTage() onblur=checkUpdateTage() id=beginn name=beginn value=" . date("Y-m-d") . " form='new_absence_entry'>";
echo "</td>\n";
echo "<td>\n";
echo "<input type=date class=datepicker onchange=updateTage() onblur=checkUpdateTage() id=ende name=ende value=" . date("Y-m-d") . " form='new_absence_entry'>";
echo "</td>\n";
echo "<td>\n";
echo "<input list='reasons' name=grund form='new_absence_entry'>";
echo "$datalist";
echo "</td>\n";
echo "<td id=tage title='Feiertage werden anschließend automatisch vom Server abgezogen.'>\n";
echo "1";
echo "</td>\n";
echo "<td>\n";
echo "</td>\n";
echo "<td>\n";
echo "<input type=submit id=save_new class=no-print name=submitStunden value='Eintragen' form='new_absence_entry'>";
echo "</td>\n";
echo "</tr>\n";
echo "<tr style='display: none; background-color: #BDE682;' id=warning_message_tr>\n";
echo "<td id=warning_message_td colspan='5'>\n";
echo "Foo!";
echo "</td>\n";
echo "</form>\n";
echo "</tr>\n";
echo "</thead>\n";
//Ausgabe
echo "<tbody>\n"
 . "$tablebody"
 . "</tbody>\n";
echo "</table>\n";
echo "</div>\n";
require 'contact-form.php';
?>
</body>
</html>
