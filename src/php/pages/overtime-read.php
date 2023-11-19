<?php
/*
 * Copyright (C) 2017 Martin Mandelkow
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
$employee_key = user_input::get_variable_from_any_input('employee_key', FILTER_SANITIZE_NUMBER_INT, $workforce->get_default_employee_key());
create_cookie('employee_key', $employee_key, 1);
$sql_query = "SELECT * FROM `Stunden` WHERE `employee_key` = :employee_key ORDER BY `Datum` DESC";
$result = database_wrapper::instance()->run($sql_query, array('employee_key' => $employee_key));
$tablebody = "<tbody>\n";
while ($row = $result->fetch(PDO::FETCH_OBJ)) {
    $tablebody .= "<tr>\n";
    $tablebody .= "<td>";
    $tablebody .= "<a href='" . PDR_HTTP_SERVER_APPLICATION_PATH . "src/php/pages/roster-day-read.php?datum=" . date("Y-m-d", strtotime($row->Datum)) . "'>" . date("d.m.Y", strtotime($row->Datum)) . "</a>";
    $tablebody .= "</td>\n";
    $tablebody .= "<td>" . "$row->Grund" . "</td>\n";
    $tablebody .= "<td>" . "$row->Stunden" . "</td>\n";
    $tablebody .= "<td>" . "$row->Saldo" . "</td>\n";
    $tablebody .= "</tr>\n";
}
$tablebody .= "</tbody>\n";

//Hier beginnt die Ausgabe
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
echo "<div id=main-area>\n";

echo build_html_navigation_elements::build_select_employee($employee_key, $workforce->List_of_employees);
echo build_html_navigation_elements::build_button_open_edit_version('src/php/pages/overtime-edit.php', array('employee_key' => $employee_key));
//echo "</div>\n";
echo "<table>\n";
/*
 * table head
 */
echo "<thead><tr>\n" .
 "<th>" . gettext('Date') . "</th>\n" .
 "<th>" . gettext('Reason') . "</th>\n" .
 "<th>" . gettext('Hours') . "</th>\n" .
 "<th>" . gettext('Balance') . "</th>\n" .
 "</tr></thead>\n";
/*
 * table body
 */
echo "$tablebody";
echo "</table>\n";
echo "</div><!-- id=main-area -->\n";
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php';
?>
</body>
</html>
