<?php
require 'default.php';


//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';
$mandant = user_input::get_variable_from_any_input('mandant', FILTER_SANITIZE_NUMBER_INT, min(array_keys($List_of_branch_objects)));
$datum = user_input::get_variable_from_any_input('datum', FILTER_SANITIZE_NUMBER_INT, date('Y-m-d'));
$year = date('Y', strtotime($datum));
create_cookie('mandant', $mandant, 30);
create_cookie('datum', $datum, 0.5);
create_cookie('year', $year, 0.5);

$sql_query = "SELECT * FROM Notdienst WHERE YEAR(Datum) = $year AND Mandant = $mandant";
$result = mysqli_query_verbose($sql_query);
while ($row = mysqli_fetch_object($result)) {
    $Notdienste['VK'][] = $row->VK;
    $Notdienste['Datum'][] = $row->Datum;
    $Notdienste['Mandant'][] = $row->Mandant;
}
require 'head.php';
?>
<table class="table_with_border">
    <tr><td>Datum</td><td>Name</td><td>Ersatz</td></tr>
    <?php
    foreach ($Notdienste['Datum'] as $key => $datum) {
        echo "\n<tr><td>" . date('d.m.Y', strtotime($Notdienste['Datum'][$key])) . '</td>';
        echo '<td>';
        echo (isset($workforce->List_of_employees[$Notdienste['VK'][$key]])) ? $workforce->List_of_employees[$Notdienste['VK'][$key]]->last_name : "";
        echo '</td>';
        echo "<td style=width:40%></td></tr>";
    }
    ?>

</table>
</body>
</html>
