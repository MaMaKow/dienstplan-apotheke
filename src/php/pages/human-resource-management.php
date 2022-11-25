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
/*
 * TODO: rename this file workforce-management. I think, I like this term much more
 */
human_resource_management::write_employee_data_to_database(); //$success = write_employee_data_to_database();
$employee_id = user_input::convert_post_empty_to_php_null(user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_object']->employee_id));
create_cookie('employee_id', $employee_id, 1);

$Worker = human_resource_management::read_employee_data_from_database($employee_id);
$workforce = new workforce();
/**
 * add a "new employee" to the list. This can be used as a template to create a new employee.
 */
$List_of_employees = $workforce->List_of_employees;
$List_of_employees[] = new \employee(NULL, gettext("new employee"), null, 40, 30, null, null, null, null, 28);
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
$session->exit_on_missing_privilege('create_employee');
/*
 * TODO: Test what it looks like, when the input fields are in front of their labels, not behind them.
 */
?>
<div class="centered_form_div">
    <?= build_html_navigation_elements::build_select_employee($employee_id, $List_of_employees) ?>
    <form accept-charset='utf-8' method='POST' id='human_resource_management'>

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
            <?php echo human_resource_management::make_radio_profession_list($Worker["profession"]) ?>
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
        <?php echo human_resource_management::make_radio_branch_list($Worker["branch"]); ?>
        <fieldset>
            <legend><?= gettext("Abilities") ?></legend>
            <?php echo human_resource_management::make_checkbox_ability("goods_receipt", "Wareneingang", $Worker["goods_receipt"]); ?>
            <br>
            <?php echo human_resource_management::make_checkbox_ability("compounding", "Rezeptur", $Worker["compounding"]); ?>
        </fieldset>
        <fieldset>
            <legend><?= gettext("Employment") ?></legend>
            <p>
                <label for="start_of_employment"><?= gettext("Start of employment") ?>: </label>
                <input type='date' id="start_of_employment" name='start_of_employment' value="<?php echo $Worker["start_of_employment"] ?>">
                <br>
                <label for="end_of_employment"><?= gettext("End of employment") ?>:  </label>
                <input type='date' name='end_of_employment' id="end_of_employment" value="<?php echo $Worker["end_of_employment"] ?>">
                <img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/information.svg"
                     class="inline-image"
                     title="<?= gettext("This is the last day the employee worked.") ?>">
            </p>
        </fieldset>

        <input type=submit id=save_new class='no_print' name=submitStunden value='<?= gettext("Register") ?>' form='human_resource_management'>

    </form>
</div>
<?php
/*
 * TODO: Add a delete employee button.
 * The deletion of employees is cascading to delete also the associated user.
 */
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php';
?>
</body>
</html>
