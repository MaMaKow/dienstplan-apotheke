<?php
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
