<?php
require 'default.php';
//print_debug_variable($_POST);
require 'human-resource-management.php';
require 'db-lesen-mitarbeiter.php';
//print_debug_variable($Mitarbeiter);
require 'db-lesen-mandant.php';
if (isset($_POST['auswahl_mitarbeiter'])) {
    $auswahl_mitarbeiter = filter_input(INPUT_POST, 'auswahl_mitarbeiter', FILTER_VALIDATE_INT);
} elseif (isset($_GET['auswahl_mitarbeiter'])) {
    $auswahl_mitarbeiter = filter_input(INPUT_GET, 'auswahl_mitarbeiter', FILTER_VALIDATE_INT);
} elseif (isset($_COOKIE['auswahl_mitarbeiter'])) {
    $auswahl_mitarbeiter = filter_input(INPUT_COOKIE, 'auswahl_mitarbeiter', FILTER_VALIDATE_INT);
} else {
    $auswahl_mitarbeiter = 1;
}
if (isset($auswahl_mitarbeiter)) {
    create_cookie('auswahl_mitarbeiter', $auswahl_mitarbeiter, 30);
}

write_employee_data_to_database(); //$success = write_employee_data_to_database();
$Worker = read_employee_data_from_database($auswahl_mitarbeiter);

require 'head.php';
require 'navigation.php';

echo make_select_employee($auswahl_mitarbeiter);
?>
<div class="warningmsg">CAVE! <b>VK-Nummer</b> und <b>Beschäftigungsbeginn</b> sind <em>Primärschlüssel</em>. Bitte sorgfältig eingeben! Korrekturen sind aufwändig und benötigen direkten Zugang zur Datenbank.</div>
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
        <label for="holidays">Urlaubstage: </label>
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

        <input type=submit id=save_new class='no-print clear_left' name=submitStunden value='Eintragen' form='human_resource_management'>
    </p>

</form>
</body>
</html>
