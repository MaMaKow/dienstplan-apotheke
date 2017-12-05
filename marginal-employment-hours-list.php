<?php
/*
 * This script is upposed to prepare a list of days and hours worked by people in "mini jobs".
 * German law defines "Geringfügig entlohnte Beschäftigung" in § 8 Abs. 1 Nr. 1 SGB IV
 * This list will be filled with the known data from the database.
 * There will also be the option to print a blank list.
 */

require 'default.php';

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
require 'db-lesen-mitarbeiter.php';

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
$sql_query = "SELECT `Datum`, MIN(`Dienstbeginn`), MAX(`Dienstende`), SUM(`Stunden`) "
        . "WHERE  `VK` = $employee_id AND MONTH(`Datum`) = $month AND YEAR(`Datum`) = $year "
        . "GROUP BY `Datum`";
$pdo->exec($sql_query);
$result = $pdo->fetchAll();
/*
  print_debug_variable($result);
 */

require 'head.php';
require 'navigation.php';
require 'src/php/pages/menu.php';
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
        foreach ($List_of_employees as $employee_id_option => $employee_name) {
            echo "<option value=$employee_id_option";
            if ($employee_id_option == $employee_id) {
                echo " SELECTED ";
            }
            echo ">$employee_name</option>\n";
        }
        ?>
    </SELECT>
</FORM>
<TABLE class="table_with_border">
    <TR>
        <TD><?= gettext("Date") ?></TD>
        <TD><?= gettext("Beginn") ?></TD>
        <TD><?= gettext("End") ?></TD>
        <TD><?= gettext("Hours") ?></TD>
    </TR>

</TABLE>
<?php require 'contact-form.php'; ?>
</BODY>
</HTML>
