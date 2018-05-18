<?php
require 'default.php';
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
    $tablebody .= "<a href='tag-out.php?datum=" . date("Y-m-d", strtotime($row->Datum)) . "'>" . date("d.m.Y", strtotime($row->Datum)) . "</a>";
    $tablebody .= "</td>\n";
    $tablebody .= "<td>" . "$row->Grund" . "</td>\n";
    $tablebody .= "<td>" . "$row->Stunden" . "</td>\n";
    $tablebody .= "<td>" . "$row->Saldo" . "</td>\n";
    $tablebody .= "</tr>\n";
}
$tablebody .= "</tbody>\n";

//Hier beginnt die Ausgabe
require 'head.php';
require 'src/php/pages/menu.php';
echo "<div id=main-area>\n";

echo build_html_navigation_elements::build_select_employee($employee_id, $workforce->List_of_employees);
echo "<div class=no-print><br><a href=stunden-in.php?employee_id=$employee_id>[" . gettext("Edit") . "]</a><br><br></div>\n";
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
require 'contact-form.php';
?>
</body>
</html>
