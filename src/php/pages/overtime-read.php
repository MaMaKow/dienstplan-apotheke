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
$VKmax = max(array_keys($workforce->List_of_employees)); //Wir suchen die höchste VK-Nummer.
$employee_id = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_employee_id']);
create_cookie('employee_id', $employee_id, 1);
$sql_query = "SELECT * FROM `Stunden` WHERE `VK` = :employee_id ORDER BY `Aktualisierung` DESC";
$result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id));
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

echo build_html_navigation_elements::build_select_employee($employee_id, $workforce->List_of_employees);
echo build_html_navigation_elements::build_button_open_edit_version('src/php/pages/overtime-edit.php', array('employee_id' => $employee_id));
echo "</div>\n";
echo "<table>\n";
//Überschrift
echo "<thead><tr>\n" .
 "<th>Datum</th>\n" .
 "<th>Grund</th>\n" .
 "<th>Stunden</th>\n" .
 "<th>Saldo</th>\n" .
 "</tr></thead>\n";
//Ausgabe
echo "$tablebody";
echo "</table>\n";
echo "</form>\n";
echo "</div>\n";
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'contact-form.php';
?>
</body>
</html>
