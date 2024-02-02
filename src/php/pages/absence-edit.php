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
require '../../../default.php';
$year = \user_input::get_variable_from_any_input('year', FILTER_SANITIZE_NUMBER_INT, date('Y'));

$dateStartObject = new \DateTime("$year-01-01");
$dateEndObject = new \DateTime("$year-12-31");
$workforce = new \workforce($dateStartObject->format("Y-m-d"), $dateEndObject->format("Y-m-d"));
$employee_key = \user_input::get_variable_from_any_input('employee_key', FILTER_SANITIZE_NUMBER_INT, $workforce->get_default_employee_key());
create_cookie('year', $year, 1);
create_cookie('employee_key', $employee_key, 30);

if ($session->user_has_privilege(sessions::PRIVILEGE_CREATE_ABSENCE)) {
    \PDR\Utility\AbsenceUtility::handleUserInput();
}
if (isset($_POST) && !empty($_POST)) {
    // POST data has been submitted
    $location = \PDR_HTTP_SERVER_APPLICATION_PATH . 'src/php/pages/absence-edit.php' . "?year=$year&employee_key=$employee_key";
    header('Location:' . $location);
    die("<p>Redirect to: <a href=$location>$location</a></p>");
}
$number_of_holidays_due = \PDR\Utility\AbsenceUtility::getNumberOfHolidaysDue($employee_key, $workforce, $year);
$number_of_holidays_principle = $workforce->List_of_employees[$employee_key]->holidays;
$number_of_holidays_taken = \PDR\Database\AbsenceDatabaseHandler::getNumberOfHolidaysTaken($employee_key, $year);
$number_of_remaining_holidays_submitted = \PDR\Database\AbsenceDatabaseHandler::getNumberOfRemainingHolidaysSubmitted($employee_key, $year);
$number_of_remaining_holidays_left = $number_of_holidays_due - ($number_of_holidays_taken + $number_of_remaining_holidays_submitted);

$remaining_holidays_div = "<div class='remaining_holidays'>";
$remaining_holidays_div .= "<p>";
$remaining_holidays_div .= "<span>" . sprintf(gettext('The employee is entitled to %2$s of %3$s vacation days in the year %1$s.'), $year, $number_of_holidays_due, $number_of_holidays_principle) . " </span> ";
$remaining_holidays_div .= "<span>" . sprintf(gettext('There have so far been taken %1$s holidays.'), $number_of_holidays_taken) . " </span> ";
$remaining_holidays_div .= "<span>" . sprintf(gettext('%1$s remaining vacation days in %2$s have already been applied for.'), $number_of_remaining_holidays_submitted, ($year + 1)) . " </span> ";
$remaining_holidays_div .= "<span>" . sprintf(gettext('There are still %1$s vacation days available.'), $number_of_remaining_holidays_left) . " </span> ";
$remaining_holidays_div .= "</p>";
$remaining_holidays_div .= "</div>";
/**
 * @TODO: An option to automatically mark vacation days as 'remaining holidays'.
 * @todo Move Select into database handler!
 */
$sql_query = 'SELECT * FROM `absence` WHERE `employee_key` = :employee_key and (Year(`start`) = :year or Year(`end`) =:year2) ORDER BY `start` DESC';
$result = \database_wrapper::instance()->run($sql_query, array('employee_key' => $employee_key, 'year' => $year, 'year2' => $year));
$tablebody = '';
while ($row = $result->fetch(\PDO::FETCH_OBJ)) {
    $absence = new \PDR\Roster\Absence(
            $row->employee_key,
            new \DateTime($row->start), new DateTime($row->end),
            $row->days, $row->reason_id, $row->comment, $row->approval, $row->user,
            new \DateTime($row->timestamp)
    );
    $collectionOfOverlappingAbsences = \PDR\Database\AbsenceDatabaseHandler::findOverlappingAbsences($employee_key, $row->start, $row->end);

    /**
     * @todo <p lang=de>Wenn jemand kündigt, so können Abwesenheiten bleiben, die nach der Kündigung liegen.
     *   In dem Fall könnte man eine Warnung über user_dialog senden.</p>
     */
    $html_form_id = "change_absence_entry_" . $row->start;
    $tablebody .= "<tr class='absence_row' data-approval='$row->approval' style='height: 1em;'>"
            . "<form accept-charset='utf-8' method=POST id='$html_form_id'>"
            . "\n";
    /*
     * start
     */
    $tablebody .= "<td><div id=start_out_$row->start>";
    $tablebody .= date('d.m.Y', strtotime($row->start)) . "</div>";
    $tablebody .= "<input id=start_in_$row->start style='display: none;' type=date name='beginn' value=" . date('Y-m-d', strtotime($row->start)) . " form='$html_form_id'> ";
    $tablebody .= "<input style='display: none;' type=date name='start_old' value=" . date('Y-m-d', strtotime($row->start)) . " form='$html_form_id'> ";
    $tablebody .= \PDR\Output\HTML\AbsenceHtmlBuilder::buildInfoOverlap($collectionOfOverlappingAbsences);
    $tablebody .= "</td>\n";
    /*
     * end
     */
    $tablebody .= "<td><div id=end_out_$row->start form='$html_form_id'>";
    $tablebody .= date('d.m.Y', strtotime($row->end)) . "</div>";
    $tablebody .= "<input id=end_in_$row->start style='display: none;' type=date name='ende' value=" . date('Y-m-d', strtotime($row->end)) . " form='$html_form_id'> ";
    if ($session->user_has_privilege(\sessions::PRIVILEGE_CREATE_ABSENCE)) {
        $tablebody .= \PDR\Output\HTML\AbsenceHtmlBuilder::buildButtonCutOverlap($collectionOfOverlappingAbsences, $absence, $workforce->get_employee_object($employee_key));
    }
    $tablebody .= "</td>\n";

    /*
     * reason
     */
    $tablebody .= "<td><div id='reason_out_$row->start' data-reason_id='$row->reason_id'>" . \PDR\Utility\AbsenceUtility::getReasonStringLocalized($row->reason_id) . "</div>";
    $htmlIdReason = "reason_in_$row->start";
    $tablebody .= \PDR\Output\HTML\AbsenceHtmlBuilder::buildReasonInputSelect($row->reason_id, $htmlIdReason, $html_form_id, $session);
    $tablebody .= "</td>\n";
    /*
     * comment
     */
    $tablebody .= "<td><div id=comment_out_$row->start>$row->comment</div>";
    $tablebody .= "<input id=comment_in_$row->start style='display: none;' type=text name='comment' value='$row->comment' form='$html_form_id'> ";
    $tablebody .= "</td>\n";
    /*
     * days
     */
    $tablebody .= "<td>$row->days</td>\n";
    $tablebody .= "<td><span id=absence_out_$row->start data-absence_approval=$row->approval>" . localization::gettext($row->approval) . "</span>";
    $html_id = "absence_in_$row->start";
    $tablebody .= \PDR\Output\HTML\AbsenceHtmlBuilder::buildApprovalInputSelect($row->approval, $html_id, $html_form_id, $session);
    $tablebody .= "</td>\n";
    if ($session->user_has_privilege(sessions::PRIVILEGE_CREATE_ABSENCE)) {
        $tablebody .= "<td style='font-size: 1em; height: 1em'>\n"
                . "<input hidden name='employee_key' value='$employee_key' form='$html_form_id'>\n"
                . \PDR\Output\HTML\AbsenceHtmlBuilder::buildButtonSubmitDelete($row)
                . \PDR\Output\HTML\AbsenceHtmlBuilder::buildButtonCancelEdit($row)
                . \PDR\Output\HTML\AbsenceHtmlBuilder::buildButtonEdit($row)
                . \PDR\Output\HTML\AbsenceHtmlBuilder::buildButtonSubmitSave($row)
                . "";
        $tablebody .= "</td>\n";
    }
    $tablebody .= "</form>\n"
            . "</tr>\n";
}



//Here beginns the output:
require \PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require \PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';

echo "<div id=main-area>\n";

$user_dialog = new user_dialog();
echo $user_dialog->build_messages();
echo \form_element_builder::build_html_select_year($year);
echo \build_html_navigation_elements::build_select_employee($employee_key, $workforce->List_of_employees);

echo "<table id=absence_table class='table_with_underline_rows'>" . PHP_EOL;
/*
 * Head
 */
echo "<thead>\n";
echo "<tr><th>" . gettext('Start') . "</th><th>" . gettext('End') . "</th><th>" . gettext('Reason') . "</th><th>" . gettext('Comment') . "</th><th>" . gettext('Days') . "</th><th>" . gettext('Approval') . "</th></tr>\n";
/*
 * Input with calculation of the saldo via javascript.
 */

if ($session->user_has_privilege(\sessions::PRIVILEGE_CREATE_ABSENCE)) {

    echo "<tr class=no_print id=input_line_new>\n"
    . "<form accept-charset='utf-8' method=POST id='new_absence_entry'>\n";
    echo "<td>\n"
    . "<input type=hidden name=employee_key value=$employee_key form='new_absence_entry'>\n";
    echo "<input type=date class=datepicker onchange=updateTage() onblur=checkUpdateTage() id=beginn name=beginn value=" . date("Y-m-d") . " form='new_absence_entry'>";
    echo "</td>\n";
    echo "<td>\n";
    echo "<input type=date class=datepicker onchange=updateTage() onblur=checkUpdateTage() id=ende name=ende value=" . date("Y-m-d") . " form='new_absence_entry'>";
    echo "</td>\n";
    echo "<td>" . \PDR\Output\HTML\AbsenceHtmlBuilder::buildReasonInputSelect(\PDR\Utility\AbsenceUtility::REASON_VACATION, 'new_absence_reason_id_select', 'new_absence_entry', $session) . "</td>" . PHP_EOL;
    echo "<td><input type='text' id='new_absence_input_comment' name='comment' form='new_absence_entry'></td>\n";
    echo "<td id=tage title='Feiertage werden anschließend automatisch vom Server abgezogen.'>1</td>\n";
    echo "<td>" . \PDR\Output\HTML\AbsenceHtmlBuilder::buildApprovalInputSelect('not_yet_approved', 'new_absence_approval_select', 'new_absence_entry', $session) . "</td>" . PHP_EOL;
    echo "<td>\n";
    echo "<button type=submit id=save_new class=no_print name=command value='insert_new' form='new_absence_entry'>" . gettext('Save') . "</button>";
    echo "</td>\n";
    echo "</tr>\n";
    echo "<tr style='display: none; background-color: #BDE682;' id=warning_message_tr>\n";
    echo "<td id=warning_message_td colspan='5'>\n";
    echo "Foo!";
    echo "</td>\n";
    echo "</form>\n";
    echo "</tr>\n";
}
echo "</thead>\n";
//Ausgabe
echo "<tbody>\n"
 . "$tablebody"
 . "</tbody>\n";
echo "</table>\n";
echo "</div>\n";
echo "$remaining_holidays_div";
require \PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php';
?>
</body>
</html>
