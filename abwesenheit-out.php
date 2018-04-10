<?php
require 'default.php';
//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';
$VKmax = max(array_keys($List_of_employees)); //Wir suchen die höchste VK-Nummer.
$employee_id = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_employee_id']);
create_cookie("employee_id", $employee_id, 1);
$vk = $employee_id;
$sql_query = "SELECT * FROM `absence`
		WHERE `employee_id` = " . $vk . "
		ORDER BY `start` ASC
		";
$result = mysqli_query_verbose($sql_query);
$number_of_rows = mysqli_num_rows($result);
$tablebody = "";
$i = 1;
while ($row = mysqli_fetch_object($result)) {
    $tablebody .= "<tr>\n";
    $tablebody .= "<td>\n";
    $tablebody .= date('d.m.Y', strtotime($row->start));
    $tablebody .= "\n</td>\n";
    $tablebody .= "<td>\n";
    $tablebody .= date('d.m.Y', strtotime($row->end));
    $tablebody .= "\n</td>\n";
    if ($i == $number_of_rows) {
        //TODO: This whole part might be unnecessary. We might remove it with some testing.
        $tablebody .= "<td id=letzterGrund>\n";
    } else {
        $tablebody .= "<td>\n";
    }
    $tablebody .= "$row->reason";
    $tablebody .= "\n</td>\n";
    $tablebody .= "<td>\n";
    $tablebody .= "$row->days";
    $tablebody .= "\n</td>\n";
    $tablebody .= "\n</tr>\n";
    $i++;
}
require 'head.php';
require 'navigation.php';
require 'src/php/pages/menu.php';
//Hier beginnt die Ausgabe
echo "<div id=main-area>\n";

echo build_select_employee($employee_id, $List_of_employees);

echo "<a class=no-print href='abwesenheit-in.php?employee_id=$employee_id'><br>[" . gettext("Edit") . "]</a>";
echo "<table>\n";
//Überschrift
echo "<tr>\n"
 . "<th>\n"
 . "" . gettext("Start") . "\n"
 . "</th>\n"
 . "<th>\n"
 . "" . gettext("End") . "\n"
 . "</th>\n"
 . "<th>\n"
 . "" . gettext("Reason") . "\n"
 . "</th>\n"
 . "<th>\n"
 . "" . gettext("Days") . "\n"
 . "</th>\n"
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
