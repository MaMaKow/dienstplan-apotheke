<?php
require 'default.php';
$VKmax = max(array_keys($workforce->List_of_employees)); //Wir suchen die höchste VK-Nummer.
$employee_id = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_employee_id']);
create_cookie('employee_id', $employee_id, 1);
$vk = $employee_id;

//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';
$sql_query = "SELECT * FROM `Stunden`
				WHERE `VK` = " . $vk . "
				ORDER BY `Aktualisierung` ASC
				";
$result = mysqli_query_verbose($sql_query);
$number_of_rows = mysqli_num_rows($result);
$tablebody = "<tbody>\n";
$i = 1;
while ($row = mysqli_fetch_object($result)) {
    $tablebody .= "<tr>\n";
    $tablebody .= "<td>";
    $tablebody .= "<a href='tag-out.php?datum=" . date("Y-m-d", strtotime($row->Datum)) . "'>" . date("d.m.Y", strtotime($row->Datum)) . "</a>";
    $tablebody .= "</td>\n";
    $tablebody .= "<td>";
    $tablebody .= "$row->Grund";
    $tablebody .= "</td>\n";
    $tablebody .= "<td>";
    $tablebody .= "$row->Stunden";
    $tablebody .= "</td>\n";
    if ($i == $number_of_rows) {
        $tablebody .= "<td id=saldoAlt>";
    } else {
        $tablebody .= "<td>";
    }
    $tablebody .= "$row->Saldo";
    $saldo = $row->Saldo; //Wir tragen den Saldo mit uns fort.
    $tablebody .= "</td>\n";
    $tablebody .= "</tr>\n";
    $i++;
}
$tablebody .= "</tbody>\n";

//Hier beginnt die Ausgabe
require 'head.php';
require 'navigation.php';
require 'src/php/pages/menu.php';
echo "<div id=main-area>\n";

echo build_select_employee($employee_id, $workforce->List_of_employees);
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
