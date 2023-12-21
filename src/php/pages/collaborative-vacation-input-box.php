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
        'comment' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'start' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'end' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'approval' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
    );
    $Absence_details = filter_var_array($Absence_details_unsafe, $filters);
    unset($Absence_details_unsafe);
    unset($absence_details_json_unsafe);
    $Absence_details['mode'] = "edit";
    $employee_key = $Absence_details['employeeKey'];
} elseif (filter_has_var(INPUT_GET, 'highlight_details_json')) {
    /*
     * A new entry will be created:
     */
    $highlight_details_json_unsafe = filter_input(INPUT_GET, 'highlight_details_json', FILTER_UNSAFE_RAW);
    $Highlight_details_unsafe = json_decode($highlight_details_json_unsafe, TRUE);
    $filters = array(
        //'highlight_absence_create_from_date_sql' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        //'highlight_absence_create_to_date_sql' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'date_range_min' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'date_range_max' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
    );
    $Highlight_details = filter_var_array($Highlight_details_unsafe, $filters);
    $employee_key = user_input::get_variable_from_any_input('employee_key', FILTER_SANITIZE_NUMBER_INT, $workforce->get_default_employee_key());
    $Absence_details['employeeKey'] = $employee_key;
    $Absence_details['reasonId'] = absence::REASON_VACATION;
    $Absence_details['start'] = date('Y-m-d', $Highlight_details['date_range_min']);
    $Absence_details['end'] = date('Y-m-d', $Highlight_details['date_range_max']);
    $Absence_details['comment'] = '';
    $Absence_details['mode'] = "create";
} else {
    $employee_key = user_input::get_variable_from_any_input('employee_key', FILTER_SANITIZE_NUMBER_INT, $workforce->get_default_employee_key());
}
?>
<form accept-charset='utf-8' id="input_box_form" method="POST">
    <p><?= gettext("Employee") ?><br><select name="employee_key" id="employee_key_select"></p>
    <?php
    if ($session->user_has_privilege('create_absence')) {
        /*
         * The user is allowed to create an absence for anyone:
         */
        foreach ($workforce->List_of_employees as $employee_key_option => $employee_object) {
            if ($employee_key_option == $employee_key) {
                $option_selected = "selected";
            } else {
                $option_selected = "";
            }
            echo "<option id='employee_key_option_$employee_key_option' value='$employee_key_option' $option_selected>";
            echo "$employee_key_option $employee_object->last_name";
            echo "</option>\n";
        }
    } elseif ($session->user_has_privilege('request_own_absence') and "" === $employee_key) {
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
        echo "<option id='employee_key_option_" . $employee_key . "' value=" . $employee_key . ">";
        echo $employee_key . " " . $workforce->List_of_employees[$employee_key]->last_name;
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
<p><?= gettext("Start") ?><br><input type="date" id="input_box_form_start_date" name="start_date" value="<?= $Absence_details['start'] ?>"></p>
<p><?= gettext("End") ?><br><input type="date" id="input_box_form_end_date" name="end_date" value="<?= $Absence_details['end'] ?>"></p>
<p><?= gettext("Reason") ?><br><?= PDR\Output\HTML\AbsenceHtmlBuilder::buildReasonInputSelect($Absence_details['reasonId'], 'absence_reason_input_select', 'input_box_form') ?></p>
<p><?= gettext("Comment") ?><br><input type="text" id="input_box_form_comment" name="comment" value="<?= $Absence_details['comment'] ?>"></p>
<?php
if ($session->user_has_privilege('create_absence') and "edit" === $Absence_details['mode']) {
    echo "<p>" . gettext("Approval") . "<br>";
    echo "<select id='input_box_form_approval' name='approval'>";

    foreach (absence::$List_of_approval_states as $approval_state) {
        //TODO: Remove all occurences of "disapprove" and change them to "deny".
        if ($approval_state == $Absence_details['approval']) {
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
        and ( $_SESSION['user_object']->get_employee_key() === $employee_key
        or "" === $employee_key)
        )
) {
    ?>
    <p>
        <button type="submit" value="save" name="command" class="button_tight"><?= gettext("Save") ?></button>
        <?php if ("edit" === $Absence_details['mode']) { ?>
            <button type="submit" value="delete" name="command" id="input_box_form_button_delete" class="button_tight"><?= gettext("Delete") ?></button>
        <?php } ?>
    </p>
<?php } ?>

<input type="hidden" id="employee_key_old" name="employee_key_old" value="<?= $Absence_details['employeeKey'] ?>">
<input type="hidden" id="input_box_form_start_date_old" name="start_date_old" value="<?= $Absence_details['start'] ?>">
</form>
<a title="<?= gettext("Close"); ?>" href="#" onclick="remove_form_div()">
    <span id="remove_form_div_span">
        x
    </span>
</a>
