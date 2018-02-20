<?php
require 'default.php';
require 'human-resource-management.php';
write_employee_data_to_database(); //$success = write_employee_data_to_database();
require 'db-lesen-mitarbeiter.php';
if (filter_has_var(INPUT_POST, "worker_id")) {
    $employee_id = filter_input(INPUT_POST, "worker_id", FILTER_VALIDATE_INT);
} elseif (filter_has_var(INPUT_POST, "employee_id")) {
    $employee_id = filter_input(INPUT_POST, "employee_id", FILTER_VALIDATE_INT);
} elseif (filter_has_var(INPUT_GET, "employee_id")) {
    $employee_id = filter_input(INPUT_GET, "employee_id", FILTER_VALIDATE_INT);
} elseif (filter_has_var(INPUT_COOKIE, "employee_id")) {
    $employee_id = filter_input(INPUT_COOKIE, "employee_id", FILTER_VALIDATE_INT);
} else {
    $employee_id = 1;
}
if (isset($employee_id)) {
    create_cookie('employee_id', $employee_id, 30);
}

$Worker = read_employee_data_from_database($employee_id);

require 'head.php';
require 'navigation.php';
require 'src/php/pages/menu.php';
if (!$session->user_has_privilege('create_employee')) {
    echo build_warning_messages("", ["Die notwendige Berechtigung zum Erstellen von Mitarbeitern fehlt. Bitte wenden Sie sich an einen Administrator."]);
    die();
}

echo build_select_employee($employee_id, $List_of_employees);
?>
<form method='POST' id='human_resource_management'>

    <p>
        <label for="worker_id">VK: </label>
        <input type='text' name='worker_id' id="worker_id" value="<?php echo $Worker["worker_id"] ?>">
        <label for="last_name">Nachname: </label>
        <input type='text' name='last_name' id="last_name" value="<?php echo $Worker["last_name"] ?>">
        <label for="first_name">Vorname: </label>
        <input type='text' name='first_name' id="first_name" value="<?php echo $Worker["first_name"] ?>">
    </p><p>
        <?php echo make_radio_profession_list($Worker["profession"]) ?>
    </p><p>
        <label for="working_hours">Stunden: </label>
        <input type='number' min='0' step='any' name='working_hours' id='working_hours' value='<?php echo $Worker["working_hours"] ?>'>
        <label for="working_week_hours">Arbeitswochenstunden: </label>
        <input type='number' min='0' step='any' name='working_week_hours' id="working_week_hours" value="<?php echo $Worker["working_week_hours"] ?>">
        <label for="lunch_break_minutes">Mittag: </label>
        <input type='number' min='0' step='any' name='lunch_break_minutes' id="lunch_break_minutes" value="<?php echo $Worker["lunch_break_minutes"] ?>">
    </p><p>
        <label for="holidays"><?= gettext("vacation days"); ?>: </label>
        <input type='number' min='0' step='any' name='holidays' id="holidays" value="<?php echo $Worker["holidays"] ?>">
    </p><p>
        <?php echo make_radio_branch_list($Worker["branch"]); ?>
    </p><p>
        <?php echo make_checkbox_ability("goods_receipt", "Wareneingang", $Worker["goods_receipt"]); ?>
        <?php echo make_checkbox_ability("compounding", "Rezeptur", $Worker["compounding"]); ?>
    </p><p>
        <label for="start_of_employment">Beschäftigungsbeginn: </label>
        <input type='date' id="start_of_employment" name='start_of_employment' value="<?php echo $Worker["start_of_employment"] ?>">
        <label for="end_of_employment">Beschäftigungsende:  </label>
        <input type='date' name='end_of_employment' id="end_of_employment" value="<?php echo $Worker["end_of_employment"] ?>">
    </p><p>

        <input type=submit id=save_new class='no-print' name=submitStunden value='Eintragen' form='human_resource_management'>
    </p>

</form>
</body>
</html>
