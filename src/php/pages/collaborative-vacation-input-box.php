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
require_once "../../../default.php";
$workforce = new workforce();

if (filter_has_var(INPUT_GET, 'absence_details_json')) {
    /*
     * An existing entry will be edited:
     */
    $absence_details_json_unsafe = filter_input(INPUT_GET, 'absence_details_json', FILTER_UNSAFE_RAW);
    $Absence_details_unsafe = json_decode($absence_details_json_unsafe, TRUE);
    $filters = array(
        'employeeKey' => FILTER_SANITIZE_NUMBER_INT,
        'reasonId' => FILTER_SANITIZE_NUMBER_INT,
        'comment' => FILTER_SANITIZE_SPECIAL_CHARS,
        'start' => FILTER_SANITIZE_SPECIAL_CHARS,
        'end' => FILTER_SANITIZE_SPECIAL_CHARS,
        'approval' => FILTER_SANITIZE_SPECIAL_CHARS,
    );
    $Absence_details = filter_var_array($Absence_details_unsafe, $filters);
    $dateStartObject = new DateTime($Absence_details['start']);
    $dateEndObject = new DateTime($Absence_details['end']);
    $employeeKey = $Absence_details['employeeKey'];
    $employeeObject = $workforce->get_employee_object($employeeKey);
    $days = PDR\Utility\AbsenceUtility::calculateEmployeeAbsenceDays($dateStartObject, $dateEndObject, $employeeObject);

    $absence = new PDR\Roster\Absence(
            $employeeKey,
            $dateStartObject,
            $dateEndObject,
            $days,
            $Absence_details['reasonId'],
            $Absence_details['comment'],
            $Absence_details['approval'],
            $session->getUserName(),
            new DateTime()
    );
    unset($Absence_details_unsafe);
    unset($absence_details_json_unsafe);
    $Absence_details['mode'] = "edit";
} elseif (filter_has_var(INPUT_GET, 'highlight_details_json')) {
    /*
     * A new entry will be created:
     */
    $highlight_details_json_unsafe = filter_input(INPUT_GET, 'highlight_details_json', FILTER_UNSAFE_RAW);
    $Highlight_details_unsafe = json_decode($highlight_details_json_unsafe, TRUE);
    $filters = array(
        //'highlight_absence_create_from_date_sql' => FILTER_SANITIZE_SPECIAL_CHARS,
        //'highlight_absence_create_to_date_sql' => FILTER_SANITIZE_SPECIAL_CHARS,
        'date_range_min' => FILTER_SANITIZE_SPECIAL_CHARS,
        'date_range_max' => FILTER_SANITIZE_SPECIAL_CHARS,
    );
    $Highlight_details = filter_var_array($Highlight_details_unsafe, $filters);
    $employeeKey = user_input::get_variable_from_any_input('employee_key', FILTER_SANITIZE_NUMBER_INT, $workforce->get_default_employee_key());
    $dateStartObject = new DateTime($Highlight_details['date_range_min']);
    $dateEndObject = new DateTime($Highlight_details['date_range_max']);
    $employeeObject = $workforce->get_employee_object($employeeKey);
    $days = PDR\Utility\AbsenceUtility::calculateEmployeeAbsenceDays(clone $dateStartObject, clone $dateEndObject, $employeeObject);
    $absence = new PDR\Roster\Absence($employeeKey,
            $dateStartObject, $dateEndObject, $days,
            \PDR\Utility\AbsenceUtility::REASON_VACATION,
            '',
            "not_yet_approved",
            $session->getUserName(),
            new DateTime()
    );
    $Absence_details['mode'] = "create";
} else {
    $employeeKey = user_input::get_variable_from_any_input('employee_key', FILTER_SANITIZE_NUMBER_INT, $workforce->get_default_employee_key());
}
?>
<form accept-charset='utf-8' id="inputBoxForm" method="POST">
    <p><?= gettext("Employee") ?><br><select name="employee_key" id="employeeKeySelect"></p>
    <?php
    if ($session->user_has_privilege('create_absence')) {
        /*
         * The user is allowed to create an absence for anyone:
         */
        $workforce = new workforce($dateStartObject->format("Y-m-d"), $dateEndObject->format("Y-m-d"));
        foreach ($workforce->List_of_employees as $employeeKeyOption => $employee_object) {
            if ($employeeKeyOption == $employeeKey) {
                $option_selected = "selected";
            } else {
                $option_selected = "";
            }
            echo "<option id='employee_key_option_$employeeKeyOption' value='$employeeKeyOption' $option_selected>";
            echo "$employeeKeyOption $employee_object->last_name";
            echo "</option>\n";
        }
    } elseif ($session->user_has_privilege('request_own_absence') and "" === $employeeKey) {
        /**
         * The user is allowed to create an absence for himself and
         * This absence is new.
         * @todo <p>This has to be tested very intensively!</p>
         * CAVE! This get_employee_key() might be empty.
         */
        $session_employee_key = $_SESSION['user_object']->get_employee_key();
        echo "<option id='employee_key_option_" . $session_employee_key . "' value=" . $session_employee_key . ">";
        echo $session_employee_key . " " . $workforce->List_of_employees[$session_employee_key]->last_name;
        echo "</option>\n";
    } else {
        /*
         * The user is NOT allowed to create any absence
         * or this is an existing absence.
         */
        echo "<option id='employee_key_option_" . $employeeKey . "' value=" . $employeeKey . ">";
        echo $employeeKey . " " . $workforce->List_of_employees[$employeeKey]->last_name;
        echo "</option>\n";
    }
    ?>
</select>
<!--
<img src="" style="width: 0" alt=""
     onerror="prefill_input_box_form(); this.parentNode.removeChild(this);"
     data-comment="This element is necessary to allow interaction of javascript with this element. After the execution, it is removed."
     />
-->
<p><?= gettext("Start") ?><br><input type="date" id="inputBoxFormStartDate" name="start_date" value="<?= $absence->getStart()->format("Y-m-d") ?>"></p>
<p><?= gettext("End") ?><br><input type="date" id="inputBoxFormEndDate" name="end_date" value="<?= $absence->getEnd()->format("Y-m-d") ?>"></p>
<p><?= gettext("Reason") ?><br><?= PDR\Output\HTML\AbsenceHtmlBuilder::buildReasonInputSelect($absence->getReasonId(), 'absenceReasonInputSelect', 'inputBoxForm', $session) ?></p>
<p><?= gettext("Comment") ?><br><input type="text" id="inputBoxFormComment" name="comment" value="<?= $absence->getComment() ?>"></p>
<?php
if ($session->user_has_privilege('create_absence') and "edit" === $Absence_details['mode']) {
    echo "<p>" . gettext("Approval") . "<br>";
    echo "<select id='input_box_form_approval' name='approval'>";

    foreach (\PDR\Utility\AbsenceUtility::$ListOfApprovalStates as $approval_state) {
        //TODO: Remove all occurences of "disapprove" and change them to "deny".
        if ($approval_state == $absence->getApproval()) {
            echo "<option value='$approval_state' selected>" . localization::gettext($approval_state) . "</option>\n";
        } else {
            echo "<option value='$approval_state'>" . localization::gettext($approval_state) . "</option>\n";
        }
    }
    echo "</select>";
    echo "</p>";
}
if (
        $session->user_has_privilege('create_absence')
        or ( $session->user_has_privilege('request_own_absence')
        and ( $_SESSION['user_object']->get_employee_key() === $employeeKey
        or "" === $employeeKey)
        )
) {
    ?>
    <p>
        <button type="submit" value="save" name="command" class="button-tight"><?= gettext("Save") ?></button>
        <?php if ("edit" === $Absence_details['mode']) { ?>
            <button type="submit" value="delete" name="command" id="inputBoxFormButtonDelete" class="button-tight"><?= gettext("Delete") ?></button>
        <?php } ?>
    </p>
<?php } ?>

<input type="hidden" id="employee_key_old" name="employee_key_old" value="<?= $absence->getEmployeeKey() ?>">
<input type="hidden" id="inputBoxFormStartDateOld" name="start_date_old" value="<?= $absence->getStart()->format("Y-m-d") ?>">
</form>
<a title="<?= gettext("Close"); ?>" href="#" onclick="remove_form_div()">
    <span id="removeFormDivSpan">
        x
    </span>
</a>
