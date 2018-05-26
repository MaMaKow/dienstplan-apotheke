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


$branch_id = user_input::get_variable_from_any_input('mandant', FILTER_SANITIZE_NUMBER_INT, min(array_keys($List_of_branch_objects)));
$date_sql = user_input::get_variable_from_any_input('datum', FILTER_SANITIZE_NUMBER_INT, date('Y-m-d'));
$year = date('Y', strtotime($date_sql));
create_cookie('branch_id', $branch_id, 30);
create_cookie('datum', $date_sql, 0.5);
create_cookie('year', $year, 0.5);
$workforce = new workforce();

$sql_query = "SELECT * FROM Notdienst WHERE YEAR(Datum) = :year AND Mandant = :branch_id";
$result = database_wrapper::instance()->run($sql_query, array('year' => $year, 'branch_id' => $branch_id));
while ($row = $result->fetch(PDO::FETCH_OBJ)) {
    $Notdienste['VK'][] = $row->VK;
    $Notdienste['Datum'][] = $row->Datum;
    $Notdienste['Mandant'][] = $row->Mandant;
}
require 'head.php';
echo build_html_navigation_elements::build_select_branch($branch_id, $date_sql)
?>
<table class="table_with_border">
    <tr><td>Datum</td><td>Name</td><td>Ersatz</td></tr>
    <?php
    foreach ($Notdienste['Datum'] as $key => $date_sql) {
        echo "\n<tr><td>" . date('D d.m.Y', strtotime($date_sql)) . '</td>';
        echo '<td>';
        echo (isset($workforce->List_of_employees[$Notdienste['VK'][$key]])) ? $workforce->List_of_employees[$Notdienste['VK'][$key]]->last_name : "?";
        echo '</td>';
        echo "<td style=width:40%></td></tr>";
    }
    ?>

</table>
</body>
</html>
