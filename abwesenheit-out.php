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
$workforce = new workforce();
$VKmax = max(array_keys($workforce->List_of_employees)); //Wir suchen die höchste VK-Nummer.
$employee_id = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_employee_id']);
create_cookie("employee_id", $employee_id, 1);
$vk = $employee_id;
$sql_query = "SELECT * FROM `absence` WHERE `employee_id` = :employee_id ORDER BY `start` DESC";
$result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id));
$tablebody = "";
while ($row = $result->fetch(PDO::FETCH_OBJ)) {
    $tablebody .= "<tr>";
    $tablebody .= "<td>" . date('d.m.Y', strtotime($row->start)) . "</td>";
    $tablebody .= "<td>" . date('d.m.Y', strtotime($row->end)) . "</td>";
    $tablebody .= "<td>" . "$row->reason" . "</td>";
    $tablebody .= "<td>" . "$row->days" . "</td>";
    $tablebody .= "</tr>\n";
}
require 'head.php';
require 'src/php/pages/menu.php';
//Hier beginnt die Ausgabe
echo "<div id=main-area>\n";

echo build_html_navigation_elements::build_select_employee($employee_id, $workforce->List_of_employees);

echo "<a class=no-print href='abwesenheit-in.php?employee_id=$employee_id'><br>[" . gettext("Edit") . "]</a>";
echo "<table>\n";
//Überschrift
echo "<tr>\n"
 . "<th>" . gettext("Start") . "</th>"
 . "<th>" . gettext("End") . "</th>"
 . "<th>" . gettext("Reason") . "</th>"
 . "<th>" . gettext("Days") . "</th>"
 . "</tr>\n";
//Ausgabe
echo "$tablebody";
echo "</table>\n";
echo "</form>";
echo "</div>\n";
require 'contact-form.php';
?>

</body>
</html>
