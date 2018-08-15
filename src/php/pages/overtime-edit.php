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
/*
 * TODO: Edit option for existing entries
 */
$workforce = new workforce();
$year = user_input::get_variable_from_any_input('year', FILTER_SANITIZE_NUMBER_INT, date('Y'));
create_cookie('year', $year, 1);
$employee_id = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_employee_id']);
create_cookie('employee_id', $employee_id, 1);

overtime::handle_user_input($session, $employee_id);
list($balance, $date_old) = overtime::get_current_balance($employee_id);
/*
 * Get the overtime data for the chosen year:
 */
$sql_query = "SELECT * FROM `Stunden` WHERE `VK` = :employee_id and Year(`Datum`) = :year ORDER BY `Datum` DESC";
$result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id, 'year' => $year));
$tablebody = "<tbody>\n";
$i = 1;
while ($row = $result->fetch(PDO::FETCH_OBJ)) {
    $tablebody .= "<tr>\n";
    $tablebody .= "<td>\n";
    $tablebody .= "<form accept-charset='utf-8' onsubmit='return confirmDelete()' method=POST id=delete_" . htmlentities($row->Datum) . ">\n";
    $tablebody .= "" . date('d.m.Y', strtotime($row->Datum)) . " <input class=no_print type=submit name=loeschen[" . htmlentities($employee_id) . "][" . htmlentities($row->Datum) . "] value='X' title='Diesen Datensatz löschen'>\n";
    $tablebody .= "</form>\n";
    $tablebody .= "\n</td>\n";
    $tablebody .= "<td>\n";
    $tablebody .= htmlentities($row->Stunden);
    $tablebody .= "\n</td>\n";
    if ($i === 1) { //Get the last row.
        $tablebody .= "<td id=balance_old>\n";
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



//Start of output:
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
$session->exit_on_missing_privilege('create_overtime');

echo "<div id=main-area>\n";
echo user_dialog::build_messages();

echo absence::build_html_select_year($year);
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

/*
 * Input fields.
 * The balance will be visibly calculated by JavaScript.
 * But the calculated value is not used as an input.
 */
echo "<tr class='no_print'>\n";
echo "<td>\n";
echo "<input type=date id=date_chooser_input class='datepicker' value=" . date('Y-m-d') . " name=datum form=insert_new_overtime>\n";
echo "</td>\n";
echo "<td>\n";
echo "<input type=text onchange=update_overtime_balance() id=stunden name=stunden form=insert_new_overtime>\n";
echo "</td>\n";
echo "<td>\n";
echo "<p id=balance_new>" . htmlentities($balance) . " </p>\n";
echo "</td>\n";
echo "<td>\n";
echo "<input type=text id=grund name=grund form=insert_new_overtime>\n";
echo "</td>\n";
echo "<td>";
echo "<input class=no_print type=submit name=submitStunden value='Eintragen' form=insert_new_overtime></td>\n";
echo "</tr>\n";
//Ausgabe
echo "$tablebody";
echo "</table>\n";
echo "</div>\n";
echo "<form accept-charset='utf-8' method=POST id=insert_new_overtime onsubmit='return overtime_input_validation();'>\n"
 . "<input hidden name=employee_id value=" . htmlentities($employee_id) . " form=insert_new_overtime>\n"
 . "<input hidden id='user_sequence_warning' name=user_has_been_warned_about_date_sequence value='0' form=insert_new_overtime>\n"
 . "<input hidden id='date_of_last_entry' name='date_of_last_entry' value='$date_old' form=insert_new_overtime>\n"
 . "</form>\n";
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php';
?>
</body>
</html>
