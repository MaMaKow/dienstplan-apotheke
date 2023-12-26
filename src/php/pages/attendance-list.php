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
$month_number = (int) user_input::get_variable_from_any_input('month_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS, date('n'));
create_cookie("month_number", $month_number, 1);
$year = (int) user_input::get_variable_from_any_input('year', FILTER_SANITIZE_FULL_SPECIAL_CHARS, date('Y'));
create_cookie("year", $year, 1);
// Create a DateTime object for the start date
$dateStartObject = new DateTime();
$dateStartObject->setDate($year, $month_number, 1);
$dateStartObject->setTime(0, 0, 0);
$dateEndObject = clone $dateStartObject;
$dateEndObject->setDate($year, $month_number, $dateStartObject->format("t"));
//The employee list needs a date, because nobody is working with us forever.
$workforce = new workforce($dateStartObject->format('Y-m-d'));

require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
echo form_element_builder::build_html_select_month($month_number);
echo form_element_builder::build_html_select_year($year);
?>
<TABLE class="table_with_border">
    <TR>
        <TD>Anwesenheit</TD>
        <?php
        foreach ($workforce->List_of_employees as $employee_key => $employee_object) {
            echo '<TD style="padding-bottom: 0" title="' . $employee_object->first_name . " " . $employee_object->last_name . '">' . mb_substr($employee_object->last_name, 0, 4) . "<br>" . $workforce->get_employee_short_descriptor($employee_key) . "</TD>";
        }
        ?>
    </TR>
    <?php
    $configuration = new \PDR\Application\configuration();
    $locale = $configuration->getLanguage();
    $dateFormatter = new IntlDateFormatter($locale, IntlDateFormatter::FULL, IntlDateFormatter::NONE);
    for ($currentDate = clone $dateStartObject; $currentDate <= $dateEndObject; $currentDate->modify('+1 day')) {
        $date_sql = $currentDate->format('Y-m-d');
        if ($currentDate->format('N') >= 6) {
            $dateFormatter->setPattern('EEE dd.MM.');
            $dateString = $dateFormatter->format($currentDate);
            echo '<TR class=wochenende><TD style="padding-bottom: 0">' . $dateString . '</TD>';
            foreach (array_keys($workforce->List_of_employees) as $employee_key) {
                echo '<TD></TD>';
            }
        } else {
            $absenceCollection = PDR\Database\AbsenceDatabaseHandler::readAbsenteesOnDate($date_sql);
            $having_emergency_service = pharmacy_emergency_service::having_emergency_service($date_sql);
            $dateFormatter->setPattern('EEE dd.MM.YYYY');
            $dateString = $dateFormatter->format($currentDate);
            echo '<TR><TD style="padding-bottom: 0">' . $dateString . '</TD>';
            foreach (array_keys($workforce->List_of_employees) as $employee_key) {
                if ($absenceCollection->containsEmployeeKey($employee_key)) {
                    $reason_short_string = mb_substr(\PDR\Utility\AbsenceUtility::getReasonStringLocalized($absenceCollection->getAbsenceByEmployeeKey($employee_key)->getReasonId()), 0, 4);
                    echo "<TD style='padding-bottom: 0' title='" . \PDR\Utility\AbsenceUtility::getReasonStringLocalized($absenceCollection->getAbsenceByEmployeeKey($employee_key)->getReasonId()) . "'>" . $reason_short_string . "</TD>";
                } elseif (FALSE !== $having_emergency_service and $having_emergency_service['employee_key'] == $employee_key) {
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
</TABLE>
<?php require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php'; ?>
</BODY>
</HTML>
