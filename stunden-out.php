<?php
require 'default.php';
$VKmax = max(array_keys($List_of_employees)); //Wir suchen die höchste VK-Nummer.
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
$tablebody = "\t\t\t<tbody>\n";
$i = 1;
while ($row = mysqli_fetch_object($result)) {
    $tablebody .= "\t\t\t<tr>\n";
    $tablebody .= "\t\t\t\t<td>";
    $tablebody .= "<a href='tag-out.php?datum=" . date("Y-m-d", strtotime($row->Datum)) . "'>" . date("d.m.Y", strtotime($row->Datum)) . "</a>";
    $tablebody .= "</td>\n";
    $tablebody .= "\t\t\t\t<td>";
    $tablebody .= "$row->Grund";
    $tablebody .= "</td>\n";
    $tablebody .= "\t\t\t\t<td>";
    $tablebody .= "$row->Stunden";
    $tablebody .= "</td>\n";
    if ($i == $number_of_rows) {
        $tablebody .= "\t\t\t\t<td id=saldoAlt>";
    } else {
        $tablebody .= "\t\t\t\t<td>";
    }
    $tablebody .= "$row->Saldo";
    $saldo = $row->Saldo; //Wir tragen den Saldo mit uns fort.
    $tablebody .= "\t\t\t\t</td>\n";
    $tablebody .= "\t\t\t</tr>\n";
    $i++;
}
$tablebody .= "\t\t\t</tbody>\n";

//Hier beginnt die Ausgabe
require 'head.php';
require 'navigation.php';
require 'src/php/pages/menu.php';
echo "<div id=main-area>\n";

echo build_select_employee($employee_id, $List_of_employees);
echo "\t\t\t<div class=no-print><br><a href=stunden-in.php?employee_id=$employee_id>[" . gettext("Edit") . "]</a><br><br></div>\n";
echo "\t\t<table>\n";
//Überschrift
echo "\t\t\t<thead><tr>\n" .
 "\t\t\t\t<th>Datum</th>\n" .
 "\t\t\t\t<th>Grund</th>\n" .
 "\t\t\t\t<th>Stunden</th>\n" .
 "\t\t\t\t<th>Saldo</th>\n" .
 "\t\t\t</tr></thead>\n";
//Ausgabe
echo "$tablebody";
echo "\t\t</table>\n";
echo "\t</form>\n";
echo "</div>\n";
require 'contact-form.php';
?>
</body>
</html>
