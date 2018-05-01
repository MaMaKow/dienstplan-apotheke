<?php
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
    $beginn = filter_input(INPUT_POST, 'beginn', FILTER_SANITIZE_STRING);
    $sql_query = "DELETE FROM `absence` WHERE `employee_id` = '$employee_id' AND `start` = '$beginn'";
    $result = mysqli_query_verbose($sql_query);
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
        $beginn_old = filter_input(INPUT_POST, 'beginn_old', FILTER_SANITIZE_STRING);
        $sql_query = "DELETE FROM `absence` WHERE `employee_id` = '$employee_id' AND `start` = '$beginn_old'";
        //echo "$sql_query<br>\n";
        $result = mysqli_query_verbose($sql_query);
    }
    $approval = "approved"; //TODO: There will be a time to handle cases of non-approved holidays!
    $sql_query = "INSERT INTO `absence` "
            . "(employee_id, start, end, days, reason, user, approval) "
            . "VALUES ('$employee_id', '$beginn', '$ende', '$tage', '$grund', '" . $_SESSION['user_name'] . "', '$approval')";
    //echo "$sql_query<br>\n";
    global $database_connection_mysqli; //TODO: There must be a much better way to solve this!
    if (!($result = mysqli_query($database_connection_mysqli, $sql_query))) {
        $error_string = mysqli_error($database_connection_mysqli);
        if (strpos($error_string, 'Duplicate') !== false) {
            global $Fehlermeldung;
            $Fehlermeldung[] = "<b>An diesem Datum existiert bereits ein Eintrag!</b>\n Die Daten wurden daher nicht in die Datenbank eingefügt.";
        } else {
            //Are there other errors, that we should handle?
            error_log("Error: $sql_query <br>" . mysqli_error($database_connection_mysqli));
            die("Error: $sql_query <br>" . mysqli_error($database_connection_mysqli));
        }
    }
}

$sql_query = 'SELECT * FROM `absence`
				WHERE `employee_id` = ' . $employee_id . '
				ORDER BY `start` ASC
				';
$result = mysqli_query_verbose($sql_query);
$number_of_rows = mysqli_num_rows($result);
$tablebody = '';
$i = 1;
while ($row = mysqli_fetch_object($result)) {
    $tablebody .= "<tr class='absence_row' data-approval='$row->approval' style='height: 1em;'>"
            . "<form accept-charset='utf-8' method=POST id='change_absence_entry_" . $row->start . "'>"
            . "\n";
    $tablebody .= "<td>\n<div id=beginn_out_" . $row->start . ">";
    $tablebody .= date('d.m.Y', strtotime($row->start)) . "</div>";
    $tablebody .= "<input id=beginn_in_" . $row->start . " style='display: none;' type=date name='beginn' value=" . date('Y-m-d', strtotime($row->start)) . " form='change_absence_entry_" . $row->start . "'> ";
    $tablebody .= "<input id=beginn_in_old_" . $row->start . " style='display: none;' type=date name='beginn_old' value=" . date('Y-m-d', strtotime($row->start)) . " form='change_absence_entry_" . $row->start . "'> ";
    $tablebody .= "\n</td>\n";
    $tablebody .= "<td>\n<div id=ende_out_" . $row->start . " form='change_absence_entry_" . $row->start . "'>";
    $tablebody .= date('d.m.Y', strtotime($row->end)) . "</div>";
    $tablebody .= "<input id=ende_in_" . $row->start . " style='display: none;' type=date name='ende' value=" . date('Y-m-d', strtotime($row->end)) . " form='change_absence_entry_" . $row->start . "'> ";
    $tablebody .= "\n</td>\n";
    if ($i == $number_of_rows) {
        $tablebody .= "<td id=letzterGrund><div id=grund_out_" . $row->start . ">\n";
    } else {
        $tablebody .= "<td>\n<div id=grund_out_" . $row->start . ">";
    }
    $tablebody .= "$row->reason" . "</div>";
    $tablebody .= "<input id=grund_in_" . $row->start . " style='display: none;' list='reasons' type=text name='grund' value='" . $row->reason . "' form='change_absence_entry_" . $row->start . "'> ";
    $tablebody .= "\n</td>\n";
    $tablebody .= "<td>\n";
    $tablebody .= "$row->days";
    $tablebody .= "\n</td>\n";
    $tablebody .= "<td>\n";
    $tablebody .= "$row->approval";
    $tablebody .= "\n</td>\n";
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
    $tablebody .= "\n</td>\n";
    $tablebody .= "</form>\n"
            . "</tr>\n";
    ++$i;
}
$sql_query = "SELECT `reason` FROM `absence`  GROUP BY `reason` HAVING COUNT(*) > 3 ORDER BY `reason` ASC";
$result = mysqli_query_verbose($sql_query);
$datalist = "<datalist id='reasons'>\n";
while ($row = mysqli_fetch_object($result)) {
    $datalist .= "<option value='$row->reason'>\n";
}
$datalist .= "</datalist>\n";

//Here beginns the output:
require 'head.php';
require 'src/php/pages/menu.php';
if (!$session->user_has_privilege('create_absence')) {
    echo build_warning_messages("", ["Die notwendige Berechtigung zum Erstellen von Abwesenheiten fehlt. Bitte wenden Sie sich an einen Administrator."]);
    die();
}

echo "<div id=main-area>\n";

echo build_warning_messages($Fehlermeldung, $Warnmeldung);

if (isset($Feiertagsmeldung)) {
    echo "<div class=error_container>\n";
    echo "<div class=warningmsg>\n<H3>Die folgenden Feiertage werden nicht auf die Abwesenheit angerechnet:</H3>";
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
//Überschrift
echo "<thead>\n"
 . "<tr>\n"
 . "<th>\n"
 . "Beginn\n"
 . "</th>\n"
 . "<th>\n"
 . "Ende\n"
 . "</th>\n"
 . "<th>\n"
 . "Grund\n"
 . "</th>\n"
 . "<th>\n"
 . "Tage\n"
 . "</th>\n"
 . "<th>\n"
 . ""
 . "</th>\n"
 . "</tr>\n"
 . "</thead>\n";
//Ausgabe
echo "<tbody>\n"
 . "$tablebody"
 . "</tbody>\n";
//echo "</form>\n";
//Eingabe. Der Saldo wird natürlich berechnet.
echo "<tfoot>"
 . "\n";
echo "";
echo "<tr class=no-print id=input_line_new>\n"
 . "<form accept-charset='utf-8' method=POST id='new_absence_entry'>\n";
echo "<td>\n"
 . "<input type=hidden name=employee_id value=$employee_id form='new_absence_entry'>\n";
echo "<input type=date class=datepicker onchange=updateTage() onblur=checkUpdateTage() id=beginn name=beginn value=" . date("Y-m-d") . " form='new_absence_entry'>";
echo "\n</td>\n";
echo "<td>\n";
echo "<input type=date class=datepicker onchange=updateTage() onblur=checkUpdateTage() id=ende name=ende value=" . date("Y-m-d") . " form='new_absence_entry'>";
echo "\n</td>\n";
echo "<td>\n";
echo "<input list='reasons' name=grund form='new_absence_entry'>";
echo "$datalist";
echo "\n</td>\n";
echo "<td id=tage title='Feiertage werden anschließend automatisch vom Server abgezogen.'>\n";
echo "1";
echo "\n</td>\n";
echo "<td>\n";
echo "";
echo "\n</td>\n";
echo "\n</tr>\n";
echo "\n<tr style='display: none; background-color: #BDE682;' id=warning_message_tr>\n";
echo "<td id=warning_message_td colspan='5'>\n";
echo "\n</td>\n";
echo "</form>\n"
 . "</tr>\n"
 . "</tfoot>";
echo "</table>\n";
echo "<input type=submit id=save_new class=no-print name=submitStunden value='Eintragen' form='new_absence_entry'>";
echo "</div>\n";
require 'contact-form.php';
?>
</body>
</html>
