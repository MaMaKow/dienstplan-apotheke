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
$workforce = new workforce();
$year = user_input::get_variable_from_any_input('year', FILTER_SANITIZE_NUMBER_INT, date('Y'));
create_cookie('year', $year, 1);
$employee_id = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_employee_id']);
create_cookie('employee_id', $employee_id, 30);
absence::handle_user_input();


/*
 * TODO: Find overlapping absences.
 */

$sql_query = 'SELECT * FROM `absence` WHERE `employee_id` = :employee_id and (Year(`start`) = :year or Year(`end`) =:year2) ORDER BY `start` DESC';
$result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id, 'year' => $year, 'year2' => $year));
$tablebody = '';
while ($row = $result->fetch(PDO::FETCH_OBJ)) {
    $html_form = "change_absence_entry_" . $row->start;
    $tablebody .= "<tr class='absence_row' data-approval='$row->approval' style='height: 1em;'>"
            . "<form accept-charset='utf-8' method=POST id='$html_form'>"
            . "\n";
    /*
     * start
     */
    $tablebody .= "<td><div id=start_out_$row->start>";
    $tablebody .= date('d.m.Y', strtotime($row->start)) . "</div>";
    $tablebody .= "<input id=start_in_$row->start style='display: none;' type=date name='beginn' value=" . date('Y-m-d', strtotime($row->start)) . " form='$html_form'> ";
    $tablebody .= "<input style='display: none;' type=date name='start_old' value=" . date('Y-m-d', strtotime($row->start)) . " form='$html_form'> ";
    $tablebody .= "</td>\n";
    /*
     * end
     */
    $tablebody .= "<td><div id=end_out_$row->start form='$html_form'>";
    $tablebody .= date('d.m.Y', strtotime($row->end)) . "</div>";
    $tablebody .= "<input id=end_in_$row->start style='display: none;' type=date name='ende' value=" . date('Y-m-d', strtotime($row->end)) . " form='$html_form'> ";
    $tablebody .= "</td>\n";
    /*
     * reason
     */
    $tablebody .= "<td><div id=reason_out_$row->start>" . pdr_gettext($row->reason) . "</div>";
    $html_id = "reason_in_$row->start";
    $tablebody .= absence::build_reason_input_select($row->reason, $html_id, $html_form);
    $tablebody .= "</td>\n";
    /*
     * comment
     */
    $tablebody .= "<td><div id=comment_out_$row->start>$row->comment</div>";
    $html_id = "comment_in_$row->start";
    $tablebody .= "<input id=comment_in_$row->start style='display: none;' type=text name='comment' value='$row->comment' form='$html_form'> ";
    $tablebody .= "</td>\n";
    /*
     * days
     */
    $tablebody .= "<td>$row->days</td>\n";
    $tablebody .= "<td><span id=absence_out_$row->start>" . pdr_gettext($row->approval) . "</span>";
    $html_id = "absence_in_$row->start";
    $tablebody .= absence::build_approval_input_select($row->approval, $html_id, $html_form);
    $tablebody .= "</td>\n";
    $tablebody .= "<td style='font-size: 1em; height: 1em'>\n"
            . "<input hidden name='employee_id' value='$employee_id' form='$html_form'>\n"
            . "<button type=submit id=delete_$row->start class='button_small delete_button no_print' title='Diese Zeile löschen' name=command value=delete onclick='return confirmDelete()'>\n"
            . "<img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/delete.png' alt='Diese Zeile löschen'>\n"
            . "</button>\n"
            . "<button type=button id=cancel_$row->start class='button_small no_print' title='Bearbeitung abbrechen' onclick='return cancelEdit(\"$row->start\")' style='display: none; border-radius: 32px; background-color: transparent;'>\n"
            . "<img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/delete.png' alt='Bearbeitung abbrechen'>\n"
            . "</button>\n"
            . "<button type=button id=edit_$row->start class='button_small edit_button no_print' title='Diese Zeile bearbeiten' name=command onclick='showEdit(\"$row->start\")'>\n"
            . "<img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/pencil-pictogram.svg' alt='Diese Zeile bearbeiten'>\n"
            . "</button>\n"
            . "<button type='submit' id='save_$row->start' class='button_small no_print' title='Veränderungen dieser Zeile speichern' name='command' value='replace' style='display: none; border-radius: 32px;'>\n"
            . "<img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/save.png' alt='Veränderungen dieser Zeile speichern'>\n"
            . "</button>\n"
            . "";
    $tablebody .= "</td>\n";
    $tablebody .= "</form>\n"
            . "</tr>\n";
}

//Here beginns the output:
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
$session->exit_on_missing_privilege('create_absence');

echo "<div id=main-area>\n";

echo user_dialog::build_messages();
echo absence::build_html_select_year($year);
echo build_html_navigation_elements::build_select_employee($employee_id, $workforce->List_of_employees);

echo build_html_navigation_elements::build_button_open_readonly_version('src/php/pages/absence-read.php', array('employee_id' => $employee_id));
echo "<table id=absence_table>\n";
/*
 * Head
 */
echo "<thead>\n";
echo "<tr><th>" . gettext('Start') . "</th><th>" . gettext('End') . "</th><th>" . gettext('Reason') . "</th><th>" . gettext('Comment') . "</th><th>" . gettext('Days') . "</th><th>" . gettext('Approval') . "</th></tr>\n";
/*
 * Input with calculation of the saldo via javascript.
 */
echo "<tr class=no_print id=input_line_new>\n"
 . "<form accept-charset='utf-8' method=POST id='new_absence_entry'>\n";
echo "<td>\n"
 . "<input type=hidden name=employee_id value=$employee_id form='new_absence_entry'>\n";
echo "<input type=date class=datepicker onchange=updateTage() onblur=checkUpdateTage() id=beginn name=beginn value=" . date("Y-m-d") . " form='new_absence_entry'>";
echo "</td>\n";
echo "<td>\n";
echo "<input type=date class=datepicker onchange=updateTage() onblur=checkUpdateTage() id=ende name=ende value=" . date("Y-m-d") . " form='new_absence_entry'>";
echo "</td>\n";
echo "<td>" . absence::build_reason_input_select(NULL, NULL, 'new_absence_entry') . "</td>\n";
echo "<td><input type='text' name='comment' form='new_absence_entry'></td>\n";
echo "<td id=tage title='Feiertage werden anschließend automatisch vom Server abgezogen.'>1</td>\n";
echo "<td>" . absence::build_approval_input_select('not_yet_approved', NULL, 'new_absence_entry') . "</td>\n";
echo "<td>\n";
echo "<button type=submit id=save_new class=no_print name=command value='insert_new' form='new_absence_entry'>" . gettext('Save') . "</button>";
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
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'contact-form.php';
?>
</body>
</html>
