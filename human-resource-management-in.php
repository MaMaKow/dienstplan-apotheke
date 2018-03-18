<?php
require 'default.php';
require 'human-resource-management.php';
write_employee_data_to_database(); //$success = write_employee_data_to_database();
require 'db-lesen-mitarbeiter.php';
$employee_id = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_employee_id']);
create_cookie('employee_id', $employee_id, 1);

$Worker = read_employee_data_from_database($employee_id);

require 'head.php';
require 'navigation.php';
require 'src/php/pages/menu.php';
if (!$session->user_has_privilege('create_employee')) {
    echo build_warning_messages("", ["Die notwendige Berechtigung zum Erstellen von Mitarbeitern fehlt. Bitte wenden Sie sich an einen Administrator."]);
    die();
}
/*
 * TODO: Test what it looks like, when the input fields are in front of their labels, not behind them.
 */
?>
<div class="centered_form_div">
    <?= build_select_employee($employee_id, $List_of_employees) ?>
    <form method='POST' id='human_resource_management'>

        <fieldset>
            <legend><?= gettext("Personal Data") ?>:</legend>
            <label for="employee_id"><?= gettext("Employee ID") ?>: </label>
            <input type='text' name='employee_id' id="employee_id" value="<?php echo $Worker["employee_id"] ?>">
            <br>
            <label for="last_name"><?= gettext("Last name") ?>: </label>
            <input type='text' name='last_name' id="last_name" value="<?php echo $Worker["last_name"] ?>">
            <br>
            <label for="first_name"><?= gettext("First name") ?>: </label>
            <input type='text' name='first_name' id="first_name" value="<?php echo $Worker["first_name"] ?>">
        </fieldset>
        <p>
            <?php echo make_radio_profession_list($Worker["profession"]) ?>
        </p>
        <fieldset class="nowrap">
            <legend><?= gettext("Working hours") ?>:</legend>
            <p >
                <label for="working_hours"><?= gettext("Working hours") ?>: </label>
                <input type='number' min='0' step='any' name='working_hours' id='working_hours' value='<?php echo $Worker["working_hours"] ?>'>
                <span class="form_input_unit">h</span>
                <br>
                <label for="working_week_hours"><?= gettext("Working week hours") ?>: </label>
                <input type='number' min='0' step='any' name='working_week_hours' id="working_week_hours" value="<?php echo $Worker["working_week_hours"] ?>">
                <span class="form_input_unit">h</span>
                <br>
                <label for="lunch_break_minutes"><?= gettext("Lunch break") ?>: </label>
                <input type='number' min='0' step='any' name='lunch_break_minutes' id="lunch_break_minutes" value="<?php echo $Worker["lunch_break_minutes"] ?>">
                <span class="form_input_unit">min</span>
                <br>
                <label for="holidays"><?= gettext("Vacation days"); ?>: </label>
                <input type='number' min='0' step='any' name='holidays' id="holidays" value="<?php echo $Worker["holidays"] ?>">
                <span class="form_input_unit">d</span>
            </p>
        </fieldset>
        <?php echo make_radio_branch_list($Worker["branch"]); ?>
        <fieldset>
            <legend><?= gettext("Abilities") ?></legend>
            <?php echo make_checkbox_ability("goods_receipt", "Wareneingang", $Worker["goods_receipt"]); ?>
            <br>
            <?php echo make_checkbox_ability("compounding", "Rezeptur", $Worker["compounding"]); ?>
        </fieldset>
        <fieldset>
            <legend><?= gettext("Employment") ?></legend>
            <p>
                <label for="start_of_employment"><?= gettext("Start of employment") ?>: </label>
                <input type='date' id="start_of_employment" name='start_of_employment' value="<?php echo $Worker["start_of_employment"] ?>">
                <br>
                <label for="end_of_employment"><?= gettext("End of employment") ?>:  </label>
                <input type='date' name='end_of_employment' id="end_of_employment" value="<?php echo $Worker["end_of_employment"] ?>">
            </p>
        </fieldset>

        <input type=submit id=save_new class='no-print' name=submitStunden value='<?= gettext("Register") ?>' form='human_resource_management'>

    </form>
</div>
</body>
</html>
