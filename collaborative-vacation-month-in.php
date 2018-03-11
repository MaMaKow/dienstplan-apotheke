<?php
require_once "default.php";
require_once "db-lesen-mitarbeiter.php";
require_once "db-lesen-abwesenheit.php";
if (filter_has_var(INPUT_POST, 'employee_id')) {
    $employee_id = filter_input(INPUT_POST, employee_id, FILTER_SANITIZE_NUMBER_INT);
} elseif (filter_has_var(INPUT_COOKIE, 'employee_id')) {
    $employee_id = filter_input(INPUT_COOKIE, employee_id, FILTER_SANITIZE_NUMBER_INT);
}
if (isset($employee_id)) {
    create_cookie("employee_id", $employee_id, 30);
}
require_once "src/php/collaborative-vacation.php";
handle_user_data_input();
require "head.php";
require 'src/php/pages/menu.php';
if (!$session->user_has_privilege('request_own_absence') and ! $session->user_has_privilege('create_absence')) {
    echo build_warning_messages("", ["Die notwendige Berechtigung zum Beantragen von Abwesenheiten fehlt. Bitte wenden Sie sich an einen Administrator."]);
    die();
}

echo "<div id='input_box_data_div'></div>";
echo build_datalist();
echo "<script>var employee_id = " . json_encode($employee_id, JSON_HEX_TAG) . ";</script>\n";
echo build_absence_month($year, $month_number);
require 'contact-form.php';
?>
</BODY>
</HTML>
