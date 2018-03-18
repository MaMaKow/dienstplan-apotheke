<?php
require_once "default.php";
require_once "db-lesen-mitarbeiter.php";
require_once "db-lesen-abwesenheit.php";
$employee_id = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_employee_id']);
create_cookie('employee_id', $employee_id, 1);
require_once "src/php/collaborative-vacation.php";
require "head.php";
require 'src/php/pages/menu.php';
if (!$session->user_has_privilege('request_own_absence') and ! $session->user_has_privilege('create_absence')) {
    echo build_warning_messages("", ["Die notwendige Berechtigung zum Beantragen von Abwesenheiten fehlt. Bitte wenden Sie sich an einen Administrator."]);
    die();
}

handle_user_data_input();
echo "<div id='input_box_data_div'></div>";
echo build_datalist();
echo "<script>var employee_id = " . json_encode($employee_id, JSON_HEX_TAG) . ";</script>\n";
echo build_absence_year($year);
require 'contact-form.php';
?>
</BODY>
</HTML>
