<?php
/*
 * This script is upposed to prepare a list of days and hours worked by people in "mini jobs".
 * German law defines "Geringfügig entlohnte Beschäftigung" in § 8 Abs. 1 Nr. 1 SGB IV
 * This list will be filled with the known data from the database.
 * There will also be the option to print a blank list.
 */
require '../../../default.php';

if (filter_has_var(INPUT_POST, "month")) {
    $month = filter_input(INPUT_POST, 'month', FILTER_SANITIZE_NUMBER_INT);
} else {
    $month = date("n");
}
if (filter_has_var(INPUT_POST, "year")) {
    $year = filter_input(INPUT_POST, 'year', FILTER_SANITIZE_NUMBER_INT);
} else {
    $year = date("Y");
}
$start_datum = mktime(0, 0, 0, $month, 1, $year);
$date_unix = $start_datum;

if (filter_has_var(INPUT_POST, "employee_id")) {
    $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
} else {
    $employee_id = $_SESSION['user_employee_id'];
}

//The employee list needs a $date_unix, because nobody is working with us forever.
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'db-lesen-mitarbeiter.php';

$Months = array();
for ($i = 1; $i <= 12; $i++) {
    $Months[$i] = strftime('%B', mktime(0, 0, 0, $i, 1));
}
$Years = array();
$sql_query = "SELECT DISTINCT YEAR(`Datum`) AS `year` FROM `Dienstplan`";
$result = mysqli_query_verbose($sql_query);
while ($row = mysqli_fetch_object($result)) {
    $Years[] = $row->year;
}
$sql_query = "SELECT `Datum` as `date`, MIN(`Dienstbeginn`) as `start`, MAX(`Dienstende`) as `end`, SUM(`Stunden`) as `hours`"
        . "FROM `Dienstplan` "
        . "WHERE  `VK` = $employee_id AND MONTH(`Datum`) = $month AND YEAR(`Datum`) = $year "
        . "GROUP BY `Datum`";
$statement = $pdo->prepare($sql_query);
$statement->execute();
$result = $statement->fetchAll();
$table_body_html = "<tbody>";
foreach ($result as $row_number => $row) {
    $table_body_html .= "<tr>";
    $table_body_html .= "<td>" . strftime('%a %x', strtotime($row['date'])) . "</td>";
    $table_body_html .= "<td>" . strftime('%H:%M', strtotime($row['start'])) . "</td>";
    $table_body_html .= "<td>" . strftime('%H:%M', strtotime($row['end'])) . "</td>";
    $table_body_html .= "<td>" . $row['hours'] . "</td>";
    $table_body_html .= "</tr>";
}
$table_body_html .= "</tbody>";
/*
 */

require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'navigation.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
?>
<FORM method=post class="no-print">
    <SELECT name=month onchange=this.form.submit()>
        <?php
        foreach ($Months as $month_number_option => $month_name) {
            echo "<option value=$month_number_option";
            if ($month_number_option == $month) {
                echo " SELECTED ";
            }
            echo ">$month_name</option>\n";
        }
        ?>
    </SELECT>
    <SELECT name=year onchange=this.form.submit()>
        <?php
        foreach ($Years as $year_option) {
            echo "<option value=$year_option";
            if ($year_option == $year) {
                echo " SELECTED ";
            }
            echo ">$year_option</option>\n";
        }
        ?>
    </SELECT>
    <SELECT name=employee_id onchange=this.form.submit()>
        <?php
        foreach ($workforce->List_of_employees as $employee_id_option => $employee_object) {
            echo "<option value=$employee_id_option";
            if ($employee_id_option == $employee_id) {
                echo " SELECTED ";
            }
            echo ">$employee_object->last_name</option>\n";
        }
        ?>
    </SELECT>
</FORM>
<H1>Stundenzettel</H1>
<H2><?= $List_of_employee_full_names[$employee_id] ?></H2>
<TABLE class="table_with_border" id="marginal_employment_hours_list_table">
    <THEAD>
        <TR><!--This following part is specific to German law. No other translation semms necessary.-->
            <TH>Datum</TH>
            <TH>Beginn</TH>
            <TH>Ende</TH>
            <TH>Arbeitszeit <SMALL>(abzüglich Pausen)</SMALL></TH>
        </TR>
    </THEAD>
    <?= $table_body_html ?>
</TABLE>
<!--
A signature line for the employee or the employer or both does not seem to be necessary.
If that ever changes:
HTML:
<input type="text" class="print_signature" />
CSS:
.print_signature {
    border: 0;
    border-bottom: 1px solid #000;
}

-->
<?php require PDR_FILE_SYSTEM_APPLICATION_PATH . 'contact-form.php'; ?>
</BODY>
</HTML>
