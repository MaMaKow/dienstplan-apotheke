<?php
/* This script is upposed to prepare an attendance list.
 * That list can be attached on a white wall and filled by pencil.
 * Known absences are prefilled.
 */

require 'default.php';
$month = user_input::get_variable_from_any_input('month', FILTER_SANITIZE_STRING, date('n'));
$year = user_input::get_variable_from_any_input('year', FILTER_SANITIZE_STRING, date('Y'));
$start_date_unix = mktime(0, 0, 0, $month, 1, $year);
$date_unix = $start_date_unix;
$date_sql = date('Y-m-d', $date_unix);

//The employee list needs a $date_unix, because nobody is working with us forever.
$workforce = new workforce($date_sql);

$Months = array();
for ($i = 1; $i <= 12; $i++) {
    $Months[$i] = strftime('%B', mktime(0, 0, 0, $i, 1));
}
$Years = array();
$sql_query = "SELECT DISTINCT YEAR(`Datum`) AS `year` FROM `Dienstplan`";
$result = database_wrapper::instance()->run($sql_query);
while ($row = $result->fetch(PDO::FETCH_OBJ)) {
    $Years[] = $row->year;
}
require 'head.php';
require 'src/php/pages/menu.php';
?>
<FORM method=post class="no-print">
    <SELECT name=month onchange=this.form.submit()>
        <?php
        foreach ($Months as $month_number => $month_name) {
            echo "<option value=$month_number";
            if ($month_number == $month) {
                echo " SELECTED ";
            }
            echo ">$month_name</option>\n";
        }
        ?>
    </SELECT>
    <SELECT name=year onchange=this.form.submit()>
        <?php
        foreach ($Years as $year_number) {
            echo "<option value=$year_number";
            if ($year_number == $year) {
                echo " SELECTED ";
            }
            echo ">$year_number</option>\n";
        }
        ?>
    </SELECT>
</FORM>
<TABLE class="table_with_border">
    <TR>
        <TD>Anwesenheit</TD>
        <?php
        foreach ($workforce->List_of_employees as $employee_id => $employee_object) {
            echo '<TD style="padding-bottom: 0">' . mb_substr($employee_object->last_name, 0, 4) . "<br>$employee_id</TD>";
        }
        ?>
    </TR>
    <?php
    for ($date_unix = $start_date_unix; $date_unix < strtotime('+ 1 month', $start_date_unix); $date_unix = $date_unix + PDR_ONE_DAY_IN_SECONDS) {
        $date_sql = date("Y-m-d", $date_unix);
        if (date('N', $date_unix) >= 6) {
            echo '<TR class=wochenende><TD style="padding-bottom: 0">' . strftime('%a %d.%m.', $date_unix) . '</TD>';
            foreach (array_keys($workforce->List_of_employees) as $employee_id) {
                echo '<TD></TD>';
            }
        } else {
            $Absentees = absence::read_absentees_from_database($date_sql);
            $having_emergency_service = pharmacy_emergency_service::having_emergency_service($date_sql);
            echo '<TR><TD style="padding-bottom: 0">' . strftime('%a %d.%m.%Y', $date_unix) . '</TD>';
            //TODO: The following part is not localized. It will not wrk in any other language:
            foreach (array_keys($workforce->List_of_employees) as $employee_id) {
                if (isset($Absentees[$employee_id])) {
                    /*
                     * TODO: Once, that the database only accepts a SET of absences, find some akronyms to put here!
                     * if (preg_match('/Krank/i', $Absentees[$employee_id])) {
                      $reason_short_string = 'K';
                      } elseif (preg_match('/Kur/i', $Absentees[$employee_id])) {
                      $reason_short_string = 'K';
                      } elseif (preg_match('/Urlaub/i', $Absentees[$employee_id])) {
                      $reason_short_string = 'U';
                      } elseif (preg_match('/Elternzeit/i', $Absentees[$employee_id])) {
                      $reason_short_string = 'E';
                      } elseif (preg_match('/Nicht angestellt/i', $Absentees[$employee_id])) {
                      $reason_short_string = 'N/A';
                      } elseif (preg_match('/Notdienst/i', $Absentees[$employee_id])) {
                      $reason_short_string = 'NA';
                      } else {
                      $reason_short_string = mb_substr($Absentees[$employee_id], 0, 4);
                      }
                     */
                    $reason_short_string = mb_substr($Absentees[$employee_id], 0, 4);
                    echo "<TD style='padding-bottom: 0' title='" . $Absentees[$employee_id] . "'>" . $reason_short_string . "</TD>";
                } elseif (FALSE !== $having_emergency_service and $having_emergency_service['employee_id'] == $employee_id) {
                    $reason_short_string = mb_substr(gettext("emergency service"), 0, 4);
                    echo "<TD style='padding-bottom: 0' title='" . gettext("emergency service") . "'>" . $reason_short_string . "</TD>";
                } else {
                    echo '<TD></TD>';
                }
            }
        }
        echo "</TR>\n";
    }
    ?>

</TD>
</TABLE>
<!--
Legende
<TABLE>
    <TR><TD>K</TD><TD>Krank</TD><TD>U</TD><TD>Urlaub</TD><TD>E</TD><TD>Elternzeit</TD>
        <TD>N/A</TD><TD>Nicht angestellt</TD><TD>N</TD><TD>Notdienst</TD><TD>NA</TD><TD>Ausgleich nach Notdienst</TD></TR>
</TABLE>
-->
<?php require 'contact-form.php'; ?>
</BODY>
</HTML>
