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

/**
 * The numbers are arbitrary.
 * They must be integers passing FILTER_SANITIZE_NUMBER_INT
 *   and they must not be between 1 and 12 inclusive
 *   as those are the real months of the year.
 */
const PDR_YEAR_QUARTER_FIRST = 121;
const PDR_YEAR_QUARTER_SECOND = 122;
const PDR_YEAR_QUARTER_THIRD = 123;
const PDR_YEAR_QUARTER_FOURTH = 124;
const PDR_YEAR_FULL = 1212;

$month_or_part = user_input::get_variable_from_any_input('month_or_part', FILTER_SANITIZE_NUMBER_INT, date('n'));
$year = user_input::get_variable_from_any_input('year', FILTER_SANITIZE_NUMBER_INT, date('Y'));
\PDR\Utility\GeneralUtility::createCookie('month_or_part', $month_or_part, 1);
\PDR\Utility\GeneralUtility::createCookie('year', $year, 1);
if ($month_or_part <= 12) {
    $date_start_object = new DateTime($year . '-' . $month_or_part . '-' . 1);
    $date_end_object = clone $date_start_object;
    $date_end_object->add(new DateInterval('P1M'));
    $date_end_object->sub(new DateInterval('P1D'));
} else {
    /*
     * Another range was selected. We show a bigger part of the year:
     */
    switch ($month_or_part) {
        case PDR_YEAR_QUARTER_FIRST:
            $date_start_object = new DateTime($year . '-' . 1 . '-' . 1);
            $date_end_object = clone $date_start_object;
            $date_end_object->add(new DateInterval('P3M'));
            $date_end_object->sub(new DateInterval('P1D'));
            break;
        case PDR_YEAR_QUARTER_SECOND:
            $date_start_object = new DateTime($year . '-' . 4 . '-' . 1);
            $date_end_object = clone $date_start_object;
            $date_end_object->add(new DateInterval('P3M'));
            $date_end_object->sub(new DateInterval('P1D'));
            break;
        case PDR_YEAR_QUARTER_THIRD:
            $date_start_object = new DateTime($year . '-' . 7 . '-' . 1);
            $date_end_object = clone $date_start_object;
            $date_end_object->add(new DateInterval('P3M'));
            $date_end_object->sub(new DateInterval('P1D'));
            break;
        case PDR_YEAR_QUARTER_FOURTH:
            $date_start_object = new DateTime($year . '-' . 10 . '-' . 1);
            $date_end_object = clone $date_start_object;
            $date_end_object->add(new DateInterval('P3M'));
            $date_end_object->sub(new DateInterval('P1D'));
            break;
        case PDR_YEAR_FULL:
            $date_start_object = new DateTime($year . '-' . 1 . '-' . 1);
            $date_end_object = clone $date_start_object;
            $date_end_object->add(new DateInterval('P12M'));
            $date_end_object->sub(new DateInterval('P1D'));
            break;

        default:
            throw new Exception('Not implemented, yet!');
            break;
    }
}

$workforce = new workforce($date_start_object->format('Y-m-d'), $date_end_object->format('Y-m-d'));
$employee_key = user_input::get_variable_from_any_input('employee_key', FILTER_SANITIZE_NUMBER_INT, $workforce->get_default_employee_key());
\PDR\Utility\GeneralUtility::createCookie('employee_key', $employee_key, 1);
if (!$workforce->employee_exists($employee_key)) {
    $employee_key = $workforce->get_default_employee_key();
}
if (isset($_POST) && !empty($_POST)) {
    // POST data has been submitted
    $location = PDR_HTTP_SERVER_APPLICATION_PATH . 'src/php/pages/marginal-employment-hours-list.php' . "?year=$year&month_or_part=$month_or_part&employee_key=$employee_key";
    header('Location:' . $location);
    die("<p>Redirect to: <a href=$location>$location</a></p>");
}

$configuration = new \PDR\Application\configuration();
$locale = $configuration->getLanguage();
$monthFormatter = new IntlDateFormatter($locale, IntlDateFormatter::FULL, IntlDateFormatter::NONE);
$monthFormatter->setPattern('MMMM');

$Months = array();
for ($i = 1; $i <= 12; $i++) {
    $date = mktime(0, 0, 0, $i, 1);
    $Months[$i] = $monthFormatter->format($date);
}
$Years = \PDR\Utility\AbsenceUtility::getRosteringYears();
$table_body_html = "<tbody>";
$weekdayFormatter = new IntlDateFormatter($locale, IntlDateFormatter::FULL, IntlDateFormatter::NONE);
$weekdayFormatter->setPattern('EEE dd.MM.yyyy'); // 'EEEE' represents the full weekday name

for ($date_object = clone $date_start_object; $date_object <= $date_end_object; $date_object->add(new DateInterval('P1D'))) {
    $sql_query = "SELECT `Datum` as `date`, MIN(`Dienstbeginn`) as `start`, MAX(`Dienstende`) as `end`, SUM(`Stunden`) as `hours`"
            . "FROM `Dienstplan` "
            . "WHERE  `employee_key` = :employee_key AND `Datum` = :date "
            . "GROUP BY `Datum`";

    $result = database_wrapper::instance()->run($sql_query, array(
        'employee_key' => $employee_key,
        'date' => $date_object->format('Y-m-d')));
    $absence = PDR\Database\AbsenceDatabaseHandler::getSpecificAbsenceObject($date_object, $employee_key);

    $table_body_html .= "<tr>";
    $dateString = $weekdayFormatter->format($date_object->getTimestamp());
    $table_body_html .= "<td>" . $dateString . "</td>";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        //There should only be one row here.
        $timeStartObject = new DateTime($row['start']);
        $timeStartString = $timeStartObject->format("H:i");
        $timeEndObject = new DateTime($row['end']);
        $timeEndString = $timeEndObject->format("H:i");
        $table_body_html .= "<td>" . $timeStartString . "</td>";
        $table_body_html .= "<td>" . $timeEndString . "</td>";
        $table_body_html .= "<td>" . round($row['hours'], 2);
        if (isset($workforce->List_of_employees[$employee_key])) {
            $employee_object = $workforce->List_of_employees[$employee_key];
            $principle_working_hours = $employee_object->get_principle_roster_on_date($date_object)[0]->working_hours;
            /**
             * <p lang=de>Die folgende Rechnung ist allein nicht ganz korrekt:
             * $overtime_hours = $row['hours'] - $principle_working_hours;
             * Sie berücksichtigt z.B. nicht "Kind krank"
             * Daher ergänzen wir im nächsten Schritt noch den Blick auf die Abwesenheit.
             * </p>
             */
            $overtime_hours = $row['hours'] - $principle_working_hours;
            if (null !== $absence and !in_array($absence->getReasonId(), array(\PDR\Utility\AbsenceUtility::REASON_TAKEN_OVERTIME))) {
                $overtime_hours += $principle_working_hours;
            }

            $overtime_hours_round = round($overtime_hours, 2);
            if ($overtime_hours_round > 0) {
                $table_body_html .= "<span> (+$overtime_hours_round)</span>";
            } elseif ($overtime_hours_round < 0) {
                $table_body_html .= "<span> ($overtime_hours_round)</span>";
            }
        }
        $table_body_html .= "</td>";
    }
    if (null !== $absence) {
        $table_body_html .= "<td colspan='3'>" . \PDR\Utility\AbsenceUtility::getReasonStringLocalized($absence->getReasonId()) . "</td>";
    }

    $table_body_html .= "</tr>";
}
$table_body_html .= "</tbody>";
/*
 */

require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
?>
<FORM method='post' class='no_print'>
    <SELECT name='month_or_part' onchange='this.form.submit()'>
        <?php
        foreach ($Months as $month_number_option => $month_name) {
            echo "<option value='$month_number_option'";
            if ($month_number_option == $month_or_part) {
                echo " SELECTED ";
            }
            echo ">$month_name</option>\n";
        }
        echo "<option value='" . PDR_YEAR_QUARTER_FIRST . "'";
        if (PDR_YEAR_QUARTER_FIRST == $month_or_part) {
            echo " SELECTED ";
        }
        echo ">" . gettext('First quarter') . "</option>\n";

        echo "<option value='" . PDR_YEAR_QUARTER_SECOND . "'";
        if (PDR_YEAR_QUARTER_SECOND == $month_or_part) {
            echo " SELECTED ";
        }
        echo ">" . gettext('Second quarter') . "</option>\n";

        echo "<option value='" . PDR_YEAR_QUARTER_THIRD . "'";
        if (PDR_YEAR_QUARTER_THIRD == $month_or_part) {
            echo " SELECTED ";
        }
        echo ">" . gettext('Third quarter') . "</option>\n";

        echo "<option value='" . PDR_YEAR_QUARTER_FOURTH . "'";
        if (PDR_YEAR_QUARTER_FOURTH == $month_or_part) {
            echo " SELECTED ";
        }
        echo ">" . gettext('Fourth quarter') . "</option>\n";

        echo "<option value='" . PDR_YEAR_FULL . "'";
        if (PDR_YEAR_FULL == $month_or_part) {
            echo " SELECTED ";
        }
        echo ">" . gettext('Full year') . "</option>\n";
        ?>
    </SELECT>
    <SELECT name='year' onchange='this.form.submit()'>
        <?php
        foreach ($Years as $year_option) {
            echo "<option value='$year_option'";
            if ($year_option == $year) {
                echo " SELECTED ";
            }
            echo ">$year_option</option>\n";
        }
        ?>
    </SELECT>
    <SELECT name='employee_key' onchange='this.form.submit()'>
        <?php
        foreach ($workforce->List_of_employees as $employee_key_option => $employee_object) {
            echo "<option value='$employee_key_option'";
            if ($employee_key_option == $employee_key) {
                echo " SELECTED ";
            }
            echo ">$employee_object->last_name</option>\n";
        }
        ?>
    </SELECT>
</FORM>
<H1>Stundenzettel</H1>

<?php echo build_html_navigation_elements::build_button_link_roster_employee_table_page($employee_key); ?>
<H2><?= $workforce->List_of_employees[$employee_key]->full_name ?></H2>
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
