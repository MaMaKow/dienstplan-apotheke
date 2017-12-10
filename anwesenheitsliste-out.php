<?php
/* This script is upposed to prepare an attendance list.
 * That list can be attached on a white wall and filled by pencil.
 * Known absences are prefilled.
 */

require 'default.php';
require 'db-lesen-abwesenheit.php';
require_once PDR_FILE_SYSTEM_APPLICATION_PATH . "/src/php/classes/class.emergency_service.php";

if (filter_has_var(INPUT_POST, "month")) {
    $month = filter_input(INPUT_POST, 'month', FILTER_SANITIZE_STRING);
} else {
    $month = date("n");
}
if (filter_has_var(INPUT_POST, "year")) {
    $year = filter_input(INPUT_POST, 'year', FILTER_SANITIZE_STRING);
} else {
    $year = date("Y");
}
$start_datum = mktime(0, 0, 0, $month, 1, $year);
$date_unix = $start_datum;

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
require 'head.php';
require 'navigation.php';
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
        foreach ($List_of_employees as $employee_id => $name) {
            echo '<TD style="padding-bottom: 0">' . mb_substr($name, 0, 4) . "<br>$employee_id</TD>";
        }
        ?>
    </TR>
    <?php
    //$start_datum = strtotime('01.02.2016');
    for ($date_unix = $start_datum; $date_unix < strtotime('+ 1 month', $start_datum); $date_unix = strtotime('+ 1 day', $date_unix)) {
        $date_sql = date("Y-m-d", $date_unix);
        if (date('N', $date_unix) >= 6) {
            echo '<TR class=wochenende><TD style="padding-bottom: 0">' . strftime('%a %d.%m.', $date_unix) . '</TD>';
            foreach ($List_of_employees as $employee_id => $name) {
                echo '<TD></TD>';
            }
        } else {
            $Abwesende = db_lesen_abwesenheit($date_unix);
            $having_emergency_service = pharmacy_emergency_service::having_emergency_service($date_sql);
            echo '<TR><TD style="padding-bottom: 0">' . strftime('%a %d.%m.%Y', $date_unix) . '</TD>';
            //TODO: The following part is not localized. It will not wrk in any other language:
            foreach ($List_of_employees as $employee_id => $name) {
                if (isset($Abwesende[$employee_id])) {
                    /*                    if (preg_match('/Krank/i', $Abwesende[$employee_id])) {
                      $reason_short_string = 'K';
                      } elseif (preg_match('/Kur/i', $Abwesende[$employee_id])) {
                      $reason_short_string = 'K';
                      } elseif (preg_match('/Urlaub/i', $Abwesende[$employee_id])) {
                      $reason_short_string = 'U';
                      } elseif (preg_match('/Elternzeit/i', $Abwesende[$employee_id])) {
                      $reason_short_string = 'E';
                      } elseif (preg_match('/Nicht angestellt/i', $Abwesende[$employee_id])) {
                      $reason_short_string = 'N/A';
                      } elseif (preg_match('/Notdienst/i', $Abwesende[$employee_id])) {
                      $reason_short_string = 'NA';
                      } else {
                      $reason_short_string = mb_substr($Abwesende[$employee_id], 0, 4);
                      }
                     */
                    $reason_short_string = mb_substr($Abwesende[$employee_id], 0, 4);
                    echo "<TD style='padding-bottom: 0' title='" . $Abwesende[$employee_id] . "'>" . $reason_short_string . "</TD>";
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
