<?php
if (filter_has_var(INPUT_GET, 'datum')) {
    $datum = filter_input(INPUT_GET, 'datum', FILTER_SANITIZE_STRING);
}
if (filter_has_var(INPUT_GET, 'mandant')) {
    $mandant = filter_input(INPUT_GET, 'mandant', FILTER_SANITIZE_NUMBER_INT);
}
if (filter_has_var(INPUT_GET, 'employee_id')) {
    $employee_id = filter_input(INPUT_GET, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
}
