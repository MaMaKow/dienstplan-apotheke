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
/* This script is supposed to prepare an attendance list.
 * That list can be attached on a white wall and filled by pencil.
 * Known absences are prefilled.
 */

require '../../../default.php';
$month_number = user_input::get_variable_from_any_input('month_number', FILTER_SANITIZE_STRING, date('n'));
create_cookie("month_number", $month_number, 1);
$year = user_input::get_variable_from_any_input('year', FILTER_SANITIZE_STRING, date('Y'));
create_cookie("year", $year, 1);
$start_date_unix = mktime(0, 0, 0, $month_number, 1, $year);
$date_unix = $start_date_unix;
$date_sql = date('Y-m-d', $date_unix);

//The employee list needs a $date_unix, because nobody is working with us forever.
$workforce = new workforce($date_sql);

require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
echo form_element_builder::build_html_select_month($month_number);
echo form_element_builder::build_html_select_year($year);
?>
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
                    $reason_short_string = mb_substr(localization::gettext($Absentees[$employee_id]), 0, 4);
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
<?php require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php'; ?>
</BODY>
</HTML>
