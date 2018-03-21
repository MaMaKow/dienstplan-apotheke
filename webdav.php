<?php

//Wir erstellen eine umfassende Icalendar Datei (ICS). Diese kann dann von Kalenderprogrammen aboniert werden.
require_once 'default.php';
if (!isset($_SESSION['user_employee_id'])) {
    require_once PDR_FILE_SYSTEM_APPLICATION_PATH . '/src/php/basic_access_authentication.php';
}
$tage = 30;
require_once PDR_FILE_SYSTEM_APPLICATION_PATH . '/db-lesen-mitarbeiter.php';
if (filter_has_var(INPUT_GET, 'datum')) {
    $datum = filter_input(INPUT_GET, 'datum', FILTER_SANITIZE_STRING);
} else {
    $datum = date('Y-m-d');
}
if (filter_has_var(INPUT_GET, 'employee_id')) {
    $employee_id = filter_input(INPUT_GET, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
} else {
    $employee_id = $_SESSION['user_employee_id'];
}
require 'db-lesen-woche-mitarbeiter.php';

require 'schreiben-ics.php';
$textICS = schreiben_ics($Dienstplan);
header('Content-type: text/Calendar');
header('Content-Disposition: attachment; filename="Calendar.ics"');
echo $textICS;
