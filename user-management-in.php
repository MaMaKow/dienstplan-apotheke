<?php
require 'default.php';
require 'db-lesen-mitarbeiter.php';
require 'db-lesen-mandant.php';
print_debug_variable($_POST);
if (filter_has_var(INPUT_POST, "user_employee_id")){
    $employee_id = filter_input(INPUT_POST, "user_employee_id", FILTER_VALIDATE_INT);    
} elseif (filter_has_var(INPUT_GET, "auswahl_mitarbeiter")) {
    $employee_id = filter_input(INPUT_GET, "auswahl_mitarbeiter", FILTER_VALIDATE_INT);
} elseif (filter_has_var(INPUT_COOKIE, "auswahl_mitarbeiter")) {
    $employee_id = filter_input(INPUT_COOKIE, "auswahl_mitarbeiter", FILTER_VALIDATE_INT);
} else {
    $employee_id = $_SESSION['user_employee_id'];
}
if (isset($employee_id)) {
    create_cookie('employee_id', $employee_id, 30);
}

function read_user_data_from_database($employee_id) {
    global $Mitarbeiter;
    $abfrage = "SELECT * FROM `users` WHERE `employee_id` = '$employee_id'";
    $ergebnis = mysqli_query_verbose($abfrage);
    while ($row = mysqli_fetch_object($ergebnis)) {
        $User["employee_id"] = $row->employee_id;
        $User["user_name"] = $row->user_name;
        $User["email"] = $row->email;
        $User["status"] = $row->status;
        $User["last_name"] = $Mitarbeiter[$row->employee_id];
    }
    return $User;
}
$User = read_user_data_from_database($employee_id);

require 'head.php';
require 'navigation.php';
require 'src/php/pages/menu.php';
if(!$session->user_has_privilege('administration')){
    echo build_warning_messages("",["Die notwendige Berechtigung zum Erstellen von Mitarbeitern fehlt. Bitte wenden Sie sich an einen Administrator."]);
    die();
}

echo build_select_employee($employee_id);
?>
<form method='POST' id='user_management'>

    <p>
        <label for="employee_id">VK: </label>
        <input type='text' name='employee_id' id="employee_id" value="<?php echo $User["employee_id"] ?>">
        <label for="last_name">Nachname: </label>
        <input type='text' name='last_name' id="last_name" value="<?php echo $User["last_name"] ?>">
    </p><p>
        <?php echo make_radio_profession_list($User["profession"]) ?>
    </p><p>
        <label for="working_hours">Stunden: </label>
        <input type='number' min='0' step='any' name='working_hours' id='working_hours' value='<?php echo $User["working_hours"] ?>'>
        <label for="working_week_hours">Arbeitswochenstunden: </label>
        <input type='number' min='0' step='any' name='working_week_hours' id="working_week_hours" value="<?php echo $User["working_week_hours"] ?>">
        <label for="lunch_break_minutes">Mittag: </label>
        <input type='number' min='0' step='any' name='lunch_break_minutes' id="lunch_break_minutes" value="<?php echo $User["lunch_break_minutes"] ?>">
    </p><p>
        <label for="holidays">Urlaubstage: </label>
        <input type='number' min='0' step='any' name='holidays' id="holidays" value="<?php echo $User["holidays"] ?>">
    </p><p>
        <?php echo make_radio_branch_list($User["branch"]); ?>
    </p><p>
        <?php echo make_checkbox_ability("goods_receipt", "Wareneingang", $User["goods_receipt"]); ?>
        <?php echo make_checkbox_ability("compounding", "Rezeptur", $User["compounding"]); ?>
    </p><p>
        <label for="start_of_employment">Beschäftigungsbeginn: </label>
        <input type='date' id="start_of_employment" name='start_of_employment' value="<?php echo $User["start_of_employment"] ?>">
        <label for="end_of_employment">Beschäftigungsende:  </label>
        <input type='date' name='end_of_employment' id="end_of_employment" value="<?php echo $User["end_of_employment"] ?>">
    </p><p>

        <input type=submit id=save_new class='no-print' name=submitStunden value='Eintragen' form='user_management'>
    </p>

</form>
</body>
</html>
