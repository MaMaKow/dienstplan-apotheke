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
$month_number = (int) user_input::get_variable_from_any_input('month_number', FILTER_SANITIZE_NUMBER_INT, date('n'));
\PDR\Utility\GeneralUtility::createCookie("month_number", $month_number, 1);
$year = (int) user_input::get_variable_from_any_input('year', FILTER_SANITIZE_NUMBER_INT, date('Y'));
\PDR\Utility\GeneralUtility::createCookie("year", $year, 1);
if (isset($_POST) && !empty($_POST)) {
    // POST data has been submitted
    $location = PDR_HTTP_SERVER_APPLICATION_PATH . 'src/php/pages/sick-note-tracking.php' . "?year=$year&month_number=$month_number";
    header('Location:' . $location);
    die("<p>Redirect to: <a href=$location>$location</a></p>");
}

// Create DateTime object for the start of the month
$startOfMonth = new DateTime("$year-$month_number-01 00:00:00");

// Calculate the end of the month by adding 1 month and subtracting 1 second
$endOfMonth = clone $startOfMonth;
$endOfMonth->add(new DateInterval('P1M'));
$endOfMonth->sub(new DateInterval('PT1S'));

$absenceCollectionMonth = PDR\Database\AbsenceDatabaseHandler::getAllAbsenceObjectsInPeriod($startOfMonth, $endOfMonth);
$workforce = new workforce($startOfMonth->format("Y-m-d"), $endOfMonth->format("Y-m-d"));

require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
echo form_element_builder::build_html_select_month($month_number);
echo form_element_builder::build_html_select_year($year);
?>
<TABLE class="table-with-border">
    <TR>
        <TD>Name</TD>
        <TD>Von</TD>
        <TD>Bis</TD>
        <TD>Mit Krankschreibung (Arzt)</TD>
        <TD>Kind krank</TD>
        <!--
        <TD>Arbeitsunfall</TD>
        <TD>Krankenhausaufenthalt</TD>
        -->
    </TR>
    <?php
    foreach ($absenceCollectionMonth as $absenceObject) {
        if (\PDR\Utility\AbsenceUtility::REASON_SICKNESS !== $absenceObject->getReasonId() and \PDR\Utility\AbsenceUtility::REASON_SICKNESS_OF_CHILD !== $absenceObject->getReasonId()) {
            /**
             * const REASON_SICKNESS = 3;
             * const REASON_SICKNESS_OF_CHILD = 4;
             * Wir betrachten hier nur die Fälle Krankheit und Krankheit des Kindes.
             */
            continue;
        }
        $reason_sickness_of_child_checked = "";
        if (\PDR\Utility\AbsenceUtility::REASON_SICKNESS_OF_CHILD === $absenceObject->getReasonId()) {
            $reason_sickness_of_child_checked = "✘";
        }
        echo '<TR style="padding-bottom: 0">';
        $employee_object = $workforce->get_employee_object($absenceObject->getEmployeeKey());
        echo '<TD style="padding-bottom: 0">'
        . $employee_object->first_name . " " . $employee_object->last_name
        . "</TD>";
        echo '<TD style="padding-bottom: 0">'
        . $absenceObject->getStart()->format("d.m.Y")
        . "</TD>";
        echo '<TD style="padding-bottom: 0">'
        . $absenceObject->getEnd()->format("d.m.Y")
        . "</TD>";
        echo '<TD style="padding-bottom: 0">'//Krankschreibung?
        . '<div style="border: 1px solid #000; width: 1em; height: 1em; display: inline-block;"></div>'
        . "</TD>";
        echo '<TD style="padding-bottom: 0">'//Kind krank
        . '<div style="border: 1px solid #000; width: 1em; height: 1em; display: inline-block;">' . $reason_sickness_of_child_checked . '</div>'
        . "</TD>";
        /*
          echo '<TD style="padding-bottom: 0">'//Arbeitsunfall
          . '<div style="border: 1px solid #000; width: 1em; height: 1em; display: inline-block;"></div>'
          . "</TD>";
          echo '<TD style="padding-bottom: 0">'//Krankenhausaufenthalt
          . '<div style="border: 1px solid #000; width: 1em; height: 1em; display: inline-block;"></div>'
          . "</TD>";
          echo '</TR>';
         */
    }
    ?>
</TABLE>
<?php require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php'; ?>
</BODY>
</HTML>
