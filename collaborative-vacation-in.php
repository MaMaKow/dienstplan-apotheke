<?php

require_once "default.php";
require_once "db-lesen-mitarbeiter.php";
require_once "db-lesen-abwesenheit.php";
if (isset($_POST['employee_id'])) {
    $employee_id = filter_input(INPUT_POST, employee_id, FILTER_SANITIZE_NUMBER_INT);
} elseif (isset($_COOKIE['employee_id'])) {
    $employee_id = filter_input(INPUT_COOKIE, employee_id, FILTER_SANITIZE_NUMBER_INT);
}
if (isset($employee_id)) {
    create_cookie("employee_id", $employee_id, 30);
}
require_once "src/php/collaborative-vacation.php";
?>
