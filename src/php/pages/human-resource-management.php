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
if ($session->user_has_privilege(sessions::PRIVILEGE_ADMINISTRATION)) {
    human_resource_management::write_employee_data_to_database(); //$success = write_employee_data_to_database();
}
$workforce = new PDR\Workforce\Workforce();
$employee_key = user_input::convert_post_empty_to_php_null(user_input::get_variable_from_any_input('employee_key', FILTER_SANITIZE_NUMBER_INT, $workforce->getDefaultEmployeeKey()));
\PDR\Utility\GeneralUtility::createCookie('employee_key', $employee_key, 1);
if (isset($_POST) && !empty($_POST)) {
    // POST data has been submitted
    $location = PDR_HTTP_SERVER_APPLICATION_PATH . 'src/php/pages/human-resource-management.php' . "?&employee_key=$employee_key";
    header('Location:' . $location);
    die("<p>Redirect to: <a href=$location>$location</a></p>");
}

try {
    $employee = $workforce->getEmployeeObject($employee_key);
} catch (Exception $exception) {
    $employee = $workforce->getEmptyEmployee();
}
/**
 * add a "new employee" to the list. This can be used as a template to create a new employee.
 */
$List_of_employees = $workforce->getListOfEmployees();
$List_of_employees[] = new PDR\Workforce\Employee(NULL, gettext("new employee"), null, 40, 30, null, false, false, null, null, null, 28);
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
$session->exit_on_missing_privilege('create_employee');
/**
 * @TODO: Test what it looks like, when the input fields are in front of their labels, not behind them.
 */
?>
<div class="centered-form-div">
    <?= build_html_navigation_elements::build_select_employee($employee_key, $List_of_employees) ?>
    <form accept-charset='utf-8' method='POST' id='human_resource_management'>

        <fieldset>
            <input type='hidden' name='employee_key' id="employee_key" value="<?= $employee ? $employee->getEmployeeKey() : "" ?>">
            <legend><?= gettext("Personal Data") ?>:</legend>
            <label for="last_name"><?= gettext("Last name") ?>: </label>
            <input type='text' name='last_name' id="last_name" value="<?= $employee->getLastName() ? $employee->getLastName() : "" ?>">
            <br>
            <label for="first_name"><?= gettext("First name") ?>: </label>
            <input type='text' name='first_name' id="first_name" value="<?= $employee->getFirstName() ?>">
        </fieldset>
        <p>
            <?= human_resource_management::make_radio_profession_list($employee->getProfession()) ?>
        </p>
        <fieldset class="no-wrap">
            <legend><?= gettext("Working hours") ?>:</legend>
            <p >
                <label for="working_week_hours"><?= gettext("Working hours") ?>: </label>
                <input type='number' required min='0' step='any' name='working_week_hours' id="working_week_hours" value="<?= $employee->getWorkingWeekHours() ?>">
                <span class="form-input-unit">h</span>
                <br>
                <label for="lunch_break_minutes"><?= gettext("Lunch break") ?>: </label>
                <input type='number' required min='0' step='any' name='lunch_break_minutes' id="lunch_break_minutes" value="<?= $employee->getLunchBreakMinutes() ?>">
                <span class="form-input-unit">min</span>
                <br>
                <label for="holidays"><?= gettext("Vacation days"); ?>: </label>
                <input type='number' required min='0' step='any' name='holidays' id="holidays" value="<?= $employee->getHolidays() ?>">
                <span class="form-input-unit">d</span>
            </p>
        </fieldset>
        <?= human_resource_management::make_radio_branch_list($employee->getPrincipleBranchId()); ?>
        <fieldset>
            <legend><?= gettext("Abilities") ?></legend>
            <?= human_resource_management::make_checkbox_ability("goods_receipt", gettext("Goods receipt"), $employee->canDoGoodsReceipt()); ?>
            <br>
            <?= human_resource_management::make_checkbox_ability("compounding", gettext("Compounding"), $employee->canDoCompounding()); ?>
        </fieldset>
        <fieldset>
            <legend><?= gettext("Employment") ?></legend>
            <p>
                <label for="start_of_employment"><?= gettext("Start of employment") ?>: </label>
                <input type='date' id="start_of_employment" name='start_of_employment' value="<?= $employee->getStartOfEmployment() ?>">
                <br>
                <label for="end_of_employment"><?= gettext("End of employment") ?>:  </label>
                <input type='date' name='end_of_employment' id="end_of_employment" value="<?= $employee->getEndOfEmployment() ?>">
                <img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/information.svg"
                     class="inline-image"
                     title="<?= gettext("This is the last day the employee worked.") ?>">
            </p>
        </fieldset>

        <input type=submit id=save_new class='no-print' name=save-employee value='<?= gettext("Save Employee") ?>' form='human_resource_management'>

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
