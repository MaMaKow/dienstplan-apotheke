<?php
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
