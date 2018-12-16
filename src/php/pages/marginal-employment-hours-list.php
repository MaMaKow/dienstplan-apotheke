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
/*
 * This script is upposed to prepare a list of days and hours worked by people in "mini jobs".
 * German law defines "Geringfügig entlohnte Beschäftigung" in § 8 Abs. 1 Nr. 1 SGB IV
 * This list will be filled with the known data from the database.
 * There will also be the option to print a blank list.
 */
require '../../../default.php';

$month = user_input::get_variable_from_any_input('month', FILTER_SANITIZE_NUMBER_INT, $month = date('n'));
$year = user_input::get_variable_from_any_input('year', FILTER_SANITIZE_NUMBER_INT, $month = date('Y'));
$year = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_object']->employee_id);

$start_datum = mktime(0, 0, 0, $month, 1, $year);
$date_unix = $start_datum;
$date_sql = date('Y-m-d', $date_unix);

$workforce = new workforce($date_sql);
$Months = array();
for ($i = 1; $i <= 12; $i++) {
    $Months[$i] = strftime('%B', mktime(0, 0, 0, $i, 1));
}
$Years = absence::get_rostering_years();
$sql_query = "SELECT `Datum` as `date`, MIN(`Dienstbeginn`) as `start`, MAX(`Dienstende`) as `end`, SUM(`Stunden`) as `hours`"
        . "FROM `Dienstplan` "
        . "WHERE  `VK` = :employee_id AND MONTH(`Datum`) = :month AND YEAR(`Datum`) = :year "
        . "GROUP BY `Datum`";

$result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id, 'month' => $month, 'year' => $year));
$table_body_html = "<tbody>";
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $table_body_html .= "<tr>";
    $table_body_html .= "<td>" . strftime('%a %x', strtotime($row['date'])) . "</td>";
    $table_body_html .= "<td>" . strftime('%H:%M', strtotime($row['start'])) . "</td>";
    $table_body_html .= "<td>" . strftime('%H:%M', strtotime($row['end'])) . "</td>";
    $table_body_html .= "<td>" . round($row['hours'], 2) . "</td>";
    $table_body_html .= "</tr>";
}
$table_body_html .= "</tbody>";
/*
 */

require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
?>
<FORM method=post class="no_print">
    <SELECT name=month onchange=this.form.submit()>
        <?php
        /*
         * TODO: Add more options:
         * e.g. whole year, first/second/third/fourth quarter
         */
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
<H2><?= $workforce->List_of_employees[$employee_id]->full_name ?></H2>
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
<?php require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php'; ?>
</BODY>
</HTML>
